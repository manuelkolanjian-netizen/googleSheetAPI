<?php

declare(strict_types=1);

/**
 * Client for interacting with Google Sheets API v4
 */
class GoogleSheetsClient
{
    private const API_BASE_URL = 'https://sheets.googleapis.com/v4/spreadsheets';
    private string $apiKey;

    /**
     * @param string $apiKey Google Sheets API key
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Fetch values from a Google Sheet
     *
     * @param string $spreadsheetId The spreadsheet ID
     * @param string $sheet The sheet name
     * @param string|null $range Optional range (e.g., "A1:C10"). If null, fetches entire sheet.
     * @return array The raw API response
     * @throws RuntimeException If the API request fails
     */
    public function getValues(string $spreadsheetId, string $sheet, ?string $range = null): array
    {
        // Construct the range parameter
        $fullRange = $sheet;
        if ($range !== null && $range !== '') {
            $fullRange .= '!' . $range;
        }

        // Build the API URL
        $url = sprintf(
            '%s/%s/values/%s?key=%s',
            self::API_BASE_URL,
            urlencode($spreadsheetId),
            urlencode($fullRange),
            urlencode($this->apiKey)
        );

        // Make the API request using cURL for better error handling
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors (network failures)
        if ($response === false || !empty($curlError)) {
            throw new RuntimeException('Network request failed: ' . ($curlError ?: 'Unknown error'));
        }

        // Handle HTTP errors from Google API
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API request failed with HTTP {$httpCode}";
            throw new RuntimeException($errorMessage, $httpCode);
        }

        // Parse JSON response
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response from API');
        }

        return $data;
    }

    /**
     * Validate and sanitize spreadsheet ID
     *
     * @param string $spreadsheetId
     * @return string Sanitized spreadsheet ID
     * @throws InvalidArgumentException If spreadsheet ID is invalid
     */
    public static function validateSpreadsheetId(string $spreadsheetId): string
    {
        $spreadsheetId = trim($spreadsheetId);
        if (empty($spreadsheetId)) {
            throw new InvalidArgumentException('spreadsheet_id is required and cannot be empty');
        }
        // Google Spreadsheet IDs are alphanumeric with dashes and underscores
        // Basic validation: non-empty string without spaces or special chars that could break URL
        if (preg_match('/[<>\'"&]/', $spreadsheetId)) {
            throw new InvalidArgumentException('spreadsheet_id contains invalid characters');
        }
        return $spreadsheetId;
    }

    /**
     * Validate and sanitize sheet name
     *
     * @param string $sheet
     * @return string Sanitized sheet name
     * @throws InvalidArgumentException If sheet name is invalid
     */
    public static function validateSheetName(string $sheet): string
    {
        $sheet = trim($sheet);
        if (empty($sheet)) {
            throw new InvalidArgumentException('sheet is required and cannot be empty');
        }
        // Sheet names can contain spaces, but we validate for obvious injection attempts
        if (preg_match('/[<>\'"&]/', $sheet)) {
            throw new InvalidArgumentException('sheet contains invalid characters');
        }
        return $sheet;
    }

    /**
     * Validate and sanitize range
     *
     * @param string|null $range
     * @return string|null Sanitized range or null
     */
    public static function validateRange(?string $range): ?string
    {
        if ($range === null || $range === '') {
            return null;
        }
        $range = trim($range);
        // Basic validation for range (e.g., "A1:C10" or "A:Z")
        // Allow alphanumeric, colons, and basic range notation
        if (preg_match('/[<>\'"&]/', $range)) {
            throw new InvalidArgumentException('range contains invalid characters');
        }
        return $range;
    }
}
