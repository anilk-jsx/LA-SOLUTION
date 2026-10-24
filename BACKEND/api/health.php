<?php
/**
 * LA SOLUTION PARTNERSHIP - HEALTH CHECK ENDPOINT
 * Diagnostic verification of database connectivity, tables, and server environment.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Utils/Response.php';

setupCorsHeaders();

try {
    $pdo = Database::getConnection();
    $dbConnected = true;
    $dbVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

    // Check table counts for inquiries and admin_users
    $tables = ['inquiries', 'admin_users'];
    $tableCounts = [];
    foreach ($tables as $t) {
        try {
            $tableCounts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        } catch (Exception $e) {
            $tableCounts[$t] = 'Not created (' . $e->getMessage() . ')';
        }
    }

    Response::success("LA Solution Backend API is healthy and operational.", [
        'status'         => 'UP',
        'app_name'       => APP_NAME,
        'version'        => APP_VERSION,
        'environment'    => APP_ENV,
        'php_version'    => PHP_VERSION,
        'database'       => [
            'connected' => true,
            'host'      => DB_HOST,
            'name'      => DB_NAME,
            'version'   => $dbVersion,
            'tables'    => $tableCounts
        ],
        'server_time'    => date('Y-m-d H:i:s T')
    ]);

} catch (Throwable $e) {
    Response::json(false, "Health Check Failed: " . $e->getMessage(), [
        'status'      => 'DOWN',
        'error'       => $e->getMessage(),
        'php_version' => PHP_VERSION,
        'server_time' => date('Y-m-d H:i:s T')
    ], 500);
}
