<?php $cmsSettings = cms_settings(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($title ?? 'Dashboard') ?> - <?= e($cmsSettings['site_name']) ?></title>
    <?php if (!empty($cmsSettings['favicon'])): ?>
    <link rel="icon" type="image/x-icon" href="<?= e($cmsSettings['favicon']) ?>">
    <?php endif; ?>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- ApexCharts -->
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: <?= e($cmsSettings['primary_color']) ?>;
            --secondary-color: <?= e($cmsSettings['secondary_color']) ?>;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #0ea5e9;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --sidebar-width: 260px;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f1f5f9;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
        }

        .sidebar-header small {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.05);
            color: #fff;
            border-left-color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .nav-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: rgba(0,0,0,0.2);
        }

        .nav-submenu .nav-link {
            padding-left: 3.5rem;
            font-size: 0.875rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top Navbar */
        .top-navbar {
            background: #fff;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .breadcrumb {
            margin-bottom: 0;
            background: none;
            padding: 0;
        }

        .breadcrumb-item a {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: var(--dark-color);
            font-weight: 500;
        }

        /* Content Area */
        .content-area {
            padding: 1.5rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Stats Cards */
        .stat-card {
            border-radius: 12px;
            padding: 1.25rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card.primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
        .stat-card.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .stat-card.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .stat-card.danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .stat-card.info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }

        .stat-card .stat-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.2;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-card .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        /* Tables */
        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            vertical-align: middle;
            padding: 0.875rem;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.375rem 0.625rem;
            border-radius: 6px;
        }

        /* Rating Stars */
        .rating-stars {
            color: #fbbf24;
        }

        .rating-value {
            font-weight: 600;
            font-size: 1.25rem;
        }

        /* Progress */
        .progress {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
        }

        /* Notifications Dropdown */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.625rem;
            min-width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* User Dropdown */
        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }

        /* Loading Spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Form styling */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.625rem 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        /* Alert styling */
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center">
                <?php if (!empty($cmsSettings['logo'])): ?>
                    <img src="<?= e($cmsSettings['logo']) ?>" alt="Logo" class="me-2" style="max-height: 40px; max-width: 60px;">
                <?php else: ?>
                    <i class="bi bi-building me-2" style="font-size: 1.5rem;"></i>
                <?php endif; ?>
                <div>
                    <h5 class="mb-0"><?= e($cmsSettings['organization_name'] ?: MUNICIPALITY_NAME) ?></h5>
                    <small><?= e($cmsSettings['site_tagline']) ?></small>
                </div>
            </div>
        </div>

        <ul class="sidebar-nav nav flex-column">
            <?php
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            foreach (MENU_ITEMS as $key => $item):
                if (!has_role(...$item['roles'])) continue;
                $isActive = $currentPath === $item['url'] || strpos($currentPath, $item['url'] . '/') === 0;
                $hasSubmenu = !empty($item['submenu']);
            ?>
            <li class="nav-item">
                <?php if ($hasSubmenu): ?>
                <a class="nav-link <?= $isActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#submenu-<?= $key ?>">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <?= $item['label'] ?>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                </a>
                <ul class="nav-submenu collapse <?= $isActive ? 'show' : '' ?>" id="submenu-<?= $key ?>">
                    <?php foreach ($item['submenu'] as $sub): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPath === $sub['url'] ? 'active' : '' ?>" href="<?= $sub['url'] ?>">
                            <?= $sub['label'] ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= $item['url'] ?>">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <?= $item['label'] ?>
                </a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="sidebar-footer p-3 border-top border-secondary mt-auto">
            <small class="text-muted">
                FY: <?= current_financial_year()['label'] ?><br>
                <?= quarter_label(current_quarter()) ?>
            </small>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-lg-none me-2" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/"><i class="bi bi-house"></i></a></li>
                        <?php if (isset($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <?php if (isset($crumb['url'])): ?>
                                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= e($crumb['label']) ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active"><?= e($crumb['label']) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Help & Documentation -->
                <div class="dropdown">
                    <button class="btn btn-link text-secondary" data-bs-toggle="dropdown" title="Help & Documentation">
                        <i class="bi bi-question-circle fs-5"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 280px;">
                        <div class="p-3 border-bottom bg-light">
                            <h6 class="mb-0"><i class="bi bi-book me-2"></i>Documentation</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="/docs/Training_Manual.html" target="_blank" class="list-group-item list-group-item-action">
                                <i class="bi bi-mortarboard me-2 text-primary"></i>Training Manual
                                <small class="d-block text-muted">Complete user guide</small>
                            </a>
                            <a href="/docs/Marketing_Brochure.html" target="_blank" class="list-group-item list-group-item-action">
                                <i class="bi bi-megaphone me-2 text-success"></i>Marketing Brochure
                                <small class="d-block text-muted">Product overview</small>
                            </a>
                            <a href="/docs/Cloud_Architecture_Review.html" target="_blank" class="list-group-item list-group-item-action">
                                <i class="bi bi-cloud me-2 text-info"></i>Cloud Architecture
                                <small class="d-block text-muted">AWS, Azure, GCP compliance</small>
                            </a>
                            <a href="/docs/Security_Architecture_Review.html" target="_blank" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2 text-danger"></i>Security Review
                                <small class="d-block text-muted">Security architecture</small>
                            </a>
                        </div>
                        <div class="p-2 border-top text-center bg-light">
                            <a href="/training" class="small"><i class="bi bi-play-circle me-1"></i>Training Center</a>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link text-secondary position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="notification-badge badge bg-danger" id="notificationCount">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Notifications</h6>
                        </div>
                        <div class="notification-list" id="notificationList" style="max-height: 300px; overflow-y: auto;">
                            <div class="p-3 text-center text-muted">
                                <small>No new notifications</small>
                            </div>
                        </div>
                        <div class="p-2 border-top text-center">
                            <a href="/notifications" class="small">View All</a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown user-dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?= strtoupper(substr(user()['first_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="d-none d-md-inline"><?= e(user()['first_name'] ?? 'User') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= e(user()['email'] ?? '') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <main class="content-area">
            <?php if ($flashSuccess = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= e($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($flashError = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?= e($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($content)): ?>
                <?= $content ?>
            <?php endif; ?>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>

    <script>
        // CSRF Token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Sidebar Toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Load notifications
        async function loadNotifications() {
            try {
                const response = await fetch('/api/notifications', {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();

                const countEl = document.getElementById('notificationCount');
                const listEl = document.getElementById('notificationList');

                if (data.notifications && data.notifications.length > 0) {
                    countEl.textContent = data.unread_count || 0;
                    countEl.style.display = data.unread_count > 0 ? 'flex' : 'none';

                    listEl.innerHTML = data.notifications.map(n => `
                        <a href="${n.link || '#'}" class="d-block p-3 border-bottom text-decoration-none ${n.is_read ? '' : 'bg-light'}">
                            <div class="d-flex justify-content-between">
                                <strong class="text-dark">${n.title}</strong>
                                <small class="text-muted">${n.time_ago}</small>
                            </div>
                            <small class="text-muted">${n.message}</small>
                        </a>
                    `).join('');
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        }

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', loadNotifications);

        // Helper function for AJAX requests
        async function apiRequest(url, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            return response.json();
        }

        // Format currency
        function formatCurrency(amount) {
            return 'R ' + new Intl.NumberFormat('en-ZA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        // Format percentage
        function formatPercentage(value) {
            return parseFloat(value).toFixed(2) + '%';
        }

        // Rating color
        function getRatingColor(rating) {
            if (rating >= 4) return '#10b981';
            if (rating >= 3) return '#f59e0b';
            if (rating >= 2) return '#f97316';
            return '#ef4444';
        }
    </script>

    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
