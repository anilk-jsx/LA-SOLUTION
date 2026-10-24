<?php
/**
 * LA SOLUTION PARTNERSHIP - DASHBOARD OVERVIEW ANALYTICS API
 * GET /api/dashboard/stats.php
 * Returns KPI numbers, Chart.js datasets, recent submissions, and hardcoded verticals health.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Utils/Response.php';
require_once __DIR__ . '/../../src/Models/Inquiry.php';
require_once __DIR__ . '/../../src/Models/Vertical.php';

setupCorsHeaders();

try {
    $metrics = Inquiry::getDashboardMetrics();
    $verticals = Vertical::getAll();

    $response = [
        'kpis'            => $metrics['kpis'],
        'trend_chart'     => $metrics['trend_chart'],
        'vertical_chart'  => $metrics['vertical_chart'],
        'recent_leads'    => $metrics['recent_leads'],
        'verticals'       => $verticals,
        'system'          => [
            'verticals_active' => '8 / 8',
            'uptime'           => '99.98%',
            'operational'      => true
        ]
    ];

    Response::success("Dashboard metrics retrieved.", $response);

} catch (Throwable $e) {
    Response::serverError("Failed to load dashboard metrics: " . $e->getMessage());
}
