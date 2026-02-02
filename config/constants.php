<?php
/**
 * System Constants
 * SDBIP & IDP Management System
 */

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DIRECTOR', 'director');
define('ROLE_MANAGER', 'manager');
define('ROLE_EMPLOYEE', 'employee');
define('ROLE_ASSESSOR', 'independent_assessor');

// Assessment Statuses
define('STATUS_DRAFT', 'draft');
define('STATUS_SUBMITTED', 'submitted');
define('STATUS_MANAGER_REVIEW', 'manager_review');
define('STATUS_INDEPENDENT_REVIEW', 'independent_review');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// POE Statuses
define('POE_PENDING', 'pending');
define('POE_ACCEPTED', 'accepted');
define('POE_REJECTED', 'rejected');

// Achievement Statuses
define('ACHIEVED', 'achieved');
define('PARTIALLY_ACHIEVED', 'partially_achieved');
define('NOT_ACHIEVED', 'not_achieved');
define('PENDING', 'pending');

// SLA Categories
define('SLA_BUDGET', 'budget');
define('SLA_INTERNAL_CONTROL', 'internal_control');
define('SLA_HR_VACANCY', 'hr_vacancy');
define('SLA_NONE', 'none');

// Financial Year Status
define('FY_PLANNING', 'planning');
define('FY_ACTIVE', 'active');
define('FY_CLOSED', 'closed');
define('FY_ARCHIVED', 'archived');

// Project Statuses
define('PROJECT_PLANNING', 'planning');
define('PROJECT_PROCUREMENT', 'procurement');
define('PROJECT_IN_PROGRESS', 'in_progress');
define('PROJECT_COMPLETED', 'completed');
define('PROJECT_ON_HOLD', 'on_hold');
define('PROJECT_CANCELLED', 'cancelled');

// Notification Types
define('NOTIFY_INFO', 'info');
define('NOTIFY_WARNING', 'warning');
define('NOTIFY_SUCCESS', 'success');
define('NOTIFY_ERROR', 'error');
define('NOTIFY_TASK', 'task');
define('NOTIFY_DEADLINE', 'deadline');
define('NOTIFY_REVIEW', 'review');

// Rating Scale
define('RATING_MIN', 1);
define('RATING_MAX', 5);

// Rating Descriptions
define('RATING_DESCRIPTIONS', [
    1 => 'Unacceptable - Target significantly missed',
    2 => 'Needs Improvement - Target partially missed',
    3 => 'Meets Expectations - Target achieved',
    4 => 'Exceeds Expectations - Target exceeded',
    5 => 'Outstanding - Target significantly exceeded'
]);

// National Priorities (NDP)
define('NATIONAL_PRIORITIES', [
    'NDP Priority 1: Capable, ethical and developmental state',
    'NDP Priority 2: Economic transformation and job creation',
    'NDP Priority 3: Education, skills and health',
    'NDP Priority 4: Consolidating the social wage',
    'NDP Priority 5: Spatial integration and human settlements',
    'NDP Priority 6: Social cohesion and safe communities',
    'NDP Priority 7: A better Africa and world'
]);

// Menu Items
define('MENU_ITEMS', [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'url' => '/',
        'roles' => ['admin', 'director', 'manager', 'employee', 'independent_assessor']
    ],
    'idp' => [
        'label' => 'IDP Management',
        'icon' => 'bi-diagram-3',
        'url' => '/idp',
        'roles' => ['admin', 'director', 'manager']
    ],
    'imbizo' => [
        'label' => 'Mayoral Imbizo',
        'icon' => 'bi-people',
        'url' => '/imbizo',
        'roles' => ['admin', 'director', 'manager', 'employee'],
        'submenu' => [
            ['label' => 'Sessions', 'url' => '/imbizo'],
            ['label' => 'Action Items', 'url' => '/imbizo/action-items']
        ]
    ],
    'sdbip' => [
        'label' => 'SDBIP',
        'icon' => 'bi-graph-up',
        'url' => '/sdbip',
        'roles' => ['admin', 'director', 'manager', 'employee'],
        'submenu' => [
            ['label' => 'Strategic Objectives', 'url' => '/sdbip/objectives'],
            ['label' => 'KPIs', 'url' => '/sdbip/kpis'],
            ['label' => 'Quarterly Targets', 'url' => '/sdbip/targets']
        ]
    ],
    'assessment' => [
        'label' => 'Assessment',
        'icon' => 'bi-clipboard-check',
        'url' => '/assessment',
        'roles' => ['admin', 'director', 'manager', 'employee', 'independent_assessor'],
        'submenu' => [
            ['label' => 'Self Assessment', 'url' => '/assessment/self'],
            ['label' => 'Manager Review', 'url' => '/assessment/manager'],
            ['label' => 'Independent Review', 'url' => '/assessment/independent']
        ]
    ],
    'poe' => [
        'label' => 'Proof of Evidence',
        'icon' => 'bi-file-earmark-check',
        'url' => '/poe',
        'roles' => ['admin', 'director', 'manager', 'employee', 'independent_assessor']
    ],
    'budget' => [
        'label' => 'Budget',
        'icon' => 'bi-currency-dollar',
        'url' => '/budget',
        'roles' => ['admin', 'director', 'manager'],
        'submenu' => [
            ['label' => 'Projections', 'url' => '/budget/projections'],
            ['label' => 'Capital Projects', 'url' => '/budget/projects']
        ]
    ],
    'reports' => [
        'label' => 'Reports',
        'icon' => 'bi-file-text',
        'url' => '/reports',
        'roles' => ['admin', 'director', 'manager', 'independent_assessor'],
        'submenu' => [
            ['label' => 'Quarterly Reports', 'url' => '/reports/quarterly'],
            ['label' => 'Directorate Performance', 'url' => '/reports/directorate'],
            ['label' => 'AI Analysis', 'url' => '/reports/ai']
        ]
    ],
    'admin' => [
        'label' => 'Administration',
        'icon' => 'bi-gear',
        'url' => '/admin',
        'roles' => ['admin'],
        'submenu' => [
            ['label' => 'Users', 'url' => '/admin/users'],
            ['label' => 'Directorates', 'url' => '/admin/directorates'],
            ['label' => 'Financial Years', 'url' => '/admin/financial-years'],
            ['label' => 'Settings', 'url' => '/admin/settings']
        ]
    ],
    'cpanel' => [
        'label' => 'Control Panel',
        'icon' => 'bi-gear-wide-connected',
        'url' => '/cpanel',
        'roles' => ['admin'],
        'submenu' => [
            ['label' => 'Overview', 'url' => '/cpanel'],
            ['label' => 'Modules', 'url' => '/cpanel/modules'],
            ['label' => 'Database', 'url' => '/cpanel/database'],
            ['label' => 'Integrations', 'url' => '/cpanel/integrations'],
            ['label' => 'Backup', 'url' => '/cpanel/backup'],
            ['label' => 'Audit Logs', 'url' => '/cpanel/logs']
        ]
    ],
    'training' => [
        'label' => 'Training',
        'icon' => 'bi-mortarboard',
        'url' => '/training',
        'roles' => ['admin', 'director', 'manager', 'employee', 'independent_assessor'],
        'submenu' => [
            ['label' => 'All Modules', 'url' => '/training'],
            ['label' => 'Quick Start', 'url' => '/training/quick-start'],
            ['label' => 'FAQ', 'url' => '/training/faq'],
            ['label' => 'Videos', 'url' => '/training/videos'],
            ['label' => 'Glossary', 'url' => '/training/glossary'],
            ['label' => 'Cloud Architecture', 'url' => '/training/architecture']
        ]
    ]
]);
