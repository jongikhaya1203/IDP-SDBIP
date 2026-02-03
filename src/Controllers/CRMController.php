<?php
/**
 * CRM Controller
 * Handles reminder management, SLA tracking, and escalation for quarterly submissions
 */

class CRMController {

    /**
     * CRM Dashboard
     */
    public function index(): void {
        $db = db();
        $fyId = $_SESSION['current_financial_year_id'] ?? $db->fetch("SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1")['id'] ?? 1;
        $quarter = current_quarter();

        // Get submission statistics
        $submissionStats = $db->fetch("
            SELECT
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.status IN ('submitted', 'manager_review', 'independent_review', 'approved') THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN qa.status = 'draft' OR qa.status IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN qa.status = 'approved' THEN 1 ELSE 0 END) as approved
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.quarter = ? AND qa.financial_year_id = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1
        ", [$quarter, $fyId, $fyId]);

        // Get directorate performance summary
        $directorateStats = $db->fetchAll("
            SELECT
                d.id,
                d.name,
                d.code,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.status IN ('submitted', 'manager_review', 'independent_review', 'approved') THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN qa.status = 'draft' OR qa.status IS NULL THEN 1 ELSE 0 END) as pending,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                u.email as head_email,
                CONCAT(u.first_name, ' ', u.last_name) as head_name
            FROM directorates d
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.quarter = ? AND qa.financial_year_id = ?
            LEFT JOIN users u ON u.id = d.head_user_id
            WHERE d.is_active = 1
            GROUP BY d.id
            ORDER BY d.name
        ", [$fyId, $quarter, $fyId]);

        // Get recent reminder logs
        $recentLogs = $db->fetchAll("
            SELECT rl.*, d.name as directorate_name, u.first_name, u.last_name
            FROM crm_reminder_logs rl
            LEFT JOIN directorates d ON d.id = rl.directorate_id
            LEFT JOIN users u ON u.id = rl.sent_by
            ORDER BY rl.sent_at DESC
            LIMIT 20
        ");

        // Get SLA configuration
        $slaConfig = $this->getSLAConfig();

        // Get overdue submissions
        $overdueSubmissions = $this->getOverdueSubmissions($fyId, $quarter);

        $data = [
            'title' => 'CRM Portal - Reminder Management',
            'breadcrumbs' => [
                ['label' => 'CRM Portal']
            ],
            'submissionStats' => $submissionStats,
            'directorateStats' => $directorateStats,
            'recentLogs' => $recentLogs,
            'slaConfig' => $slaConfig,
            'overdueSubmissions' => $overdueSubmissions,
            'currentQuarter' => $quarter,
            'financialYearId' => $fyId
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/crm/index.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    /**
     * SLA Configuration Page
     */
    public function slaConfig(): void {
        $slaConfig = $this->getSLAConfig();

        $data = [
            'title' => 'SLA Configuration',
            'breadcrumbs' => [
                ['label' => 'CRM Portal', 'url' => '/crm'],
                ['label' => 'SLA Configuration']
            ],
            'slaConfig' => $slaConfig
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/crm/sla-config.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    /**
     * Save SLA Configuration
     */
    public function saveSLAConfig(): void {
        $config = [
            'reminder_days_before_deadline' => (int)($_POST['reminder_days_before'] ?? 7),
            'first_reminder_days' => (int)($_POST['first_reminder_days'] ?? 14),
            'second_reminder_days' => (int)($_POST['second_reminder_days'] ?? 7),
            'escalation_days' => (int)($_POST['escalation_days'] ?? 3),
            'final_warning_days' => (int)($_POST['final_warning_days'] ?? 1),
            'auto_send_reminders' => isset($_POST['auto_send_reminders']),
            'escalate_to_mm' => isset($_POST['escalate_to_mm']),
            'escalate_to_mayor' => isset($_POST['escalate_to_mayor']),
            'include_performance_summary' => isset($_POST['include_performance_summary']),
            'cc_emails' => $_POST['cc_emails'] ?? '',
            'submission_deadline_day' => (int)($_POST['submission_deadline_day'] ?? 15),
            'criticality_high_threshold' => (int)($_POST['criticality_high_threshold'] ?? 5),
            'criticality_medium_threshold' => (int)($_POST['criticality_medium_threshold'] ?? 3)
        ];

        $configFile = ROOT_PATH . '/config/crm_sla_config.json';
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));

        flash('success', 'SLA configuration saved successfully');
        redirect('/crm/sla-config');
    }

    /**
     * Reminder Logs Page
     */
    public function logs(): void {
        $db = db();

        $page = (int)($_GET['page'] ?? 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $filterType = $_GET['type'] ?? '';
        $filterDirectorate = $_GET['directorate'] ?? '';

        $where = "1=1";
        $params = [];

        if ($filterType) {
            $where .= " AND rl.reminder_type = ?";
            $params[] = $filterType;
        }
        if ($filterDirectorate) {
            $where .= " AND rl.directorate_id = ?";
            $params[] = $filterDirectorate;
        }

        $totalLogs = $db->fetch("
            SELECT COUNT(*) as count FROM crm_reminder_logs rl WHERE {$where}
        ", $params)['count'];

        $logs = $db->fetchAll("
            SELECT rl.*, d.name as directorate_name, d.code as directorate_code,
                   u.first_name, u.last_name
            FROM crm_reminder_logs rl
            LEFT JOIN directorates d ON d.id = rl.directorate_id
            LEFT JOIN users u ON u.id = rl.sent_by
            WHERE {$where}
            ORDER BY rl.sent_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params);

        $directorates = $db->fetchAll("SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name");

        $data = [
            'title' => 'Reminder Logs',
            'breadcrumbs' => [
                ['label' => 'CRM Portal', 'url' => '/crm'],
                ['label' => 'Reminder Logs']
            ],
            'logs' => $logs,
            'directorates' => $directorates,
            'totalLogs' => $totalLogs,
            'currentPage' => $page,
            'totalPages' => ceil($totalLogs / $limit),
            'filterType' => $filterType,
            'filterDirectorate' => $filterDirectorate
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/crm/logs.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    /**
     * Send Reminder to Directorate
     */
    public function sendReminder(): void {
        $db = db();
        $user = user();

        $directorateId = (int)($_POST['directorate_id'] ?? 0);
        $reminderType = $_POST['reminder_type'] ?? 'submission_reminder';
        $customMessage = $_POST['custom_message'] ?? '';
        $includePerformance = isset($_POST['include_performance']);

        if (!$directorateId) {
            flash('error', 'Please select a directorate');
            redirect('/crm');
            return;
        }

        $directorate = $db->fetch("
            SELECT d.*, u.email as head_email, u.first_name, u.last_name
            FROM directorates d
            LEFT JOIN users u ON u.id = d.head_user_id
            WHERE d.id = ?
        ", [$directorateId]);

        if (!$directorate) {
            flash('error', 'Directorate not found');
            redirect('/crm');
            return;
        }

        // Get directorate performance data
        $fyId = $_SESSION['current_financial_year_id'] ?? $db->fetch("SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1")['id'] ?? 1;
        $quarter = current_quarter();
        $performanceData = $this->getDirectoratePerformance($directorateId, $fyId, $quarter);

        // Build email content
        $emailContent = $this->buildReminderEmail($directorate, $reminderType, $customMessage, $performanceData, $includePerformance);

        // Attempt to send email
        $emailSent = $this->sendEmail(
            $directorate['head_email'] ?? '',
            $emailContent['subject'],
            $emailContent['body']
        );

        // Log the reminder
        $db->insert('crm_reminder_logs', [
            'directorate_id' => $directorateId,
            'reminder_type' => $reminderType,
            'recipient_email' => $directorate['head_email'] ?? 'N/A',
            'subject' => $emailContent['subject'],
            'message' => $emailContent['body'],
            'status' => $emailSent ? 'sent' : 'failed',
            'sent_by' => $user['id'],
            'sent_at' => date('Y-m-d H:i:s'),
            'performance_snapshot' => json_encode($performanceData)
        ]);

        // Create in-app notification
        if ($directorate['head_user_id']) {
            $db->insert('notifications', [
                'user_id' => $directorate['head_user_id'],
                'type' => $reminderType === 'escalation' ? 'warning' : 'deadline',
                'title' => $emailContent['subject'],
                'message' => strip_tags(substr($emailContent['body'], 0, 500)),
                'link' => '/assessment/self?quarter=' . $quarter
            ]);
        }

        if ($emailSent) {
            flash('success', 'Reminder sent successfully to ' . ($directorate['head_email'] ?? $directorate['name']));
        } else {
            flash('warning', 'Reminder logged but email could not be sent. Check SMTP configuration.');
        }

        redirect('/crm');
    }

    /**
     * Send Bulk Reminders
     */
    public function sendBulkReminders(): void {
        $db = db();
        $user = user();

        $reminderType = $_POST['reminder_type'] ?? 'submission_reminder';
        $targetGroup = $_POST['target_group'] ?? 'pending'; // pending, all, overdue

        $fyId = $_SESSION['current_financial_year_id'] ?? $db->fetch("SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1")['id'] ?? 1;
        $quarter = current_quarter();

        // Get directorates based on target group
        $directorates = $this->getTargetDirectorates($targetGroup, $fyId, $quarter);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($directorates as $directorate) {
            $performanceData = $this->getDirectoratePerformance($directorate['id'], $fyId, $quarter);
            $emailContent = $this->buildReminderEmail($directorate, $reminderType, '', $performanceData, true);

            $emailSent = $this->sendEmail(
                $directorate['head_email'] ?? '',
                $emailContent['subject'],
                $emailContent['body']
            );

            // Log the reminder
            $db->insert('crm_reminder_logs', [
                'directorate_id' => $directorate['id'],
                'reminder_type' => $reminderType,
                'recipient_email' => $directorate['head_email'] ?? 'N/A',
                'subject' => $emailContent['subject'],
                'message' => $emailContent['body'],
                'status' => $emailSent ? 'sent' : 'failed',
                'sent_by' => $user['id'],
                'sent_at' => date('Y-m-d H:i:s'),
                'performance_snapshot' => json_encode($performanceData),
                'is_bulk' => 1
            ]);

            if ($emailSent) {
                $sentCount++;
            } else {
                $failedCount++;
            }

            // Create in-app notification
            if ($directorate['head_user_id']) {
                $db->insert('notifications', [
                    'user_id' => $directorate['head_user_id'],
                    'type' => 'deadline',
                    'title' => $emailContent['subject'],
                    'message' => strip_tags(substr($emailContent['body'], 0, 500)),
                    'link' => '/assessment/self?quarter=' . $quarter
                ]);
            }
        }

        flash('success', "Bulk reminders sent: {$sentCount} successful, {$failedCount} failed");
        redirect('/crm');
    }

    /**
     * Escalate Non-Submission
     */
    public function escalate(): void {
        $db = db();
        $user = user();

        $directorateId = (int)($_POST['directorate_id'] ?? 0);
        $escalationLevel = $_POST['escalation_level'] ?? 'director'; // director, mm, mayor

        $directorate = $db->fetch("
            SELECT d.*, u.email as head_email, u.first_name, u.last_name
            FROM directorates d
            LEFT JOIN users u ON u.id = d.head_user_id
            WHERE d.id = ?
        ", [$directorateId]);

        if (!$directorate) {
            flash('error', 'Directorate not found');
            redirect('/crm');
            return;
        }

        $fyId = $_SESSION['current_financial_year_id'] ?? $db->fetch("SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1")['id'] ?? 1;
        $quarter = current_quarter();
        $performanceData = $this->getDirectoratePerformance($directorateId, $fyId, $quarter);

        // Build escalation email
        $emailContent = $this->buildEscalationEmail($directorate, $escalationLevel, $performanceData);

        // Get escalation recipients
        $recipients = $this->getEscalationRecipients($escalationLevel, $directorate);

        $emailSent = false;
        foreach ($recipients as $recipient) {
            $sent = $this->sendEmail($recipient, $emailContent['subject'], $emailContent['body']);
            if ($sent) $emailSent = true;
        }

        // Log the escalation
        $db->insert('crm_reminder_logs', [
            'directorate_id' => $directorateId,
            'reminder_type' => 'escalation_' . $escalationLevel,
            'recipient_email' => implode(', ', $recipients),
            'subject' => $emailContent['subject'],
            'message' => $emailContent['body'],
            'status' => $emailSent ? 'sent' : 'failed',
            'sent_by' => $user['id'],
            'sent_at' => date('Y-m-d H:i:s'),
            'performance_snapshot' => json_encode($performanceData),
            'escalation_level' => $escalationLevel
        ]);

        flash($emailSent ? 'success' : 'warning',
            $emailSent ? 'Escalation sent successfully' : 'Escalation logged but email could not be sent');
        redirect('/crm');
    }

    /**
     * Performance Preview Report
     */
    public function performancePreview(): void {
        $db = db();
        $directorateId = (int)($_GET['directorate_id'] ?? 0);

        $fyId = $_SESSION['current_financial_year_id'] ?? $db->fetch("SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1")['id'] ?? 1;
        $quarter = current_quarter();

        $directorate = $db->fetch("SELECT * FROM directorates WHERE id = ?", [$directorateId]);
        $performanceData = $this->getDirectoratePerformance($directorateId, $fyId, $quarter);

        $data = [
            'title' => 'Performance Preview - ' . ($directorate['name'] ?? 'Unknown'),
            'breadcrumbs' => [
                ['label' => 'CRM Portal', 'url' => '/crm'],
                ['label' => 'Performance Preview']
            ],
            'directorate' => $directorate,
            'performance' => $performanceData,
            'quarter' => $quarter
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/crm/performance-preview.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    // ============ PRIVATE HELPER METHODS ============

    private function getSLAConfig(): array {
        $configFile = ROOT_PATH . '/config/crm_sla_config.json';
        $defaults = [
            'reminder_days_before_deadline' => 7,
            'first_reminder_days' => 14,
            'second_reminder_days' => 7,
            'escalation_days' => 3,
            'final_warning_days' => 1,
            'auto_send_reminders' => false,
            'escalate_to_mm' => true,
            'escalate_to_mayor' => false,
            'include_performance_summary' => true,
            'cc_emails' => '',
            'submission_deadline_day' => 15,
            'criticality_high_threshold' => 5,
            'criticality_medium_threshold' => 3
        ];

        if (file_exists($configFile)) {
            $saved = json_decode(file_get_contents($configFile), true);
            return array_merge($defaults, $saved ?? []);
        }

        return $defaults;
    }

    private function getDirectoratePerformance(int $directorateId, int $fyId, int $quarter): array {
        $db = db();

        $stats = $db->fetch("
            SELECT
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.status IN ('submitted', 'manager_review', 'independent_review', 'approved') THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN qa.status = 'draft' OR qa.status IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN qa.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                SUM(CASE WHEN qa.achievement_status = 'partially_achieved' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN qa.achievement_status = 'not_achieved' THEN 1 ELSE 0 END) as not_achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.quarter = ? AND qa.financial_year_id = ?
            WHERE k.directorate_id = ? AND so.financial_year_id = ? AND k.is_active = 1
        ", [$quarter, $fyId, $directorateId, $fyId]);

        // Get pending KPIs list
        $pendingKpis = $db->fetchAll("
            SELECT k.kpi_code, k.kpi_name, qa.status
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.quarter = ? AND qa.financial_year_id = ?
            WHERE k.directorate_id = ? AND so.financial_year_id = ? AND k.is_active = 1
            AND (qa.status = 'draft' OR qa.status IS NULL)
            ORDER BY k.kpi_code
        ", [$quarter, $fyId, $directorateId, $fyId]);

        return [
            'stats' => $stats,
            'pending_kpis' => $pendingKpis,
            'submission_rate' => $stats['total_kpis'] > 0
                ? round(($stats['submitted'] / $stats['total_kpis']) * 100, 1)
                : 0
        ];
    }

    private function getOverdueSubmissions(int $fyId, int $quarter): array {
        $db = db();
        $slaConfig = $this->getSLAConfig();

        // Calculate deadline based on quarter
        $deadlineDay = $slaConfig['submission_deadline_day'];
        $quarterEndMonths = [1 => 10, 2 => 1, 3 => 4, 4 => 7]; // Month after quarter ends
        $year = date('Y');
        if ($quarter == 2) $year++; // Q2 ends in Dec, deadline in Jan next year

        $deadline = "{$year}-" . str_pad($quarterEndMonths[$quarter], 2, '0', STR_PAD_LEFT) . "-" . str_pad($deadlineDay, 2, '0', STR_PAD_LEFT);

        $isOverdue = strtotime($deadline) < time();

        if (!$isOverdue) {
            return [];
        }

        return $db->fetchAll("
            SELECT
                d.id, d.name, d.code,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.status = 'draft' OR qa.status IS NULL THEN 1 ELSE 0 END) as pending_count,
                DATEDIFF(NOW(), ?) as days_overdue
            FROM directorates d
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.quarter = ? AND qa.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id
            HAVING pending_count > 0
            ORDER BY pending_count DESC
        ", [$deadline, $fyId, $quarter, $fyId]);
    }

    private function getTargetDirectorates(string $targetGroup, int $fyId, int $quarter): array {
        $db = db();

        $havingClause = match($targetGroup) {
            'pending' => 'HAVING pending_count > 0',
            'overdue' => 'HAVING pending_count > 0',
            default => ''
        };

        return $db->fetchAll("
            SELECT
                d.id, d.name, d.code, d.head_user_id,
                u.email as head_email, u.first_name, u.last_name,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.status = 'draft' OR qa.status IS NULL THEN 1 ELSE 0 END) as pending_count
            FROM directorates d
            LEFT JOIN users u ON u.id = d.head_user_id
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.quarter = ? AND qa.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id
            {$havingClause}
            ORDER BY d.name
        ", [$fyId, $quarter, $fyId]);
    }

    private function buildReminderEmail(array $directorate, string $type, string $customMessage, array $performance, bool $includePerformance): array {
        $quarter = current_quarter();
        $quarterLabel = quarter_label($quarter);

        $subjects = [
            'submission_reminder' => "Reminder: {$quarterLabel} KPI Submission Required - {$directorate['name']}",
            'first_reminder' => "First Reminder: {$quarterLabel} KPI Submission Deadline Approaching",
            'second_reminder' => "URGENT: {$quarterLabel} KPI Submission - Second Reminder",
            'final_warning' => "FINAL WARNING: {$quarterLabel} KPI Submission Overdue",
            'escalation' => "ESCALATION: Non-Compliance - {$directorate['name']} {$quarterLabel} Submissions"
        ];

        $subject = $subjects[$type] ?? $subjects['submission_reminder'];

        $body = "Dear {$directorate['first_name']} {$directorate['last_name']},\n\n";

        switch ($type) {
            case 'first_reminder':
                $body .= "This is a friendly reminder that the {$quarterLabel} KPI submission deadline is approaching.\n\n";
                break;
            case 'second_reminder':
                $body .= "This is an URGENT reminder that the {$quarterLabel} KPI submission deadline is imminent.\n\n";
                break;
            case 'final_warning':
                $body .= "FINAL WARNING: The {$quarterLabel} KPI submission deadline has passed. Immediate action is required.\n\n";
                break;
            case 'escalation':
                $body .= "This matter has been escalated due to non-compliance with submission requirements.\n\n";
                break;
            default:
                $body .= "Please be reminded to submit your {$quarterLabel} KPI assessments.\n\n";
        }

        if ($customMessage) {
            $body .= "{$customMessage}\n\n";
        }

        if ($includePerformance && $performance) {
            $stats = $performance['stats'];
            $body .= "=== CURRENT PERFORMANCE STATUS ===\n";
            $body .= "Directorate: {$directorate['name']} ({$directorate['code']})\n";
            $body .= "Total KPIs: {$stats['total_kpis']}\n";
            $body .= "Submitted: {$stats['submitted']}\n";
            $body .= "Pending: {$stats['pending']}\n";
            $body .= "Submission Rate: {$performance['submission_rate']}%\n";

            if (!empty($performance['pending_kpis'])) {
                $body .= "\n=== PENDING KPIs ===\n";
                foreach (array_slice($performance['pending_kpis'], 0, 10) as $kpi) {
                    $body .= "- {$kpi['kpi_code']}: {$kpi['kpi_name']}\n";
                }
                if (count($performance['pending_kpis']) > 10) {
                    $remaining = count($performance['pending_kpis']) - 10;
                    $body .= "... and {$remaining} more pending KPIs\n";
                }
            }
            $body .= "\n";
        }

        $body .= "Please log in to the system to complete your submissions: " . APP_URL . "/assessment/self\n\n";
        $body .= "Failure to submit on time may result in escalation to senior management.\n\n";
        $body .= "Regards,\n";
        $body .= APP_NAME . " - Performance Management System\n";

        return ['subject' => $subject, 'body' => $body];
    }

    private function buildEscalationEmail(array $directorate, string $level, array $performance): array {
        $quarter = current_quarter();
        $quarterLabel = quarter_label($quarter);
        $stats = $performance['stats'];

        $levelNames = [
            'director' => 'Directorate Head',
            'mm' => 'Municipal Manager',
            'mayor' => 'Executive Mayor'
        ];

        $subject = "ESCALATION NOTICE: {$directorate['name']} - {$quarterLabel} KPI Non-Submission";

        $body = "Dear {$levelNames[$level]},\n\n";
        $body .= "This is an escalation notice regarding non-compliance with {$quarterLabel} KPI submission requirements.\n\n";
        $body .= "=== NON-COMPLIANT DIRECTORATE ===\n";
        $body .= "Directorate: {$directorate['name']} ({$directorate['code']})\n";
        $body .= "Directorate Head: {$directorate['first_name']} {$directorate['last_name']}\n\n";
        $body .= "=== SUBMISSION STATUS ===\n";
        $body .= "Total KPIs: {$stats['total_kpis']}\n";
        $body .= "Submitted: {$stats['submitted']}\n";
        $body .= "PENDING: {$stats['pending']} ({$performance['submission_rate']}% completion)\n\n";

        $body .= "=== CRITICALITY ASSESSMENT ===\n";
        $criticality = $this->assessCriticality($stats['pending']);
        $body .= "Risk Level: {$criticality['level']}\n";
        $body .= "Impact: {$criticality['impact']}\n\n";

        $body .= "Immediate intervention is required to ensure compliance with MFMA reporting requirements.\n\n";
        $body .= "Regards,\n";
        $body .= APP_NAME . " - Performance Management System\n";

        return ['subject' => $subject, 'body' => $body];
    }

    private function assessCriticality(int $pendingCount): array {
        $slaConfig = $this->getSLAConfig();

        if ($pendingCount >= $slaConfig['criticality_high_threshold']) {
            return [
                'level' => 'HIGH',
                'impact' => 'Significant risk to audit outcome and service delivery reporting',
                'color' => 'danger'
            ];
        } elseif ($pendingCount >= $slaConfig['criticality_medium_threshold']) {
            return [
                'level' => 'MEDIUM',
                'impact' => 'Moderate risk requiring management attention',
                'color' => 'warning'
            ];
        } else {
            return [
                'level' => 'LOW',
                'impact' => 'Minor delays, manageable with follow-up',
                'color' => 'info'
            ];
        }
    }

    private function getEscalationRecipients(string $level, array $directorate): array {
        $db = db();
        $recipients = [];
        $slaConfig = $this->getSLAConfig();

        // Always include directorate head
        if (!empty($directorate['head_email'])) {
            $recipients[] = $directorate['head_email'];
        }

        // Add CC emails from config
        if (!empty($slaConfig['cc_emails'])) {
            $ccEmails = array_map('trim', explode(',', $slaConfig['cc_emails']));
            $recipients = array_merge($recipients, $ccEmails);
        }

        // Get MM email if escalating to MM or Mayor
        if ($level === 'mm' || $level === 'mayor') {
            $mm = $db->fetch("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
            if ($mm) {
                $recipients[] = $mm['email'];
            }
        }

        return array_unique(array_filter($recipients));
    }

    private function sendEmail(string $to, string $subject, string $body): bool {
        if (empty($to)) {
            return false;
        }

        // Load SMTP settings
        $configFile = ROOT_PATH . '/config/integrations.json';
        if (!file_exists($configFile)) {
            error_log("CRM: No integration config found");
            return false;
        }

        $settings = json_decode(file_get_contents($configFile), true);

        if (empty($settings['smtp_host']) || empty($settings['smtp_username'])) {
            error_log("CRM: SMTP not configured");
            return false;
        }

        try {
            // Use PHP mail() with custom headers if no PHPMailer
            $headers = [
                'From' => $settings['smtp_from_name'] . ' <' . $settings['smtp_from_email'] . '>',
                'Reply-To' => $settings['smtp_from_email'],
                'X-Mailer' => 'PHP/' . phpversion(),
                'Content-Type' => 'text/plain; charset=UTF-8'
            ];

            $headerString = '';
            foreach ($headers as $key => $value) {
                $headerString .= "{$key}: {$value}\r\n";
            }

            // For production, use proper SMTP library
            // For now, log the email
            error_log("CRM Email: To={$to}, Subject={$subject}");

            // Attempt to send via mail()
            return @mail($to, $subject, $body, $headerString);
        } catch (Exception $e) {
            error_log("CRM Email Error: " . $e->getMessage());
            return false;
        }
    }
}
