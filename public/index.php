<?php
/**
 * Application Entry Point
 * SDBIP & IDP Management System
 */

// Load configuration first (sets session ini before session_start)
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/constants.php';

// Start session after ini settings are configured
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create upload directories if they don't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!is_dir(POE_PATH)) {
    mkdir(POE_PATH, 0755, true);
}

/**
 * Simple Router
 */
class Router {
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, $handler, array $middleware = []): self {
        $this->routes['GET'][$path] = ['handler' => $handler, 'middleware' => $middleware];
        return $this;
    }

    public function post(string $path, $handler, array $middleware = []): self {
        $this->routes['POST'][$path] = ['handler' => $handler, 'middleware' => $middleware];
        return $this;
    }

    public function addMiddleware(string $name, callable $handler): self {
        $this->middleware[$name] = $handler;
        return $this;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash except for root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Find matching route
        $matchedRoute = null;
        $params = [];

        foreach ($this->routes[$method] ?? [] as $path => $route) {
            $pattern = $this->pathToRegex($path);
            if (preg_match($pattern, $uri, $matches)) {
                $matchedRoute = $route;
                // Extract named parameters
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                break;
            }
        }

        if ($matchedRoute) {
            // Run middleware
            foreach ($matchedRoute['middleware'] as $middlewareName) {
                if (isset($this->middleware[$middlewareName])) {
                    $result = call_user_func($this->middleware[$middlewareName]);
                    if ($result === false) {
                        return;
                    }
                }
            }

            // Execute handler
            $handler = $matchedRoute['handler'];
            if (is_callable($handler)) {
                call_user_func_array($handler, $params);
            } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                list($controller, $action) = explode('@', $handler);
                $controllerClass = $controller;
                if (class_exists($controllerClass)) {
                    $instance = new $controllerClass();
                    call_user_func_array([$instance, $action], $params);
                } else {
                    $this->notFound();
                }
            }
        } else {
            $this->notFound();
        }
    }

    private function pathToRegex(string $path): string {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function notFound(): void {
        http_response_code(404);
        if (is_ajax()) {
            json_response(['error' => 'Not Found'], 404);
        } else {
            view('errors.404');
        }
    }
}

// Initialize Router
$router = new Router();

// Add Middleware
$router->addMiddleware('auth', function() {
    if (!is_logged_in()) {
        if (is_ajax()) {
            json_response(['error' => 'Unauthorized'], 401);
        } else {
            redirect('/login');
        }
        return false;
    }
    return true;
});

$router->addMiddleware('guest', function() {
    if (is_logged_in()) {
        redirect('/');
        return false;
    }
    return true;
});

$router->addMiddleware('admin', function() {
    if (!has_role('admin')) {
        if (is_ajax()) {
            json_response(['error' => 'Forbidden'], 403);
        } else {
            http_response_code(403);
            view('errors.403');
        }
        return false;
    }
    return true;
});

$router->addMiddleware('manager', function() {
    if (!has_role('admin', 'director', 'manager')) {
        if (is_ajax()) {
            json_response(['error' => 'Forbidden'], 403);
        } else {
            http_response_code(403);
            view('errors.403');
        }
        return false;
    }
    return true;
});

$router->addMiddleware('assessor', function() {
    if (!has_role('admin', 'independent_assessor')) {
        if (is_ajax()) {
            json_response(['error' => 'Forbidden'], 403);
        } else {
            http_response_code(403);
            view('errors.403');
        }
        return false;
    }
    return true;
});

$router->addMiddleware('csrf', function() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf()) {
        if (is_ajax()) {
            json_response(['error' => 'CSRF token mismatch'], 419);
        } else {
            flash('error', 'Session expired. Please try again.');
            back();
        }
        return false;
    }
    return true;
});

// =====================================================
// PUBLIC ROUTES
// =====================================================
$router->get('/login', 'AuthController@showLogin', ['guest']);
$router->post('/login', 'AuthController@login', ['guest', 'csrf']);
$router->get('/logout', 'AuthController@logout', ['auth']);

// =====================================================
// DASHBOARD ROUTES
// =====================================================
$router->get('/', 'DashboardController@index', ['auth']);
$router->get('/dashboard', 'DashboardController@index', ['auth']);
$router->get('/api/dashboard/stats', 'DashboardController@getStats', ['auth']);
$router->get('/api/dashboard/charts', 'DashboardController@getChartData', ['auth']);

// =====================================================
// IDP ROUTES
// =====================================================
$router->get('/idp', 'IDPController@index', ['auth', 'manager']);
$router->get('/idp/objectives', 'IDPController@objectives', ['auth', 'manager']);
$router->get('/idp/objectives/create', 'IDPController@create', ['auth', 'manager']);
$router->post('/idp/objectives', 'IDPController@store', ['auth', 'manager', 'csrf']);
$router->get('/idp/objectives/{id}', 'IDPController@show', ['auth']);
$router->get('/idp/objectives/{id}/edit', 'IDPController@edit', ['auth', 'manager']);
$router->post('/idp/objectives/{id}', 'IDPController@update', ['auth', 'manager', 'csrf']);
$router->post('/idp/objectives/{id}/delete', 'IDPController@delete', ['auth', 'manager', 'csrf']);

// =====================================================
// SDBIP ROUTES
// =====================================================
$router->get('/sdbip', 'SDBIPController@index', ['auth']);
$router->get('/sdbip/objectives', 'SDBIPController@objectives', ['auth']);
$router->get('/sdbip/kpis', 'SDBIPController@kpis', ['auth']);
$router->get('/sdbip/kpis/create', 'SDBIPController@createKPI', ['auth', 'manager']);
$router->post('/sdbip/kpis', 'SDBIPController@storeKPI', ['auth', 'manager', 'csrf']);
$router->get('/sdbip/kpis/{id}', 'SDBIPController@showKPI', ['auth']);
$router->get('/sdbip/kpis/{id}/edit', 'SDBIPController@editKPI', ['auth', 'manager']);
$router->post('/sdbip/kpis/{id}', 'SDBIPController@updateKPI', ['auth', 'manager', 'csrf']);
$router->post('/sdbip/kpis/{id}/delete', 'SDBIPController@deleteKPI', ['auth', 'manager', 'csrf']);
$router->get('/sdbip/targets', 'SDBIPController@targets', ['auth']);

// =====================================================
// ASSESSMENT ROUTES
// =====================================================
$router->get('/assessment', 'AssessmentController@index', ['auth']);
$router->get('/assessment/self', 'AssessmentController@selfAssessment', ['auth']);
$router->post('/assessment/self/{id}', 'AssessmentController@submitSelfAssessment', ['auth', 'csrf']);
$router->get('/assessment/manager', 'AssessmentController@managerReview', ['auth', 'manager']);
$router->post('/assessment/manager/{id}', 'AssessmentController@submitManagerReview', ['auth', 'manager', 'csrf']);
$router->get('/assessment/independent', 'AssessmentController@independentReview', ['auth', 'assessor']);
$router->post('/assessment/independent/{id}', 'AssessmentController@submitIndependentReview', ['auth', 'assessor', 'csrf']);
$router->get('/assessment/quarterly', 'AssessmentController@quarterlyReview', ['auth']);
$router->get('/assessment/{id}', 'AssessmentController@show', ['auth']);

// =====================================================
// POE ROUTES
// =====================================================
$router->get('/poe', 'POEController@index', ['auth']);
$router->get('/poe/upload/{quarterlyId}', 'POEController@showUpload', ['auth']);
$router->post('/poe/upload/{quarterlyId}', 'POEController@upload', ['auth', 'csrf']);
$router->get('/poe/{id}', 'POEController@show', ['auth']);
$router->get('/poe/{id}/download', 'POEController@download', ['auth']);
$router->post('/poe/{id}/accept', 'POEController@accept', ['auth', 'manager', 'csrf']);
$router->post('/poe/{id}/reject', 'POEController@reject', ['auth', 'manager', 'csrf']);
$router->get('/poe/review', 'POEController@review', ['auth', 'manager']);

// =====================================================
// BUDGET ROUTES
// =====================================================
$router->get('/budget', 'BudgetController@index', ['auth', 'manager']);
$router->get('/budget/projections', 'BudgetController@projections', ['auth', 'manager']);
$router->post('/budget/projections', 'BudgetController@updateProjections', ['auth', 'manager', 'csrf']);
$router->get('/budget/projects', 'BudgetController@projects', ['auth', 'manager']);
$router->get('/budget/projects/create', 'BudgetController@createProject', ['auth', 'manager']);
$router->post('/budget/projects', 'BudgetController@storeProject', ['auth', 'manager', 'csrf']);
$router->get('/budget/projects/{id}', 'BudgetController@showProject', ['auth']);
$router->get('/budget/projects/{id}/edit', 'BudgetController@editProject', ['auth', 'manager']);
$router->post('/budget/projects/{id}', 'BudgetController@updateProject', ['auth', 'manager', 'csrf']);

// =====================================================
// REPORTS ROUTES
// =====================================================
$router->get('/reports', 'ReportController@index', ['auth', 'manager']);
$router->get('/reports/quarterly', 'ReportController@quarterly', ['auth', 'manager']);
$router->get('/reports/quarterly/{quarter}', 'ReportController@quarterlyDetail', ['auth', 'manager']);
$router->get('/reports/directorate', 'ReportController@directorate', ['auth', 'manager']);
$router->get('/reports/directorate/{id}', 'ReportController@directorateDetail', ['auth', 'manager']);
$router->get('/reports/ai', 'AIReportController@index', ['auth', 'manager']);
$router->post('/reports/ai/generate', 'AIReportController@generate', ['auth', 'manager', 'csrf']);
$router->get('/reports/ai/{id}', 'AIReportController@show', ['auth']);
$router->get('/reports/export/excel', 'ReportController@exportExcel', ['auth', 'manager']);
$router->get('/reports/export/pdf', 'ReportController@exportPdf', ['auth', 'manager']);

// =====================================================
// ADMIN ROUTES
// =====================================================
$router->get('/admin', 'AdminController@index', ['auth', 'admin']);
$router->get('/admin/users', 'AdminController@users', ['auth', 'admin']);
$router->get('/admin/users/create', 'AdminController@createUser', ['auth', 'admin']);
$router->post('/admin/users', 'AdminController@storeUser', ['auth', 'admin', 'csrf']);
$router->get('/admin/users/{id}', 'AdminController@showUser', ['auth', 'admin']);
$router->get('/admin/users/{id}/edit', 'AdminController@editUser', ['auth', 'admin']);
$router->post('/admin/users/{id}', 'AdminController@updateUser', ['auth', 'admin', 'csrf']);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', ['auth', 'admin', 'csrf']);
$router->get('/admin/directorates', 'AdminController@directorates', ['auth', 'admin']);
$router->get('/admin/financial-years', 'AdminController@financialYears', ['auth', 'admin']);
$router->get('/admin/settings', 'AdminController@settings', ['auth', 'admin']);
$router->post('/admin/settings', 'AdminController@updateSettings', ['auth', 'admin', 'csrf']);

// =====================================================
// CONTROL PANEL ROUTES
// =====================================================
$router->get('/cpanel', 'CpanelController@index', ['auth', 'admin']);
$router->get('/cpanel/modules', 'CpanelController@modules', ['auth', 'admin']);
$router->post('/cpanel/modules', 'CpanelController@updateModules', ['auth', 'admin', 'csrf']);
$router->get('/cpanel/database', 'CpanelController@database', ['auth', 'admin']);
$router->get('/cpanel/integrations', 'CpanelController@integrations', ['auth', 'admin']);
$router->get('/cpanel/backup', 'CpanelController@backup', ['auth', 'admin']);
$router->post('/cpanel/backup', 'CpanelController@createBackup', ['auth', 'admin', 'csrf']);
$router->get('/cpanel/logs', 'CpanelController@logs', ['auth', 'admin']);

// =====================================================
// MAYORAL IDP IMBIZO ROUTES
// =====================================================
$router->get('/imbizo', 'ImbizoController@index', ['auth']);
$router->get('/imbizo/create', 'ImbizoController@create', ['auth', 'manager']);
$router->post('/imbizo', 'ImbizoController@store', ['auth', 'manager', 'csrf']);
$router->get('/imbizo/action-items', 'ImbizoController@actionItems', ['auth']);
$router->get('/imbizo/action-items/{id}', 'ImbizoController@showActionItem', ['auth']);
$router->post('/imbizo/action-items/{id}/comments', 'ImbizoController@addComment', ['auth', 'csrf']);
$router->post('/imbizo/action-items/{id}/poe', 'ImbizoController@uploadPOE', ['auth', 'csrf']);
$router->get('/imbizo/{id}', 'ImbizoController@show', ['auth']);
$router->get('/imbizo/{id}/livestream', 'ImbizoController@livestream', ['auth']);
$router->post('/imbizo/{id}/start-live', 'ImbizoController@startLive', ['auth', 'manager', 'csrf']);
$router->post('/imbizo/{id}/end-live', 'ImbizoController@endLive', ['auth', 'manager', 'csrf']);
$router->post('/imbizo/{id}/action-items', 'ImbizoController@addActionItem', ['auth', 'csrf']);
$router->post('/imbizo/{id}/generate-minutes', 'ImbizoController@generateMinutes', ['auth', 'manager', 'csrf']);

// =====================================================
// TRAINING ROUTES
// =====================================================
$router->get('/training', 'TrainingController@index', ['auth']);
$router->get('/training/faq', 'TrainingController@faq', ['auth']);
$router->get('/training/videos', 'TrainingController@videos', ['auth']);
$router->get('/training/glossary', 'TrainingController@glossary', ['auth']);
$router->get('/training/quick-start', 'TrainingController@quickStart', ['auth']);
$router->get('/training/module/{slug}', 'TrainingController@module', ['auth']);
$router->get('/training/architecture', 'TrainingController@architecture', ['auth']);
$router->get('/training/architecture/download', 'TrainingController@downloadArchitecture', ['auth']);

// =====================================================
// MAYORAL LEKGOTLA ROUTES
// =====================================================
$router->get('/lekgotla', 'LekgotlaController@index', ['auth']);
$router->get('/lekgotla/comparison', 'LekgotlaController@comparison', ['auth']);
$router->get('/lekgotla/create', 'LekgotlaController@create', ['auth', 'manager']);
$router->post('/lekgotla/store', 'LekgotlaController@store', ['auth', 'manager', 'csrf']);
$router->get('/lekgotla/export', 'LekgotlaController@exportComparison', ['auth']);
$router->get('/lekgotla/session/{id}', 'LekgotlaController@session', ['auth']);
$router->get('/lekgotla/session/{id}/add-change', 'LekgotlaController@addChange', ['auth', 'manager']);
$router->post('/lekgotla/session/{id}/store-change', 'LekgotlaController@storeChange', ['auth', 'manager', 'csrf']);
$router->post('/lekgotla/session/{id}/approve', 'LekgotlaController@approveSession', ['auth', 'admin', 'csrf']);
$router->post('/lekgotla/change/{id}/review', 'LekgotlaController@reviewChange', ['auth', 'manager', 'csrf']);

// =====================================================
// API ROUTES
// =====================================================
$router->get('/api/directorates', 'APIController@directorates', ['auth']);
$router->get('/api/directorates/{id}/kpis', 'APIController@directorateKpis', ['auth']);
$router->get('/api/kpis', 'APIController@kpis', ['auth']);
$router->get('/api/kpis/{id}/actuals', 'APIController@kpiActuals', ['auth']);
$router->get('/api/performance/summary', 'APIController@performanceSummary', ['auth']);
$router->get('/api/performance/trends', 'APIController@performanceTrends', ['auth']);
$router->get('/api/notifications', 'APIController@notifications', ['auth']);
$router->post('/api/notifications/{id}/read', 'APIController@markNotificationRead', ['auth']);

// Dispatch the request
$router->dispatch();
