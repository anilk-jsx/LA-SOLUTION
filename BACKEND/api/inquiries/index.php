<?php
/**
 * LA SOLUTION PARTNERSHIP - INQUIRIES COLLECTION API
 * GET  /api/inquiries/index.php -> List, filter, search, and paginate inquiries
 * POST /api/inquiries/index.php -> Submit new partnership inquiry (Public & Admin)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Utils/Validator.php';
require_once __DIR__ . '/../../src/Models/Inquiry.php';

setupCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    // 1. List / Filter Inquiries
    try {
        $filters = [
            'search'   => $_GET['search'] ?? '',
            'vertical' => $_GET['vertical'] ?? 'ALL',
            'status'   => $_GET['status'] ?? 'ALL',
            'type'     => $_GET['type'] ?? 'ALL',
            'priority' => $_GET['priority'] ?? 'ALL'
        ];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (isset($_GET['limit']) ? (int)$_GET['limit'] : 8);

        $result = Inquiry::getAll($filters, $page, $perPage);

        Response::success("Inquiries retrieved successfully.", $result['items'], [
            'pagination' => $result['pagination'],
            'filters'    => $filters
        ]);
    } catch (Throwable $e) {
        Response::serverError("Failed to fetch inquiries: " . $e->getMessage());
    }

} elseif ($method === 'POST') {
    // 2. Create New Inquiry
    try {
        $data = Validator::getRequestData();

        // Validation
        $errors = Validator::validateInquiry($data, false);
        if (!empty($errors)) {
            Response::error("Please fill in all required fields correctly.", $errors, 422);
        }

        // Create inquiry in DB
        $newInquiry = Inquiry::create($data);

        Response::created("Thank you! Your partnership inquiry has been logged successfully.", $newInquiry, [
            'lead_code' => $newInquiry['lead_code']
        ]);
    } catch (Throwable $e) {
        Response::serverError("Failed to save partnership inquiry: " . $e->getMessage());
    }

} else {
    Response::error("Method not allowed", null, 405);
}
