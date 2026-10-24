<?php
/**
 * LA SOLUTION PARTNERSHIP - BACKEND CONFIGURATION
 * Centralized settings for Database, CORS, Environment, and Security.
 */

// Set Timezone (Odisha, India - IST)
date_default_timezone_set('Asia/Kolkata');

// Environment Detection ('development' or 'production')
$isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1', '::1'])
               || (php_sapi_name() === 'cli');

define('APP_ENV', $isLocalhost ? 'development' : 'production');
define('APP_NAME', 'LA Solution Partnership Backend API');
define('APP_VERSION', '1.0.0');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0'); // Return JSON errors rather than breaking response
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Database Credentials
// Auto-switches or overrides via environment variables if present
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: ($isLocalhost ? 'la_solution_db' : 'u705414379_las_admin_db'));
define('DB_USER', getenv('DB_USER') ?: ($isLocalhost ? 'root' : 'u705414379_las_admin'));
define('DB_PASS', getenv('DB_PASS') ?: ($isLocalhost ? '' : '/W7Tr|#Z9k'));
define('DB_CHARSET', 'utf8mb4');

// Authentication / Token Secret
define('AUTH_SECRET_KEY', getenv('AUTH_SECRET_KEY') ?: 'la_solution_super_secret_jwt_key_2026!#$');
define('TOKEN_EXPIRY_HOURS', 72); // 3 days

// CORS & HTTP Headers Configuration Helper
function setupCorsHeaders() {
    $allowedOrigins = [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:8000',
        'https://lasolution.in',
        'https://admin.lasolution.in'
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    if (in_array($origin, $allowedOrigins) || $origin === '*') {
        header("Access-Control-Allow-Origin: " . ($origin === '*' ? '*' : $origin));
    } else {
        header("Access-Control-Allow-Origin: *");
    }

    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400"); // 24 hours preflight cache
    header("Content-Type: application/json; charset=UTF-8");

    // Respond immediately to preflight OPTIONS requests
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
