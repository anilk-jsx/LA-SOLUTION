<?php
/**
 * LA SOLUTION PARTNERSHIP - ADMIN LOGIN API
 * POST /api/auth/login.php
 * Payload: { username: "admin", password: "..." }
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Utils/Validator.php';
require_once __DIR__ . '/../../src/Models/AdminUser.php';

setupCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Method not allowed. Use POST.", null, 405);
}

$data = Validator::getRequestData();
$username = trim($data['username'] ?? $data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($password)) {
    Response::error("Please provide both username and password.", null, 422);
}

try {
    $user = AdminUser::authenticate($username, $password);
    if (!$user) {
        Response::error("Invalid credentials or account is inactive.", null, 401);
    }

    Response::success("Welcome back, {$user['full_name']}!", $user);
} catch (Throwable $e) {
    Response::serverError("Authentication failed: " . $e->getMessage());
}
