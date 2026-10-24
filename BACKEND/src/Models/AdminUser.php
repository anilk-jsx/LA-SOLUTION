<?php
/**
 * LA SOLUTION PARTNERSHIP - ADMIN USER MODEL
 * Administrator authentication, password hashing, and token session management.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Utils/Auth.php';

class AdminUser {

    /**
     * Authenticate an administrator with username/email and password
     */
    public static function authenticate(string $usernameOrEmail, string $password): ?array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash, full_name, role, status
            FROM admin_users 
            WHERE (username = :username OR email = :email) AND status = 'active'
            LIMIT 1
        ");
        $clean = trim($usernameOrEmail);
        $stmt->execute([
            ':username' => $clean,
            ':email'    => $clean
        ]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Generate and store new auth token
        $token = Auth::generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + (TOKEN_EXPIRY_HOURS * 3600));

        $updateStmt = $pdo->prepare("
            UPDATE admin_users 
            SET auth_token = :token, token_expires_at = :expires, last_login_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':token'   => $token,
            ':expires' => $expiresAt,
            ':id'      => $user['id']
        ]);

        // Don't expose password hash
        unset($user['password_hash']);
        $user['token'] = $token;
        $user['token_expires_at'] = $expiresAt;

        // Start session
        Auth::initSession();
        $_SESSION['admin_user'] = $user;

        return $user;
    }

    /**
     * Invalidate user token / logout
     */
    public static function logout(?string $token = null): void {
        Auth::initSession();
        if (isset($_SESSION['admin_user'])) {
            unset($_SESSION['admin_user']);
        }

        if ($token) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("UPDATE admin_users SET auth_token = NULL, token_expires_at = NULL WHERE auth_token = :t");
                $stmt->execute([':t' => $token]);
            } catch (Throwable $e) {
                // Ignore
            }
        }
    }
}
