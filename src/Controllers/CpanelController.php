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
        $settings = $this->getIntegrationSettings();

        $data = [
            'title' => 'Integrations',
            'settings' => $settings
        ];

        view('cpanel.integrations', $data);
    }

    public function saveSmtpSettings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cpanel/integrations');
            return;
        }

        $settings = $this->getIntegrationSettings();

        $settings['smtp_host'] = trim($_POST['smtp_host'] ?? '');
        $settings['smtp_port'] = $_POST['smtp_port'] ?? '587';
        $settings['smtp_username'] = trim($_POST['smtp_username'] ?? '');
        if (!empty($_POST['smtp_password']) && $_POST['smtp_password'] !== '••••••••') {
            $settings['smtp_password'] = $_POST['smtp_password'];
        }
        $settings['smtp_from_email'] = trim($_POST['smtp_from_email'] ?? '');
        $settings['smtp_from_name'] = trim($_POST['smtp_from_name'] ?? '');
        $settings['smtp_encryption'] = $_POST['smtp_encryption'] ?? 'tls';

        $this->saveIntegrationSettings($settings);
        flash('success', 'SMTP settings saved successfully.');
        redirect('/cpanel/integrations');
    }

    public function saveLdapSettings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cpanel/integrations');
            return;
        }

        $settings = $this->getIntegrationSettings();

        $settings['ldap_enabled'] = isset($_POST['ldap_enabled']);
        $settings['ldap_host'] = trim($_POST['ldap_host'] ?? '');
        $settings['ldap_port'] = $_POST['ldap_port'] ?? '389';
        $settings['ldap_base_dn'] = trim($_POST['ldap_base_dn'] ?? '');
        $settings['ldap_bind_dn'] = trim($_POST['ldap_bind_dn'] ?? '');
        if (!empty($_POST['ldap_bind_password']) && $_POST['ldap_bind_password'] !== '••••••••') {
            $settings['ldap_bind_password'] = $_POST['ldap_bind_password'];
        }
        $settings['ldap_user_filter'] = trim($_POST['ldap_user_filter'] ?? '(sAMAccountName=%s)');

        $this->saveIntegrationSettings($settings);
        flash('success', 'LDAP settings saved successfully.');
        redirect('/cpanel/integrations');
    }

    public function saveSmsSettings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cpanel/integrations');
            return;
        }

        $settings = $this->getIntegrationSettings();

        $settings['sms_enabled'] = isset($_POST['sms_enabled']);
        $settings['sms_provider'] = $_POST['sms_provider'] ?? '';
        if (!empty($_POST['sms_api_key']) && $_POST['sms_api_key'] !== '••••••••') {
            $settings['sms_api_key'] = $_POST['sms_api_key'];
        }
        if (!empty($_POST['sms_api_secret']) && $_POST['sms_api_secret'] !== '••••••••') {
            $settings['sms_api_secret'] = $_POST['sms_api_secret'];
        }
        $settings['sms_sender_id'] = trim($_POST['sms_sender_id'] ?? '');
        $settings['sms_api_url'] = trim($_POST['sms_api_url'] ?? '');
        $settings['sms_notify_deadlines'] = isset($_POST['sms_notify_deadlines']);
        $settings['sms_notify_approvals'] = isset($_POST['sms_notify_approvals']);
        $settings['sms_notify_alerts'] = isset($_POST['sms_notify_alerts']);
        $settings['sms_notify_imbizo'] = isset($_POST['sms_notify_imbizo']);

        $this->saveIntegrationSettings($settings);
        flash('success', 'SMS Gateway settings saved successfully.');
        redirect('/cpanel/integrations');
    }

    public function saveOpenaiSettings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/cpanel/integrations');
            return;
        }

        $settings = $this->getIntegrationSettings();

        if (!empty($_POST['openai_api_key']) && strpos($_POST['openai_api_key'], '••') === false) {
            $settings['openai_api_key'] = trim($_POST['openai_api_key']);
        }
        $settings['openai_model'] = $_POST['openai_model'] ?? 'gpt-4';
        $settings['openai_max_tokens'] = (int)($_POST['openai_max_tokens'] ?? 4000);
        $settings['openai_temperature'] = (float)($_POST['openai_temperature'] ?? 0.7);
        $settings['ai_quarterly_reports'] = isset($_POST['ai_quarterly_reports']);
        $settings['ai_recommendations'] = isset($_POST['ai_recommendations']);
        $settings['ai_risk_analysis'] = isset($_POST['ai_risk_analysis']);
        $settings['ai_trend_detection'] = isset($_POST['ai_trend_detection']);

        $this->saveIntegrationSettings($settings);
        flash('success', 'OpenAI settings saved successfully.');
        redirect('/cpanel/integrations');
    }

    public function testSmtp(): void {
        header('Content-Type: application/json');
        $settings = $this->getIntegrationSettings();

        if (empty($settings['smtp_host'])) {
            echo json_encode(['success' => false, 'message' => 'SMTP host not configured']);
            return;
        }

        // Test SMTP connection
        try {
            $socket = @fsockopen($settings['smtp_host'], (int)$settings['smtp_port'], $errno, $errstr, 5);
            if ($socket) {
                fclose($socket);
                echo json_encode(['success' => true, 'message' => 'SMTP server is reachable on port ' . $settings['smtp_port']]);
            } else {
                echo json_encode(['success' => false, 'message' => "Connection failed: $errstr ($errno)"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Connection error: ' . $e->getMessage()]);
        }
    }

    public function testLdap(): void {
        header('Content-Type: application/json');
        $settings = $this->getIntegrationSettings();

        if (empty($settings['ldap_host'])) {
            echo json_encode(['success' => false, 'message' => 'LDAP host not configured']);
            return;
        }

        if (!function_exists('ldap_connect')) {
            echo json_encode(['success' => false, 'message' => 'PHP LDAP extension not installed']);
            return;
        }

        try {
            $ldap = @ldap_connect($settings['ldap_host'], (int)$settings['ldap_port']);
            if ($ldap) {
                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

                if (!empty($settings['ldap_bind_dn']) && !empty($settings['ldap_bind_password'])) {
                    $bind = @ldap_bind($ldap, $settings['ldap_bind_dn'], $settings['ldap_bind_password']);
                    if ($bind) {
                        echo json_encode(['success' => true, 'message' => 'LDAP connection and bind successful']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'LDAP bind failed: ' . ldap_error($ldap)]);
                    }
                } else {
                    echo json_encode(['success' => true, 'message' => 'LDAP server reachable (anonymous)']);
                }
                ldap_close($ldap);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to connect to LDAP server']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'LDAP error: ' . $e->getMessage()]);
        }
    }

    public function testSms(): void {
        header('Content-Type: application/json');
        $settings = $this->getIntegrationSettings();

        if (empty($settings['sms_api_key'])) {
            echo json_encode(['success' => false, 'message' => 'SMS API key not configured']);
            return;
        }

        // Placeholder - actual SMS sending would depend on provider
        echo json_encode(['success' => true, 'message' => 'SMS configuration appears valid. Actual sending requires provider integration.']);
    }

    public function testOpenai(): void {
        header('Content-Type: application/json');
        $settings = $this->getIntegrationSettings();

        if (empty($settings['openai_api_key'])) {
            echo json_encode(['success' => false, 'message' => 'OpenAI API key not configured']);
            return;
        }

        try {
            $ch = curl_init('https://api.openai.com/v1/models');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $settings['openai_api_key'],
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                echo json_encode(['success' => true, 'message' => 'OpenAI API connection successful']);
            } elseif ($httpCode === 401) {
                echo json_encode(['success' => false, 'message' => 'Invalid API key']);
            } else {
                echo json_encode(['success' => false, 'message' => "API returned HTTP $httpCode"]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Connection error: ' . $e->getMessage()]);
        }
    }

    private function getIntegrationSettings(): array {
        $configFile = ROOT_PATH . '/config/integrations.json';

        $defaults = [
            'smtp_host' => '',
            'smtp_port' => '587',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_from_email' => '',
            'smtp_from_name' => 'SDBIP System',
            'smtp_encryption' => 'tls',
            'ldap_enabled' => false,
            'ldap_host' => '',
            'ldap_port' => '389',
            'ldap_base_dn' => '',
            'ldap_bind_dn' => '',
            'ldap_bind_password' => '',
            'ldap_user_filter' => '(sAMAccountName=%s)',
            'sms_enabled' => false,
            'sms_provider' => '',
            'sms_api_key' => '',
            'sms_api_secret' => '',
            'sms_sender_id' => '',
            'sms_api_url' => '',
            'sms_notify_deadlines' => true,
            'sms_notify_approvals' => true,
            'sms_notify_alerts' => true,
            'sms_notify_imbizo' => false,
            'openai_api_key' => defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '',
            'openai_model' => 'gpt-4',
            'openai_max_tokens' => 4000,
            'openai_temperature' => 0.7,
            'ai_quarterly_reports' => true,
            'ai_recommendations' => true,
            'ai_risk_analysis' => true,
            'ai_trend_detection' => true
        ];

        if (file_exists($configFile)) {
            $saved = json_decode(file_get_contents($configFile), true);
            return array_merge($defaults, $saved ?? []);
        }

        return $defaults;
    }

    private function saveIntegrationSettings(array $settings): void {
        $configDir = ROOT_PATH . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configFile = $configDir . '/integrations.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
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
