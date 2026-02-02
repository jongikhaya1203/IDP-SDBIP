<?php
/**
 * Dashboard Controller
 * Main dashboard with performance overview and statistics
 */

class DashboardController {

    public function index(): void {
        $db = db();
        $user = user();

        // Get current financial year
        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );

        if (!$financialYear) {
            $financialYear = $db->fetch(
                "SELECT * FROM financial_years ORDER BY start_date DESC LIMIT 1"
            );
        }

        $fyId = $financialYear['id'] ?? 0;

        // Build directorate filter based on user role
        $directorateFilter = '';
        $params = [$fyId];

        if (!has_role('admin', 'independent_assessor') && $user['directorate_id']) {
            $directorateFilter = 'AND k.directorate_id = ?';
            $params[] = $user['directorate_id'];
        }

        // Get KPI statistics
        $kpiStats = $db->fetch("
            SELECT
                COUNT(DISTINCT k.id) as total_kpis,
                COUNT(DISTINCT CASE WHEN qa.achievement_status = 'achieved' THEN k.id END) as achieved,
                COUNT(DISTINCT CASE WHEN qa.achievement_status = 'partially_achieved' THEN k.id END) as partial,
                COUNT(DISTINCT CASE WHEN qa.achievement_status = 'not_achieved' THEN k.id END) as not_achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1 {$directorateFilter}
        ", array_merge([$fyId], $params));

        // Get budget statistics
        $budgetStats = $db->fetch("
            SELECT
                SUM(projected_revenue) as projected_revenue,
                SUM(actual_revenue) as actual_revenue,
                SUM(capital_expenditure_projected) as capex_projected,
                SUM(capital_expenditure_actual) as capex_actual,
                SUM(operating_expenditure_projected) as opex_projected,
                SUM(operating_expenditure_actual) as opex_actual
            FROM budget_projections
            WHERE financial_year_id = ?
        ", [$fyId]);

        // Get directorate performance
        $directoratePerformance = $db->fetchAll("
            SELECT
                d.id,
                d.name,
                d.code,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                ROUND(
                    (SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) /
                    NULLIF(COUNT(DISTINCT k.id), 0)) * 100, 1
                ) as achievement_rate
            FROM directorates d
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id, d.name, d.code
            ORDER BY avg_rating DESC
        ", [$fyId, $fyId]);

        // Get pending tasks for current user
        $pendingTasks = $db->fetchAll("
            SELECT
                qa.id,
                k.kpi_code,
                k.kpi_name,
                qa.quarter,
                qa.status,
                d.name as directorate
            FROM kpi_quarterly_actuals qa
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN directorates d ON d.id = k.directorate_id
            WHERE qa.financial_year_id = ?
            AND qa.status IN ('draft', 'submitted', 'manager_review', 'independent_review')
            ORDER BY qa.quarter, k.kpi_code
            LIMIT 10
        ", [$fyId]);

        // Get recent activities
        $recentActivities = $db->fetchAll("
            SELECT
                al.action,
                al.table_name,
                al.created_at,
                u.first_name,
                u.last_name
            FROM audit_log al
            LEFT JOIN users u ON u.id = al.user_id
            WHERE al.action IN ('create', 'update', 'approve', 'reject')
            ORDER BY al.created_at DESC
            LIMIT 8
        ");

        // Get capital projects summary
        $projectsSummary = $db->fetch("
            SELECT
                COUNT(*) as total_projects,
                SUM(total_budget) as total_budget,
                SUM(q1_spent + q2_spent + q3_spent + q4_spent) as total_spent,
                ROUND(AVG(completion_percentage), 1) as avg_completion
            FROM capital_projects
            WHERE financial_year_id = ?
        ", [$fyId]);

        // Quarterly data for charts
        $quarterlyData = $db->fetchAll("
            SELECT
                qa.quarter,
                COUNT(DISTINCT qa.kpi_id) as total,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                SUM(CASE WHEN qa.achievement_status = 'partially_achieved' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN qa.achievement_status = 'not_achieved' THEN 1 ELSE 0 END) as not_achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating
            FROM kpi_quarterly_actuals qa
            JOIN kpis k ON k.id = qa.kpi_id
            WHERE qa.financial_year_id = ? {$directorateFilter}
            GROUP BY qa.quarter
            ORDER BY qa.quarter
        ", $params);

        // SLA category breakdown
        $slaBreakdown = $db->fetchAll("
            SELECT
                k.sla_category,
                COUNT(*) as count,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1
            GROUP BY k.sla_category
        ", [$fyId, $fyId]);

        $data = [
            'title' => 'Dashboard',
            'breadcrumbs' => [['label' => 'Dashboard']],
            'financialYear' => $financialYear,
            'currentQuarter' => current_quarter(),
            'kpiStats' => $kpiStats,
            'budgetStats' => $budgetStats,
            'directoratePerformance' => $directoratePerformance,
            'pendingTasks' => $pendingTasks,
            'recentActivities' => $recentActivities,
            'projectsSummary' => $projectsSummary,
            'quarterlyData' => $quarterlyData,
            'slaBreakdown' => $slaBreakdown
        ];

        // Render view
        ob_start();
        extract($data);
        include VIEWS_PATH . '/dashboard/index.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function getStats(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $stats = $db->fetch("
            SELECT
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                COUNT(CASE WHEN qa.status IN ('submitted', 'manager_review', 'independent_review') THEN 1 END) as pending_reviews
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1
        ", [$fyId, $fyId]);

        json_response(['stats' => $stats]);
    }

    public function getChartData(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Quarterly performance trend
        $quarterlyTrend = $db->fetchAll("
            SELECT
                qa.quarter,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                ROUND(
                    (SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) /
                    NULLIF(COUNT(*), 0)) * 100, 1
                ) as achievement_rate
            FROM kpi_quarterly_actuals qa
            WHERE qa.financial_year_id = ?
            GROUP BY qa.quarter
            ORDER BY qa.quarter
        ", [$fyId]);

        // Directorate comparison
        $directorateComparison = $db->fetchAll("
            SELECT
                d.code,
                d.name,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating
            FROM directorates d
            LEFT JOIN kpis k ON k.directorate_id = d.id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id, d.code, d.name
        ", [$fyId]);

        // Budget utilization
        $budgetUtilization = $db->fetchAll("
            SELECT
                d.code,
                d.name,
                SUM(bp.capital_expenditure_projected) as projected,
                SUM(bp.capital_expenditure_actual) as actual
            FROM directorates d
            LEFT JOIN budget_projections bp ON bp.directorate_id = d.id AND bp.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id, d.code, d.name
        ", [$fyId]);

        json_response([
            'quarterlyTrend' => $quarterlyTrend,
            'directorateComparison' => $directorateComparison,
            'budgetUtilization' => $budgetUtilization
        ]);
    }
}
