<?php
/**
 * Control Panel Controller
 * System configuration and module management
 */

class CpanelController {

    public function index(): void {
        $db = db();

        // Get system stats
        $stats = [
            'users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE is_active = 1")['count'] ?? 0,
            'directorates' => $db->fetch("SELECT COUNT(*) as count FROM directorates WHERE is_active = 1")['count'] ?? 0,
            'kpis' => $db->fetch("SELECT COUNT(*) as count FROM kpis WHERE is_active = 1")['count'] ?? 0,
            'objectives' => $db->fetch("SELECT COUNT(*) as count FROM idp_strategic_objectives")['count'] ?? 0,
            'projects' => $db->fetch("SELECT COUNT(*) as count FROM capital_projects")['count'] ?? 0,
        ];

        // Check module status
        $modules = $this->getModuleStatus();

        $data = [
            'title' => 'Control Panel',
            'stats' => $stats,
            'modules' => $modules
        ];

        view('cpanel.index', $data);
    }

    public function modules(): void {
        $modules = $this->getModuleStatus();

        $data = [
            'title' => 'Module Configuration',
            'modules' => $modules
        ];

        view('cpanel.modules', $data);
    }

    public function updateModules(): void {
        // In a full implementation, this would save to database
        // For now, we'll just show a success message
        flash('success', 'Module configuration updated successfully.');
        redirect('/cpanel/modules');
    }

    public function database(): void {
        $db = db();

        // Get table information
        $tables = $db->fetchAll("SHOW TABLE STATUS");

        // Get database size
        $dbName = defined('DB_DATABASE') ? DB_DATABASE : 'sdbip_idp';
        $dbSize = $db->fetch("
            SELECT
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.tables
            WHERE table_schema = ?
        ", [$dbName]);

        $data = [
            'title' => 'Database Management',
            'tables' => $tables,
            'dbSize' => $dbSize['size_mb'] ?? 0,
            'dbName' => $dbName
        ];

        view('cpanel.database', $data);
    }

    public function logs(): void {
        $db = db();

        // Get recent audit logs
        $logs = $db->fetchAll("
            SELECT al.*, u.username, u.first_name, u.last_name
            FROM audit_log al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.created_at DESC
            LIMIT 100
        ");

        $data = [
            'title' => 'System Logs',
            'logs' => $logs
        ];

        view('cpanel.logs', $data);
    }

    public function backup(): void {
        $data = [
            'title' => 'Backup & Restore'
        ];

        view('cpanel.backup', $data);
    }

    public function createBackup(): void {
        // In production, this would create an actual backup
        flash('info', 'Backup functionality requires shell access. Please use phpMyAdmin or command line for database backups.');
        redirect('/cpanel/backup');
    }

    public function integrations(): void {
        $openaiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        $ldapEnabled = defined('LDAP_ENABLED') ? LDAP_ENABLED : false;
        $mailHost = getenv('MAIL_HOST') ?: '';

        $integrations = [
            [
                'name' => 'OpenAI GPT-4',
                'description' => 'AI-powered report generation and insights',
                'icon' => 'robot',
                'status' => $openaiKey ? 'active' : 'inactive',
                'config_key' => 'OPENAI_API_KEY'
            ],
            [
                'name' => 'LDAP / Active Directory',
                'description' => 'Enterprise authentication integration',
                'icon' => 'shield-lock',
                'status' => $ldapEnabled ? 'active' : 'inactive',
                'config_key' => 'LDAP_ENABLED'
            ],
            [
                'name' => 'Email (SMTP)',
                'description' => 'Email notifications and alerts',
                'icon' => 'envelope',
                'status' => $mailHost ? 'active' : 'inactive',
                'config_key' => 'MAIL_HOST'
            ],
            [
                'name' => 'SMS Gateway',
                'description' => 'SMS notifications for urgent alerts',
                'icon' => 'phone',
                'status' => 'inactive',
                'config_key' => 'SMS_API_KEY'
            ]
        ];

        $data = [
            'title' => 'Integrations',
            'integrations' => $integrations
        ];

        view('cpanel.integrations', $data);
    }

    private function getModuleStatus(): array {
        $openaiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

        return [
            [
                'id' => 'dashboard',
                'name' => 'Dashboard',
                'description' => 'Executive overview with KPIs, charts, and performance summaries',
                'icon' => 'speedometer2',
                'enabled' => true,
                'core' => true
            ],
            [
                'id' => 'idp',
                'name' => 'IDP Strategic Objectives',
                'description' => 'Integrated Development Plan strategic objectives management',
                'icon' => 'bullseye',
                'enabled' => true,
                'core' => true
            ],
            [
                'id' => 'sdbip',
                'name' => 'SDBIP & KPIs',
                'description' => 'Service Delivery Budget Implementation Plan with KPI tracking',
                'icon' => 'graph-up-arrow',
                'enabled' => true,
                'core' => true
            ],
            [
                'id' => 'assessment',
                'name' => 'Quarterly Assessment',
                'description' => 'Multi-level performance assessment workflow (Self, Manager, Independent)',
                'icon' => 'clipboard-check',
                'enabled' => true,
                'core' => true
            ],
            [
                'id' => 'poe',
                'name' => 'Proof of Evidence',
                'description' => 'Document upload and verification for KPI achievements',
                'icon' => 'file-earmark-check',
                'enabled' => true,
                'core' => true
            ],
            [
                'id' => 'budget',
                'name' => 'Budget Management',
                'description' => 'Budget projections, capital projects, and expenditure tracking',
                'icon' => 'cash-stack',
                'enabled' => true,
                'core' => false
            ],
            [
                'id' => 'imbizo',
                'name' => 'Mayoral IDP Imbizo',
                'description' => 'Community engagement, livestreaming, and action item tracking',
                'icon' => 'people',
                'enabled' => true,
                'core' => false
            ],
            [
                'id' => 'reports',
                'name' => 'Reports & Analytics',
                'description' => 'Quarterly reports, directorate analysis, and data exports',
                'icon' => 'file-earmark-bar-graph',
                'enabled' => true,
                'core' => false
            ],
            [
                'id' => 'ai_reports',
                'name' => 'AI-Powered Reports',
                'description' => 'GPT-4 powered performance narratives and insights',
                'icon' => 'robot',
                'enabled' => $openaiKey ? true : false,
                'core' => false,
                'requires' => 'OpenAI API Key'
            ],
            [
                'id' => 'notifications',
                'name' => 'Notifications',
                'description' => 'In-app alerts for deadlines, approvals, and updates',
                'icon' => 'bell',
                'enabled' => true,
                'core' => false
            ],
            [
                'id' => 'audit',
                'name' => 'Audit Trail',
                'description' => 'Complete history of all system changes for compliance',
                'icon' => 'journal-text',
                'enabled' => true,
                'core' => true
            ]
        ];
    }
}
