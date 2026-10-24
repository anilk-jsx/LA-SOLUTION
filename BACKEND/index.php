<?php
/**
 * LA SOLUTION PARTNERSHIP - BACKEND API PORTAL
 * Interactive API Documentation and Status Hub.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$dbStatus = 'Connecting...';
$dbClass = 'badge-danger';
$leadsCount = 0;
$verticalsCount = 8;

try {
    $pdo = Database::getConnection();
    $dbStatus = 'Connected (' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . ')';
    $dbClass = 'badge-success';
    $leadsCount = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
} catch (Throwable $e) {
    $dbStatus = 'Connection Error: ' . $e->getMessage();
    $dbClass = 'badge-danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LA Solution Backend API Service | Overview & Documentation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --surface: #ffffff;
            --bg: #f8fbfe;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #16a34a;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); line-height: 1.6; padding: 40px 20px; }
        .container { max-width: 1040px; margin: 0 auto; }
        .header-card { background: var(--surface); border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(2, 132, 199, 0.08); border: 1px solid var(--border); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: gap; gap: 20px; }
        .brand-box h1 { font-family: 'Outfit', sans-serif; font-size: 1.8rem; color: var(--text-main); font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .brand-box h1 i { color: var(--primary); }
        .brand-box p { color: var(--text-muted); font-size: 0.95rem; margin-top: 4px; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 9999px; font-size: 0.82rem; font-weight: 700; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 24px; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04); }
        .stat-label { font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 700; }
        .stat-value { font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 4px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 28px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04); margin-bottom: 24px; }
        .card-title { font-family: 'Outfit', sans-serif; font-size: 1.3rem; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; color: var(--text-main); }
        .endpoint-table { width: 100%; border-collapse: collapse; font-size: 0.92rem; }
        .endpoint-table th { background: #f1f5f9; text-align: left; padding: 12px 14px; font-weight: 700; color: #334155; border-bottom: 1px solid var(--border); }
        .endpoint-table td { padding: 12px 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .method { display: inline-block; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; font-family: monospace; }
        .method-get { background: #e0f2fe; color: #0369a1; }
        .method-post { background: #dcfce7; color: #15803d; }
        .method-put { background: #fef3c7; color: #b45309; }
        .method-delete { background: #fee2e2; color: #b91c1c; }
        .code-link { font-family: monospace; color: var(--primary); font-weight: 600; text-decoration: none; }
        .code-link:hover { text-decoration: underline; }
        .quick-actions { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid var(--border); }
        .btn-secondary:hover { background: #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Top Status Card -->
        <div class="header-card">
            <div class="brand-box">
                <h1><i class="fa-solid fa-umbrella"></i> LA SOLUTION BACKEND API</h1>
                <p>High-performance REST API services for Landing Page inquiries &amp; Admin CRM Leads.</p>
            </div>
            <div>
                <span class="badge <?= $dbClass ?>"><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> <?= htmlspecialchars($dbStatus) ?></span>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid">
            <div class="stat-card">
                <div class="stat-label">Total Leads in Database</div>
                <div class="stat-value"><?= $leadsCount ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Ecosystem Verticals</div>
                <div class="stat-value"><?= $verticalsCount ?> / 8</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Environment</div>
                <div class="stat-value" style="font-size: 1.3rem; text-transform: uppercase; color: #334155;"><?= APP_ENV ?></div>
            </div>
        </div>

        <!-- API Endpoints Directory -->
        <div class="card">
            <h2 class="card-title"><i class="fa-solid fa-network-wired text-primary"></i> RESTful API Endpoints Directory</h2>
            <div style="overflow-x: auto;">
                <table class="endpoint-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint URL</th>
                            <th>Description</th>
                            <th>Target Consumer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><a href="api/health.php" target="_blank" class="code-link">/api/health.php</a></td>
                            <td>System health check, database status &amp; diagnostics</td>
                            <td>Monitoring / All</td>
                        </tr>
                        <tr>
                            <td><span class="method method-post">POST</span></td>
                            <td><span class="code-link">/api/inquiries/index.php</span></td>
                            <td>Submit partnership inquiry (Lead capture)</td>
                            <td>Landing Page / Admin</td>
                        </tr>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><a href="api/inquiries/index.php" target="_blank" class="code-link">/api/inquiries/index.php</a></td>
                            <td>List inquiries with search, status &amp; vertical filters</td>
                            <td>Admin CRM Tab</td>
                        </tr>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><span class="code-link">/api/inquiries/detail.php?id={id}</span></td>
                            <td>Fetch single inquiry details</td>
                            <td>Admin CRM Modal</td>
                        </tr>
                        <tr>
                            <td><span class="method method-put">PUT</span></td>
                            <td><span class="code-link">/api/inquiries/detail.php?id={id}</span></td>
                            <td>Update inquiry information, status, priority, notes</td>
                            <td>Admin CRM Edit</td>
                        </tr>
                        <tr>
                            <td><span class="method method-delete">DELETE</span></td>
                            <td><span class="code-link">/api/inquiries/detail.php?id={id}</span></td>
                            <td>Permanently delete an inquiry record</td>
                            <td>Admin CRM Table</td>
                        </tr>
                        <tr>
                            <td><span class="method method-post">POST</span></td>
                            <td><span class="code-link">/api/inquiries/status.php</span></td>
                            <td>Quick status toggle (New, Contacted, In Discussion, MOU Signed)</td>
                            <td>Admin CRM Actions</td>
                        </tr>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><a href="api/inquiries/export.php" target="_blank" class="code-link">/api/inquiries/export.php</a></td>
                            <td>Stream dynamic CSV file export of leads database</td>
                            <td>Admin CRM Toolbar</td>
                        </tr>
                        <tr>
                            <td><span class="method method-get">GET</span></td>
                            <td><a href="api/dashboard/stats.php" target="_blank" class="code-link">/api/dashboard/stats.php</a></td>
                            <td>Real-time KPIs, Chart.js trends, vertical shares &amp; recent leads</td>
                            <td>Admin Overview Tab</td>
                        </tr>
                        <tr>
                            <td><span class="method method-post">POST</span></td>
                            <td><span class="code-link">/api/auth/login.php</span></td>
                            <td>Authenticate administrator &amp; receive bearer session token</td>
                            <td>Admin Security</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="quick-actions">
                <a href="database/migrate.php" class="btn btn-secondary"><i class="fa-solid fa-database"></i> Run Database Migration</a>
                <a href="api/health.php" target="_blank" class="btn btn-secondary"><i class="fa-solid fa-heart-pulse"></i> Verify Health API</a>
                <a href="../ADMIN-PANEL/admindashboard.html" class="btn btn-primary"><i class="fa-solid fa-gauge"></i> Open Admin Panel</a>
                <a href="../LANDING-PAGE/index.html" class="btn btn-secondary"><i class="fa-solid fa-globe"></i> Open Landing Page</a>
            </div>
        </div>
    </div>
</body>
</html>
