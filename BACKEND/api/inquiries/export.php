<?php
/**
 * LA SOLUTION PARTNERSHIP - INQUIRIES CSV EXPORT API
 * GET /api/inquiries/export.php -> Streams CSV download
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/Inquiry.php';

// Check if CORS is needed, but this is a direct browser file download
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$pdo = Database::getConnection();

$filters = [
    'search'   => $_GET['search'] ?? '',
    'vertical' => $_GET['vertical'] ?? 'ALL',
    'status'   => $_GET['status'] ?? 'ALL',
    'type'     => $_GET['type'] ?? 'ALL',
    'priority' => $_GET['priority'] ?? 'ALL'
];

// Fetch all matching records without pagination
$conditions = [];
$params = [];

if (!empty($filters['search'])) {
    $search = '%' . trim($filters['search']) . '%';
    $conditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search OR location LIKE :search OR vertical LIKE :search OR lead_code LIKE :search)";
    $params[':search'] = $search;
}
if (!empty($filters['vertical']) && $filters['vertical'] !== 'ALL') {
    $conditions[] = "vertical LIKE :vertical";
    $params[':vertical'] = '%' . trim($filters['vertical']) . '%';
}
if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
    $conditions[] = "status = :status";
    $params[':status'] = trim($filters['status']);
}
if (!empty($filters['type']) && $filters['type'] !== 'ALL') {
    $conditions[] = "interest_type LIKE :type";
    $params[':type'] = '%' . trim($filters['type']) . '%';
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
$sql = "SELECT * FROM inquiries {$whereClause} ORDER BY created_at DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$filename = "LA_Solution_Ecosystem_Leads_" . date('Y-m-d_His') . ".csv";

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

// Write UTF-8 BOM for Microsoft Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Headers matching Admin Dashboard CSV specifications
fputcsv($output, [
    'ID',
    'Name',
    'Phone',
    'Email',
    'Nature_of_Interest',
    'Vertical',
    'Location',
    'Date',
    'Status',
    'Priority',
    'Message',
    'Notes'
]);

foreach ($rows as $r) {
    fputcsv($output, [
        $r['lead_code'],
        $r['name'],
        $r['phone'],
        $r['email'],
        $r['interest_type'],
        $r['vertical'],
        $r['location'],
        $r['created_at'],
        $r['status'],
        $r['priority'],
        $r['message'],
        $r['notes']
    ]);
}

fclose($output);
exit;
