<?php
/**
 * Imbizo Controller
 * Manages Mayoral IDP Imbizo Sessions, Action Items, and Livestreaming
 */

class ImbizoController {

    public function index(): void {
        $db = db();

        $sessions = $db->fetchAll(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM imbizo_action_items WHERE session_id = s.id) as action_count,
                    (SELECT COUNT(*) FROM imbizo_action_items WHERE session_id = s.id AND status = 'completed') as completed_count,
                    u.first_name, u.last_name
             FROM imbizo_sessions s
             LEFT JOIN users u ON u.id = s.created_by
             ORDER BY s.session_date DESC, s.start_time DESC"
        );

        // Get statistics
        $stats = [
            'total_sessions' => count($sessions),
            'total_actions' => $db->fetch("SELECT COUNT(*) as cnt FROM imbizo_action_items")['cnt'] ?? 0,
            'pending_actions' => $db->fetch("SELECT COUNT(*) as cnt FROM imbizo_action_items WHERE status IN ('pending', 'in_progress')")['cnt'] ?? 0,
            'completed_actions' => $db->fetch("SELECT COUNT(*) as cnt FROM imbizo_action_items WHERE status = 'completed'")['cnt'] ?? 0
        ];

        view('imbizo.index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'title' => 'Mayoral IDP Imbizo'
        ]);
    }

    public function create(): void {
        $db = db();
        $wards = $db->fetchAll("SELECT * FROM wards WHERE is_active = 1 ORDER BY ward_number");

        view('imbizo.create', [
            'wards' => $wards,
            'title' => 'Schedule New Imbizo'
        ]);
    }

    public function store(): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            redirect('/imbizo');
            return;
        }

        $db = db();

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'session_date' => $_POST['session_date'] ?? null,
            'start_time' => $_POST['start_time'] ?? null,
            'end_time' => $_POST['end_time'] ?? null,
            'ward_id' => $_POST['ward_id'] ?: null,
            'ward_name' => $_POST['ward_name'] ?? null,
            'venue' => trim($_POST['venue'] ?? ''),
            'youtube_url' => trim($_POST['youtube_url'] ?? ''),
            'facebook_url' => trim($_POST['facebook_url'] ?? ''),
            'twitter_url' => trim($_POST['twitter_url'] ?? ''),
            'municipal_stream_url' => trim($_POST['municipal_stream_url'] ?? ''),
            'status' => 'scheduled',
            'created_by' => user()['id']
        ];

        try {
            $sessionId = $db->insert('imbizo_sessions', $data);
            flash('success', 'Imbizo session scheduled successfully');
            redirect('/imbizo/' . $sessionId);
        } catch (Exception $e) {
            flash('error', 'Failed to create session: ' . $e->getMessage());
            redirect('/imbizo/create');
        }
    }

    public function show(string $id): void {
        $db = db();

        $session = $db->fetch(
            "SELECT s.*, u.first_name, u.last_name
             FROM imbizo_sessions s
             LEFT JOIN users u ON u.id = s.created_by
             WHERE s.id = ?",
            [$id]
        );

        if (!$session) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $actionItems = $db->fetchAll(
            "SELECT ai.*, d.name as directorate_name, d.code as directorate_code,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to,
                    (SELECT COUNT(*) FROM imbizo_comments WHERE action_item_id = ai.id) as comment_count,
                    (SELECT COUNT(*) FROM imbizo_poe WHERE action_item_id = ai.id) as poe_count
             FROM imbizo_action_items ai
             LEFT JOIN directorates d ON d.id = ai.assigned_directorate_id
             LEFT JOIN users u ON u.id = ai.assigned_user_id
             WHERE ai.session_id = ?
             ORDER BY ai.item_number",
            [$id]
        );

        view('imbizo.show', [
            'session' => $session,
            'actionItems' => $actionItems,
            'title' => $session['title']
        ]);
    }

    public function livestream(string $id): void {
        $db = db();

        $session = $db->fetch("SELECT * FROM imbizo_sessions WHERE id = ?", [$id]);

        if (!$session) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");
        $users = $db->fetchAll("SELECT id, first_name, last_name, role FROM users WHERE is_active = 1 ORDER BY first_name");

        view('imbizo.livestream', [
            'session' => $session,
            'directorates' => $directorates,
            'users' => $users,
            'title' => 'Live: ' . $session['title']
        ]);
    }

    public function startLive(string $id): void {
        if (!verify_csrf()) {
            json_response(['error' => 'Invalid request'], 400);
            return;
        }

        $db = db();
        $db->update('imbizo_sessions', ['status' => 'live'], 'id = ?', [$id]);

        json_response(['success' => true, 'message' => 'Session is now live']);
    }

    public function endLive(string $id): void {
        if (!verify_csrf()) {
            json_response(['error' => 'Invalid request'], 400);
            return;
        }

        $db = db();
        $db->update('imbizo_sessions', [
            'status' => 'completed',
            'end_time' => date('H:i:s')
        ], 'id = ?', [$id]);

        json_response(['success' => true, 'message' => 'Session ended']);
    }

    public function addActionItem(string $sessionId): void {
        if (!verify_csrf()) {
            json_response(['error' => 'Invalid request'], 400);
            return;
        }

        $db = db();

        // Get next item number
        $lastItem = $db->fetch(
            "SELECT item_number FROM imbizo_action_items WHERE session_id = ? ORDER BY id DESC LIMIT 1",
            [$sessionId]
        );
        $nextNum = $lastItem ? intval(substr($lastItem['item_number'], -3)) + 1 : 1;
        $itemNumber = 'AI-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $data = [
            'session_id' => $sessionId,
            'item_number' => $itemNumber,
            'description' => trim($_POST['description'] ?? ''),
            'commitment' => trim($_POST['commitment'] ?? ''),
            'community_concern' => trim($_POST['community_concern'] ?? ''),
            'target_date' => $_POST['target_date'] ?: null,
            'priority' => $_POST['priority'] ?? 'medium',
            'assigned_directorate_id' => $_POST['directorate_id'] ?: null,
            'assigned_user_id' => $_POST['user_id'] ?: null,
            'ward_name' => $_POST['ward_name'] ?? null,
            'status' => 'pending'
        ];

        try {
            $itemId = $db->insert('imbizo_action_items', $data);

            // Fetch the created item with relations
            $item = $db->fetch(
                "SELECT ai.*, d.name as directorate_name
                 FROM imbizo_action_items ai
                 LEFT JOIN directorates d ON d.id = ai.assigned_directorate_id
                 WHERE ai.id = ?",
                [$itemId]
            );

            json_response(['success' => true, 'item' => $item]);
        } catch (Exception $e) {
            json_response(['error' => $e->getMessage()], 500);
        }
    }

    public function actionItems(): void {
        $db = db();

        $filter = $_GET['filter'] ?? 'all';
        $directorateId = $_GET['directorate_id'] ?? null;

        $where = "1=1";
        $params = [];

        if ($filter === 'pending') {
            $where .= " AND ai.status IN ('pending', 'in_progress')";
        } elseif ($filter === 'overdue') {
            $where .= " AND ai.status != 'completed' AND ai.target_date < CURDATE()";
        } elseif ($filter === 'completed') {
            $where .= " AND ai.status = 'completed'";
        }

        if ($directorateId) {
            $where .= " AND ai.assigned_directorate_id = ?";
            $params[] = $directorateId;
        }

        // For non-admin users, show only their directorate's items
        $user = user();
        if (!has_role('admin', 'director') && $user['directorate_id']) {
            $where .= " AND ai.assigned_directorate_id = ?";
            $params[] = $user['directorate_id'];
        }

        $items = $db->fetchAll(
            "SELECT ai.*, s.title as session_title, s.session_date,
                    d.name as directorate_name, d.code as directorate_code,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to,
                    (SELECT COUNT(*) FROM imbizo_comments WHERE action_item_id = ai.id) as comment_count,
                    (SELECT COUNT(*) FROM imbizo_poe WHERE action_item_id = ai.id) as poe_count
             FROM imbizo_action_items ai
             JOIN imbizo_sessions s ON s.id = ai.session_id
             LEFT JOIN directorates d ON d.id = ai.assigned_directorate_id
             LEFT JOIN users u ON u.id = ai.assigned_user_id
             WHERE $where
             ORDER BY
                CASE ai.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
                ai.target_date ASC",
            $params
        );

        $directorates = $db->fetchAll("SELECT * FROM directorates WHERE is_active = 1 ORDER BY name");

        view('imbizo.action-items', [
            'items' => $items,
            'directorates' => $directorates,
            'filter' => $filter,
            'selectedDirectorate' => $directorateId,
            'title' => 'Imbizo Action Items'
        ]);
    }

    public function showActionItem(string $id): void {
        $db = db();

        $item = $db->fetch(
            "SELECT ai.*, s.title as session_title, s.session_date,
                    d.name as directorate_name, d.code as directorate_code,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to
             FROM imbizo_action_items ai
             JOIN imbizo_sessions s ON s.id = ai.session_id
             LEFT JOIN directorates d ON d.id = ai.assigned_directorate_id
             LEFT JOIN users u ON u.id = ai.assigned_user_id
             WHERE ai.id = ?",
            [$id]
        );

        if (!$item) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $comments = $db->fetchAll(
            "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as author_name, u.role
             FROM imbizo_comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.action_item_id = ?
             ORDER BY c.created_at DESC",
            [$id]
        );

        $poeFiles = $db->fetchAll(
            "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) as uploader_name
             FROM imbizo_poe p
             JOIN users u ON u.id = p.uploaded_by
             WHERE p.action_item_id = ?
             ORDER BY p.created_at DESC",
            [$id]
        );

        view('imbizo.action-item-detail', [
            'item' => $item,
            'comments' => $comments,
            'poeFiles' => $poeFiles,
            'title' => 'Action Item: ' . $item['item_number']
        ]);
    }

    public function addComment(string $actionItemId): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            back();
            return;
        }

        $db = db();

        $data = [
            'action_item_id' => $actionItemId,
            'user_id' => user()['id'],
            'comment_type' => $_POST['comment_type'] ?? 'response',
            'content' => trim($_POST['content'] ?? ''),
            'new_status' => $_POST['new_status'] ?? null,
            'progress_update' => $_POST['progress_update'] ?? null
        ];

        try {
            $db->insert('imbizo_comments', $data);

            // Update action item status if provided
            if ($data['new_status']) {
                $updateData = ['status' => $data['new_status']];
                if ($data['progress_update']) {
                    $updateData['progress_percentage'] = $data['progress_update'];
                }
                if ($data['new_status'] === 'completed') {
                    $updateData['completed_at'] = date('Y-m-d H:i:s');
                    $updateData['progress_percentage'] = 100;
                }
                $db->update('imbizo_action_items', $updateData, 'id = ?', [$actionItemId]);
            }

            flash('success', 'Response added successfully');
        } catch (Exception $e) {
            flash('error', 'Failed to add response');
        }

        redirect('/imbizo/action-items/' . $actionItemId);
    }

    public function uploadPOE(string $actionItemId): void {
        if (!verify_csrf()) {
            flash('error', 'Invalid request');
            back();
            return;
        }

        if (empty($_FILES['poe_file']['name'])) {
            flash('error', 'Please select a file to upload');
            back();
            return;
        }

        $file = $_FILES['poe_file'];
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedTypes)) {
            flash('error', 'Invalid file type');
            back();
            return;
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            flash('error', 'File too large (max 10MB)');
            back();
            return;
        }

        // Create upload directory
        $uploadDir = UPLOAD_PATH . '/imbizo/' . $actionItemId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $filepath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $db = db();
            $db->insert('imbizo_poe', [
                'action_item_id' => $actionItemId,
                'file_name' => $file['name'],
                'file_path' => 'uploads/imbizo/' . $actionItemId . '/' . $filename,
                'file_type' => $ext,
                'file_size' => $file['size'],
                'description' => trim($_POST['description'] ?? ''),
                'uploaded_by' => user()['id']
            ]);

            flash('success', 'Evidence uploaded successfully');
        } else {
            flash('error', 'Failed to upload file');
        }

        redirect('/imbizo/action-items/' . $actionItemId);
    }

    public function generateMinutes(string $sessionId): void {
        if (!verify_csrf()) {
            json_response(['error' => 'Invalid request'], 400);
            return;
        }

        $db = db();
        $session = $db->fetch("SELECT * FROM imbizo_sessions WHERE id = ?", [$sessionId]);

        if (!$session) {
            json_response(['error' => 'Session not found'], 404);
            return;
        }

        $actionItems = $db->fetchAll(
            "SELECT ai.*, d.name as directorate_name
             FROM imbizo_action_items ai
             LEFT JOIN directorates d ON d.id = ai.assigned_directorate_id
             WHERE ai.session_id = ?
             ORDER BY ai.item_number",
            [$sessionId]
        );

        // Build context for AI
        $context = "Mayoral IDP Imbizo Session\n";
        $context .= "Title: {$session['title']}\n";
        $context .= "Date: {$session['session_date']}\n";
        $context .= "Venue: {$session['venue']}\n";
        $context .= "Ward: {$session['ward_name']}\n\n";
        $context .= "Action Items Captured:\n";

        foreach ($actionItems as $item) {
            $context .= "\n{$item['item_number']}: {$item['description']}\n";
            $context .= "  - Commitment: {$item['commitment']}\n";
            $context .= "  - Assigned to: {$item['directorate_name']}\n";
            $context .= "  - Target Date: {$item['target_date']}\n";
            $context .= "  - Priority: {$item['priority']}\n";
        }

        // Call OpenAI API if configured
        if (!empty(OPENAI_API_KEY)) {
            try {
                $aiService = new AIReportService();
                $minutes = $aiService->generateImbizoMinutes($context);

                $db->update('imbizo_sessions', [
                    'ai_minutes' => $minutes['minutes'] ?? '',
                    'ai_summary' => $minutes['summary'] ?? ''
                ], 'id = ?', [$sessionId]);

                json_response(['success' => true, 'minutes' => $minutes]);
            } catch (Exception $e) {
                json_response(['error' => 'AI generation failed: ' . $e->getMessage()], 500);
            }
        } else {
            // Generate basic minutes without AI
            $minutes = $this->generateBasicMinutes($session, $actionItems);
            $db->update('imbizo_sessions', ['ai_minutes' => $minutes], 'id = ?', [$sessionId]);
            json_response(['success' => true, 'minutes' => ['minutes' => $minutes]]);
        }
    }

    private function generateBasicMinutes(array $session, array $actionItems): string {
        $minutes = "MAYORAL IDP IMBIZO - MINUTES\n";
        $minutes .= "=" . str_repeat("=", 50) . "\n\n";
        $minutes .= "Session: {$session['title']}\n";
        $minutes .= "Date: " . format_date($session['session_date'], 'd F Y') . "\n";
        $minutes .= "Time: {$session['start_time']}" . ($session['end_time'] ? " - {$session['end_time']}" : "") . "\n";
        $minutes .= "Venue: {$session['venue']}\n";
        $minutes .= "Ward: {$session['ward_name']}\n\n";

        $minutes .= "ACTION ITEMS\n";
        $minutes .= "-" . str_repeat("-", 50) . "\n\n";

        foreach ($actionItems as $item) {
            $minutes .= "{$item['item_number']}\n";
            $minutes .= "Description: {$item['description']}\n";
            if ($item['commitment']) {
                $minutes .= "Mayor's Commitment: {$item['commitment']}\n";
            }
            $minutes .= "Assigned To: {$item['directorate_name']}\n";
            $minutes .= "Target Date: " . ($item['target_date'] ? format_date($item['target_date'], 'd F Y') : 'TBD') . "\n";
            $minutes .= "Priority: " . ucfirst($item['priority']) . "\n";
            $minutes .= "\n";
        }

        $minutes .= "\nTotal Action Items: " . count($actionItems) . "\n";

        return $minutes;
    }
}
