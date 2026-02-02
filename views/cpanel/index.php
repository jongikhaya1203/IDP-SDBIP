<?php
$pageTitle = $title ?? 'Control Panel';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="bi bi-gear-wide-connected me-2"></i>Control Panel</h1>
        <p class="text-muted mb-0">System configuration and module management</p>
    </div>
</div>

<!-- System Stats -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center border-primary">
            <div class="card-body py-3">
                <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                <h3 class="mb-0 mt-2"><?= $stats['users'] ?></h3>
                <small class="text-muted">Users</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-info">
            <div class="card-body py-3">
                <i class="bi bi-building text-info" style="font-size: 1.5rem;"></i>
                <h3 class="mb-0 mt-2"><?= $stats['directorates'] ?></h3>
                <small class="text-muted">Directorates</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-success">
            <div class="card-body py-3">
                <i class="bi bi-bullseye text-success" style="font-size: 1.5rem;"></i>
                <h3 class="mb-0 mt-2"><?= $stats['objectives'] ?></h3>
                <small class="text-muted">Objectives</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-warning">
            <div class="card-body py-3">
                <i class="bi bi-graph-up text-warning" style="font-size: 1.5rem;"></i>
                <h3 class="mb-0 mt-2"><?= $stats['kpis'] ?></h3>
                <small class="text-muted">KPIs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-secondary">
            <div class="card-body py-3">
                <i class="bi bi-folder text-secondary" style="font-size: 1.5rem;"></i>
                <h3 class="mb-0 mt-2"><?= $stats['projects'] ?></h3>
                <small class="text-muted">Projects</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-danger">
            <div class="card-body py-3">
                <i class="bi bi-hdd text-danger" style="font-size: 1.5rem;"></i>
                <h3 class="mb-0 mt-2"><?= count($modules) ?></h3>
                <small class="text-muted">Modules</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="<?= url('/cpanel/modules') ?>" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-puzzle d-block mb-1" style="font-size: 1.5rem;"></i>
                            Module Configuration
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= url('/cpanel/database') ?>" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-database d-block mb-1" style="font-size: 1.5rem;"></i>
                            Database Management
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= url('/cpanel/integrations') ?>" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-plug d-block mb-1" style="font-size: 1.5rem;"></i>
                            Integrations
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= url('/cpanel/backup') ?>" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-cloud-arrow-up d-block mb-1" style="font-size: 1.5rem;"></i>
                            Backup & Restore
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module Overview -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-puzzle me-2"></i>Active Modules</h5>
                <a href="<?= url('/cpanel/modules') ?>" class="btn btn-sm btn-outline-primary">Configure</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Module</th>
                                <th>Description</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-<?= $module['icon'] ?> me-2 text-primary"></i>
                                    <strong><?= e($module['name']) ?></strong>
                                </td>
                                <td><small class="text-muted"><?= e($module['description']) ?></small></td>
                                <td class="text-center">
                                    <?php if ($module['enabled']): ?>
                                    <span class="badge bg-success">Enabled</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($module['core'] ?? false): ?>
                                    <span class="badge bg-primary">Core</span>
                                    <?php else: ?>
                                    <span class="badge bg-light text-dark">Optional</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- System Health -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>System Health</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Database</span>
                        <span class="text-success"><i class="bi bi-check-circle"></i> Connected</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>File Storage</span>
                        <span class="text-success"><i class="bi bi-check-circle"></i> Available</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>OpenAI API</span>
                        <?php if (defined('OPENAI_API_KEY') && OPENAI_API_KEY): ?>
                        <span class="text-success"><i class="bi bi-check-circle"></i> Configured</span>
                        <?php else: ?>
                        <span class="text-warning"><i class="bi bi-exclamation-circle"></i> Not Set</span>
                        <?php endif; ?>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-<?= (defined('OPENAI_API_KEY') && OPENAI_API_KEY) ? 'success' : 'warning' ?>" style="width: 100%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>LDAP</span>
                        <?php if (defined('LDAP_ENABLED') && LDAP_ENABLED): ?>
                        <span class="text-success"><i class="bi bi-check-circle"></i> Enabled</span>
                        <?php else: ?>
                        <span class="text-muted"><i class="bi bi-dash-circle"></i> Disabled</span>
                        <?php endif; ?>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-secondary" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Administration</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= url('/admin/users') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> User Management
                </a>
                <a href="<?= url('/admin/directorates') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-building me-2"></i> Directorates
                </a>
                <a href="<?= url('/admin/financial-years') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-calendar3 me-2"></i> Financial Years
                </a>
                <a href="<?= url('/admin/settings') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-gear me-2"></i> System Settings
                </a>
                <a href="<?= url('/cpanel/logs') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-journal-text me-2"></i> Audit Logs
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
