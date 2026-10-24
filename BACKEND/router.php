<?php
/**
 * LA SOLUTION PARTNERSHIP - BUILT-IN PHP SERVER ROUTER
 * Used when running: php -S localhost:8000 router.php
 * Handles requests whether prefixed with /BACKEND/ or not.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Strip leading /BACKEND or /backend prefix if requested
if (preg_match('#^/backend(/.*)?$#i', $uri, $matches)) {
    $uri = $matches[1] ?? '/';
    if ($uri === '' || $uri === '/') {
        $uri = '/index.php';
    }
}

$filePath = __DIR__ . $uri;

// If requesting existing static or PHP file directly, let PHP server serve it
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    // If it's a PHP file, execute it
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        require $filePath;
        exit;
    }
    return false;
}

// If directory, look for index.php
if (is_dir($filePath) && file_exists(rtrim($filePath, '/') . '/index.php')) {
    require rtrim($filePath, '/') . '/index.php';
    exit;
}

// Return 404 JSON for API routes
http_response_code(404);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
echo json_encode([
    'success' => false,
    'message' => "File or endpoint not found: " . ($_SERVER['REQUEST_URI'] ?? ''),
    'hint'    => "If running from inside the BACKEND folder, use /api/... or run with router.php"
], JSON_UNESCAPED_SLASHES);
exit;
