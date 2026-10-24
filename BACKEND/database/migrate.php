<?php
/**
 * LA SOLUTION PARTNERSHIP - DATABASE MIGRATION SCRIPT
 * Sets up the inquiries and admin_users tables and seeds baseline data.
 * Can be run via CLI or through web browser.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$isCli = (php_sapi_name() === 'cli');

function outputMsg(string $msg, string $type = 'info'): void {
    global $isCli;
    if ($isCli) {
        $prefix = match($type) {
            'success' => "[OK] ",
            'error'   => "[ERROR] ",
            'warning' => "[WARN] ",
            default   => "[INFO] "
        };
        echo $prefix . $msg . PHP_EOL;
    } else {
        $colors = [
            'success' => '#16a34a',
            'error'   => '#dc2626',
            'warning' => '#d97706',
            'info'    => '#0284c7'
        ];
        $color = $colors[$type] ?? '#334155';
        echo "<div style='font-family: monospace; padding: 6px 12px; margin: 4px 0; border-left: 4px solid {$color}; background: #f8fafc; color: #0f172a;'>";
        echo "<strong>[" . strtoupper($type) . "]</strong> " . htmlspecialchars($msg);
        echo "</div>";
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>LA Solution - Database Migration</title></head><body style='font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px;'>";
    echo "<h2 style='color: #0284c7;'>LA Solution Database Migration Runner</h2><hr style='border: 1px solid #e2e8f0; margin-bottom: 20px;'>";
}

try {
    outputMsg("Starting database migration process...", 'info');
    outputMsg("Host: " . DB_HOST . ", Database: " . DB_NAME, 'info');

    // 1. Ensure Database Exists
    $rootPdo = Database::getRootConnection();
    $dbName = DB_NAME;
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    outputMsg("Database `{$dbName}` verified/created.", 'success');

    // 2. Connect to the targeted Database
    $pdo = Database::getConnection();

    // 3. Drop unwanted deprecated tables (verticals, activity_logs)
    $pdo->exec("DROP TABLE IF EXISTS `activity_logs`;");
    $pdo->exec("DROP TABLE IF EXISTS `verticals`;");
    outputMsg("Cleaned up unwanted tables (verticals, activity_logs removed).", 'info');

    // 4. Execute Schema for Inquiries and Admin Users
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    if ($schemaSql) {
        $statements = array_filter(array_map('trim', explode(';', $schemaSql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
            }
        }
        outputMsg("Applied core schema (inquiries, admin_users).", 'success');
    }

    // 5. Ensure status enum includes all required values
    try {
        $pdo->exec("ALTER TABLE `inquiries` MODIFY COLUMN `status` ENUM('New', 'Contacted', 'In Discussion', 'MOU Signed', 'Closed', 'Archived') NOT NULL DEFAULT 'New';");
    } catch (Exception $e) {
        // Ignored if already up to date
    }

    // 6. Execute Seed Data
    $seedSql = file_get_contents(__DIR__ . '/seed.sql');
    if ($seedSql) {
        $seedStatements = array_filter(array_map('trim', explode(';', $seedSql)));
        foreach ($seedStatements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
            }
        }
        outputMsg("Populated seed data (Admin Account, Baseline Inquiries).", 'success');
    }

    // 7. Verification
    $leadsCount = $pdo->query("SELECT COUNT(*) FROM `inquiries`")->fetchColumn();
    $adminCount = $pdo->query("SELECT COUNT(*) FROM `admin_users`")->fetchColumn();

    outputMsg("Verification: {$leadsCount} Inquiries, {$adminCount} Admin Users in database.", 'success');
    outputMsg("Migration completed with zero errors!", 'success');

} catch (Throwable $e) {
    outputMsg("Migration Failed: " . $e->getMessage(), 'error');
}

if (!$isCli) {
    echo "<br><p><a href='../api/health.php' style='display: inline-block; padding: 10px 18px; background: #0284c7; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;'>Verify Health Check API &rarr;</a></p>";
    echo "</body></html>";
}
