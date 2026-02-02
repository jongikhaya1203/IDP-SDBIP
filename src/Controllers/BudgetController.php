<?php
/**
 * Budget Controller
 * Manages budget projections and capital projects
 */

class BudgetController {

    public function index(): void {
        $db = db();

        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");

        // Get budget summary by directorate
        $summary = $db->fetchAll(
            "SELECT d.id, d.name, d.code, d.budget_allocation,
                    COALESCE(SUM(bp.projected_revenue), 0) as total_projected_revenue,
                    COALESCE(SUM(bp.actual_revenue), 0) as total_actual_revenue,
                    COALESCE(SUM(bp.operating_expenditure_projected), 0) as total_opex_projected,
                    COALESCE(SUM(bp.operating_expenditure_actual), 0) as total_opex_actual
             FROM directorates d
             LEFT JOIN budget_projections bp ON bp.directorate_id = d.id AND bp.financial_year_id = ?
             WHERE d.is_active = 1
             GROUP BY d.id
             ORDER BY d.name",
            [$financialYear['id'] ?? 0]
        );

        // Get capital projects summary
        $projectsSummary = $db->fetch(
            "SELECT COUNT(*) as total_projects,
                    SUM(total_budget) as total_budget,
                    SUM(q1_spent + q2_spent + q3_spent + q4_spent) as total_spent,
                    AVG(completion_percentage) as avg_completion
             FROM capital_projects
             WHERE financial_year_id = ?",
            [$financialYear['id'] ?? 0]
        );

        view('budget.index', [
            'financialYear' => $financialYear,
            'summary' => $summary,
            'projectsSummary' => $projectsSummary,
            'title' => 'Budget Overview'
        ]);
    }

    public function projections(): void {
        $db = db();

        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");
        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");

        $selectedDirectorate = $_GET['directorate_id'] ?? null;

        $where = "bp.financial_year_id = ?";
        $params = [$financialYear['id'] ?? 0];

        if ($selectedDirectorate) {
            $where .= " AND bp.directorate_id = ?";
            $params[] = $selectedDirectorate;
        }

        $projections = $db->fetchAll(
            "SELECT bp.*, d.name as directorate_name, d.code as directorate_code
             FROM budget_projections bp
             JOIN directorates d ON d.id = bp.directorate_id
             WHERE $where
             ORDER BY bp.directorate_id, bp.month",
            $params
        );

        // Group by directorate
        $grouped = [];
        foreach ($projections as $p) {
            $grouped[$p['directorate_id']]['name'] = $p['directorate_name'];
            $grouped[$p['directorate_id']]['code'] = $p['directorate_code'];
            $grouped[$p['directorate_id']]['months'][$p['month']] = $p;
        }

        view('budget.projections', [
            'financialYear' => $financialYear,
            'directorates' => $directorates,
            'grouped' => $grouped,
            'selectedDirectorate' => $selectedDirectorate,
            'title' => 'Budget Projections'
        ]);
    }

    public function updateProjections(): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/budget/projections');
            return;
        }

        $db = db();
        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");

        foreach ($_POST['projections'] ?? [] as $id => $data) {
            $db->update('budget_projections', [
                'actual_revenue' => floatval($data['actual_revenue'] ?? 0),
                'operating_expenditure_actual' => floatval($data['opex_actual'] ?? 0),
                'capital_expenditure_actual' => floatval($data['capex_actual'] ?? 0)
            ], 'id = ?', [$id]);
        }

        flash('success', 'Budget projections updated successfully');
        redirect('/budget/projections');
    }

    public function projects(): void {
        $db = db();

        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");

        $projects = $db->fetchAll(
            "SELECT cp.*, d.name as directorate_name, d.code as directorate_code
             FROM capital_projects cp
             JOIN directorates d ON d.id = cp.directorate_id
             WHERE cp.financial_year_id = ?
             ORDER BY cp.project_code",
            [$financialYear['id'] ?? 0]
        );

        view('budget.projects', [
            'financialYear' => $financialYear,
            'projects' => $projects,
            'title' => 'Capital Projects'
        ]);
    }

    public function createProject(): void {
        $db = db();

        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");
        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");

        view('budget.create-project', [
            'financialYear' => $financialYear,
            'directorates' => $directorates,
            'title' => 'New Capital Project'
        ]);
    }

    public function storeProject(): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/budget/projects');
            return;
        }

        $db = db();
        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");

        $data = [
            'financial_year_id' => $financialYear['id'],
            'directorate_id' => $_POST['directorate_id'],
            'project_name' => trim($_POST['project_name'] ?? ''),
            'project_code' => trim($_POST['project_code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'total_budget' => floatval($_POST['total_budget'] ?? 0),
            'q1_budget' => floatval($_POST['q1_budget'] ?? 0),
            'q2_budget' => floatval($_POST['q2_budget'] ?? 0),
            'q3_budget' => floatval($_POST['q3_budget'] ?? 0),
            'q4_budget' => floatval($_POST['q4_budget'] ?? 0),
            'status' => 'planning',
            'completion_percentage' => 0
        ];

        try {
            $db->insert('capital_projects', $data);
            flash('success', 'Capital project created successfully');
        } catch (Exception $e) {
            flash('error', 'Failed to create project: ' . $e->getMessage());
        }

        redirect('/budget/projects');
    }

    public function showProject(string $id): void {
        $db = db();

        $project = $db->fetch(
            "SELECT cp.*, d.name as directorate_name, d.code as directorate_code
             FROM capital_projects cp
             JOIN directorates d ON d.id = cp.directorate_id
             WHERE cp.id = ?",
            [$id]
        );

        if (!$project) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        view('budget.project-detail', [
            'project' => $project,
            'title' => $project['project_name']
        ]);
    }

    public function editProject(string $id): void {
        $db = db();

        $project = $db->fetch("SELECT * FROM capital_projects WHERE id = ?", [$id]);

        if (!$project) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");

        view('budget.edit-project', [
            'project' => $project,
            'directorates' => $directorates,
            'title' => 'Edit Project'
        ]);
    }

    public function updateProject(string $id): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/budget/projects/' . $id . '/edit');
            return;
        }

        $db = db();

        $data = [
            'directorate_id' => $_POST['directorate_id'],
            'project_name' => trim($_POST['project_name'] ?? ''),
            'project_code' => trim($_POST['project_code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'total_budget' => floatval($_POST['total_budget'] ?? 0),
            'q1_budget' => floatval($_POST['q1_budget'] ?? 0),
            'q2_budget' => floatval($_POST['q2_budget'] ?? 0),
            'q3_budget' => floatval($_POST['q3_budget'] ?? 0),
            'q4_budget' => floatval($_POST['q4_budget'] ?? 0),
            'q1_spent' => floatval($_POST['q1_spent'] ?? 0),
            'q2_spent' => floatval($_POST['q2_spent'] ?? 0),
            'q3_spent' => floatval($_POST['q3_spent'] ?? 0),
            'q4_spent' => floatval($_POST['q4_spent'] ?? 0),
            'status' => $_POST['status'] ?? 'planning',
            'completion_percentage' => intval($_POST['completion_percentage'] ?? 0)
        ];

        try {
            $db->update('capital_projects', $data, 'id = ?', [$id]);
            flash('success', 'Project updated successfully');
        } catch (Exception $e) {
            flash('error', 'Failed to update project: ' . $e->getMessage());
        }

        redirect('/budget/projects');
    }
}
