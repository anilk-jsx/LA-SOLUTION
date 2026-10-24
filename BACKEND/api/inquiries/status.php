<?php
/**
 * LA SOLUTION PARTNERSHIP - INQUIRY STATUS QUICK UPDATE API
 * POST /api/inquiries/status.php
 * Payload: { id: "LEAD-101", status: "Contacted", notes: "Called applicant" }
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Utils/Validator.php';
require_once __DIR__ . '/../../src/Models/Inquiry.php';

setupCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Method not allowed. Use POST.", null, 405);
}

$data = Validator::getRequestData();
$id = $data['id'] ?? $data['lead_id'] ?? null;
$status = $data['status'] ?? null;
$notes = $data['notes'] ?? null;

if (!$id || !$status) {
    Response::error("Both 'id' and 'status' are required.", null, 400);
}

$allowedStatuses = ['New', 'Contacted', 'In Discussion', 'MOU Signed', 'Closed', 'Archived'];
if (!in_array($status, $allowedStatuses, true)) {
    Response::error("Invalid status. Allowed values: " . implode(', ', $allowedStatuses), null, 422);
}

$updated = Inquiry::updateStatus($id, $status, $notes, $data['performed_by'] ?? 'Admin');
if (!$updated) {
    Response::notFound("Inquiry not found.");
}

Response::success("Status for inquiry [{$updated['lead_code']}] updated to '{$status}'.", $updated);
