<?php
/**
 * SDBIP Controller
 * Manages Strategic Objectives, KPIs, and Quarterly Targets
 */

class SDBIPController {

    public function index(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Get summary statistics
        $stats = $db->fetch("
            SELECT
                COUNT(DISTINCT so.id) as total_objectives,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(k.budget_allocated) as total_budget
            FROM idp_strategic_objectives so
            LEFT JOIN kpis k ON k.strategic_objective_id = so.id AND k.is_active = 1
            WHERE so.financial_year_id = ? AND so.is_active = 1
        ", [$fyId]);

        // Get objectives by directorate
        $objectives = $db->fetchAll("
            SELECT
                d.name as directorate_name,
                d.code as directorate_code,
                COUNT(DISTINCT so.id) as objective_count,
                COUNT(DISTINCT k.id) as kpi_count
            FROM directorates d
            LEFT JOIN idp_strategic_objectives so ON so.directorate_id = d.id AND so.financial_year_id = ?
            LEFT JOIN kpis k ON k.strategic_objective_id = so.id AND k.is_active = 1
            WHERE d.is_active = 1
            GROUP BY d.id, d.name, d.code
            ORDER BY d.name
        ", [$fyId]);

        $data = [
            'title' => 'SDBIP Overview',
            'breadcrumbs' => [['label' => 'SDBIP']],
            'financialYear' => $financialYear,
            'stats' => $stats,
            'objectives' => $objectives
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/index.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function objectives(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $directorateId = $_GET['directorate'] ?? null;
        $search = $_GET['search'] ?? '';

        $where = ['so.financial_year_id = ?', 'so.is_active = 1'];
        $params = [$fyId];

        if ($directorateId) {
            $where[] = 'so.directorate_id = ?';
            $params[] = $directorateId;
        }

        if ($search) {
            $where[] = '(so.objective_name LIKE ? OR so.objective_code LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $objectives = $db->fetchAll("
            SELECT
                so.*,
                d.name as directorate_name,
                d.code as directorate_code,
                COUNT(DISTINCT k.id) as kpi_count,
                u.first_name as created_by_name
            FROM idp_strategic_objectives so
            JOIN directorates d ON d.id = so.directorate_id
            LEFT JOIN kpis k ON k.strategic_objective_id = so.id AND k.is_active = 1
            LEFT JOIN users u ON u.id = so.created_by
            WHERE {$whereClause}
            GROUP BY so.id
            ORDER BY so.objective_code
        ", $params);

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Strategic Objectives',
            'breadcrumbs' => [
                ['label' => 'SDBIP', 'url' => '/sdbip'],
                ['label' => 'Strategic Objectives']
            ],
            'financialYear' => $financialYear,
            'objectives' => $objectives,
            'directorates' => $directorates,
            'selectedDirectorate' => $directorateId,
            'search' => $search
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/objectives.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function kpis(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $directorateId = $_GET['directorate'] ?? null;
        $slaCategory = $_GET['sla'] ?? null;
        $search = $_GET['search'] ?? '';

        $where = ['so.financial_year_id = ?', 'k.is_active = 1'];
        $params = [$fyId];

        if ($directorateId) {
            $where[] = 'k.directorate_id = ?';
            $params[] = $directorateId;
        }

        if ($slaCategory) {
            $where[] = 'k.sla_category = ?';
            $params[] = $slaCategory;
        }

        if ($search) {
            $where[] = '(k.kpi_name LIKE ? OR k.kpi_code LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $kpis = $db->fetchAll("
            SELECT
                k.*,
                so.objective_name,
                so.objective_code,
                d.name as directorate_name,
                d.code as directorate_code,
                u.first_name as responsible_name,
                u.last_name as responsible_surname
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN users u ON u.id = k.responsible_user_id
            WHERE {$whereClause}
            ORDER BY k.kpi_code
        ", $params);

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Key Performance Indicators',
            'breadcrumbs' => [
                ['label' => 'SDBIP', 'url' => '/sdbip'],
                ['label' => 'KPIs']
            ],
            'financialYear' => $financialYear,
            'kpis' => $kpis,
            'directorates' => $directorates,
            'selectedDirectorate' => $directorateId,
            'selectedSla' => $slaCategory,
            'search' => $search
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/kpis.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function createKPI(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $objectives = $db->fetchAll("
            SELECT so.id, so.objective_code, so.objective_name, d.name as directorate_name
            FROM idp_strategic_objectives so
            JOIN directorates d ON d.id = so.directorate_id
            WHERE so.financial_year_id = ? AND so.is_active = 1
            ORDER BY d.name, so.objective_code
        ", [$fyId]);

        $users = $db->fetchAll("
            SELECT id, first_name, last_name, job_title, directorate_id
            FROM users
            WHERE is_active = 1 AND role IN ('director', 'manager', 'employee')
            ORDER BY first_name, last_name
        ");

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Create KPI',
            'breadcrumbs' => [
                ['label' => 'SDBIP', 'url' => '/sdbip'],
                ['label' => 'KPIs', 'url' => '/sdbip/kpis'],
                ['label' => 'Create']
            ],
            'objectives' => $objectives,
            'users' => $users,
            'directorates' => $directorates
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/kpi-form.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function storeKPI(): void {
        $db = db();

        $data = [
            'strategic_objective_id' => (int)$_POST['strategic_objective_id'],
            'kpi_code' => trim($_POST['kpi_code']),
            'kpi_name' => trim($_POST['kpi_name']),
            'description' => trim($_POST['description'] ?? ''),
            'unit_of_measure' => trim($_POST['unit_of_measure']),
            'baseline' => trim($_POST['baseline'] ?? ''),
            'annual_target' => trim($_POST['annual_target']),
            'q1_target' => trim($_POST['q1_target'] ?? ''),
            'q2_target' => trim($_POST['q2_target'] ?? ''),
            'q3_target' => trim($_POST['q3_target'] ?? ''),
            'q4_target' => trim($_POST['q4_target'] ?? ''),
            'sla_category' => $_POST['sla_category'] ?? 'none',
            'budget_required' => (float)($_POST['budget_required'] ?? 0),
            'budget_allocated' => (float)($_POST['budget_allocated'] ?? 0),
            'responsible_user_id' => (int)$_POST['responsible_user_id'] ?: null,
            'directorate_id' => (int)$_POST['directorate_id'],
            'data_source' => trim($_POST['data_source'] ?? ''),
            'calculation_method' => trim($_POST['calculation_method'] ?? ''),
            'reporting_frequency' => $_POST['reporting_frequency'] ?? 'quarterly',
            'is_strategic' => isset($_POST['is_strategic']) ? 1 : 0,
            'created_by' => user()['id']
        ];

        try {
            $kpiId = $db->insert('kpis', $data);

            // Create quarterly actual records
            $financialYear = $db->fetch(
                "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
            );

            if ($financialYear) {
                $targets = ['q1_target', 'q2_target', 'q3_target', 'q4_target'];
                foreach ($targets as $i => $field) {
                    $quarter = $i + 1;
                    $db->insert('kpi_quarterly_actuals', [
                        'kpi_id' => $kpiId,
                        'quarter' => $quarter,
                        'financial_year_id' => $financialYear['id'],
                        'target_value' => $data[$field],
                        'status' => 'draft'
                    ]);
                }
            }

            // Audit log
            $db->insert('audit_log', [
                'user_id' => user()['id'],
                'action' => 'create',
                'table_name' => 'kpis',
                'record_id' => $kpiId,
                'new_values' => json_encode($data)
            ]);

            flash('success', 'KPI created successfully');
            redirect('/sdbip/kpis/' . $kpiId);
        } catch (Exception $e) {
            flash('error', 'Failed to create KPI: ' . $e->getMessage());
            $_SESSION['old'] = $_POST;
            redirect('/sdbip/kpis/create');
        }
    }

    public function showKPI(string $id): void {
        $db = db();

        $kpi = $db->fetch("
            SELECT
                k.*,
                so.objective_name,
                so.objective_code,
                so.national_priority_alignment,
                d.name as directorate_name,
                d.code as directorate_code,
                u.first_name as responsible_first_name,
                u.last_name as responsible_last_name,
                u.job_title as responsible_job_title
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN users u ON u.id = k.responsible_user_id
            WHERE k.id = ?
        ", [$id]);

        if (!$kpi) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        // Get quarterly actuals
        $quarterlies = $db->fetchAll("
            SELECT qa.*,
                   su.first_name as self_submitter_name,
                   mu.first_name as manager_name,
                   iu.first_name as independent_name
            FROM kpi_quarterly_actuals qa
            LEFT JOIN users su ON su.id = qa.self_submitted_by
            LEFT JOIN users mu ON mu.id = qa.manager_user_id
            LEFT JOIN users iu ON iu.id = qa.independent_user_id
            WHERE qa.kpi_id = ?
            ORDER BY qa.quarter
        ", [$id]);

        // Get POE for each quarterly
        foreach ($quarterlies as &$q) {
            $q['poe'] = $db->fetchAll("
                SELECT * FROM proof_of_evidence
                WHERE kpi_quarterly_id = ? AND is_active = 1
                ORDER BY upload_date DESC
            ", [$q['id']]);
        }

        $data = [
            'title' => $kpi['kpi_code'] . ' - ' . $kpi['kpi_name'],
            'breadcrumbs' => [
                ['label' => 'SDBIP', 'url' => '/sdbip'],
                ['label' => 'KPIs', 'url' => '/sdbip/kpis'],
                ['label' => $kpi['kpi_code']]
            ],
            'kpi' => $kpi,
            'quarterlies' => $quarterlies
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/kpi-detail.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function targets(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $quarter = $_GET['quarter'] ?? current_quarter();
        $directorateId = $_GET['directorate'] ?? null;

        $where = ['so.financial_year_id = ?', 'k.is_active = 1', 'qa.quarter = ?'];
        $params = [$fyId, $quarter];

        if ($directorateId) {
            $where[] = 'k.directorate_id = ?';
            $params[] = $directorateId;
        }

        $whereClause = implode(' AND ', $where);

        $targets = $db->fetchAll("
            SELECT
                k.id as kpi_id,
                k.kpi_code,
                k.kpi_name,
                k.sla_category,
                d.code as directorate_code,
                qa.id as quarterly_id,
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
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE {$whereClause}
            ORDER BY d.code, k.kpi_code
        ", array_merge([$fyId], $params));

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Quarterly Targets',
            'breadcrumbs' => [
                ['label' => 'SDBIP', 'url' => '/sdbip'],
                ['label' => 'Quarterly Targets']
            ],
            'financialYear' => $financialYear,
            'targets' => $targets,
            'directorates' => $directorates,
            'selectedQuarter' => $quarter,
            'selectedDirectorate' => $directorateId
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/targets.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function editKPI(string $id): void {
        $db = db();

        $kpi = $db->fetch("SELECT * FROM kpis WHERE id = ?", [$id]);
        if (!$kpi) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $objectives = $db->fetchAll("
            SELECT so.id, so.objective_code, so.objective_name, d.name as directorate_name
            FROM idp_strategic_objectives so
            JOIN directorates d ON d.id = so.directorate_id
            WHERE so.financial_year_id = ? AND so.is_active = 1
            ORDER BY d.name, so.objective_code
        ", [$fyId]);

        $users = $db->fetchAll("
            SELECT id, first_name, last_name, job_title
            FROM users WHERE is_active = 1
            ORDER BY first_name
        ");

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Edit KPI',
            'breadcrumbs' => [
                ['label' => 'SDBIP', 'url' => '/sdbip'],
                ['label' => 'KPIs', 'url' => '/sdbip/kpis'],
                ['label' => 'Edit']
            ],
            'kpi' => $kpi,
            'objectives' => $objectives,
            'users' => $users,
            'directorates' => $directorates
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/sdbip/kpi-form.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function updateKPI(string $id): void {
        $db = db();

        $old = $db->fetch("SELECT * FROM kpis WHERE id = ?", [$id]);
        if (!$old) {
            flash('error', 'KPI not found');
            redirect('/sdbip/kpis');
            return;
        }

        $data = [
            'strategic_objective_id' => (int)$_POST['strategic_objective_id'],
            'kpi_code' => trim($_POST['kpi_code']),
            'kpi_name' => trim($_POST['kpi_name']),
            'description' => trim($_POST['description'] ?? ''),
            'unit_of_measure' => trim($_POST['unit_of_measure']),
            'baseline' => trim($_POST['baseline'] ?? ''),
            'annual_target' => trim($_POST['annual_target']),
            'q1_target' => trim($_POST['q1_target'] ?? ''),
            'q2_target' => trim($_POST['q2_target'] ?? ''),
            'q3_target' => trim($_POST['q3_target'] ?? ''),
            'q4_target' => trim($_POST['q4_target'] ?? ''),
            'sla_category' => $_POST['sla_category'] ?? 'none',
            'budget_required' => (float)($_POST['budget_required'] ?? 0),
            'budget_allocated' => (float)($_POST['budget_allocated'] ?? 0),
            'responsible_user_id' => (int)$_POST['responsible_user_id'] ?: null,
            'directorate_id' => (int)$_POST['directorate_id'],
            'data_source' => trim($_POST['data_source'] ?? ''),
            'calculation_method' => trim($_POST['calculation_method'] ?? ''),
            'reporting_frequency' => $_POST['reporting_frequency'] ?? 'quarterly',
            'is_strategic' => isset($_POST['is_strategic']) ? 1 : 0
        ];

        try {
            $db->update('kpis', $data, 'id = ?', [$id]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => user()['id'],
                'action' => 'update',
                'table_name' => 'kpis',
                'record_id' => $id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($data)
            ]);

            flash('success', 'KPI updated successfully');
            redirect('/sdbip/kpis/' . $id);
        } catch (Exception $e) {
            flash('error', 'Failed to update KPI: ' . $e->getMessage());
            redirect('/sdbip/kpis/' . $id . '/edit');
        }
    }

    public function deleteKPI(string $id): void {
        $db = db();

        $kpi = $db->fetch("SELECT * FROM kpis WHERE id = ?", [$id]);
        if (!$kpi) {
            flash('error', 'KPI not found');
            redirect('/sdbip/kpis');
            return;
        }

        try {
            $db->update('kpis', ['is_active' => 0], 'id = ?', [$id]);

            $db->insert('audit_log', [
                'user_id' => user()['id'],
                'action' => 'delete',
                'table_name' => 'kpis',
                'record_id' => $id,
                'old_values' => json_encode($kpi)
            ]);

            flash('success', 'KPI deleted successfully');
        } catch (Exception $e) {
            flash('error', 'Failed to delete KPI');
        }

        redirect('/sdbip/kpis');
    }
}
