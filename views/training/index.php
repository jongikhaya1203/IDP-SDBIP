<?php
$pageTitle = $title ?? 'Training Center';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="bi bi-mortarboard me-2"></i>Training Center</h1>
        <p class="text-muted mb-0">Learn how to use the SDBIP/IDP Management System</p>
    </div>
    <div>
        <a href="<?= url('/training/quick-start') ?>" class="btn btn-primary">
            <i class="bi bi-lightning me-1"></i> Quick Start Guide
        </a>
    </div>
</div>

<!-- Quick Links -->
<div class="row mb-4">
    <div class="col-md-3">
        <a href="<?= url('/training/faq') ?>" class="card text-decoration-none h-100 border-primary">
            <div class="card-body text-center">
                <i class="bi bi-question-circle text-primary" style="font-size: 2rem;"></i>
                <h5 class="mt-2 mb-0 text-dark">FAQ</h5>
                <small class="text-muted">Common questions</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= url('/training/videos') ?>" class="card text-decoration-none h-100 border-danger">
            <div class="card-body text-center">
                <i class="bi bi-play-circle text-danger" style="font-size: 2rem;"></i>
                <h5 class="mt-2 mb-0 text-dark">Videos</h5>
                <small class="text-muted">Tutorial videos</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= url('/training/glossary') ?>" class="card text-decoration-none h-100 border-success">
            <div class="card-body text-center">
                <i class="bi bi-book text-success" style="font-size: 2rem;"></i>
                <h5 class="mt-2 mb-0 text-dark">Glossary</h5>
                <small class="text-muted">Terms & definitions</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="<?= url('/training/quick-start') ?>" class="card text-decoration-none h-100 border-warning">
            <div class="card-body text-center">
                <i class="bi bi-rocket-takeoff text-warning" style="font-size: 2rem;"></i>
                <h5 class="mt-2 mb-0 text-dark">Quick Start</h5>
                <small class="text-muted">Get started fast</small>
            </div>
        </a>
    </div>
</div>

<!-- Training Modules -->
<h4 class="mb-3"><i class="bi bi-collection me-2"></i>Training Modules</h4>

<div class="row">
    <?php foreach ($modules as $module): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded p-2 me-3">
                        <i class="bi bi-<?= $module['icon'] ?>" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= e($module['title']) ?></h5>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i><?= $module['duration'] ?>
                            <span class="badge bg-<?= $module['level'] === 'Beginner' ? 'success' : ($module['level'] === 'Intermediate' ? 'warning' : 'danger') ?> ms-2">
                                <?= $module['level'] ?>
                            </span>
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted"><?= e($module['description']) ?></p>
                <h6>Topics Covered:</h6>
                <ul class="small mb-0">
                    <?php foreach (array_slice($module['topics'], 0, 3) as $topic): ?>
                    <li><?= e($topic) ?></li>
                    <?php endforeach; ?>
                    <?php if (count($module['topics']) > 3): ?>
                    <li class="text-muted">+ <?= count($module['topics']) - 3 ?> more...</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-footer bg-transparent">
                <a href="<?= url('/training/module/' . $module['slug']) ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-book me-1"></i> Start Module
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
