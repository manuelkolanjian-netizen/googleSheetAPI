<?php

declare(strict_types=1);

/**
 * Response utility class for handling HTTP responses
 */
class Response
{
    /**
     * Send a JSON response with appropriate headers
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send an error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function error(string $message, int $statusCode = 500): void
    {
        self::json(['error' => $message], $statusCode);
    }
}
