<?php
/**
 * LA SOLUTION PARTNERSHIP - ADMIN LOGOUT API
 * POST /api/auth/logout.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Utils/Auth.php';
require_once __DIR__ . '/../../src/Models/AdminUser.php';

setupCorsHeaders();

$token = Auth::getBearerToken();
AdminUser::logout($token);

Response::success("Logged out successfully.");
