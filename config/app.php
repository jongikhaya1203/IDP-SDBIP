<?php
/**
 * Application Configuration
 * SDBIP & IDP Management System
 */

// Load database config first (includes env loading)
require_once __DIR__ . '/database.php';

// Application settings
define('APP_NAME', getenv('APP_NAME') ?: 'SDBIP & IDP Management System');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:3000');

// Generate APP_KEY if not set
$appKey = getenv('APP_KEY');
if (empty($appKey)) {
    $appKey = bin2hex(random_bytes(32));
}
define('APP_KEY', $appKey);

// Path definitions
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('SRC_PATH', ROOT_PATH . '/src');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('POE_PATH', UPLOAD_PATH . '/poe');

// LDAP Configuration
define('LDAP_ENABLED', filter_var(getenv('LDAP_ENABLED') ?: false, FILTER_VALIDATE_BOOLEAN));
define('LDAP_HOST', getenv('LDAP_HOST') ?: '');
define('LDAP_PORT', (int)(getenv('LDAP_PORT') ?: 389));
define('LDAP_BASE_DN', getenv('LDAP_BASE_DN') ?: '');
define('LDAP_BIND_DN', getenv('LDAP_BIND_DN') ?: '');
define('LDAP_BIND_PASSWORD', getenv('LDAP_BIND_PASSWORD') ?: '');
define('LDAP_USER_FILTER', getenv('LDAP_USER_FILTER') ?: '(sAMAccountName=%s)');

// LDAP Group Mapping
define('LDAP_GROUP_ADMIN', getenv('LDAP_GROUP_ADMIN') ?: 'SDBIP_Admins');
define('LDAP_GROUP_DIRECTOR', getenv('LDAP_GROUP_DIRECTOR') ?: 'SDBIP_Directors');
define('LDAP_GROUP_MANAGER', getenv('LDAP_GROUP_MANAGER') ?: 'SDBIP_Managers');
define('LDAP_GROUP_ASSESSOR', getenv('LDAP_GROUP_ASSESSOR') ?: 'SDBIP_Assessors');

// OpenAI Configuration
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_MODEL', getenv('OPENAI_MODEL') ?: 'gpt-4');
define('OPENAI_MAX_TOKENS', (int)(getenv('OPENAI_MAX_TOKENS') ?: 4000));

// Session Configuration
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 3600));
define('SESSION_SECURE', filter_var(getenv('SESSION_SECURE') ?: false, FILTER_VALIDATE_BOOLEAN));

// File Upload Settings
define('UPLOAD_MAX_SIZE', (int)(getenv('UPLOAD_MAX_SIZE') ?: 10485760)); // 10MB
define('UPLOAD_ALLOWED_TYPES', explode(',', getenv('UPLOAD_ALLOWED_TYPES') ?: 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif'));

// Municipality Settings
define('MUNICIPALITY_NAME', getenv('MUNICIPALITY_NAME') ?: 'Sample Municipality');
define('MUNICIPALITY_CODE', getenv('MUNICIPALITY_CODE') ?: 'DC99');
define('PROVINCE', getenv('PROVINCE') ?: 'Gauteng');

// Rating Weights
define('RATING_SELF_WEIGHT', (float)(getenv('RATING_SELF_WEIGHT') ?: 0.20));
define('RATING_MANAGER_WEIGHT', (float)(getenv('RATING_MANAGER_WEIGHT') ?: 0.40));
define('RATING_INDEPENDENT_WEIGHT', (float)(getenv('RATING_INDEPENDENT_WEIGHT') ?: 0.40));

// POE Settings
define('POE_RESUBMISSION_DAYS', (int)(getenv('POE_RESUBMISSION_DAYS') ?: 7));

// SA Municipal Financial Year
define('FY_START_MONTH', 7); // July
define('FY_END_MONTH', 6);   // June

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone to South Africa
date_default_timezone_set('Africa/Johannesburg');

// Session configuration (only if session not started yet)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');

    if (SESSION_SECURE) {
        ini_set('session.cookie_secure', 1);
    }
}

/**
 * Helper Functions
 */

function asset(string $path): string {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

function view(string $name, array $data = []): void {
    extract($data);
    $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $name) . '.php';
    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        throw new Exception("View not found: {$name}");
    }
}

function redirect(string $url): void {
    header("Location: " . url($url));
    exit;
}

function back(): void {
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    header("Location: " . $referer);
    exit;
}

function old(string $key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function flash(string $key, $value = null) {
    if ($value === null) {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    $_SESSION['flash'][$key] = $value;
}

function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_date(string $date, string $format = 'd M Y'): string {
    return date($format, strtotime($date));
}

function format_currency(float $amount): string {
    return 'R ' . number_format($amount, 2, '.', ' ');
}

function format_percentage(float $value): string {
    return number_format($value, 2) . '%';
}

function current_financial_year(): array {
    $now = new DateTime();
    $month = (int)$now->format('n');
    $year = (int)$now->format('Y');

    if ($month >= FY_START_MONTH) {
        $startYear = $year;
        $endYear = $year + 1;
    } else {
        $startYear = $year - 1;
        $endYear = $year;
    }

    return [
        'label' => "{$startYear}/{$endYear}",
        'start' => "{$startYear}-07-01",
        'end' => "{$endYear}-06-30"
    ];
}

function current_quarter(): int {
    $now = new DateTime();
    $month = (int)$now->format('n');

    // SA Municipal quarters: Q1=Jul-Sep, Q2=Oct-Dec, Q3=Jan-Mar, Q4=Apr-Jun
    if ($month >= 7 && $month <= 9) return 1;
    if ($month >= 10 && $month <= 12) return 2;
    if ($month >= 1 && $month <= 3) return 3;
    return 4;
}

function quarter_label(int $quarter): string {
    $labels = [
        1 => 'Q1 (Jul-Sep)',
        2 => 'Q2 (Oct-Dec)',
        3 => 'Q3 (Jan-Mar)',
        4 => 'Q4 (Apr-Jun)'
    ];
    return $labels[$quarter] ?? '';
}

function rating_color(float $rating): string {
    if ($rating >= 4) return 'success';
    if ($rating >= 3) return 'warning';
    if ($rating >= 2) return 'orange';
    return 'danger';
}

function achievement_badge(string $status): string {
    $badges = [
        'achieved' => '<span class="badge bg-success">Achieved</span>',
        'partially_achieved' => '<span class="badge bg-warning text-dark">Partially Achieved</span>',
        'not_achieved' => '<span class="badge bg-danger">Not Achieved</span>',
        'pending' => '<span class="badge bg-secondary">Pending</span>'
    ];
    return $badges[$status] ?? $badges['pending'];
}

function sla_badge(string $category): string {
    $badges = [
        'budget' => '<span class="badge bg-primary">Budget</span>',
        'internal_control' => '<span class="badge bg-info">Internal Control</span>',
        'hr_vacancy' => '<span class="badge bg-warning text-dark">HR Vacancy</span>',
        'none' => '<span class="badge bg-secondary">N/A</span>'
    ];
    return $badges[$category] ?? $badges['none'];
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function is_ajax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function has_role(string ...$roles): bool {
    $user = user();
    if (!$user) return false;
    return in_array($user['role'], $roles);
}

function can_access_directorate(int $directorateId): bool {
    $user = user();
    if (!$user) return false;
    if (has_role('admin', 'independent_assessor')) return true;
    return $user['directorate_id'] == $directorateId;
}

function cms_settings(): array {
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

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
        $settings = array_merge($defaults, $saved ?? []);
    } else {
        $settings = $defaults;
    }

    return $settings;
}

// Autoloader for classes
spl_autoload_register(function ($class) {
    $paths = [
        SRC_PATH . '/Controllers/',
        SRC_PATH . '/Models/',
        SRC_PATH . '/Services/',
        SRC_PATH . '/Middleware/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
