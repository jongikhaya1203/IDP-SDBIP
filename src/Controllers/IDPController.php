<?php
/**
 * IDP Controller
 * Manages Integrated Development Plan Strategic Objectives
 */

class IDPController {

    public function index(): void {
        $db = db();
        $fy = current_financial_year();

        // Get current financial year from DB
        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );

        if (!$financialYear) {
            $financialYear = ['id' => 0, 'year_label' => $fy['label']];
        }

        // Get objectives summary
        $objectives = $db->fetchAll(
            "SELECT so.*, d.name as directorate_name, d.code as directorate_code,
                    (SELECT COUNT(*) FROM kpis WHERE strategic_objective_id = so.id) as kpi_count
             FROM idp_strategic_objectives so
             LEFT JOIN directorates d ON d.id = so.directorate_id
             WHERE so.financial_year_id = ?
             ORDER BY so.objective_code",
            [$financialYear['id']]
        );

        view('idp.index', [
            'objectives' => $objectives,
            'financialYear' => $financialYear,
            'title' => 'IDP Strategic Objectives'
        ]);
    }

    public function objectives(): void {
        $this->index();
    }

    public function create(): void {
        $db = db();

        $financialYear = $db->fetch("SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1");
        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");

        view('idp.create', [
            'financialYear' => $financialYear,
            'directorates' => $directorates,
            'title' => 'Create Strategic Objective'
        ]);
    }

    public function store(): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/idp/objectives');
            return;
        }

        $db = db();

        $data = [
            'financial_year_id' => $_POST['financial_year_id'] ?? null,
            'objective_code' => trim($_POST['objective_code'] ?? ''),
            'objective_name' => trim($_POST['objective_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'directorate_id' => $_POST['directorate_id'] ?? null,
            'weight' => floatval($_POST['weight'] ?? 0),
            'national_priority_alignment' => trim($_POST['national_priority_alignment'] ?? ''),
            'provincial_priority_alignment' => trim($_POST['provincial_priority_alignment'] ?? ''),
            'idp_goal' => trim($_POST['idp_goal'] ?? ''),
            'is_active' => 1,
            'created_by' => user()['id']
        ];

        try {
            $db->insert('idp_strategic_objectives', $data);
            flash('success', 'Strategic Objective created successfully');
        } catch (Exception $e) {
            flash('error', 'Failed to create objective: ' . $e->getMessage());
        }

        redirect('/idp/objectives');
    }

    public function show(string $id): void {
        $db = db();

        $objective = $db->fetch(
            "SELECT so.*, d.name as directorate_name, fy.year_label
             FROM idp_strategic_objectives so
             LEFT JOIN directorates d ON d.id = so.directorate_id
             LEFT JOIN financial_years fy ON fy.id = so.financial_year_id
             WHERE so.id = ?",
            [$id]
        );

        if (!$objective) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        // Get KPIs linked to this objective
        $kpis = $db->fetchAll(
            "SELECT k.*, u.first_name, u.last_name
             FROM kpis k
             LEFT JOIN users u ON u.id = k.responsible_user_id
             WHERE k.strategic_objective_id = ?
             ORDER BY k.kpi_code",
            [$id]
        );

        view('idp.show', [
            'objective' => $objective,
            'kpis' => $kpis,
            'title' => $objective['objective_name']
        ]);
    }

    public function edit(string $id): void {
        $db = db();

        $objective = $db->fetch("SELECT * FROM idp_strategic_objectives WHERE id = ?", [$id]);

        if (!$objective) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $financialYears = $db->fetchAll("SELECT * FROM financial_years ORDER BY start_date DESC");
        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");

        view('idp.edit', [
            'objective' => $objective,
            'financialYears' => $financialYears,
            'directorates' => $directorates,
            'title' => 'Edit Strategic Objective'
        ]);
    }

    public function update(string $id): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/idp/objectives/' . $id . '/edit');
            return;
        }

        $db = db();

        $data = [
            'objective_code' => trim($_POST['objective_code'] ?? ''),
            'objective_name' => trim($_POST['objective_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'directorate_id' => $_POST['directorate_id'] ?? null,
            'weight' => floatval($_POST['weight'] ?? 0),
            'national_priority_alignment' => trim($_POST['national_priority_alignment'] ?? ''),
            'provincial_priority_alignment' => trim($_POST['provincial_priority_alignment'] ?? ''),
            'idp_goal' => trim($_POST['idp_goal'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        try {
            $db->update('idp_strategic_objectives', $data, 'id = ?', [$id]);
            flash('success', 'Strategic Objective updated successfully');
        } catch (Exception $e) {
            flash('error', 'Failed to update objective: ' . $e->getMessage());
        }

        redirect('/idp/objectives');
    }

    public function delete(string $id): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/idp/objectives');
            return;
        }

        $db = db();

        try {
            // Check if there are linked KPIs
            $kpiCount = $db->fetch("SELECT COUNT(*) as cnt FROM kpis WHERE strategic_objective_id = ?", [$id]);

            if ($kpiCount['cnt'] > 0) {
                flash('error', 'Cannot delete objective with linked KPIs. Remove KPIs first.');
            } else {
                $db->delete('idp_strategic_objectives', 'id = ?', [$id]);
                flash('success', 'Strategic Objective deleted successfully');
            }
        } catch (Exception $e) {
            flash('error', 'Failed to delete objective: ' . $e->getMessage());
        }

        redirect('/idp/objectives');
    }
}
