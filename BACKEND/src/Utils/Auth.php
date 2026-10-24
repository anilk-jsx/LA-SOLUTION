<?php
/**
 * LA SOLUTION PARTNERSHIP - AUTHENTICATION MANAGER
 * Session and Bearer Token verification for Admin Dashboard operations.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Response.php';

class Auth {

    /**
     * Start session if not started
     */
    public static function initSession(): void {
        if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Extract token from Authorization header or request
     */
    public static function getBearerToken(): ?string {
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }

        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if ($authHeader && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Check custom header or GET/POST token parameter
        return $headers['X-Auth-Token'] ?? $headers['x-auth-token'] ?? $_GET['token'] ?? $_POST['token'] ?? null;
    }

    /**
     * Get current authenticated user (or null if unauthenticated)
     */
    public static function user(): ?array {
        self::initSession();

        // 1. Check Session
        if (!empty($_SESSION['admin_user'])) {
            return $_SESSION['admin_user'];
        }

        // 2. Check Token
        $token = self::getBearerToken();
        if (!$token) {
            return null;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT id, username, email, full_name, role, status, token_expires_at
                FROM admin_users
                WHERE auth_token = :token AND status = 'active'
                LIMIT 1
            ");
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();

            if ($user) {
                // Check if token expired
                if ($user['token_expires_at'] && strtotime($user['token_expires_at']) < time()) {
                    return null;
                }
                return $user;
            }
        } catch (Throwable $e) {
            error_log("Auth verification error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Guard route - aborts with 401 if unauthenticated
     */
    public static function requireAuth(): array {
        $user = self::user();
        if (!$user) {
            Response::unauthorized("Administrative authentication required. Please login.");
        }
        return $user;
    }

    /**
     * Generate secure token
     */
    public static function generateToken(): string {
        return bin2hex(random_bytes(32));
    }
}
