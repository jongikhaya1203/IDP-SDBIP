<?php
/**
 * POE (Proof of Evidence) Controller
 * Handles file uploads and review workflow
 */

class POEController {

    public function index(): void {
        $db = db();
        $user = user();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $quarter = $_GET['quarter'] ?? current_quarter();
        $status = $_GET['status'] ?? '';

        $where = ['so.financial_year_id = ?', 'qa.quarter = ?'];
        $params = [$fyId, $quarter];

        if (!has_role('admin', 'independent_assessor') && $user['directorate_id']) {
            $where[] = 'k.directorate_id = ?';
            $params[] = $user['directorate_id'];
        }

        if ($status) {
            $where[] = '(poe.manager_status = ? OR poe.independent_status = ?)';
            $params[] = $status;
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        $poeItems = $db->fetchAll("
            SELECT
                poe.*,
                k.kpi_code,
                k.kpi_name,
                d.code as directorate_code,
                qa.quarter,
                u.first_name as uploader_name,
                u.last_name as uploader_surname
            FROM proof_of_evidence poe
            JOIN kpi_quarterly_actuals qa ON qa.id = poe.kpi_quarterly_id
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN users u ON u.id = poe.uploaded_by
            WHERE poe.is_active = 1 AND {$whereClause}
            ORDER BY poe.upload_date DESC
        ", $params);

        $data = [
            'title' => 'Proof of Evidence',
            'breadcrumbs' => [['label' => 'Proof of Evidence']],
            'financialYear' => $financialYear,
            'poeItems' => $poeItems,
            'selectedQuarter' => $quarter,
            'selectedStatus' => $status
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/poe/index.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function showUpload(string $quarterlyId): void {
        $db = db();

        $quarterly = $db->fetch("
            SELECT qa.*, k.kpi_code, k.kpi_name, d.name as directorate_name
            FROM kpi_quarterly_actuals qa
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN directorates d ON d.id = k.directorate_id
            WHERE qa.id = ?
        ", [$quarterlyId]);

        if (!$quarterly) {
            flash('error', 'Assessment not found');
            redirect('/poe');
            return;
        }

        // Get existing POE
        $existingPoe = $db->fetchAll("
            SELECT * FROM proof_of_evidence
            WHERE kpi_quarterly_id = ? AND is_active = 1
            ORDER BY upload_date DESC
        ", [$quarterlyId]);

        $data = [
            'title' => 'Upload POE',
            'breadcrumbs' => [
                ['label' => 'Proof of Evidence', 'url' => '/poe'],
                ['label' => 'Upload']
            ],
            'quarterly' => $quarterly,
            'existingPoe' => $existingPoe
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/poe/upload.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function upload(string $quarterlyId): void {
        $db = db();
        $user = user();

        $quarterly = $db->fetch("SELECT * FROM kpi_quarterly_actuals WHERE id = ?", [$quarterlyId]);
        if (!$quarterly) {
            flash('error', 'Assessment not found');
            redirect('/poe');
            return;
        }

        if (!isset($_FILES['poe_file']) || $_FILES['poe_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Please select a file to upload');
            redirect('/poe/upload/' . $quarterlyId);
            return;
        }

        $file = $_FILES['poe_file'];

        // Validate file size
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            flash('error', 'File size exceeds maximum allowed (' . (UPLOAD_MAX_SIZE / 1048576) . 'MB)');
            redirect('/poe/upload/' . $quarterlyId);
            return;
        }

        // Validate file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, UPLOAD_ALLOWED_TYPES)) {
            flash('error', 'File type not allowed. Allowed types: ' . implode(', ', UPLOAD_ALLOWED_TYPES));
            redirect('/poe/upload/' . $quarterlyId);
            return;
        }

        // Generate unique filename
        $filename = sprintf(
            'poe_%s_%s_%s.%s',
            $quarterlyId,
            date('Ymd_His'),
            bin2hex(random_bytes(4)),
            $extension
        );

        $uploadPath = POE_PATH . '/' . $filename;

        // Create directory if not exists
        if (!is_dir(POE_PATH)) {
            mkdir(POE_PATH, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            flash('error', 'Failed to save file');
            redirect('/poe/upload/' . $quarterlyId);
            return;
        }

        // Check if this is a resubmission
        $parentPoeId = $_POST['parent_poe_id'] ?? null;
        $version = 1;

        if ($parentPoeId) {
            $parentPoe = $db->fetch("SELECT version FROM proof_of_evidence WHERE id = ?", [$parentPoeId]);
            if ($parentPoe) {
                $version = $parentPoe['version'] + 1;
            }
        }

        try {
            $poeId = $db->insert('proof_of_evidence', [
                'kpi_quarterly_id' => $quarterlyId,
                'file_name' => $filename,
                'original_name' => $file['name'],
                'file_path' => 'uploads/poe/' . $filename,
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'description' => trim($_POST['description'] ?? ''),
                'uploaded_by' => $user['id'],
                'version' => $version,
                'parent_poe_id' => $parentPoeId
            ]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => 'create',
                'table_name' => 'proof_of_evidence',
                'record_id' => $poeId
            ]);

            flash('success', 'POE uploaded successfully');
            redirect('/poe/upload/' . $quarterlyId);
        } catch (Exception $e) {
            // Delete uploaded file on error
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            flash('error', 'Failed to save POE record');
            redirect('/poe/upload/' . $quarterlyId);
        }
    }

    public function show(string $id): void {
        $db = db();

        $poe = $db->fetch("
            SELECT
                poe.*,
                k.kpi_code,
                k.kpi_name,
                qa.quarter,
                d.name as directorate_name,
                u.first_name as uploader_first, u.last_name as uploader_last,
                mr.first_name as manager_reviewer_first, mr.last_name as manager_reviewer_last,
                ir.first_name as independent_reviewer_first, ir.last_name as independent_reviewer_last
            FROM proof_of_evidence poe
            JOIN kpi_quarterly_actuals qa ON qa.id = poe.kpi_quarterly_id
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN users u ON u.id = poe.uploaded_by
            LEFT JOIN users mr ON mr.id = poe.manager_reviewed_by
            LEFT JOIN users ir ON ir.id = poe.independent_reviewed_by
            WHERE poe.id = ?
        ", [$id]);

        if (!$poe) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $data = [
            'title' => 'POE Details',
            'breadcrumbs' => [
                ['label' => 'Proof of Evidence', 'url' => '/poe'],
                ['label' => $poe['original_name']]
            ],
            'poe' => $poe
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/poe/detail.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function download(string $id): void {
        $db = db();

        $poe = $db->fetch("SELECT * FROM proof_of_evidence WHERE id = ? AND is_active = 1", [$id]);

        if (!$poe) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        $filePath = PUBLIC_PATH . '/' . $poe['file_path'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'File not found on disk';
            return;
        }

        header('Content-Type: ' . $poe['file_type']);
        header('Content-Disposition: attachment; filename="' . $poe['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');

        readfile($filePath);
        exit;
    }

    public function accept(string $id): void {
        $this->updateStatus($id, 'accepted');
    }

    public function reject(string $id): void {
        $this->updateStatus($id, 'rejected');
    }

    private function updateStatus(string $id, string $status): void {
        $db = db();
        $user = user();

        $poe = $db->fetch("SELECT * FROM proof_of_evidence WHERE id = ?", [$id]);
        if (!$poe) {
            if (is_ajax()) {
                json_response(['error' => 'POE not found'], 404);
            }
            flash('error', 'POE not found');
            redirect('/poe');
            return;
        }

        $feedback = trim($_POST['feedback'] ?? '');
        $reviewerType = $_POST['reviewer_type'] ?? 'manager';

        $data = [];
        if ($reviewerType === 'independent' && has_role('admin', 'independent_assessor')) {
            $data = [
                'independent_status' => $status,
                'independent_feedback' => $feedback,
                'independent_reviewed_by' => $user['id'],
                'independent_reviewed_at' => date('Y-m-d H:i:s')
            ];
        } else {
            $data = [
                'manager_status' => $status,
                'manager_feedback' => $feedback,
                'manager_reviewed_by' => $user['id'],
                'manager_reviewed_at' => date('Y-m-d H:i:s')
            ];
        }

        if ($status === 'rejected') {
            $data['resubmission_required'] = 1;
            $data['resubmission_deadline'] = date('Y-m-d', strtotime('+' . POE_RESUBMISSION_DAYS . ' days'));
        }

        try {
            $db->update('proof_of_evidence', $data, 'id = ?', [$id]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => $status === 'accepted' ? 'approve' : 'reject',
                'table_name' => 'proof_of_evidence',
                'record_id' => $id,
                'old_values' => json_encode($poe),
                'new_values' => json_encode($data)
            ]);

            // Notify uploader if rejected
            if ($status === 'rejected') {
                $kpiInfo = $db->fetch("
                    SELECT k.kpi_code, qa.quarter
                    FROM proof_of_evidence poe
                    JOIN kpi_quarterly_actuals qa ON qa.id = poe.kpi_quarterly_id
                    JOIN kpis k ON k.id = qa.kpi_id
                    WHERE poe.id = ?
                ", [$id]);

                $db->insert('notifications', [
                    'user_id' => $poe['uploaded_by'],
                    'type' => 'warning',
                    'title' => 'POE Rejected',
                    'message' => "Your POE for {$kpiInfo['kpi_code']} Q{$kpiInfo['quarter']} was rejected. Feedback: {$feedback}. Please resubmit by " . date('d M Y', strtotime('+' . POE_RESUBMISSION_DAYS . ' days')),
                    'link' => '/poe/upload/' . $poe['kpi_quarterly_id']
                ]);
            }

            if (is_ajax()) {
                json_response(['success' => true, 'message' => 'POE ' . $status]);
            }
            flash('success', 'POE ' . $status . ' successfully');
        } catch (Exception $e) {
            if (is_ajax()) {
                json_response(['error' => 'Failed to update POE'], 500);
            }
            flash('error', 'Failed to update POE');
        }

        redirect($_SERVER['HTTP_REFERER'] ?? '/poe');
    }

    public function review(): void {
        $db = db();
        $user = user();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $quarter = $_GET['quarter'] ?? current_quarter();

        $where = ['so.financial_year_id = ?', 'qa.quarter = ?', 'poe.is_active = 1'];
        $params = [$fyId, $quarter];

        // Filter by pending status based on role
        if (has_role('independent_assessor')) {
            $where[] = "poe.independent_status = 'pending'";
        } else {
            $where[] = "poe.manager_status = 'pending'";
            if ($user['directorate_id']) {
                $where[] = 'k.directorate_id = ?';
                $params[] = $user['directorate_id'];
            }
        }

        $whereClause = implode(' AND ', $where);

        $poeItems = $db->fetchAll("
            SELECT
                poe.*,
                k.kpi_code,
                k.kpi_name,
                k.sla_category,
                d.code as directorate_code,
                d.name as directorate_name,
                qa.quarter,
                qa.actual_value,
                qa.target_value,
                u.first_name as uploader_name,
                u.last_name as uploader_surname
            FROM proof_of_evidence poe
            JOIN kpi_quarterly_actuals qa ON qa.id = poe.kpi_quarterly_id
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN users u ON u.id = poe.uploaded_by
            WHERE {$whereClause}
            ORDER BY poe.upload_date ASC
        ", $params);

        $data = [
            'title' => 'POE Review Queue',
            'breadcrumbs' => [
                ['label' => 'Proof of Evidence', 'url' => '/poe'],
                ['label' => 'Review']
            ],
            'financialYear' => $financialYear,
            'poeItems' => $poeItems,
            'selectedQuarter' => $quarter,
            'isIndependent' => has_role('independent_assessor')
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/poe/review.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }
}
