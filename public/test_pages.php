<?php
/**
 * Page Test Script
 * Tests all main routes for errors
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

echo "<html><head><title>Page Test Results</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body class='p-4'>";
echo "<h1>SDBIP/IDP System - Page Test Results</h1>";
echo "<p class='text-muted'>Testing all controllers and views...</p><hr>";

$results = [];

// Test database connection
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE,
        DB_USERNAME,
        DB_PASSWORD
    );
    $results['Database Connection'] = ['status' => 'OK', 'class' => 'success'];
} catch (Exception $e) {
    $results['Database Connection'] = ['status' => 'FAILED: ' . $e->getMessage(), 'class' => 'danger'];
}

// Test controllers exist
$controllers = [
    'AuthController',
    'DashboardController',
    'IDPController',
    'SDBIPController',
    'KPIController',
    'AssessmentController',
    'POEController',
    'BudgetController',
    'ReportController',
    'AdminController',
    'CpanelController',
    'ImbizoController'
];

foreach ($controllers as $ctrl) {
    $file = __DIR__ . '/../src/Controllers/' . $ctrl . '.php';
    if (file_exists($file)) {
        require_once $file;
        if (class_exists($ctrl)) {
            $results[$ctrl] = ['status' => 'OK', 'class' => 'success'];
        } else {
            $results[$ctrl] = ['status' => 'Class not found', 'class' => 'warning'];
        }
    } else {
        $results[$ctrl] = ['status' => 'File missing', 'class' => 'danger'];
    }
}

// Test views exist
$views = [
    // Auth
    'auth/login.php',

    // Dashboard
    'dashboard/index.php',

    // IDP
    'idp/index.php',
    'idp/show.php',
    'idp/create.php',
    'idp/edit.php',

    // SDBIP
    'sdbip/index.php',
    'sdbip/kpi-detail.php',

    // Budget
    'budget/index.php',
    'budget/projections.php',
    'budget/projects.php',
    'budget/project-detail.php',
    'budget/create-project.php',
    'budget/edit-project.php',

    // Reports
    'reports/quarterly.php',
    'reports/quarterly-detail.php',
    'reports/directorate.php',
    'reports/directorate-detail.php',

    // Admin
    'admin/users.php',
    'admin/create-user.php',
    'admin/show-user.php',
    'admin/edit-user.php',
    'admin/directorates.php',
    'admin/financial-years.php',
    'admin/settings.php',

    // Control Panel
    'cpanel/index.php',
    'cpanel/modules.php',
    'cpanel/database.php',
    'cpanel/integrations.php',
    'cpanel/backup.php',
    'cpanel/logs.php',

    // Imbizo
    'imbizo/index.php',
    'imbizo/create.php',
    'imbizo/show.php',
    'imbizo/livestream.php',
    'imbizo/action-items.php',
    'imbizo/action-item-detail.php',

    // Layout
    'layouts/main.php'
];

foreach ($views as $view) {
    $file = __DIR__ . '/../views/' . $view;
    if (file_exists($file)) {
        $results['View: ' . $view] = ['status' => 'OK', 'class' => 'success'];
    } else {
        $results['View: ' . $view] = ['status' => 'Missing', 'class' => 'danger'];
    }
}

// Test database tables
$tables = [
    'users',
    'directorates',
    'departments',
    'financial_years',
    'idp_strategic_objectives',
    'kpis',
    'kpi_quarterly_actuals',
    'proof_of_evidence',
    'budget_projections',
    'capital_projects',
    'audit_log',
    'notifications',
    'wards',
    'imbizo_sessions',
    'imbizo_action_items'
];

if (isset($db)) {
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            $results['Table: ' . $table] = ['status' => "OK ($count rows)", 'class' => 'success'];
        } catch (Exception $e) {
            $results['Table: ' . $table] = ['status' => 'Missing or error', 'class' => 'danger'];
        }
    }
}

// Output results
$okCount = 0;
$failCount = 0;

echo "<table class='table table-bordered'>";
echo "<thead class='table-dark'><tr><th>Component</th><th>Status</th></tr></thead><tbody>";

foreach ($results as $name => $result) {
    echo "<tr><td>{$name}</td><td><span class='badge bg-{$result['class']}'>{$result['status']}</span></td></tr>";
    if ($result['class'] === 'success') $okCount++;
    else $failCount++;
}

echo "</tbody></table>";

echo "<div class='alert alert-" . ($failCount === 0 ? 'success' : 'warning') . "'>";
echo "<strong>Summary:</strong> {$okCount} passed, {$failCount} failed/missing";
echo "</div>";

echo "<h3>Quick Links (Test after login)</h3>";
echo "<ul class='list-group'>";
$links = [
    '/' => 'Dashboard',
    '/idp' => 'IDP Management',
    '/sdbip' => 'SDBIP',
    '/budget' => 'Budget',
    '/budget/projections' => 'Budget Projections',
    '/budget/projects' => 'Capital Projects',
    '/reports/quarterly' => 'Quarterly Reports',
    '/reports/directorate' => 'Directorate Reports',
    '/admin/users' => 'User Management',
    '/cpanel' => 'Control Panel',
    '/imbizo' => 'Mayoral Imbizo'
];
foreach ($links as $url => $label) {
    echo "<li class='list-group-item'><a href='{$url}' target='_blank'>{$label}</a> - <code>{$url}</code></li>";
}
echo "</ul>";

echo "</body></html>";
