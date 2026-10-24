<?php
/**
 * LA SOLUTION PARTNERSHIP - CURRENT USER PROFILE API
 * GET /api/auth/me.php
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Utils/Auth.php';

setupCorsHeaders();

$user = Auth::requireAuth();
Response::success("Authenticated user profile.", $user);
