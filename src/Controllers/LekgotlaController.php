<?php
/**
 * Mayoral Lekgotla Controller
 *
 * Manages IDP priority changes based on Mayoral Imbizo commitments.
 * Provides comparison of old vs new priorities with budget impact analysis.
 */

class LekgotlaController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Dashboard - Overview of Lekgotla sessions and priority status
     */
    public function index()
    {
        $financialYearId = $_SESSION['current_financial_year_id'] ?? 1;

        // Get sessions summary
        $sessions = $this->db->query("
            SELECT ls.*, fy.year_label,
                   (SELECT COUNT(*) FROM lekgotla_priority_changes WHERE lekgotla_session_id = ls.id) as change_count,
                   (SELECT COUNT(*) FROM lekgotla_resolutions WHERE lekgotla_session_id = ls.id) as resolution_count
            FROM lekgotla_sessions ls
            JOIN financial_years fy ON ls.financial_year_id = fy.id
            WHERE ls.financial_year_id = ?
            ORDER BY ls.session_date DESC
        ", [$financialYearId])->fetchAll();

        // Get priority statistics
        $priorityStats = $this->getPriorityStatistics($financialYearId);

        // Get recent changes
        $recentChanges = $this->db->query("
            SELECT lpc.*, ip.priority_name, ip.priority_code, ls.session_name
            FROM lekgotla_priority_changes lpc
            LEFT JOIN idp_priorities ip ON lpc.priority_id = ip.id
            JOIN lekgotla_sessions ls ON lpc.lekgotla_session_id = ls.id
            WHERE ls.financial_year_id = ?
            ORDER BY lpc.created_at DESC
            LIMIT 10
        ", [$financialYearId])->fetchAll();

        // Budget impact summary
        $budgetImpact = $this->getBudgetImpactSummary($financialYearId);

        view('lekgotla.index', [
            'sessions' => $sessions,
            'priorityStats' => $priorityStats,
            'recentChanges' => $recentChanges,
            'budgetImpact' => $budgetImpact,
            'financialYearId' => $financialYearId
        ]);
    }

    /**
     * Priority Comparison View - Main comparison table
     */
    public function comparison()
    {
        $financialYearId = $_SESSION['current_financial_year_id'] ?? 1;
        $sessionId = $_GET['session_id'] ?? null;

        // Get all priorities with their status
        $priorities = $this->db->query("
            SELECT ip.*,
                   pc.name as category_name, pc.color_code, pc.icon,
                   d.name as directorate_name,
                   CASE
                       WHEN ip.status IN ('active', 'on_track') THEN 'continuing'
                       WHEN ip.status = 'discarded' THEN 'discarded'
                       WHEN ip.source_type = 'lekgotla' THEN 'new'
                       ELSE 'continuing'
                   END as comparison_status
            FROM idp_priorities ip
            LEFT JOIN priority_categories pc ON ip.category_id = pc.id
            LEFT JOIN directorates d ON ip.directorate_id = d.id
            WHERE ip.financial_year_id = ?
            ORDER BY ip.priority_level DESC, ip.priority_code
        ", [$financialYearId])->fetchAll();

        // Categorize priorities
        $continuing = array_filter($priorities, fn($p) => in_array($p['status'], ['active', 'on_track', 'at_risk']));
        $newPriorities = array_filter($priorities, fn($p) => $p['source_type'] === 'lekgotla');
        $discarded = array_filter($priorities, fn($p) => $p['status'] === 'discarded');

        // Get categories for filtering
        $categories = $this->db->query("SELECT * FROM priority_categories WHERE is_active = 1 ORDER BY display_order")->fetchAll();

        // Get linked Imbizo sessions for reference
        $imbizoSessions = $this->db->query("
            SELECT id, title as session_title, session_date
            FROM imbizo_sessions
            ORDER BY session_date DESC
        ")->fetchAll();

        // Budget summary by category
        $budgetByCategory = $this->db->query("
            SELECT pc.name, pc.color_code,
                   SUM(ip.budget_allocated) as total_allocated,
                   SUM(ip.budget_spent) as total_spent,
                   COUNT(ip.id) as priority_count
            FROM idp_priorities ip
            JOIN priority_categories pc ON ip.category_id = pc.id
            WHERE ip.financial_year_id = ? AND ip.status != 'discarded'
            GROUP BY pc.id
            ORDER BY total_allocated DESC
        ", [$financialYearId])->fetchAll();

        view('lekgotla.comparison', [
            'continuing' => $continuing,
            'newPriorities' => $newPriorities,
            'discarded' => $discarded,
            'categories' => $categories,
            'imbizoSessions' => $imbizoSessions,
            'budgetByCategory' => $budgetByCategory,
            'financialYearId' => $financialYearId
        ]);
    }

    /**
     * Create new Lekgotla session
     */
    public function create()
    {
        $financialYearId = $_SESSION['current_financial_year_id'] ?? 1;

        // Get available Imbizo sessions to link
        $imbizoSessions = $this->db->query("
            SELECT id, title as session_title, session_date, venue
            FROM imbizo_sessions
            ORDER BY session_date DESC
        ")->fetchAll();

        view('lekgotla.create', [
            'imbizoSessions' => $imbizoSessions,
            'financialYearId' => $financialYearId
        ]);
    }

    /**
     * Store new Lekgotla session
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/lekgotla');
        }

        $financialYearId = $_SESSION['current_financial_year_id'] ?? 1;

        $this->db->query("
            INSERT INTO lekgotla_sessions
            (financial_year_id, session_name, session_date, venue, presided_by, linked_imbizo_id, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, 'draft', ?)
        ", [
            $financialYearId,
            $_POST['session_name'],
            $_POST['session_date'],
            $_POST['venue'],
            $_POST['presided_by'] ?? 'Municipal Mayor',
            $_POST['linked_imbizo_id'] ?: null,
            user()['id']
        ]);

        $sessionId = $this->db->lastInsertId();

        // Create pre-lekgotla snapshot
        $this->createSnapshot($financialYearId, 'pre_lekgotla', $sessionId);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lekgotla session created successfully'];
        redirect('/lekgotla/session/' . $sessionId);
    }

    /**
     * View Lekgotla session details
     */
    public function session($id)
    {
        $session = $this->db->query("
            SELECT ls.*, fy.year_label,
                   ims.title as imbizo_title, ims.session_date as imbizo_date,
                   u.full_name as created_by_name
            FROM lekgotla_sessions ls
            JOIN financial_years fy ON ls.financial_year_id = fy.id
            LEFT JOIN imbizo_sessions ims ON ls.linked_imbizo_id = ims.id
            LEFT JOIN users u ON ls.created_by = u.id
            WHERE ls.id = ?
        ", [$id])->fetch();

        if (!$session) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Session not found'];
            redirect('/lekgotla');
        }

        // Get priority changes for this session
        $changes = $this->db->query("
            SELECT lpc.*,
                   ip.priority_code, ip.priority_name, ip.budget_allocated as original_budget,
                   pc.name as category_name,
                   iai.action_description as imbizo_action,
                   u.full_name as proposed_by_name
            FROM lekgotla_priority_changes lpc
            LEFT JOIN idp_priorities ip ON lpc.priority_id = ip.id
            LEFT JOIN priority_categories pc ON COALESCE(lpc.new_category_id, ip.category_id) = pc.id
            LEFT JOIN imbizo_action_items iai ON lpc.linked_imbizo_action_id = iai.id
            LEFT JOIN users u ON lpc.proposed_by = u.id
            WHERE lpc.lekgotla_session_id = ?
            ORDER BY lpc.change_type, lpc.created_at
        ", [$id])->fetchAll();

        // Group changes by type
        $groupedChanges = [
            'retain' => array_filter($changes, fn($c) => $c['change_type'] === 'retain'),
            'new' => array_filter($changes, fn($c) => $c['change_type'] === 'new'),
            'modify' => array_filter($changes, fn($c) => $c['change_type'] === 'modify'),
            'discard' => array_filter($changes, fn($c) => $c['change_type'] === 'discard'),
            'defer' => array_filter($changes, fn($c) => $c['change_type'] === 'defer')
        ];

        // Get resolutions
        $resolutions = $this->db->query("
            SELECT lr.*, d.name as directorate_name
            FROM lekgotla_resolutions lr
            LEFT JOIN directorates d ON lr.responsible_directorate_id = d.id
            WHERE lr.lekgotla_session_id = ?
            ORDER BY lr.resolution_number
        ", [$id])->fetchAll();

        // Budget impact calculation
        $budgetImpact = $this->calculateSessionBudgetImpact($id);

        // Get Imbizo action items if linked
        $imbizoActions = [];
        if ($session['linked_imbizo_id']) {
            $imbizoActions = $this->db->query("
                SELECT iai.*, d.name as directorate_name
                FROM imbizo_action_items iai
                LEFT JOIN directorates d ON iai.assigned_directorate_id = d.id
                WHERE iai.imbizo_session_id = ?
                ORDER BY iai.priority DESC
            ", [$session['linked_imbizo_id']])->fetchAll();
        }

        view('lekgotla.session', [
            'session' => $session,
            'groupedChanges' => $groupedChanges,
            'resolutions' => $resolutions,
            'budgetImpact' => $budgetImpact,
            'imbizoActions' => $imbizoActions
        ]);
    }

    /**
     * Add priority change to session
     */
    public function addChange($sessionId)
    {
        $session = $this->db->query("SELECT * FROM lekgotla_sessions WHERE id = ?", [$sessionId])->fetch();
        if (!$session) {
            redirect('/lekgotla');
        }

        // Get current priorities
        $priorities = $this->db->query("
            SELECT ip.*, pc.name as category_name
            FROM idp_priorities ip
            LEFT JOIN priority_categories pc ON ip.category_id = pc.id
            WHERE ip.financial_year_id = ? AND ip.status NOT IN ('discarded', 'completed')
            ORDER BY ip.priority_code
        ", [$session['financial_year_id']])->fetchAll();

        // Get categories
        $categories = $this->db->query("SELECT * FROM priority_categories WHERE is_active = 1 ORDER BY display_order")->fetchAll();

        // Get directorates
        $directorates = $this->db->query("SELECT * FROM directorates ORDER BY name")->fetchAll();

        // Get Imbizo actions if linked
        $imbizoActions = [];
        if ($session['linked_imbizo_id']) {
            $imbizoActions = $this->db->query("
                SELECT * FROM imbizo_action_items
                WHERE imbizo_session_id = ?
                ORDER BY priority DESC
            ", [$session['linked_imbizo_id']])->fetchAll();
        }

        view('lekgotla.add-change', [
            'session' => $session,
            'priorities' => $priorities,
            'categories' => $categories,
            'directorates' => $directorates,
            'imbizoActions' => $imbizoActions
        ]);
    }

    /**
     * Store priority change
     */
    public function storeChange($sessionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/lekgotla/session/' . $sessionId);
        }

        $changeType = $_POST['change_type'];

        $data = [
            'lekgotla_session_id' => $sessionId,
            'change_type' => $changeType,
            'priority_id' => $_POST['priority_id'] ?: null,
            'linked_imbizo_action_id' => $_POST['linked_imbizo_action_id'] ?: null,
            'change_reason' => $_POST['change_reason'],
            'community_impact' => $_POST['community_impact'] ?? null,
            'proposed_by' => user()['id'],
            'status' => 'proposed'
        ];

        // Handle different change types
        if ($changeType === 'new') {
            $data['new_priority_name'] = $_POST['new_priority_name'];
            $data['new_priority_description'] = $_POST['new_priority_description'];
            $data['new_category_id'] = $_POST['new_category_id'];
            $data['new_directorate_id'] = $_POST['new_directorate_id'];
            $data['new_priority_level'] = $_POST['new_priority_level'];
            $data['new_budget'] = $_POST['new_budget'] ?? 0;
            $data['imbizo_commitment'] = $_POST['imbizo_commitment'] ?? null;
        } elseif (in_array($changeType, ['modify', 'discard', 'defer', 'retain'])) {
            // Get original budget
            $original = $this->db->query("SELECT budget_allocated FROM idp_priorities WHERE id = ?", [$_POST['priority_id']])->fetch();
            $data['previous_budget'] = $original['budget_allocated'] ?? 0;

            if ($changeType === 'modify') {
                $data['new_budget'] = $_POST['new_budget'] ?? $data['previous_budget'];
                $data['budget_variance'] = $data['new_budget'] - $data['previous_budget'];
                $data['field_changed'] = $_POST['field_changed'] ?? 'budget';
                $data['old_value'] = $_POST['old_value'] ?? null;
                $data['new_value'] = $_POST['new_value'] ?? null;
            }

            $data['budget_justification'] = $_POST['budget_justification'] ?? null;
        }

        // Build insert query
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $this->db->query(
            "INSERT INTO lekgotla_priority_changes (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")",
            array_values($data)
        );

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Priority change added successfully'];
        redirect('/lekgotla/session/' . $sessionId);
    }

    /**
     * Approve changes and apply to priorities
     */
    public function approveSession($sessionId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/lekgotla/session/' . $sessionId);
        }

        $session = $this->db->query("SELECT * FROM lekgotla_sessions WHERE id = ?", [$sessionId])->fetch();

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Get all approved changes
            $changes = $this->db->query("
                SELECT * FROM lekgotla_priority_changes
                WHERE lekgotla_session_id = ? AND status = 'approved'
            ", [$sessionId])->fetchAll();

            foreach ($changes as $change) {
                $this->applyPriorityChange($change, $session['financial_year_id']);
            }

            // Update session status
            $this->db->query("
                UPDATE lekgotla_sessions
                SET status = 'approved', approved_by = ?, approved_at = NOW(), resolution_number = ?
                WHERE id = ?
            ", [user()['id'], $_POST['resolution_number'], $sessionId]);

            // Create post-lekgotla snapshot
            $this->createSnapshot($session['financial_year_id'], 'post_lekgotla', $sessionId);

            $this->db->commit();

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lekgotla session approved and changes applied'];
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error applying changes: ' . $e->getMessage()];
        }

        redirect('/lekgotla/session/' . $sessionId);
    }

    /**
     * Apply individual priority change
     */
    private function applyPriorityChange($change, $financialYearId)
    {
        switch ($change['change_type']) {
            case 'new':
                // Create new priority
                $code = $this->generatePriorityCode($financialYearId);
                $this->db->query("
                    INSERT INTO idp_priorities
                    (financial_year_id, priority_code, priority_name, description, category_id, directorate_id,
                     source_type, source_lekgotla_id, status, priority_level, budget_allocated, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, 'lekgotla', ?, 'active', ?, ?, ?)
                ", [
                    $financialYearId,
                    $code,
                    $change['new_priority_name'],
                    $change['new_priority_description'],
                    $change['new_category_id'],
                    $change['new_directorate_id'],
                    $change['lekgotla_session_id'],
                    $change['new_priority_level'],
                    $change['new_budget'] ?? 0,
                    user()['id']
                ]);
                break;

            case 'discard':
                $this->db->query("
                    UPDATE idp_priorities SET status = 'discarded', last_modified_by = ? WHERE id = ?
                ", [user()['id'], $change['priority_id']]);
                break;

            case 'defer':
                $this->db->query("
                    UPDATE idp_priorities SET status = 'deferred', last_modified_by = ? WHERE id = ?
                ", [user()['id'], $change['priority_id']]);
                break;

            case 'modify':
                if ($change['new_budget']) {
                    $this->db->query("
                        UPDATE idp_priorities SET budget_allocated = ?, last_modified_by = ? WHERE id = ?
                    ", [$change['new_budget'], user()['id'], $change['priority_id']]);
                }
                break;
        }
    }

    /**
     * Generate unique priority code
     */
    private function generatePriorityCode($financialYearId)
    {
        $result = $this->db->query("
            SELECT MAX(CAST(SUBSTRING(priority_code, 5) AS UNSIGNED)) as max_num
            FROM idp_priorities WHERE financial_year_id = ?
        ", [$financialYearId])->fetch();

        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'IDP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create snapshot of priorities
     */
    private function createSnapshot($financialYearId, $type, $sessionId = null)
    {
        $priorities = $this->db->query("
            SELECT * FROM idp_priorities WHERE financial_year_id = ?
        ", [$financialYearId])->fetchAll();

        $stats = $this->getPriorityStatistics($financialYearId);

        $this->db->query("
            INSERT INTO priority_snapshots
            (financial_year_id, snapshot_date, snapshot_type, lekgotla_session_id, priorities_data,
             total_priorities, total_budget, active_count, on_track_count, at_risk_count, completed_count, discarded_count, created_by)
            VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $financialYearId,
            $type,
            $sessionId,
            json_encode($priorities),
            count($priorities),
            $stats['total_budget'],
            $stats['active'],
            $stats['on_track'],
            $stats['at_risk'],
            $stats['completed'],
            $stats['discarded'],
            user()['id']
        ]);
    }

    /**
     * Get priority statistics
     */
    private function getPriorityStatistics($financialYearId)
    {
        $result = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'on_track' THEN 1 ELSE 0 END) as on_track,
                SUM(CASE WHEN status = 'at_risk' THEN 1 ELSE 0 END) as at_risk,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'discarded' THEN 1 ELSE 0 END) as discarded,
                SUM(CASE WHEN status = 'deferred' THEN 1 ELSE 0 END) as deferred,
                SUM(budget_allocated) as total_budget,
                SUM(budget_spent) as total_spent,
                SUM(CASE WHEN source_type = 'lekgotla' THEN 1 ELSE 0 END) as from_lekgotla,
                SUM(CASE WHEN source_type = 'imbizo' THEN 1 ELSE 0 END) as from_imbizo
            FROM idp_priorities
            WHERE financial_year_id = ?
        ", [$financialYearId])->fetch();

        return $result;
    }

    /**
     * Get budget impact summary
     */
    private function getBudgetImpactSummary($financialYearId)
    {
        return $this->db->query("
            SELECT
                SUM(CASE WHEN change_type = 'new' THEN new_budget ELSE 0 END) as new_allocations,
                SUM(CASE WHEN change_type = 'discard' THEN previous_budget ELSE 0 END) as freed_budget,
                SUM(CASE WHEN change_type = 'modify' THEN budget_variance ELSE 0 END) as net_adjustments,
                COUNT(CASE WHEN change_type = 'new' THEN 1 END) as new_count,
                COUNT(CASE WHEN change_type = 'discard' THEN 1 END) as discard_count,
                COUNT(CASE WHEN change_type = 'modify' THEN 1 END) as modify_count
            FROM lekgotla_priority_changes lpc
            JOIN lekgotla_sessions ls ON lpc.lekgotla_session_id = ls.id
            WHERE ls.financial_year_id = ? AND lpc.status = 'approved'
        ", [$financialYearId])->fetch();
    }

    /**
     * Calculate session budget impact
     */
    private function calculateSessionBudgetImpact($sessionId)
    {
        return $this->db->query("
            SELECT
                SUM(CASE WHEN change_type = 'new' THEN COALESCE(new_budget, 0) ELSE 0 END) as new_allocations,
                SUM(CASE WHEN change_type = 'discard' THEN COALESCE(previous_budget, 0) ELSE 0 END) as freed_budget,
                SUM(CASE WHEN change_type = 'modify' THEN COALESCE(budget_variance, 0) ELSE 0 END) as adjustments,
                SUM(CASE WHEN change_type = 'new' THEN COALESCE(new_budget, 0)
                         WHEN change_type = 'discard' THEN -COALESCE(previous_budget, 0)
                         WHEN change_type = 'modify' THEN COALESCE(budget_variance, 0)
                         ELSE 0 END) as net_impact
            FROM lekgotla_priority_changes
            WHERE lekgotla_session_id = ?
        ", [$sessionId])->fetch();
    }

    /**
     * Export comparison report
     */
    public function exportComparison()
    {
        $financialYearId = $_SESSION['current_financial_year_id'] ?? 1;

        // Get all data for export
        $priorities = $this->db->query("
            SELECT ip.*, pc.name as category_name, d.name as directorate_name
            FROM idp_priorities ip
            LEFT JOIN priority_categories pc ON ip.category_id = pc.id
            LEFT JOIN directorates d ON ip.directorate_id = d.id
            WHERE ip.financial_year_id = ?
            ORDER BY ip.status, ip.priority_code
        ", [$financialYearId])->fetchAll();

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="idp_priorities_comparison_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'Priority Code', 'Priority Name', 'Category', 'Directorate', 'Status',
            'Source', 'Priority Level', 'Budget Allocated', 'Budget Spent', 'Progress %'
        ]);

        // Data
        foreach ($priorities as $p) {
            fputcsv($output, [
                $p['priority_code'],
                $p['priority_name'],
                $p['category_name'],
                $p['directorate_name'],
                ucfirst($p['status']),
                ucfirst($p['source_type']),
                ucfirst($p['priority_level']),
                number_format($p['budget_allocated'], 2),
                number_format($p['budget_spent'], 2),
                $p['current_progress'] . '%'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Review change (approve/reject)
     */
    public function reviewChange($changeId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/lekgotla');
        }

        $action = $_POST['action']; // 'approve' or 'reject'
        $status = $action === 'approve' ? 'approved' : 'rejected';

        $this->db->query("
            UPDATE lekgotla_priority_changes
            SET status = ?, reviewed_by = ?, review_comments = ?,
                approval_date = CASE WHEN ? = 'approved' THEN NOW() ELSE NULL END,
                approved_by = CASE WHEN ? = 'approved' THEN ? ELSE NULL END
            WHERE id = ?
        ", [
            $status,
            user()['id'],
            $_POST['review_comments'] ?? null,
            $status,
            $status,
            user()['id'],
            $changeId
        ]);

        $change = $this->db->query("SELECT lekgotla_session_id FROM lekgotla_priority_changes WHERE id = ?", [$changeId])->fetch();

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Change ' . $status . ' successfully'];
        redirect('/lekgotla/session/' . $change['lekgotla_session_id']);
    }
}
