<?php
/**
 * Report Controller
 * Handles quarterly and directorate reports
 */

class ReportController {

    public function index(): void {
        redirect('/reports/quarterly');
    }

    public function quarterly(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Summary by quarter
        $quarterSummary = $db->fetchAll("
            SELECT
                qa.quarter,
                COUNT(DISTINCT qa.kpi_id) as total_kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                SUM(CASE WHEN qa.achievement_status = 'partially_achieved' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN qa.achievement_status = 'not_achieved' THEN 1 ELSE 0 END) as not_achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                SUM(CASE WHEN qa.status = 'approved' THEN 1 ELSE 0 END) as approved_count
            FROM kpi_quarterly_actuals qa
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            WHERE qa.financial_year_id = ? AND so.financial_year_id = ?
            GROUP BY qa.quarter
            ORDER BY qa.quarter
        ", [$fyId, $fyId]);

        $data = [
            'title' => 'Quarterly Reports',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'Quarterly']
            ],
            'financialYear' => $financialYear,
            'quarterSummary' => $quarterSummary,
            'currentQuarter' => current_quarter()
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/reports/quarterly.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function quarterlyDetail(string $quarter): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Get all KPIs for this quarter grouped by directorate
        $kpis = $db->fetchAll("
            SELECT
                d.id as directorate_id,
                d.name as directorate_name,
                d.code as directorate_code,
                k.kpi_code,
                k.kpi_name,
                k.sla_category,
                qa.target_value,
                qa.actual_value,
                qa.variance,
                qa.achievement_status,
                qa.self_rating,
                qa.manager_rating,
                qa.independent_rating,
                qa.aggregated_rating,
                qa.status
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ? AND qa.quarter = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1
            ORDER BY d.code, k.kpi_code
        ", [$fyId, $quarter, $fyId]);

        // Group by directorate
        $grouped = [];
        foreach ($kpis as $kpi) {
            $dirId = $kpi['directorate_id'];
            if (!isset($grouped[$dirId])) {
                $grouped[$dirId] = [
                    'directorate_name' => $kpi['directorate_name'],
                    'directorate_code' => $kpi['directorate_code'],
                    'kpis' => []
                ];
            }
            $grouped[$dirId]['kpis'][] = $kpi;
        }

        $data = [
            'title' => 'Q' . $quarter . ' Performance Report',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'Quarterly', 'url' => '/reports/quarterly'],
                ['label' => 'Q' . $quarter]
            ],
            'financialYear' => $financialYear,
            'quarter' => $quarter,
            'groupedKpis' => $grouped
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/reports/quarterly-detail.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function directorate(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $directorates = $db->fetchAll("
            SELECT
                d.id,
                d.name,
                d.code,
                d.budget_allocation,
                u.first_name as head_name,
                u.last_name as head_surname,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                SUM(k.budget_allocated) as kpi_budget
            FROM directorates d
            LEFT JOIN users u ON u.id = d.head_user_id
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id, d.name, d.code, d.budget_allocation, u.first_name, u.last_name
            ORDER BY d.name
        ", [$fyId, $fyId]);

        $data = [
            'title' => 'Directorate Performance',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'Directorate']
            ],
            'financialYear' => $financialYear,
            'directorates' => $directorates
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/reports/directorate.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function directorateDetail(string $id): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $directorate = $db->fetch("
            SELECT d.*, u.first_name, u.last_name
            FROM directorates d
            LEFT JOIN users u ON u.id = d.head_user_id
            WHERE d.id = ?
        ", [$id]);

        if (!$directorate) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        // Get KPIs with quarterly data
        $kpis = $db->fetchAll("
            SELECT
                k.*,
                so.objective_name,
                qa1.actual_value as q1_actual, qa1.aggregated_rating as q1_rating,
                qa2.actual_value as q2_actual, qa2.aggregated_rating as q2_rating,
                qa3.actual_value as q3_actual, qa3.aggregated_rating as q3_rating,
                qa4.actual_value as q4_actual, qa4.aggregated_rating as q4_rating
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa1 ON qa1.kpi_id = k.id AND qa1.quarter = 1 AND qa1.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa2 ON qa2.kpi_id = k.id AND qa2.quarter = 2 AND qa2.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa3 ON qa3.kpi_id = k.id AND qa3.quarter = 3 AND qa3.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa4 ON qa4.kpi_id = k.id AND qa4.quarter = 4 AND qa4.financial_year_id = ?
            WHERE k.directorate_id = ? AND k.is_active = 1 AND so.financial_year_id = ?
            ORDER BY k.kpi_code
        ", [$fyId, $fyId, $fyId, $fyId, $id, $fyId]);

        // Get SLA breakdown
        $slaBreakdown = $db->fetchAll("
            SELECT
                k.sla_category,
                COUNT(*) as count,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE k.directorate_id = ? AND k.is_active = 1 AND so.financial_year_id = ?
            GROUP BY k.sla_category
        ", [$fyId, $id, $fyId]);

        $data = [
            'title' => $directorate['name'] . ' Performance',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'Directorate', 'url' => '/reports/directorate'],
                ['label' => $directorate['code']]
            ],
            'financialYear' => $financialYear,
            'directorate' => $directorate,
            'kpis' => $kpis,
            'slaBreakdown' => $slaBreakdown
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/reports/directorate-detail.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function exportExcel(): void {
        // Simple CSV export
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $data = $db->fetchAll("
            SELECT
                d.code as directorate,
                k.kpi_code,
                k.kpi_name,
                k.unit_of_measure,
                k.annual_target,
                k.sla_category,
                qa.quarter,
                qa.target_value,
                qa.actual_value,
                qa.variance,
                qa.achievement_status,
                qa.self_rating,
                qa.manager_rating,
                qa.independent_rating,
                qa.aggregated_rating
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1
            ORDER BY d.code, k.kpi_code, qa.quarter
        ", [$fyId, $fyId]);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sdbip_report_' . date('Ymd') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'Directorate', 'KPI Code', 'KPI Name', 'Unit', 'Annual Target',
            'SLA Category', 'Quarter', 'Q Target', 'Actual', 'Variance %',
            'Status', 'Self Rating', 'Manager Rating', 'Independent Rating', 'Aggregated Rating'
        ]);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    public function exportPdf(): void {
        flash('info', 'PDF export requires additional library. Please use Excel export or print from browser.');
        redirect('/reports/quarterly');
    }
}
