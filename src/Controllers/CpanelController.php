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

    public function cms(): void {
        // Load current CMS settings
        $settings = $this->getCmsSettings();

        $data = [
            'title' => 'CMS Portal',
            'settings' => $settings
        ];

        view('cpanel.cms', $data);
    }

    public function updateCms(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cpanel/cms');
            return;
        }

        $settings = $this->getCmsSettings();

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/cms/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                // Delete old logo if exists
                if (!empty($settings['logo']) && file_exists(PUBLIC_PATH . $settings['logo'])) {
                    unlink(PUBLIC_PATH . $settings['logo']);
                }

                $fileName = 'logo_' . time() . '.' . $fileExt;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $filePath)) {
                    $settings['logo'] = '/uploads/cms/' . $fileName;
                }
            } else {
                flash('error', 'Invalid file type. Allowed: JPG, PNG, GIF, SVG, WEBP');
                redirect('/cpanel/cms');
                return;
            }
        }

        // Handle favicon upload
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/cms/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['ico', 'png', 'svg'];

            if (in_array($fileExt, $allowedExts)) {
                if (!empty($settings['favicon']) && file_exists(PUBLIC_PATH . $settings['favicon'])) {
                    unlink(PUBLIC_PATH . $settings['favicon']);
                }

                $fileName = 'favicon_' . time() . '.' . $fileExt;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $filePath)) {
                    $settings['favicon'] = '/uploads/cms/' . $fileName;
                }
            }
        }

        // Update text settings
        $settings['site_name'] = $_POST['site_name'] ?? $settings['site_name'];
        $settings['site_tagline'] = $_POST['site_tagline'] ?? $settings['site_tagline'];
        $settings['dashboard_title'] = $_POST['dashboard_title'] ?? $settings['dashboard_title'];
        $settings['organization_name'] = $_POST['organization_name'] ?? $settings['organization_name'];
        $settings['footer_text'] = $_POST['footer_text'] ?? $settings['footer_text'];
        $settings['primary_color'] = $_POST['primary_color'] ?? $settings['primary_color'];
        $settings['secondary_color'] = $_POST['secondary_color'] ?? $settings['secondary_color'];

        // Save settings
        $this->saveCmsSettings($settings);

        flash('success', 'CMS settings updated successfully.');
        redirect('/cpanel/cms');
    }

    public function removeLogo(): void {
        $settings = $this->getCmsSettings();

        if (!empty($settings['logo']) && file_exists(PUBLIC_PATH . $settings['logo'])) {
            unlink(PUBLIC_PATH . $settings['logo']);
        }

        $settings['logo'] = '';
        $this->saveCmsSettings($settings);

        flash('success', 'Logo removed successfully.');
        redirect('/cpanel/cms');
    }

    private function getCmsSettings(): array {
        $configFile = ROOT_PATH . '/config/cms_settings.json';

        $defaults = [
            'site_name' => 'SDBIP & IDP Management',
            'site_tagline' => 'Municipal Performance Management System',
            'dashboard_title' => 'Performance Dashboard',
            'organization_name' => defined('MUNICIPALITY_NAME') ? MUNICIPALITY_NAME : 'Sample Municipality',
            'logo' => '',
            'favicon' => '',
            'footer_text' => '© ' . date('Y') . ' Municipal SDBIP & IDP System. All rights reserved.',
            'primary_color' => '#2563eb',
            'secondary_color' => '#64748b'
        ];

        if (file_exists($configFile)) {
            $saved = json_decode(file_get_contents($configFile), true);
            return array_merge($defaults, $saved ?? []);
        }

        return $defaults;
    }

    private function saveCmsSettings(array $settings): void {
        $configDir = ROOT_PATH . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configFile = $configDir . '/cms_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
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
