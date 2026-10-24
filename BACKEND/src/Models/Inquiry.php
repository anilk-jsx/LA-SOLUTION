<?php
/**
 * LA SOLUTION PARTNERSHIP - INQUIRY MODEL
 * Handles CRM inquiries, lead generation, aggregation, filtering, and export.
 */

require_once __DIR__ . '/../../config/database.php';

class Inquiry {

    /**
     * Generate next incremental lead code (e.g. LEAD-107)
     */
    public static function generateLeadCode(): string {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT lead_code FROM inquiries ORDER BY id DESC LIMIT 1");
        $lastCode = $stmt->fetchColumn();

        if ($lastCode && preg_match('/LEAD-(\d+)/i', $lastCode, $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $count = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
            $nextNum = 100 + $count + 1;
        }

        return "LEAD-" . $nextNum;
    }

    /**
     * Create a new inquiry (From Landing Page or Admin Modal)
     */
    public static function create(array $data): array {
        $pdo = Database::getConnection();

        $leadCode = !empty($data['lead_code']) ? trim($data['lead_code']) : self::generateLeadCode();
        $name = trim($data['name'] ?? $data['fullName'] ?? '');
        $phone = trim($data['phone'] ?? $data['phoneNumber'] ?? '');
        $email = trim($data['email'] ?? $data['emailAddress'] ?? '');
        $interestType = trim($data['interest_type'] ?? $data['partnershipType'] ?? $data['type'] ?? 'General Inquiry');
        $vertical = trim($data['vertical'] ?? $data['verticalInterest'] ?? 'Entire Umbrella Ecosystem');
        $location = trim($data['location'] ?? $data['cityState'] ?? 'Not specified');
        $message = trim($data['message'] ?? '');
        $status = !empty($data['status']) ? trim($data['status']) : 'New';
        $priority = !empty($data['priority']) ? trim($data['priority']) : 'Medium';
        $notes = trim($data['notes'] ?? '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        $sql = "INSERT INTO inquiries 
                (lead_code, name, phone, email, interest_type, vertical, location, message, status, priority, notes, ip_address, user_agent, created_at)
                VALUES 
                (:lead_code, :name, :phone, :email, :interest_type, :vertical, :location, :message, :status, :priority, :notes, :ip, :ua, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':lead_code'     => $leadCode,
            ':name'          => $name,
            ':phone'         => $phone,
            ':email'         => $email,
            ':interest_type' => $interestType,
            ':vertical'      => $vertical,
            ':location'      => $location ?: 'Not specified',
            ':message'       => $message ?: null,
            ':status'        => $status,
            ':priority'      => $priority,
            ':notes'         => $notes ?: null,
            ':ip'            => $ip,
            ':ua'            => $ua
        ]);

        $newId = (int)$pdo->lastInsertId();
        return self::getById($newId);
    }

    /**
     * Get single inquiry by numeric ID or Lead Code
     */
    public static function getById(int|string $id): ?array {
        $pdo = Database::getConnection();

        if (is_numeric($id)) {
            $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE lead_code = :code LIMIT 1");
            $stmt->execute([':code' => (string)$id]);
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get inquiries with filtering, search, and pagination
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 8): array {
        $pdo = Database::getConnection();

        $conditions = [];
        $params = [];

        // Search Keyword
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $conditions[] = "(
                name LIKE :search 
                OR email LIKE :search 
                OR phone LIKE :search 
                OR location LIKE :search 
                OR vertical LIKE :search 
                OR lead_code LIKE :search 
                OR notes LIKE :search
            )";
            $params[':search'] = $search;
        }

        // Vertical Filter
        if (!empty($filters['vertical']) && $filters['vertical'] !== 'ALL') {
            $conditions[] = "vertical LIKE :vertical";
            $params[':vertical'] = '%' . trim($filters['vertical']) . '%';
        }

        // Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $conditions[] = "status = :status";
            $params[':status'] = trim($filters['status']);
        }

        // Partnership Type Filter
        if (!empty($filters['type']) && $filters['type'] !== 'ALL') {
            $conditions[] = "interest_type LIKE :type";
            $params[':type'] = '%' . trim($filters['type']) . '%';
        }

        // Priority Filter
        if (!empty($filters['priority']) && $filters['priority'] !== 'ALL') {
            $conditions[] = "priority = :priority";
            $params[':priority'] = trim($filters['priority']);
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        // Total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM inquiries {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Pagination
        $perPage = max(1, min(100, $perPage));
        $totalPages = (int)ceil($total / $perPage);
        $page = max(1, min($totalPages ?: 1, $page));
        $offset = ($page - 1) * $perPage;

        // Query Items
        $sql = "SELECT * FROM inquiries {$whereClause} ORDER BY created_at DESC, id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'pagination' => [
                'total'        => $total,
                'page'         => $page,
                'per_page'     => $perPage,
                'total_pages'  => $totalPages,
                'has_previous' => $page > 1,
                'has_next'     => $page < $totalPages
            ]
        ];
    }

    /**
     * Update an inquiry
     */
    public static function update(int|string $id, array $data): ?array {
        $existing = self::getById($id);
        if (!$existing) {
            return null;
        }

        $pdo = Database::getConnection();

        $fields = [];
        $params = [':id' => $existing['id']];

        $allowedFields = [
            'name', 'phone', 'email', 'interest_type', 'vertical',
            'location', 'message', 'status', 'priority', 'notes'
        ];

        if (isset($data['fullName'])) $data['name'] = $data['fullName'];
        if (isset($data['phoneNumber'])) $data['phone'] = $data['phoneNumber'];
        if (isset($data['emailAddress'])) $data['email'] = $data['emailAddress'];
        if (isset($data['partnershipType'])) $data['interest_type'] = $data['partnershipType'];
        if (isset($data['verticalInterest'])) $data['vertical'] = $data['verticalInterest'];
        if (isset($data['cityState'])) $data['location'] = $data['cityState'];
        if (isset($data['type'])) $data['interest_type'] = $data['type'];

        foreach ($allowedFields as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`{$f}` = :{$f}";
                $params[":{$f}"] = is_string($data[$f]) ? trim($data[$f]) : $data[$f];
            }
        }

        if (empty($fields)) {
            return $existing;
        }

        $sql = "UPDATE inquiries SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::getById($existing['id']);
    }

    /**
     * Quick status update
     */
    public static function updateStatus(int|string $id, string $status, ?string $notes = null): ?array {
        $existing = self::getById($id);
        if (!$existing) {
            return null;
        }

        $pdo = Database::getConnection();
        $sql = "UPDATE inquiries SET status = :status" . ($notes !== null ? ", notes = :notes" : "") . " WHERE id = :id";
        $params = [
            ':status' => $status,
            ':id'     => $existing['id']
        ];
        if ($notes !== null) {
            $params[':notes'] = $notes;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::getById($existing['id']);
    }

    /**
     * Delete an inquiry
     */
    public static function delete(int|string $id): bool {
        $existing = self::getById($id);
        if (!$existing) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = :id");
        return $stmt->execute([':id' => $existing['id']]);
    }

    /**
     * Get recent N leads for overview widget
     */
    public static function getRecent(int $limit = 4): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM inquiries ORDER BY created_at DESC, id DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Aggregate Dashboard Overview Metrics & Chart Data
     */
    public static function getDashboardMetrics(): array {
        $pdo = Database::getConnection();

        // 1. Total Inquiries
        $totalLeads = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

        // 2. Status Counts
        $statusCounts = [
            'New'           => 0,
            'Contacted'     => 0,
            'In Discussion' => 0,
            'MOU Signed'    => 0,
            'Closed'        => 0,
            'Archived'      => 0
        ];
        $stRows = $pdo->query("SELECT status, COUNT(*) as cnt FROM inquiries GROUP BY status")->fetchAll();
        foreach ($stRows as $r) {
            $statusCounts[$r['status']] = (int)$r['cnt'];
        }

        // 3. Conversion Rate (% MOU Signed or Closed / Total)
        $converted = ($statusCounts['MOU Signed'] ?? 0) + ($statusCounts['Closed'] ?? 0);
        $conversionRate = $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0;

        // 4. Monthly Trend Data (Last 6 months)
        $monthNames = [];
        $trendCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = new DateTime("first day of -{$i} month");
            $mKey = $dt->format('Y-m');
            $label = $dt->format('M');
            if ($i === 0) $label .= ' (Current)';
            $monthNames[] = $label;

            $mStmt = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE DATE_FORMAT(created_at, '%Y-%m') = :mKey");
            $mStmt->execute([':mKey' => $mKey]);
            $trendCounts[] = (int)$mStmt->fetchColumn();
        }

        // 5. Vertical Distribution Counts (Hardcoded 8 Verticals breakdown)
        $verticalLabels = [
            'Gramsanjog',
            'LASA Services',
            'LASolution IT',
            'EzyBilling SaaS',
            'Political Ops',
            'FitnessGuru',
            'SarpanchPrarthi',
            'Food & Ecommerce'
        ];
        $verticalCounts = [];
        $searchTerms = [
            'Gramsanjog',
            'LASA',
            'LASolution',
            'EzyBilling',
            'Political',
            'FitnessGuru',
            'Sarpanch',
            'Food'
        ];

        foreach ($searchTerms as $term) {
            $vStmt = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE vertical LIKE :v");
            $vStmt->execute([':v' => "%{$term}%"]);
            $verticalCounts[] = (int)$vStmt->fetchColumn();
        }

        $recent = self::getRecent(4);

        return [
            'kpis' => [
                'total_leads'     => $totalLeads,
                'active_verticals'=> 8,
                'conversion_rate' => $conversionRate . '%',
                'status_counts'   => $statusCounts
            ],
            'trend_chart' => [
                'labels' => $monthNames,
                'data'   => $trendCounts
            ],
            'vertical_chart' => [
                'labels' => $verticalLabels,
                'data'   => $verticalCounts
            ],
            'recent_leads' => $recent
        ];
    }
}
