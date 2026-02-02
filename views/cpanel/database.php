<?php
$pageTitle = $title ?? 'Database Management';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/cpanel') ?>">Control Panel</a></li>
                <li class="breadcrumb-item active">Database</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-database me-2"></i>Database Management</h1>
        <p class="text-muted mb-0">Database: <code><?= e($dbName) ?></code> | Size: <strong><?= $dbSize ?> MB</strong></p>
    </div>
    <div>
        <a href="<?= url('/cpanel/backup') ?>" class="btn btn-outline-primary">
            <i class="bi bi-cloud-arrow-up me-1"></i> Backup
        </a>
    </div>
</div>

<!-- Database Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary mb-0"><?= count($tables) ?></h3>
                <small class="text-muted">Tables</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info mb-0"><?= $dbSize ?> MB</h3>
                <small class="text-muted">Total Size</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success mb-0"><?= array_sum(array_column($tables, 'Rows')) ?></h3>
                <small class="text-muted">Total Rows</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning mb-0"><?= (defined('DB_PORT') ? DB_PORT : '3306') ?></h3>
                <small class="text-muted">MySQL Port</small>
            </div>
        </div>
    </div>
</div>

<!-- Table List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Database Tables</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Table Name</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Data Size</th>
                        <th class="text-end">Index Size</th>
                        <th>Engine</th>
                        <th>Collation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $table): ?>
                    <tr>
                        <td>
                            <i class="bi bi-table text-primary me-2"></i>
                            <code><?= e($table['Name']) ?></code>
                        </td>
                        <td class="text-end"><?= number_format($table['Rows'] ?? 0) ?></td>
                        <td class="text-end"><?= round(($table['Data_length'] ?? 0) / 1024, 2) ?> KB</td>
                        <td class="text-end"><?= round(($table['Index_length'] ?? 0) / 1024, 2) ?> KB</td>
                        <td><span class="badge bg-secondary"><?= e($table['Engine'] ?? 'InnoDB') ?></span></td>
                        <td><small class="text-muted"><?= e($table['Collation'] ?? 'utf8mb4_unicode_ci') ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Database Info -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Connection Settings</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th class="ps-0">Host:</th>
                        <td><code><?= e((defined('DB_HOST') ? DB_HOST : 'localhost')) ?></code></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Port:</th>
                        <td><code><?= e((defined('DB_PORT') ? DB_PORT : '3306')) ?></code></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Database:</th>
                        <td><code><?= e($dbName) ?></code></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Username:</th>
                        <td><code><?= e((defined('DB_USERNAME') ? DB_USERNAME : 'root')) ?></code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Maintenance</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">For advanced database operations, use phpMyAdmin or MySQL command line.</p>
                <div class="d-grid gap-2">
                    <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open phpMyAdmin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
