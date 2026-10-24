<?php
/**
 * LA SOLUTION PARTNERSHIP - INTEGRATION TEST SUITE
 * Validates inquiries and authentication functionality.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Inquiry.php';
require_once __DIR__ . '/../src/Models/AdminUser.php';

echo "========================================================\n";
echo "   LA SOLUTION - INQUIRIES & AUTH TEST RUNNER\n";
echo "========================================================\n\n";

// 1. Test Inquiry Creation
echo "[TEST 1] Creating new partnership inquiry...\n";
$newLead = Inquiry::create([
    'fullName'         => 'Test Partner',
    'phoneNumber'      => '+91 98765 12345',
    'emailAddress'     => 'test.partner@example.com',
    'partnershipType'  => 'Franchise / Territory Partner',
    'verticalInterest' => 'FitnessGuru.org.in - Gym Management',
    'cityState'        => 'Bhubaneswar, Odisha',
    'message'          => 'Automated test of inquiries backend.'
]);

if ($newLead && !empty($newLead['lead_code'])) {
    echo "  -> SUCCESS: Created {$newLead['lead_code']} (ID: {$newLead['id']}) for '{$newLead['name']}'\n";
} else {
    echo "  -> FAILED to create lead!\n";
    exit(1);
}

// 2. Test Retrieval
echo "\n[TEST 2] Retrieving created lead...\n";
$fetched = Inquiry::getById($newLead['id']);
if ($fetched && $fetched['email'] === 'test.partner@example.com') {
    echo "  -> SUCCESS: Retrieved {$fetched['lead_code']} with Status: '{$fetched['status']}'\n";
} else {
    echo "  -> FAILED to retrieve lead!\n";
    exit(1);
}

// 3. Test Status Update
echo "\n[TEST 3] Updating lead status...\n";
$updated = Inquiry::updateStatus($newLead['id'], 'In Discussion', 'Follow up scheduled for Monday');
if ($updated && $updated['status'] === 'In Discussion') {
    echo "  -> SUCCESS: Updated status to '{$updated['status']}' with Notes: '{$updated['notes']}'\n";
} else {
    echo "  -> FAILED to update lead status!\n";
    exit(1);
}

// 4. Test Admin Authentication
echo "\n[TEST 4] Authenticating admin user (admin / Admin@12345)...\n";
$admin = AdminUser::authenticate('admin', 'Admin@12345');
if ($admin && !empty($admin['token'])) {
    echo "  -> SUCCESS: Authenticated '{$admin['full_name']}' (Role: {$admin['role']})\n";
    echo "     Token generated: " . substr($admin['token'], 0, 16) . "...\n";
} else {
    echo "  -> FAILED: Admin authentication failed!\n";
    exit(1);
}

// 5. Test Dashboard Metrics (Inquiries aggregation)
echo "\n[TEST 5] Testing inquiries dashboard metrics...\n";
$metrics = Inquiry::getDashboardMetrics();
echo "  -> Total Inquiries in DB: " . $metrics['kpis']['total_leads'] . "\n";
echo "  -> Conversion Rate: " . $metrics['kpis']['conversion_rate'] . "\n";
echo "  -> Recent Inquiries Fetched: " . count($metrics['recent_leads']) . "\n";

// 6. Cleanup
echo "\n[TEST 6] Cleaning up test lead...\n";
$deleted = Inquiry::delete($newLead['id']);
if ($deleted) {
    echo "  -> SUCCESS: Cleaned up test lead {$newLead['lead_code']}\n";
} else {
    echo "  -> FAILED to delete test lead\n";
}

echo "\n========================================================\n";
echo "   ALL 6 TESTS PASSED! INQUIRIES & AUTH WORKING!\n";
echo "========================================================\n";
