<?php
/**
 * LA SOLUTION PARTNERSHIP - JSON RESPONSE UTILITY
 * Standardizes API responses across all endpoints.
 */

require_once __DIR__ . '/../../config/config.php';

class Response {

    /**
     * Send structured JSON response and terminate execution
     *
     * @param bool $success Success status flag
     * @param string $message User-friendly message
     * @param mixed $data Data payload
     * @param int $statusCode HTTP status code
     * @param array $extra Additional top-level keys (e.g. pagination, meta)
     */
    public static function json(bool $success, string $message = '', mixed $data = null, int $statusCode = 200, array $extra = []): void {
        setupCorsHeaders();
        http_response_code($statusCode);

        $payload = array_merge([
            'success'   => $success,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => date('c')
        ], $extra);

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * HTTP 200 OK
     */
    public static function success(string $message = 'Operation successful', mixed $data = null, array $extra = []): void {
        self::json(true, $message, $data, 200, $extra);
    }

    /**
     * HTTP 201 Created
     */
    public static function created(string $message = 'Resource created successfully', mixed $data = null, array $extra = []): void {
        self::json(true, $message, $data, 201, $extra);
    }

    /**
     * HTTP 400 Bad Request / Validation Failure
     */
    public static function error(string $message = 'Bad request', mixed $errors = null, int $statusCode = 400): void {
        self::json(false, $message, null, $statusCode, ['errors' => $errors]);
    }

    /**
     * HTTP 401 Unauthorized
     */
    public static function unauthorized(string $message = 'Unauthorized access. Authentication required.'): void {
        self::json(false, $message, null, 401);
    }

    /**
     * HTTP 403 Forbidden
     */
    public static function forbidden(string $message = 'Forbidden. You do not have permission.'): void {
        self::json(false, $message, null, 403);
    }

    /**
     * HTTP 404 Not Found
     */
    public static function notFound(string $message = 'Resource not found'): void {
        self::json(false, $message, null, 404);
    }

    /**
     * HTTP 500 Internal Server Error
     */
    public static function serverError(string $message = 'An unexpected internal server error occurred'): void {
        self::json(false, $message, null, 500);
    }
}
