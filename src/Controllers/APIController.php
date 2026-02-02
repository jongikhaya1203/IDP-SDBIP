<?php
/**
 * API Controller
 * Handles AJAX requests for dashboard and data endpoints
 */

class APIController {

    public function directorates(): void {
        $db = db();

        $directorates = $db->fetchAll("
            SELECT id, name, code, budget_allocation
            FROM directorates
            WHERE is_active = 1
            ORDER BY name
        ");

        json_response(['directorates' => $directorates]);
    }

    public function directorateKpis(string $id): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $kpis = $db->fetchAll("
            SELECT
                k.id,
                k.kpi_code,
                k.kpi_name,
                k.annual_target,
                k.sla_category,
                qa.actual_value,
                qa.achievement_status,
                qa.aggregated_rating
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE k.directorate_id = ? AND k.is_active = 1 AND so.financial_year_id = ?
            ORDER BY k.kpi_code
        ", [$fyId, $id, $fyId]);

        json_response(['kpis' => $kpis]);
    }

    public function kpis(): void {
        $db = db();
        $user = user();

        $financialYear = $db->fetch(
            "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $where = ['so.financial_year_id = ?', 'k.is_active = 1'];
        $params = [$fyId];

        if (!has_role('admin', 'independent_assessor') && $user['directorate_id']) {
            $where[] = 'k.directorate_id = ?';
            $params[] = $user['directorate_id'];
        }

        $whereClause = implode(' AND ', $where);

        $kpis = $db->fetchAll("
            SELECT
                k.id,
                k.kpi_code,
                k.kpi_name,
                k.unit_of_measure,
                k.annual_target,
                k.sla_category,
                d.code as directorate_code
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            WHERE {$whereClause}
            ORDER BY d.code, k.kpi_code
        ", $params);

        json_response(['kpis' => $kpis]);
    }

    public function kpiActuals(string $id): void {
        $db = db();

        $actuals = $db->fetchAll("
            SELECT
                qa.*,
                fy.year_label
            FROM kpi_quarterly_actuals qa
            JOIN financial_years fy ON fy.id = qa.financial_year_id
            WHERE qa.kpi_id = ?
            ORDER BY fy.start_date DESC, qa.quarter
        ", [$id]);

        json_response(['actuals' => $actuals]);
    }

    public function performanceSummary(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $summary = $db->fetchAll("
            SELECT
                d.id as directorate_id,
                d.name as directorate_name,
                d.code as directorate_code,
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                SUM(CASE WHEN qa.achievement_status = 'partially_achieved' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN qa.achievement_status = 'not_achieved' THEN 1 ELSE 0 END) as not_achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating
            FROM directorates d
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ?
            WHERE d.is_active = 1
            GROUP BY d.id, d.name, d.code
            ORDER BY avg_rating DESC
        ", [$fyId, $fyId]);

        json_response(['summary' => $summary]);
    }

    public function performanceTrends(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT id FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        $trends = $db->fetchAll("
            SELECT
                qa.quarter,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                COUNT(*) as total_assessed,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved
            FROM kpi_quarterly_actuals qa
            WHERE qa.financial_year_id = ? AND qa.status = 'approved'
            GROUP BY qa.quarter
            ORDER BY qa.quarter
        ", [$fyId]);

        json_response(['trends' => $trends]);
    }

    public function notifications(): void {
        $user = user();
        $db = db();

        $notifications = $db->fetchAll("
            SELECT
                id,
                type,
                title,
                message,
                link,
                is_read,
                created_at,
                TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_ago
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$user['id']]);

        // Format time ago
        foreach ($notifications as &$n) {
            $mins = $n['minutes_ago'];
            if ($mins < 60) {
                $n['time_ago'] = $mins . 'm ago';
            } elseif ($mins < 1440) {
                $n['time_ago'] = floor($mins / 60) . 'h ago';
            } else {
                $n['time_ago'] = floor($mins / 1440) . 'd ago';
            }
        }

        $unreadCount = $db->fetch(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$user['id']]
        );

        json_response([
            'notifications' => $notifications,
            'unread_count' => $unreadCount['count'] ?? 0
        ]);
    }

    public function markNotificationRead(string $id): void {
        $db = db();
        $user = user();

        $db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ], 'id = ? AND user_id = ?', [$id, $user['id']]);

        json_response(['success' => true]);
    }
}
