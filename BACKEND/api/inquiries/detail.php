<?php
/**
 * LA SOLUTION PARTNERSHIP - INQUIRY DETAIL API
 * GET    /api/inquiries/detail.php?id={id}  -> Fetch single inquiry
 * PUT    /api/inquiries/detail.php?id={id}  -> Update inquiry details
 * POST   /api/inquiries/detail.php?id={id}  -> Update inquiry (POST fallback)
 * DELETE /api/inquiries/detail.php?id={id}  -> Delete inquiry
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Utils/Validator.php';
require_once __DIR__ . '/../../src/Models/Inquiry.php';

setupCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$data = Validator::getRequestData();
$id = $_GET['id'] ?? $_GET['code'] ?? $data['id'] ?? $data['editLeadId'] ?? null;

if (!$id) {
    Response::error("Inquiry ID or lead code is required.", null, 400);
}

if ($method === 'GET') {
    // 1. Fetch Single Lead
    $lead = Inquiry::getById($id);
    if (!$lead) {
        Response::notFound("Inquiry with ID/Code '{$id}' not found.");
    }
    Response::success("Inquiry retrieved.", $lead);

} elseif ($method === 'PUT' || ($method === 'POST' && ($data['_method'] ?? '') !== 'DELETE')) {
    // 2. Update Lead Details
    $errors = Validator::validateInquiry($data, true);
    if (!empty($errors)) {
        Response::error("Validation failed.", $errors, 422);
    }

    $updated = Inquiry::update($id, $data);
    if (!$updated) {
        Response::notFound("Inquiry with ID/Code '{$id}' not found for update.");
    }

    Response::success("Inquiry [{$updated['lead_code']}] successfully updated.", $updated);

} elseif ($method === 'DELETE' || ($method === 'POST' && ($data['_method'] ?? '') === 'DELETE')) {
    // 3. Delete Lead
    $deleted = Inquiry::delete($id);
    if (!$deleted) {
        Response::notFound("Inquiry with ID/Code '{$id}' not found for deletion.");
    }

    Response::success("Inquiry [{$id}] permanently removed.");

} else {
    Response::error("Method not allowed", null, 405);
}
