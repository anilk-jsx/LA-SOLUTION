<?php
/**
 * LA SOLUTION - ADMIN CREDENTIAL GENERATOR & UPDATER
 * Generates frontend salted SHA-256 hashes and backend database bcrypt hash.
 * 
 * Usage:
 *   php BACKEND/change_credentials.php <username> <email> <password> [--apply]
 * 
 * Example:
 *   php BACKEND/change_credentials.php superadmin executive@lasolution.in MySecurePass!2026
 */

$pepper = 'LA_SOL_SYS_PEPPER_98f4e2';

$username = $argv[1] ?? null;
$email    = $argv[2] ?? null;
$password = $argv[3] ?? null;
$apply    = in_array('--apply', $argv);

if (!$username || !$email || !$password) {
    echo "========================================================\n";
    echo "   LA SOLUTION - CREDENTIAL GENERATOR HELPER\n";
    echo "========================================================\n\n";
    echo "Usage:\n";
    echo "  & \"C:\\xampp\\php\\php.exe\" BACKEND/change_credentials.php <username> <email> <password> [--apply]\n\n";
    echo "Example:\n";
    echo "  & \"C:\\xampp\\php\\php.exe\" BACKEND/change_credentials.php admin admin@lasolution.in NewPassword@2026\n\n";
    echo "Flags:\n";
    echo "  --apply : Automatically updates ADMIN-PANEL/admindashboard.html with new hashes.\n";
    exit(1);
}

$u = strtolower(trim($username));
$m = strtolower(trim($email));

// 1. Frontend Cryptographic SHA-256 Hashes
$hUser = hash('sha256', "{$u}:{$password}:{$pepper}");
$hMail = hash('sha256', "{$m}:{$password}:{$pepper}");

// 2. Backend Database Bcrypt Hash
$bcryptHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "\n========================================================\n";
echo "   CREDENTIALS GENERATED SUCCESSFULLY\n";
echo "========================================================\n\n";

echo "[NEW CREDENTIALS]\n";
echo "Username : {$username}\n";
echo "Email    : {$email}\n";
echo "Password : {$password}\n\n";

echo "--------------------------------------------------------\n";
echo "1. FRONTEND HASHES (for ADMIN-PANEL/admindashboard.html)\n";
echo "--------------------------------------------------------\n";
echo "Replace lines ~2417-2418 with:\n\n";
echo "  const _H_USER = '{$hUser}';\n";
echo "  const _H_MAIL = '{$hMail}';\n\n";

echo "--------------------------------------------------------\n";
echo "2. BACKEND DATABASE SQL (run in phpMyAdmin or MySQL)\n";
echo "--------------------------------------------------------\n";
echo "UPDATE `admin_users` \n";
echo "SET `username` = '{$username}', \n";
echo "    `email` = '{$email}', \n";
echo "    `password_hash` = '{$bcryptHash}' \n";
echo "WHERE `id` = 1;\n\n";

if ($apply) {
    $dashPath = dirname(__DIR__) . '/ADMIN-PANEL/admindashboard.html';
    if (file_exists($dashPath)) {
        $content = file_get_contents($dashPath);
        $content = preg_replace("/const _H_USER = '[^']+';/", "const _H_USER = '{$hUser}';", $content);
        $content = preg_replace("/const _H_MAIL = '[^']+';/", "const _H_MAIL = '{$hMail}';", $content);
        file_put_contents($dashPath, $content);
        echo ">>> [SUCCESS] Automatically updated ADMIN-PANEL/admindashboard.html with new hashes!\n";
    }
}
echo "========================================================\n";

