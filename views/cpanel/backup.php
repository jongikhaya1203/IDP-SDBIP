<?php
$pageTitle = $title ?? 'Backup & Restore';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/cpanel') ?>">Control Panel</a></li>
                <li class="breadcrumb-item active">Backup</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-cloud-arrow-up me-2"></i>Backup & Restore</h1>
        <p class="text-muted mb-0">Database backup and recovery options</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-download me-2"></i>Create Backup</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Create a full database backup including all tables and data.</p>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    For production environments, we recommend using MySQL command line or phpMyAdmin for reliable backups.
                </div>

                <h6>Manual Backup Commands</h6>
                <pre class="bg-light p-3 rounded"><code>mysqldump -u root -p --port=<?= (defined('DB_PORT') ? DB_PORT : '3306') ?> <?= (defined('DB_DATABASE') ? DB_DATABASE : 'sdbip_idp') ?> > backup_<?= date('Ymd') ?>.sql</code></pre>

                <div class="d-grid gap-2 mt-4">
                    <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open phpMyAdmin for Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Restore Backup</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Restore database from a previous backup file.</p>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Restoring a backup will overwrite all current data. This action cannot be undone.
                </div>

                <h6>Manual Restore Command</h6>
                <pre class="bg-light p-3 rounded"><code>mysql -u root -p --port=<?= (defined('DB_PORT') ? DB_PORT : '3306') ?> <?= (defined('DB_DATABASE') ? DB_DATABASE : 'sdbip_idp') ?> < backup_file.sql</code></pre>

                <div class="d-grid gap-2 mt-4">
                    <a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-outline-warning">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open phpMyAdmin for Import
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup Schedule -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Backup Best Practices</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <h6 class="text-success"><i class="bi bi-check-circle me-2"></i>Daily Backups</h6>
                        <p class="small text-muted mb-0">Schedule automated daily backups during off-peak hours (e.g., 2:00 AM).</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <h6 class="text-info"><i class="bi bi-cloud me-2"></i>Off-site Storage</h6>
                        <p class="small text-muted mb-0">Store backups in a separate location or cloud storage for disaster recovery.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-warning"><i class="bi bi-arrow-repeat me-2"></i>Test Restores</h6>
                        <p class="small text-muted mb-0">Periodically test backup restoration to ensure data integrity.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- POE Files Backup -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-folder me-2"></i>File Attachments Backup</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">POE (Proof of Evidence) files are stored in:</p>
        <pre class="bg-light p-3 rounded"><code><?= realpath(BASE_PATH . '/public/uploads') ?: BASE_PATH . '/public/uploads' ?></code></pre>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Remember to backup this folder along with your database backup to preserve all uploaded documents.
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
