<?php
$pageTitle = $title ?? 'Module Configuration';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/cpanel') ?>">Control Panel</a></li>
                <li class="breadcrumb-item active">Modules</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-puzzle me-2"></i>Module Configuration</h1>
        <p class="text-muted mb-0">Enable or disable system modules</p>
    </div>
</div>

<form action="<?= url('/cpanel/modules') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Core Modules -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-star-fill me-2"></i>Core Modules</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Core modules are required for system operation and cannot be disabled.</p>

                    <?php foreach ($modules as $module): ?>
                    <?php if ($module['core'] ?? false): ?>
                    <div class="card mb-3 border-primary">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-start">
                                <div class="form-check form-switch me-3">
                                    <input type="checkbox" class="form-check-input" checked disabled>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-<?= $module['icon'] ?> me-2 text-primary"></i>
                                        <strong><?= e($module['name']) ?></strong>
                                        <span class="badge bg-primary ms-2">Core</span>
                                    </div>
                                    <small class="text-muted"><?= e($module['description']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Optional Modules -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-collection me-2"></i>Optional Modules</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Optional modules can be enabled or disabled based on your requirements.</p>

                    <?php foreach ($modules as $module): ?>
                    <?php if (!($module['core'] ?? false)): ?>
                    <div class="card mb-3 <?= $module['enabled'] ? 'border-success' : 'border-secondary' ?>">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-start">
                                <div class="form-check form-switch me-3">
                                    <input type="checkbox" class="form-check-input" name="modules[<?= $module['id'] ?>]"
                                           id="module_<?= $module['id'] ?>" <?= $module['enabled'] ? 'checked' : '' ?>>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-<?= $module['icon'] ?> me-2 text-<?= $module['enabled'] ? 'success' : 'secondary' ?>"></i>
                                        <label for="module_<?= $module['id'] ?>" class="mb-0 cursor-pointer">
                                            <strong><?= e($module['name']) ?></strong>
                                        </label>
                                        <?php if ($module['enabled']): ?>
                                        <span class="badge bg-success ms-2">Active</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= e($module['description']) ?></small>
                                    <?php if (isset($module['requires'])): ?>
                                    <div class="mt-1">
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Requires: <?= e($module['requires']) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-info-circle text-info me-2"></i>
                    <span class="text-muted">Changes will take effect immediately after saving.</span>
                </div>
                <div>
                    <a href="<?= url('/cpanel') ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.cursor-pointer { cursor: pointer; }
.form-check-input:checked { background-color: #198754; border-color: #198754; }
</style>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
