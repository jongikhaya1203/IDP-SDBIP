<?php
/**
 * AI Report Controller
 * Generates comprehensive performance reports using OpenAI
 */

class AIReportController {

    public function index(): void {
        $db = db();

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Get existing AI reports
        $reports = $db->fetchAll("
            SELECT
                ar.*,
                fy.year_label,
                d.name as directorate_name,
                u.first_name as generated_by_name
            FROM ai_reports ar
            JOIN financial_years fy ON fy.id = ar.financial_year_id
            LEFT JOIN directorates d ON d.id = ar.directorate_id
            LEFT JOIN users u ON u.id = ar.generated_by
            ORDER BY ar.created_at DESC
            LIMIT 20
        ");

        $directorates = $db->fetchAll(
            "SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name"
        );

        $data = [
            'title' => 'AI Performance Reports',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'AI Analysis']
            ],
            'financialYear' => $financialYear,
            'reports' => $reports,
            'directorates' => $directorates,
            'hasApiKey' => !empty(OPENAI_API_KEY)
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/reports/ai-index.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    public function generate(): void {
        $db = db();
        $user = user();

        if (empty(OPENAI_API_KEY)) {
            flash('error', 'OpenAI API key not configured. Please set OPENAI_API_KEY in .env file.');
            redirect('/reports/ai');
            return;
        }

        $reportType = $_POST['report_type'] ?? 'quarterly_performance';
        $quarter = (int)($_POST['quarter'] ?? current_quarter());
        $directorateId = $_POST['directorate_id'] ? (int)$_POST['directorate_id'] : null;

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Gather performance data
        $performanceData = $this->gatherPerformanceData($fyId, $quarter, $directorateId);

        // Generate prompt based on report type
        $prompt = $this->buildPrompt($reportType, $performanceData, $financialYear, $quarter, $directorateId);

        try {
            // Call OpenAI API
            $response = $this->callOpenAI($prompt);

            if (!$response) {
                throw new Exception('Failed to generate report from AI');
            }

            // Parse response
            $content = $response['choices'][0]['message']['content'] ?? '';
            $tokens = $response['usage']['total_tokens'] ?? 0;

            // Extract summary and recommendations
            $summary = $this->extractSection($content, 'Executive Summary');
            $recommendations = $this->extractRecommendations($content);
            $risks = $this->extractRisks($content);

            // Determine title
            $title = $this->generateTitle($reportType, $quarter, $directorateId);

            // Save report
            $reportId = $db->insert('ai_reports', [
                'financial_year_id' => $fyId,
                'quarter' => $quarter,
                'report_type' => $reportType,
                'directorate_id' => $directorateId,
                'title' => $title,
                'content' => $content,
                'summary' => $summary,
                'recommendations' => json_encode($recommendations),
                'risk_flags' => json_encode($risks),
                'generated_by' => $user['id'],
                'model_used' => OPENAI_MODEL,
                'generation_tokens' => $tokens
            ]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => 'create',
                'table_name' => 'ai_reports',
                'record_id' => $reportId
            ]);

            flash('success', 'AI report generated successfully');
            redirect('/reports/ai/' . $reportId);
        } catch (Exception $e) {
            error_log('AI Report generation failed: ' . $e->getMessage());
            flash('error', 'Failed to generate report: ' . $e->getMessage());
            redirect('/reports/ai');
        }
    }

    public function show(string $id): void {
        $db = db();

        $report = $db->fetch("
            SELECT
                ar.*,
                fy.year_label,
                d.name as directorate_name,
                u.first_name as generated_by_first, u.last_name as generated_by_last
            FROM ai_reports ar
            JOIN financial_years fy ON fy.id = ar.financial_year_id
            LEFT JOIN directorates d ON d.id = ar.directorate_id
            LEFT JOIN users u ON u.id = ar.generated_by
            WHERE ar.id = ?
        ", [$id]);

        if (!$report) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $report['recommendations'] = json_decode($report['recommendations'] ?? '[]', true);
        $report['risk_flags'] = json_decode($report['risk_flags'] ?? '[]', true);

        $data = [
            'title' => $report['title'],
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => 'AI Analysis', 'url' => '/reports/ai'],
                ['label' => $report['title']]
            ],
            'report' => $report
        ];

        ob_start();
        extract($data);
        include VIEWS_PATH . '/reports/ai-detail.php';
        $content = ob_get_clean();

        include VIEWS_PATH . '/layouts/main.php';
    }

    private function gatherPerformanceData(int $fyId, int $quarter, ?int $directorateId): array {
        $db = db();

        $dirFilter = $directorateId ? 'AND k.directorate_id = ?' : '';
        // Base params for JOIN conditions: financial_year_id, quarter
        $baseParams = [$fyId, $quarter];
        // Params for WHERE clause financial_year_id + optional directorate filter
        $whereParams = $directorateId ? [$fyId, $directorateId] : [$fyId];

        // Overall statistics
        $stats = $db->fetch("
            SELECT
                COUNT(DISTINCT k.id) as total_kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                SUM(CASE WHEN qa.achievement_status = 'partially_achieved' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN qa.achievement_status = 'not_achieved' THEN 1 ELSE 0 END) as not_achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as avg_rating,
                ROUND(AVG(qa.self_rating), 2) as avg_self_rating,
                ROUND(AVG(qa.manager_rating), 2) as avg_manager_rating
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ? AND qa.quarter = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1 {$dirFilter}
        ", array_merge($baseParams, $whereParams));

        // Directorate breakdown
        $directorates = $db->fetchAll("
            SELECT
                d.name as directorate,
                COUNT(DISTINCT k.id) as kpis,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved,
                ROUND(AVG(qa.aggregated_rating), 2) as rating
            FROM directorates d
            LEFT JOIN kpis k ON k.directorate_id = d.id AND k.is_active = 1
            LEFT JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id AND so.financial_year_id = ?
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ? AND qa.quarter = ?
            WHERE d.is_active = 1 " . ($directorateId ? "AND d.id = ?" : "") . "
            GROUP BY d.id, d.name
        ", array_merge([$fyId, $fyId, $quarter], $directorateId ? [$directorateId] : []));

        // SLA breakdown
        $slaBreakdown = $db->fetchAll("
            SELECT
                k.sla_category,
                COUNT(*) as count,
                SUM(CASE WHEN qa.achievement_status = 'achieved' THEN 1 ELSE 0 END) as achieved
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ? AND qa.quarter = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1 {$dirFilter}
            GROUP BY k.sla_category
        ", array_merge($baseParams, $whereParams));

        // Underperforming KPIs
        $underperforming = $db->fetchAll("
            SELECT k.kpi_code, k.kpi_name, d.name as directorate, qa.target_value, qa.actual_value, qa.variance
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ? AND qa.quarter = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1 AND qa.achievement_status = 'not_achieved' {$dirFilter}
            ORDER BY qa.variance ASC
            LIMIT 10
        ", array_merge($baseParams, $whereParams));

        // Top performers
        $topPerformers = $db->fetchAll("
            SELECT k.kpi_code, k.kpi_name, d.name as directorate, qa.aggregated_rating, qa.variance
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            JOIN directorates d ON d.id = k.directorate_id
            LEFT JOIN kpi_quarterly_actuals qa ON qa.kpi_id = k.id AND qa.financial_year_id = ? AND qa.quarter = ?
            WHERE so.financial_year_id = ? AND k.is_active = 1 AND qa.aggregated_rating >= 4 {$dirFilter}
            ORDER BY qa.aggregated_rating DESC
            LIMIT 10
        ", array_merge($baseParams, $whereParams));

        // Budget data
        $budget = $db->fetch("
            SELECT
                SUM(capital_expenditure_projected) as capex_projected,
                SUM(capital_expenditure_actual) as capex_actual,
                SUM(operating_expenditure_projected) as opex_projected,
                SUM(operating_expenditure_actual) as opex_actual
            FROM budget_projections
            WHERE financial_year_id = ? " . ($directorateId ? "AND directorate_id = ?" : "") . "
        ", $directorateId ? [$fyId, $directorateId] : [$fyId]);

        return [
            'stats' => $stats,
            'directorates' => $directorates,
            'sla_breakdown' => $slaBreakdown,
            'underperforming' => $underperforming,
            'top_performers' => $topPerformers,
            'budget' => $budget
        ];
    }

    private function buildPrompt(string $reportType, array $data, array $fy, int $quarter, ?int $directorateId): string {
        $quarterLabel = quarter_label($quarter);
        $dirLabel = $directorateId ? " for the selected directorate" : " organization-wide";

        $prompt = "You are a municipal performance analyst for a South African municipality. Generate a comprehensive performance report for {$fy['year_label']} {$quarterLabel}{$dirLabel}.

## Performance Data

### Overall Statistics
- Total KPIs: {$data['stats']['total_kpis']}
- Achieved: {$data['stats']['achieved']}
- Partially Achieved: {$data['stats']['partial']}
- Not Achieved: {$data['stats']['not_achieved']}
- Average Rating: {$data['stats']['avg_rating']}/5
- Self-Assessment Average: {$data['stats']['avg_self_rating']}
- Manager Assessment Average: {$data['stats']['avg_manager_rating']}

### Directorate Performance
";
        foreach ($data['directorates'] as $d) {
            $prompt .= "- {$d['directorate']}: {$d['achieved']}/{$d['kpis']} achieved, Rating: {$d['rating']}\n";
        }

        $prompt .= "\n### SLA Category Breakdown\n";
        foreach ($data['sla_breakdown'] as $sla) {
            $prompt .= "- {$sla['sla_category']}: {$sla['achieved']}/{$sla['count']} achieved\n";
        }

        if (!empty($data['underperforming'])) {
            $prompt .= "\n### Underperforming KPIs\n";
            foreach ($data['underperforming'] as $kpi) {
                $prompt .= "- {$kpi['kpi_code']}: {$kpi['kpi_name']} (Directorate: {$kpi['directorate']}, Variance: {$kpi['variance']}%)\n";
            }
        }

        if (!empty($data['top_performers'])) {
            $prompt .= "\n### Top Performing KPIs\n";
            foreach ($data['top_performers'] as $kpi) {
                $prompt .= "- {$kpi['kpi_code']}: {$kpi['kpi_name']} (Rating: {$kpi['aggregated_rating']})\n";
            }
        }

        $capexUtil = $data['budget']['capex_projected'] > 0
            ? round(($data['budget']['capex_actual'] / $data['budget']['capex_projected']) * 100, 1)
            : 0;
        $opexUtil = $data['budget']['opex_projected'] > 0
            ? round(($data['budget']['opex_actual'] / $data['budget']['opex_projected']) * 100, 1)
            : 0;

        $prompt .= "\n### Budget Utilization
- Capital Expenditure: {$capexUtil}% utilized
- Operating Expenditure: {$opexUtil}% utilized

## Required Report Sections

Please generate a comprehensive report with the following sections:

1. **Executive Summary** (2-3 paragraphs)
2. **Key Achievements** (bullet points)
3. **Areas of Concern** (bullet points with analysis)
4. **SLA Dependency Analysis** (impact of budget, internal controls, and HR vacancies)
5. **Comparative Analysis** (directorate comparison if applicable)
6. **Risk Assessment** (identify risks with severity levels)
7. **Recommendations** (actionable items with priority levels)
8. **Outlook for Next Quarter**

Format the response in markdown. Be specific, data-driven, and actionable. Reference MFMA compliance requirements where relevant.";

        return $prompt;
    }

    private function callOpenAI(string $prompt): ?array {
        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => OPENAI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional municipal performance analyst specializing in South African local government. Your reports are data-driven, compliant with MFMA regulations, and actionable.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => OPENAI_MAX_TOKENS,
            'temperature' => 0.7
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY
            ],
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('OpenAI API error: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        return json_decode($response, true);
    }

    private function extractSection(string $content, string $sectionName): string {
        $pattern = '/##\s*' . preg_quote($sectionName, '/') . '\s*\n(.*?)(?=\n##|\z)/s';
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    private function extractRecommendations(string $content): array {
        $recommendations = [];
        $section = $this->extractSection($content, 'Recommendations');
        if (preg_match_all('/[-*]\s*(.+)/m', $section, $matches)) {
            $recommendations = array_slice($matches[1], 0, 10);
        }
        return $recommendations;
    }

    private function extractRisks(string $content): array {
        $risks = [];
        $section = $this->extractSection($content, 'Risk Assessment');
        if (preg_match_all('/[-*]\s*(.+)/m', $section, $matches)) {
            $risks = array_slice($matches[1], 0, 10);
        }
        return $risks;
    }

    private function generateTitle(string $reportType, int $quarter, ?int $directorateId): string {
        $db = db();
        $types = [
            'quarterly_performance' => 'Quarterly Performance Report',
            'annual_performance' => 'Annual Performance Report',
            'directorate_analysis' => 'Directorate Analysis',
            'budget_analysis' => 'Budget Analysis',
            'risk_assessment' => 'Risk Assessment'
        ];

        $title = $types[$reportType] ?? 'Performance Report';
        $title .= ' - ' . quarter_label($quarter);

        if ($directorateId) {
            $dir = $db->fetch("SELECT code FROM directorates WHERE id = ?", [$directorateId]);
            if ($dir) {
                $title .= ' - ' . $dir['code'];
            }
        }

        return $title;
    }

    /**
     * Generate report using predefined templates (no OpenAI required)
     */
    public function generateTemplate(): void {
        $db = db();
        $user = user();

        $reportType = $_POST['report_type'] ?? 'quarterly_performance';
        $quarter = (int)($_POST['quarter'] ?? current_quarter());
        $directorateId = !empty($_POST['directorate_id']) ? (int)$_POST['directorate_id'] : null;

        $financialYear = $db->fetch(
            "SELECT * FROM financial_years WHERE is_current = 1 LIMIT 1"
        );
        $fyId = $financialYear['id'] ?? 0;

        // Gather performance data
        $performanceData = $this->gatherPerformanceData($fyId, $quarter, $directorateId);

        // Generate report content from template
        $content = $this->generateTemplateContent($reportType, $performanceData, $financialYear, $quarter, $directorateId);

        // Extract summary and recommendations
        $summary = $this->extractSection($content, 'Executive Summary');
        $recommendations = $this->extractRecommendations($content);
        $risks = $this->extractRisks($content);

        // Determine title
        $title = $this->generateTitle($reportType, $quarter, $directorateId);

        try {
            // Save report
            $reportId = $db->insert('ai_reports', [
                'financial_year_id' => $fyId,
                'quarter' => $quarter,
                'report_type' => $reportType,
                'directorate_id' => $directorateId,
                'title' => $title,
                'content' => $content,
                'summary' => $summary,
                'recommendations' => json_encode($recommendations),
                'risk_flags' => json_encode($risks),
                'generated_by' => $user['id'],
                'model_used' => 'Template Engine',
                'generation_tokens' => 0
            ]);

            // Audit log
            $db->insert('audit_log', [
                'user_id' => $user['id'],
                'action' => 'create',
                'table_name' => 'ai_reports',
                'record_id' => $reportId
            ]);

            flash('success', 'Report generated successfully using template engine');
            redirect('/reports/ai/' . $reportId);
        } catch (Exception $e) {
            error_log('Template Report generation failed: ' . $e->getMessage());
            flash('error', 'Failed to generate report: ' . $e->getMessage());
            redirect('/reports/ai');
        }
    }

    /**
     * Generate report content using predefined templates
     */
    private function generateTemplateContent(string $reportType, array $data, array $fy, int $quarter, ?int $directorateId): string {
        $db = db();
        $quarterLabel = quarter_label($quarter);
        $yearLabel = $fy['year_label'] ?? date('Y');

        $dirName = 'Organization-wide';
        if ($directorateId) {
            $dir = $db->fetch("SELECT name FROM directorates WHERE id = ?", [$directorateId]);
            $dirName = $dir['name'] ?? 'Selected Directorate';
        }

        $stats = $data['stats'];
        $totalKpis = (int)($stats['total_kpis'] ?? 0);
        $achieved = (int)($stats['achieved'] ?? 0);
        $partial = (int)($stats['partial'] ?? 0);
        $notAchieved = (int)($stats['not_achieved'] ?? 0);
        $avgRating = number_format((float)($stats['avg_rating'] ?? 0), 2);

        $achievementRate = $totalKpis > 0 ? round(($achieved / $totalKpis) * 100, 1) : 0;
        $partialRate = $totalKpis > 0 ? round(($partial / $totalKpis) * 100, 1) : 0;
        $notAchievedRate = $totalKpis > 0 ? round(($notAchieved / $totalKpis) * 100, 1) : 0;

        // Budget calculations
        $budget = $data['budget'];
        $capexProjected = (float)($budget['capex_projected'] ?? 0);
        $capexActual = (float)($budget['capex_actual'] ?? 0);
        $opexProjected = (float)($budget['opex_projected'] ?? 0);
        $opexActual = (float)($budget['opex_actual'] ?? 0);
        $capexUtil = $capexProjected > 0 ? round(($capexActual / $capexProjected) * 100, 1) : 0;
        $opexUtil = $opexProjected > 0 ? round(($opexActual / $opexProjected) * 100, 1) : 0;

        // Performance status
        $performanceStatus = 'Critical';
        $statusColor = 'danger';
        if ($achievementRate >= 80) {
            $performanceStatus = 'Excellent';
            $statusColor = 'success';
        } elseif ($achievementRate >= 60) {
            $performanceStatus = 'Good';
            $statusColor = 'info';
        } elseif ($achievementRate >= 40) {
            $performanceStatus = 'Needs Improvement';
            $statusColor = 'warning';
        }

        $content = "# {$this->generateTitle($reportType, $quarter, $directorateId)}\n\n";
        $content .= "**Generated:** " . date('d F Y H:i') . "  \n";
        $content .= "**Financial Year:** {$yearLabel}  \n";
        $content .= "**Quarter:** {$quarterLabel}  \n";
        $content .= "**Scope:** {$dirName}  \n\n";
        $content .= "---\n\n";

        // Executive Summary
        $content .= "## Executive Summary\n\n";
        $content .= "This report presents a comprehensive analysis of performance for {$yearLabel} {$quarterLabel}. ";
        $content .= "The assessment covers {$totalKpis} Key Performance Indicators (KPIs) across various service delivery areas.\n\n";

        $content .= "**Overall Performance Status: {$performanceStatus}**\n\n";

        $content .= "The organization achieved an overall KPI achievement rate of **{$achievementRate}%** with an average performance rating of **{$avgRating}/5**. ";
        if ($achievementRate >= 70) {
            $content .= "This indicates strong service delivery and effective implementation of strategic objectives. ";
        } elseif ($achievementRate >= 50) {
            $content .= "While progress has been made, there are areas requiring focused attention to improve overall performance. ";
        } else {
            $content .= "Significant challenges are impacting service delivery, requiring immediate intervention and corrective measures. ";
        }
        $content .= "Capital expenditure utilization stands at {$capexUtil}% and operating expenditure at {$opexUtil}%, reflecting the current budget execution status.\n\n";

        // Key Performance Metrics
        $content .= "## Key Performance Metrics\n\n";
        $content .= "| Metric | Value | Percentage |\n";
        $content .= "|--------|-------|------------|\n";
        $content .= "| Total KPIs | {$totalKpis} | 100% |\n";
        $content .= "| Achieved | {$achieved} | {$achievementRate}% |\n";
        $content .= "| Partially Achieved | {$partial} | {$partialRate}% |\n";
        $content .= "| Not Achieved | {$notAchieved} | {$notAchievedRate}% |\n";
        $content .= "| Average Rating | {$avgRating} | - |\n\n";

        // Key Achievements
        $content .= "## Key Achievements\n\n";
        if (!empty($data['top_performers'])) {
            foreach ($data['top_performers'] as $kpi) {
                $rating = number_format((float)($kpi['aggregated_rating'] ?? 0), 1);
                $content .= "- **{$kpi['kpi_code']}**: {$kpi['kpi_name']} - Rating: {$rating}/5 ({$kpi['directorate']})\n";
            }
        } else {
            $content .= "- No KPIs achieved a rating of 4 or above in this period\n";
        }
        $content .= "\n";

        // Areas of Concern
        $content .= "## Areas of Concern\n\n";
        if (!empty($data['underperforming'])) {
            foreach ($data['underperforming'] as $kpi) {
                $variance = number_format((float)($kpi['variance'] ?? 0), 1);
                $content .= "- **{$kpi['kpi_code']}**: {$kpi['kpi_name']} - Variance: {$variance}% ({$kpi['directorate']})\n";
            }
        } else {
            $content .= "- No significant underperforming KPIs identified\n";
        }
        $content .= "\n";

        // Directorate Performance
        if (!empty($data['directorates'])) {
            $content .= "## Directorate Performance Analysis\n\n";
            $content .= "| Directorate | Total KPIs | Achieved | Achievement Rate | Rating |\n";
            $content .= "|-------------|------------|----------|------------------|--------|\n";
            foreach ($data['directorates'] as $d) {
                $dirAchieved = (int)($d['achieved'] ?? 0);
                $dirKpis = (int)($d['kpis'] ?? 0);
                $dirRate = $dirKpis > 0 ? round(($dirAchieved / $dirKpis) * 100, 1) : 0;
                $dirRating = number_format((float)($d['rating'] ?? 0), 2);
                $content .= "| {$d['directorate']} | {$dirKpis} | {$dirAchieved} | {$dirRate}% | {$dirRating}/5 |\n";
            }
            $content .= "\n";
        }

        // SLA Category Breakdown
        if (!empty($data['sla_breakdown'])) {
            $content .= "## SLA Category Analysis\n\n";
            $content .= "| SLA Category | Total KPIs | Achieved | Achievement Rate |\n";
            $content .= "|--------------|------------|----------|------------------|\n";
            foreach ($data['sla_breakdown'] as $sla) {
                $slaAchieved = (int)($sla['achieved'] ?? 0);
                $slaCount = (int)($sla['count'] ?? 0);
                $slaRate = $slaCount > 0 ? round(($slaAchieved / $slaCount) * 100, 1) : 0;
                $category = ucwords(str_replace('_', ' ', $sla['sla_category'] ?? 'Other'));
                $content .= "| {$category} | {$slaCount} | {$slaAchieved} | {$slaRate}% |\n";
            }
            $content .= "\n";
        }

        // Budget Analysis
        $content .= "## Budget Utilization\n\n";
        $content .= "| Budget Type | Projected | Actual | Utilization |\n";
        $content .= "|-------------|-----------|--------|-------------|\n";
        $content .= "| Capital Expenditure (CAPEX) | R" . number_format($capexProjected, 0) . " | R" . number_format($capexActual, 0) . " | {$capexUtil}% |\n";
        $content .= "| Operating Expenditure (OPEX) | R" . number_format($opexProjected, 0) . " | R" . number_format($opexActual, 0) . " | {$opexUtil}% |\n\n";

        if ($capexUtil < 50 || $opexUtil < 50) {
            $content .= "⚠️ **Budget Alert:** Low budget utilization may indicate procurement delays or implementation challenges that require management attention.\n\n";
        }

        // Risk Assessment
        $content .= "## Risk Assessment\n\n";
        $riskCount = 0;

        if ($achievementRate < 60) {
            $content .= "- **HIGH RISK:** Overall KPI achievement rate below 60% - Immediate intervention required\n";
            $riskCount++;
        }
        if ($notAchievedRate > 30) {
            $content .= "- **HIGH RISK:** More than 30% of KPIs not achieved - Service delivery at risk\n";
            $riskCount++;
        }
        if ($capexUtil < 40) {
            $content .= "- **MEDIUM RISK:** Capital expenditure utilization below 40% - Infrastructure projects may be delayed\n";
            $riskCount++;
        }
        if ($opexUtil > 90) {
            $content .= "- **MEDIUM RISK:** Operating expenditure above 90% - Budget pressure for remaining quarter\n";
            $riskCount++;
        }
        if ((float)($stats['avg_rating'] ?? 0) < 3) {
            $content .= "- **MEDIUM RISK:** Average performance rating below 3 - Quality of service delivery concerns\n";
            $riskCount++;
        }

        if ($riskCount === 0) {
            $content .= "- No significant risks identified based on current performance metrics\n";
        }
        $content .= "\n";

        // Recommendations
        $content .= "## Recommendations\n\n";

        if ($achievementRate < 70) {
            $content .= "- **Priority: HIGH** - Conduct root cause analysis for underperforming KPIs and develop corrective action plans\n";
        }
        if (!empty($data['underperforming'])) {
            $content .= "- **Priority: HIGH** - Focus immediate attention on the " . count($data['underperforming']) . " underperforming KPIs identified\n";
        }
        if ($capexUtil < 60) {
            $content .= "- **Priority: MEDIUM** - Review capital project implementation schedules and address procurement bottlenecks\n";
        }
        $content .= "- **Priority: MEDIUM** - Strengthen monitoring and evaluation processes for early warning detection\n";
        $content .= "- **Priority: MEDIUM** - Review resource allocation to ensure alignment with strategic priorities\n";
        $content .= "- **Priority: LOW** - Document lessons learned and best practices from top-performing areas\n";
        $content .= "- **Priority: LOW** - Schedule quarterly performance review meetings with all directorates\n\n";

        // MFMA Compliance Note
        $content .= "## MFMA Compliance Notes\n\n";
        $content .= "This report has been prepared in accordance with the Municipal Finance Management Act (MFMA) requirements for performance reporting. ";
        $content .= "Key compliance considerations include:\n\n";
        $content .= "- Service Delivery Budget Implementation Plan (SDBIP) alignment\n";
        $content .= "- Integrated Development Plan (IDP) strategic objective tracking\n";
        $content .= "- Quarterly performance assessment as per Section 72 reporting requirements\n";
        $content .= "- Budget variance analysis and expenditure monitoring\n\n";

        // Next Quarter Outlook
        $content .= "## Outlook for Next Quarter\n\n";
        $nextQuarter = $quarter < 4 ? $quarter + 1 : 1;
        $nextQuarterLabel = quarter_label($nextQuarter);

        $content .= "Looking ahead to {$nextQuarterLabel}, the following focus areas are recommended:\n\n";
        $content .= "1. **Performance Recovery:** Implement corrective measures for underperforming KPIs\n";
        $content .= "2. **Budget Execution:** Accelerate project implementation to improve expenditure patterns\n";
        $content .= "3. **Quality Assurance:** Maintain standards in areas showing strong performance\n";
        $content .= "4. **Stakeholder Engagement:** Regular communication with council and community on progress\n";
        $content .= "5. **Capacity Building:** Address any skills gaps identified during the assessment\n\n";

        $content .= "---\n\n";
        $content .= "*This report was generated using the IDP Performance Management System template engine.*\n";

        return $content;
    }
}
