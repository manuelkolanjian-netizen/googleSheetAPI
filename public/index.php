<?php

declare(strict_types=1);

// Load dependencies
require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/GoogleSheetsClient.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed. Only GET requests are supported.', 405);
}

try {
    // 1. Validate spreadsheet_id and sheet (HTTP 400 on failure)
    $spreadsheetId = $_GET['spreadsheet_id'] ?? null;
    $sheet = $_GET['sheet'] ?? null;

    if ($spreadsheetId === null) {
        Response::error('Missing required parameter: spreadsheet_id', 400);
    }

    if ($sheet === null) {
        Response::error('Missing required parameter: sheet', 400);
    }

    // Validate and sanitize inputs
    try {
        $spreadsheetId = GoogleSheetsClient::validateSpreadsheetId($spreadsheetId);
        $sheet = GoogleSheetsClient::validateSheetName($sheet);
        $range = GoogleSheetsClient::validateRange($_GET['range'] ?? null);
    } catch (InvalidArgumentException $e) {
        Response::error($e->getMessage(), 400);
    }

    // 2. Read GOOGLE_SHEETS_API_KEY from environment (HTTP 500 if missing)
    $apiKey = $_ENV['GOOGLE_SHEETS_API_KEY'] ?? getenv('GOOGLE_SHEETS_API_KEY');
    if (empty($apiKey)) {
        Response::error('GOOGLE_SHEETS_API_KEY environment variable is not set.', 500);
    }

    // 3. Call GoogleSheetsClient
    $client = new GoogleSheetsClient($apiKey);
    $data = $client->getValues($spreadsheetId, $sheet, $range);

    // 4. Return Google Sheets API response (HTTP 200)
    Response::json($data, 200);

} catch (RuntimeException $e) {
    // Handle API errors and network failures
    $httpCode = 502; // Default to Bad Gateway for API errors
    
    // Map specific error types to appropriate status codes
    if (str_contains($e->getMessage(), 'Network request failed')) {
        $httpCode = 500; // Network failures are Internal Server Error
    } elseif ($e->getCode() >= 400 && $e->getCode() < 600) {
        $httpCode = 502; // Google API errors become Bad Gateway from our API
    }
    
    Response::error('Failed to fetch data from Google Sheets API: ' . $e->getMessage(), $httpCode);
    
} catch (Throwable $e) {
    // Catch any other unexpected errors
    error_log('Unexpected error: ' . $e->getMessage() . ' | Type: ' . get_class($e) . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
    // Temporarily expose error for debugging - remove in production
    Response::error('An internal server error occurred: ' . $e->getMessage() . ' (' . get_class($e) . ')', 500);
}
