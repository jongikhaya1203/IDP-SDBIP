<?php
/**
 * Assessment Controller
 * Handles quarterly assessments with multi-level ratings
 */

class AssessmentController {

    public function index(): void {
        redirect('/assessment/self');
    }

    public function selfAssessment(): void {
        $db = db();
        $user = user();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;
        $currentQuarter = current_quarter();

        // Get KPIs assigned to or accessible by current user
        $where = ['so.financial_year_id = ?', 'k.is_active = 1'];
        $params = [$fyId];

        if (!has_role('admin', 'director')) {
            if ($user['directorate_id']) {
                $where[] = 'k.directorate_id = ?';
                $params[] = $user['directorate_id'];
            }
        }

        $quarter = $_GET['quarter'] ?? $currentQuarter;
        $status = $_GET['status'] ?? '';

        $where[] = 'qa.quarter = ?';
        $params[] = $quarter;

        if ($status) {
            $where[] = 'qa.status = ?';
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        $assessments = $db->fetchAll("
            SELECT
                k.id as kpi_id,
                k.kpi_code,
                k.kpi_name,
                k.unit_of_measure,
                k.sla_category,
                d.code as directorate_code,
                d.name as directorate_name,
                qa.id as quarterly_id,
                qa.quarter,
                qa.target_value,
                qa.actual_value,
                qa.self_rating,
                qa.self_comments,
                qa.status,
                qa.achievement_status,
                (SELECT COUNT(*) FROM proof_of_evidence WHERE kpi_quarterly_id = qa.id AND is_active = 1) as poe_count
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE {$whereClause}
            ORDER BY d.code, k.kpi_code
        ", array_merge([$fyId], $params));

        $data = [
            'title' => 'Self Assessment',
            'breadcrumbs' => [
                ['label' => 'Assessment', 'url' => '/assessment'],
                ['label' => 'Self Assessment']
            ],
            'financialYear' => $financialYear,
            'assessments' => $assessments,
            'selectedQuarter' => $quarter,
            'selectedStatus' => $status,
            'currentQuarter' => $currentQuarter
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/assessment/self.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function submitSelfAssessment(string $id): void {
        $db = db();
        $user = user();

        $quarterly = $db->fetch("SELECT * FROM kpi_quarterly_actuals WHERE id = ?", [$id]);
        if (!$quarterly) {
            if (is_ajax()) {
                json_response(['error' => 'Assessment not found'], 404);
            }
            flash('error', 'Assessment not found');
            redirect('/assessment/self');
            return;
        }

        $actualValue = trim($_POST['actual_value'] ?? '');
        $selfRating = (int)$_POST['self_rating'];
        $selfComments = trim($_POST['self_comments'] ?? '');

        // Validate rating
        if ($selfRating < 1 || $selfRating > 5) {
            if (is_ajax()) {
                json_response(['error' => 'Rating must be between 1 and 5'], 400);
            }
            flash('error', 'Rating must be between 1 and 5');
            redirect('/assessment/self');
            return;
        }

        // Calculate variance and achievement status
        $targetValue = $quarterly['target_value'];
        $variance = null;
        $achievementStatus = 'pending';

        if (is_numeric($actualValue) && is_numeric($targetValue) && $targetValue != 0) {
            $variance = (($actualValue - $targetValue) / abs($targetValue)) * 100;

            if ($variance >= 0) {
                $achievementStatus = 'achieved';
            } elseif ($variance >= -10) {
                $achievementStatus = 'partially_achieved';
            } else {
                $achievementStatus = 'not_achieved';
            }
        }

        $data = [
            'actual_value' => $actualValue,
            'variance' => $variance,
            'achievement_status' => $achievementStatus,
            'self_rating' => $selfRating,
            'self_comments' => $selfComments,
            'self_submitted_at' => date('Y-m-d H:i:s'),
            'self_submitted_by' => $user['id'],
            'status' => 'submitted'
        ];

        try {
            $db->update('kpi_quarterly_actuals', $data, 'id = ?', [$id]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => 'update',
                'table_name' => 'kpi_quarterly_actuals',
                'record_id' => $id,
                'old_values' => json_encode($quarterly),
                'new_values' => json_encode($data)
            ]);

            // Create notification for manager
            $kpi = $db->fetch("
                SELECT k.kpi_code, d.head_user_id
                FROM kpis k
                JOIN directorates d ON d.id = k.directorate_id
                WHERE k.id = ?
            ", [$quarterly['kpi_id']]);

            if ($kpi && $kpi['head_user_id']) {
                $db->insert('notifications', [
                    'user_id' => $kpi['head_user_id'],
                    'type' => 'review',
                    'title' => 'Self Assessment Submitted',
                    'message' => "Self assessment for {$kpi['kpi_code']} Q{$quarterly['quarter']} requires your review",
                    'link' => '/assessment/manager?quarter=' . $quarterly['quarter']
                ]);
            }

            if (is_ajax()) {
                json_response(['success' => true, 'message' => 'Assessment submitted successfully']);
            }
            flash('success', 'Self assessment submitted successfully');
        } catch (Exception $e) {
            if (is_ajax()) {
                json_response(['error' => 'Failed to submit assessment'], 500);
            }
            flash('error', 'Failed to submit assessment');
        }

        redirect('/assessment/self');
    }

    public function managerReview(): void {
        $db = db();
        $user = user();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $quarter = $_GET['quarter'] ?? current_quarter();
        $directorateId = $_GET['directorate'] ?? $user['directorate_id'];

        $where = ['so.financial_year_id = ?', 'k.is_active = 1', 'qa.quarter = ?'];
        $params = [$fyId, $quarter];

        if (!has_role('admin') && $directorateId) {
            $where[] = 'k.directorate_id = ?';
            $params[] = $directorateId;
        }

        // Show only submitted items that need manager review
        $where[] = "qa.status IN ('submitted', 'manager_review')";

        $whereClause = implode(' AND ', $where);

        $assessments = $db->fetchAll("
            SELECT
                k.id as kpi_id,
                k.kpi_code,
                k.kpi_name,
                k.unit_of_measure,
                k.sla_category,
                d.code as directorate_code,
                qa.id as quarterly_id,
                qa.quarter,
                qa.target_value,
                qa.actual_value,
                qa.variance,
                qa.achievement_status,
                qa.self_rating,
                qa.self_comments,
                qa.manager_rating,
                qa.manager_comments,
                qa.status,
                su.first_name as submitter_name,
                (SELECT COUNT(*) FROM proof_of_evidence WHERE kpi_quarterly_id = qa.id AND is_active = 1) as poe_count,
                (SELECT COUNT(*) FROM proof_of_evidence WHERE kpi_quarterly_id = qa.id AND is_active = 1 AND manager_status = 'pending') as pending_poe
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            LEFT JOIN users su ON su.id = qa.self_submitted_by
            WHERE {$whereClause}
            ORDER BY qa.status DESC, d.code, k.kpi_code
        ", array_merge([$fyId], $params));

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Manager Review',
            'breadcrumbs' => [
                ['label' => 'Assessment', 'url' => '/assessment'],
                ['label' => 'Manager Review']
            ],
            'financialYear' => $financialYear,
            'assessments' => $assessments,
            'directorates' => $directorates,
            'selectedQuarter' => $quarter,
            'selectedDirectorate' => $directorateId
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/assessment/manager.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function submitManagerReview(string $id): void {
        $db = db();
        $user = user();

        $quarterly = $db->fetch("SELECT * FROM kpi_quarterly_actuals WHERE id = ?", [$id]);
        if (!$quarterly) {
            if (is_ajax()) {
                json_response(['error' => 'Assessment not found'], 404);
            }
            flash('error', 'Assessment not found');
            redirect('/assessment/manager');
            return;
        }

        $managerRating = (int)$_POST['manager_rating'];
        $managerComments = trim($_POST['manager_comments'] ?? '');
        $action = $_POST['action'] ?? 'approve';

        if ($managerRating < 1 || $managerRating > 5) {
            if (is_ajax()) {
                json_response(['error' => 'Rating must be between 1 and 5'], 400);
            }
            flash('error', 'Rating must be between 1 and 5');
            redirect('/assessment/manager');
            return;
        }

        $data = [
            'manager_rating' => $managerRating,
            'manager_comments' => $managerComments,
            'manager_reviewed_at' => date('Y-m-d H:i:s'),
            'manager_user_id' => $user['id'],
            'status' => $action === 'reject' ? 'rejected' : 'independent_review'
        ];

        if ($action === 'reject') {
            $data['rejection_reason'] = $managerComments;
        }

        try {
            $db->update('kpi_quarterly_actuals', $data, 'id = ?', [$id]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => $action === 'reject' ? 'reject' : 'approve',
                'table_name' => 'kpi_quarterly_actuals',
                'record_id' => $id,
                'old_values' => json_encode($quarterly),
                'new_values' => json_encode($data)
            ]);

            // Create notification
            $kpi = $db->fetch("SELECT kpi_code FROM kpis WHERE id = ?", [$quarterly['kpi_id']]);

            if ($action === 'reject' && $quarterly['self_submitted_by']) {
                $db->insert('notifications', [
                    'user_id' => $quarterly['self_submitted_by'],
                    'type' => 'warning',
                    'title' => 'Assessment Rejected',
                    'message' => "Your assessment for {$kpi['kpi_code']} Q{$quarterly['quarter']} was rejected. Reason: {$managerComments}",
                    'link' => '/assessment/self?quarter=' . $quarterly['quarter']
                ]);
            }

            if (is_ajax()) {
                json_response(['success' => true, 'message' => 'Manager review submitted']);
            }
            flash('success', 'Manager review submitted successfully');
        } catch (Exception $e) {
            if (is_ajax()) {
                json_response(['error' => 'Failed to submit review'], 500);
            }
            flash('error', 'Failed to submit review');
        }

        redirect('/assessment/manager');
    }

    public function independentReview(): void {
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

        // Show only items that need independent review
        $where[] = "qa.status = 'independent_review'";

        $whereClause = implode(' AND ', $where);

        $assessments = $db->fetchAll("
            SELECT
                k.id as kpi_id,
                k.kpi_code,
                k.kpi_name,
                k.unit_of_measure,
                k.sla_category,
                d.code as directorate_code,
                d.name as directorate_name,
                qa.id as quarterly_id,
                qa.quarter,
                qa.target_value,
                qa.actual_value,
                qa.variance,
                qa.achievement_status,
                qa.self_rating,
                qa.self_comments,
                qa.manager_rating,
                qa.manager_comments,
                qa.independent_rating,
                qa.independent_comments,
                qa.status,
                su.first_name as submitter_name,
                mu.first_name as manager_name,
                (SELECT COUNT(*) FROM proof_of_evidence WHERE kpi_quarterly_id = qa.id AND is_active = 1) as poe_count
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            LEFT JOIN users su ON su.id = qa.self_submitted_by
            LEFT JOIN users mu ON mu.id = qa.manager_user_id
            WHERE {$whereClause}
            ORDER BY d.code, k.kpi_code
        ", array_merge([$fyId], $params));

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'Independent Review',
            'breadcrumbs' => [
                ['label' => 'Assessment', 'url' => '/assessment'],
                ['label' => 'Independent Review']
            ],
            'financialYear' => $financialYear,
            'assessments' => $assessments,
            'directorates' => $directorates,
            'selectedQuarter' => $quarter,
            'selectedDirectorate' => $directorateId
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/assessment/independent.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function submitIndependentReview(string $id): void {
        $db = db();
        $user = user();

        $quarterly = $db->fetch("SELECT * FROM kpi_quarterly_actuals WHERE id = ?", [$id]);
        if (!$quarterly) {
            if (is_ajax()) {
                json_response(['error' => 'Assessment not found'], 404);
            }
            flash('error', 'Assessment not found');
            redirect('/assessment/independent');
            return;
        }

        $independentRating = (int)$_POST['independent_rating'];
        $independentComments = trim($_POST['independent_comments'] ?? '');
        $action = $_POST['action'] ?? 'approve';

        if ($independentRating < 1 || $independentRating > 5) {
            if (is_ajax()) {
                json_response(['error' => 'Rating must be between 1 and 5'], 400);
            }
            flash('error', 'Rating must be between 1 and 5');
            redirect('/assessment/independent');
            return;
        }

        // Calculate aggregated rating
        $selfRating = $quarterly['self_rating'] ?? 0;
        $managerRating = $quarterly['manager_rating'] ?? 0;
        $aggregatedRating = round(
            ($selfRating * RATING_SELF_WEIGHT) +
            ($managerRating * RATING_MANAGER_WEIGHT) +
            ($independentRating * RATING_INDEPENDENT_WEIGHT),
            2
        );

        $data = [
            'independent_rating' => $independentRating,
            'independent_comments' => $independentComments,
            'independent_reviewed_at' => date('Y-m-d H:i:s'),
            'independent_user_id' => $user['id'],
            'aggregated_rating' => $aggregatedRating,
            'status' => $action === 'reject' ? 'rejected' : 'approved'
        ];

        if ($action === 'reject') {
            $data['rejection_reason'] = $independentComments;
        }

        try {
            $db->update('kpi_quarterly_actuals', $data, 'id = ?', [$id]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => $action === 'reject' ? 'reject' : 'approve',
                'table_name' => 'kpi_quarterly_actuals',
                'record_id' => $id,
                'old_values' => json_encode($quarterly),
                'new_values' => json_encode($data)
            ]);

            if (is_ajax()) {
                json_response(['success' => true, 'message' => 'Independent review submitted', 'aggregated_rating' => $aggregatedRating]);
            }
            flash('success', 'Independent review submitted. Aggregated rating: ' . $aggregatedRating);
        } catch (Exception $e) {
            if (is_ajax()) {
                json_response(['error' => 'Failed to submit review'], 500);
            }
            flash('error', 'Failed to submit review');
        }

        redirect('/assessment/independent');
    }

    public function quarterlyReview(): void {
        redirect('/assessment/self');
    }

    public function show(string $id): void {
        $db = db();

        $assessment = $db->fetch("
            SELECT
                qa.*,
                k.kpi_code,
                k.kpi_name,
                k.unit_of_measure,
                k.description,
                k.sla_category,
                k.data_source,
                so.objective_name,
                d.name as directorate_name,
                su.first_name as submitter_first, su.last_name as submitter_last,
                mu.first_name as manager_first, mu.last_name as manager_last,
                iu.first_name as independent_first, iu.last_name as independent_last
            FROM kpi_quarterly_actuals qa
            JOIN kpis k ON k.id = qa.kpi_id
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN users su ON su.id = qa.self_submitted_by
            LEFT JOIN users mu ON mu.id = qa.manager_user_id
            LEFT JOIN users iu ON iu.id = qa.independent_user_id
            WHERE qa.id = ?
        ", [$id]);

        if (!$assessment) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        // Get POE
        $poeItems = $db->fetchAll("
            SELECT poe.*, u.first_name as uploader_name
            FROM proof_of_evidence poe
            LEFT JOIN users u ON u.id = poe.uploaded_by
            WHERE poe.kpi_quarterly_id = ? AND poe.is_active = 1
            ORDER BY poe.upload_date DESC
        ", [$id]);

        $data = [
            'title' => 'Assessment Details',
            'breadcrumbs' => [
                ['label' => 'Assessment', 'url' => '/assessment'],
                ['label' => $assessment['kpi_code'] . ' Q' . $assessment['quarter']]
            ],
            'assessment' => $assessment,
            'poeItems' => $poeItems
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/assessment/detail.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }
}
