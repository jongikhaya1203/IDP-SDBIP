<?php
/**
 * Database Seeding Script
 * Run via browser: http://localhost:3000/seed.php
 */

require_once dirname(__DIR__) . '/config/app.php';

header('Content-Type: text/plain');
set_time_limit(300);

echo "=== SDBIP/IDP Database Seeding ===\n\n";

try {
    $db = db();
    $pdo = $db->getConnection();

    // Financial Years
    echo "Creating Financial Years...\n";
    $pdo->exec("INSERT IGNORE INTO financial_years (year_label, start_date, end_date, status, is_current) VALUES
        ('2023/2024', '2023-07-01', '2024-06-30', 'closed', 0),
        ('2024/2025', '2024-07-01', '2025-06-30', 'active', 1),
        ('2025/2026', '2025-07-01', '2026-06-30', 'planning', 0)");
    echo "✓ Financial Years created\n";

    // Directorates
    echo "Creating Directorates...\n";
    $pdo->exec("INSERT IGNORE INTO directorates (id, name, code, description, budget_allocation) VALUES
        (1, 'Office of the Municipal Manager', 'OMM', 'Executive management and strategic planning', 45000000.00),
        (2, 'Corporate Services', 'CORP', 'Human resources, ICT, legal services', 85000000.00),
        (3, 'Financial Services', 'FIN', 'Budget, revenue, expenditure, SCM', 120000000.00),
        (4, 'Infrastructure and Technical Services', 'INFRA', 'Roads, water, sanitation, electricity', 350000000.00),
        (5, 'Community Services', 'COMM', 'Libraries, parks, environmental health', 95000000.00),
        (6, 'Economic Development and Planning', 'ECON', 'LED, spatial planning, tourism', 55000000.00)");
    echo "✓ Directorates created\n";

    // More users
    echo "Creating Users...\n";
    $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $pdo->exec("INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role, directorate_id, employee_number, job_title, is_active) VALUES
        ('mm.ndlovu', 'mm@municipality.gov.za', '$hash', 'Thabo', 'Ndlovu', 'director', 1, 'MM001', 'Municipal Manager', 1),
        ('dir.corp', 'corp@municipality.gov.za', '$hash', 'Nomvula', 'Mokoena', 'director', 2, 'DIR002', 'Director: Corporate Services', 1),
        ('cfo.dlamini', 'cfo@municipality.gov.za', '$hash', 'Sipho', 'Dlamini', 'director', 3, 'DIR003', 'Chief Financial Officer', 1),
        ('dir.infra', 'infra@municipality.gov.za', '$hash', 'Mandla', 'Nkosi', 'director', 4, 'DIR004', 'Director: Infrastructure', 1),
        ('mgr.hr', 'hr@municipality.gov.za', '$hash', 'Grace', 'Sithole', 'manager', 2, 'MGR001', 'Manager: Human Resources', 1),
        ('mgr.finance', 'finance@municipality.gov.za', '$hash', 'Priya', 'Pillay', 'manager', 3, 'MGR003', 'Manager: Revenue', 1),
        ('assessor1', 'assessor@municipality.gov.za', '$hash', 'John', 'Smith', 'independent_assessor', NULL, 'ASS001', 'Independent Assessor', 1)");
    echo "✓ Users created\n";

    // Strategic Objectives
    echo "Creating Strategic Objectives...\n";
    $pdo->exec("INSERT IGNORE INTO idp_strategic_objectives (id, financial_year_id, objective_code, objective_name, description, directorate_id, weight, is_active) VALUES
        (1, 2, 'SO-001', 'Good Governance and Public Participation', 'Enhance good governance, accountability and community participation', 1, 15.00, 1),
        (2, 2, 'SO-002', 'Institutional Development and Transformation', 'Build institutional capacity and transform service delivery', 2, 15.00, 1),
        (3, 2, 'SO-003', 'Sound Financial Management', 'Ensure sound financial management and sustainability', 3, 20.00, 1),
        (4, 2, 'SO-004', 'Basic Service Delivery and Infrastructure', 'Provide sustainable basic services and infrastructure', 4, 25.00, 1),
        (5, 2, 'SO-005', 'Safe and Healthy Environment', 'Create a safe, healthy and sustainable environment', 5, 15.00, 1),
        (6, 2, 'SO-006', 'Local Economic Development', 'Promote local economic development and job creation', 6, 10.00, 1)");
    echo "✓ Strategic Objectives created\n";

    // KPIs
    echo "Creating KPIs...\n";
    $pdo->exec("INSERT IGNORE INTO kpis (id, strategic_objective_id, kpi_code, kpi_name, unit_of_measure, baseline, annual_target, q1_target, q2_target, q3_target, q4_target, sla_category, responsible_user_id, directorate_id, is_active) VALUES
        (1, 1, 'KPI-001', 'Percentage of council resolutions implemented', 'Percentage', 75, 90, 22, 45, 67, 90, 'none', 2, 1, 1),
        (2, 1, 'KPI-002', 'Number of public participation sessions held', 'Number', 12, 24, 6, 12, 18, 24, 'none', 2, 1, 1),
        (3, 2, 'KPI-003', 'Staff vacancy rate', 'Percentage', 15, 10, 14, 12, 11, 10, 'hr_vacancy', 5, 2, 1),
        (4, 2, 'KPI-004', 'Training programs completed', 'Number', 8, 12, 3, 6, 9, 12, 'none', 5, 2, 1),
        (5, 3, 'KPI-005', 'Percentage operating budget spent', 'Percentage', 85, 95, 20, 45, 70, 95, 'budget', 3, 3, 1),
        (6, 3, 'KPI-006', 'Debt collection rate', 'Percentage', 70, 85, 75, 78, 82, 85, 'none', 6, 3, 1),
        (7, 3, 'KPI-007', 'Audit findings resolved', 'Percentage', 60, 90, 70, 80, 85, 90, 'internal_control', 3, 3, 1),
        (8, 4, 'KPI-008', 'Kilometers of roads maintained', 'Kilometers', 50, 100, 25, 50, 75, 100, 'budget', 4, 4, 1),
        (9, 4, 'KPI-009', 'Water supply interruptions resolved within 24h', 'Percentage', 80, 95, 85, 88, 92, 95, 'none', 4, 4, 1),
        (10, 4, 'KPI-010', 'Capital projects completed on time', 'Percentage', 70, 85, 75, 78, 82, 85, 'budget', 4, 4, 1),
        (11, 5, 'KPI-011', 'Refuse collection coverage', 'Percentage', 90, 98, 92, 94, 96, 98, 'none', 4, 5, 1),
        (12, 5, 'KPI-012', 'Health inspections completed', 'Number', 200, 400, 100, 200, 300, 400, 'none', 4, 5, 1),
        (13, 6, 'KPI-013', 'New businesses registered', 'Number', 50, 100, 25, 50, 75, 100, 'none', 4, 6, 1),
        (14, 6, 'KPI-014', 'Jobs created through LED projects', 'Number', 100, 250, 60, 125, 190, 250, 'none', 4, 6, 1)");
    echo "✓ KPIs created\n";

    // Quarterly Actuals
    echo "Creating Quarterly Actuals...\n";
    $pdo->exec("INSERT IGNORE INTO kpi_quarterly_actuals (kpi_id, financial_year_id, quarter, actual_value, variance, self_rating, self_comments, achievement_status, status) VALUES
        (1, 2, 1, 25, 3, 4, 'Good progress on council resolutions', 'achieved', 'submitted'),
        (1, 2, 2, 48, 3, 4, 'Exceeded Q2 target', 'achieved', 'submitted'),
        (2, 2, 1, 7, 1, 4, 'One additional session held', 'achieved', 'submitted'),
        (2, 2, 2, 13, 1, 4, 'On track for annual target', 'achieved', 'submitted'),
        (3, 2, 1, 13, -1, 3, 'Slight improvement in vacancy rate', 'partially_achieved', 'submitted'),
        (5, 2, 1, 22, 2, 4, 'Budget spending on track', 'achieved', 'submitted'),
        (5, 2, 2, 47, 2, 4, 'Exceeded Q2 target', 'achieved', 'submitted'),
        (6, 2, 1, 72, -3, 3, 'Below target but improving', 'partially_achieved', 'submitted'),
        (8, 2, 1, 28, 3, 4, 'Roads maintenance ahead of schedule', 'achieved', 'submitted'),
        (8, 2, 2, 55, 5, 5, 'Excellent progress', 'achieved', 'submitted'),
        (9, 2, 1, 88, 3, 4, 'Good response time achieved', 'achieved', 'submitted'),
        (11, 2, 1, 93, 1, 4, 'Refuse collection improving', 'achieved', 'submitted'),
        (13, 2, 1, 30, 5, 4, 'More businesses than expected', 'achieved', 'submitted'),
        (14, 2, 1, 75, 15, 5, 'Job creation exceeding target', 'achieved', 'submitted')");
    echo "✓ Quarterly Actuals created\n";

    // Budget Projections
    echo "Creating Budget Projections...\n";
    for ($month = 1; $month <= 12; $month++) {
        $pdo->exec("INSERT IGNORE INTO budget_projections (financial_year_id, directorate_id, month, revenue_source, projected_revenue, actual_revenue, operating_expenditure_projected, operating_expenditure_actual) VALUES
            (2, 3, $month, 'Property Rates', 5000000, " . (4500000 + rand(0, 1000000)) . ", 4000000, " . (3800000 + rand(0, 400000)) . "),
            (2, 3, $month, 'Service Charges', 8000000, " . (7500000 + rand(0, 1000000)) . ", 6000000, " . (5800000 + rand(0, 400000)) . "),
            (2, 4, $month, 'Grants', 15000000, " . (14000000 + rand(0, 2000000)) . ", 12000000, " . (11500000 + rand(0, 1000000)) . ")");
    }
    echo "✓ Budget Projections created\n";

    // Capital Projects
    echo "Creating Capital Projects...\n";
    $pdo->exec("INSERT IGNORE INTO capital_projects (financial_year_id, directorate_id, project_name, project_code, description, total_budget, q1_budget, q2_budget, q3_budget, q4_budget, status, completion_percentage) VALUES
        (2, 4, 'Main Road Rehabilitation', 'CP-001', 'Rehabilitation of 15km main road', 25000000, 5000000, 8000000, 8000000, 4000000, 'in_progress', 45),
        (2, 4, 'Water Treatment Plant Upgrade', 'CP-002', 'Upgrade capacity by 20ML/day', 45000000, 10000000, 15000000, 15000000, 5000000, 'in_progress', 35),
        (2, 4, 'Sewer Network Extension', 'CP-003', 'Extend sewer network to new areas', 18000000, 4000000, 6000000, 5000000, 3000000, 'planning', 10),
        (2, 5, 'Community Hall Construction', 'CP-004', 'New community hall in Ward 5', 8000000, 2000000, 3000000, 2000000, 1000000, 'in_progress', 50),
        (2, 6, 'Industrial Park Development', 'CP-005', 'Phase 1 industrial park', 35000000, 8000000, 12000000, 10000000, 5000000, 'planning', 5)");
    echo "✓ Capital Projects created\n";

    // Get counts
    $counts = [];
    $tables = ['financial_years', 'directorates', 'users', 'idp_strategic_objectives', 'kpis', 'kpi_quarterly_actuals', 'budget_projections', 'capital_projects'];
    foreach ($tables as $table) {
        $result = $db->fetch("SELECT COUNT(*) as cnt FROM $table");
        $counts[$table] = $result['cnt'];
    }

    echo "\n=== Summary ===\n";
    foreach ($counts as $table => $count) {
        echo "$table: $count records\n";
    }

    echo "\n✓ Database seeding complete!\n";
    echo "Go to http://localhost:3000 to view the dashboard.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
