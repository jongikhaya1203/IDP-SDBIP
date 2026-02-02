<?php
$pageTitle = $title ?? 'Training Module';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/training') ?>">Training</a></li>
                <li class="breadcrumb-item active"><?= e($module['title']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">
            <i class="bi bi-<?= $module['icon'] ?> me-2"></i><?= e($module['title']) ?>
        </h1>
    </div>
    <a href="<?= url('/training') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Training
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?= $module['content'] ?>
            </div>
        </div>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4">
            <a href="<?= url('/training') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> All Modules
            </a>
            <a href="<?= url('/training/faq') ?>" class="btn btn-primary">
                Have Questions? <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Module Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Module Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th class="ps-0"><i class="bi bi-clock me-2"></i>Duration:</th>
                        <td><?= $module['duration'] ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0"><i class="bi bi-bar-chart me-2"></i>Level:</th>
                        <td>
                            <span class="badge bg-<?= $module['level'] === 'Beginner' ? 'success' : ($module['level'] === 'Intermediate' ? 'warning' : 'danger') ?>">
                                <?= $module['level'] ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Topics -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Topics Covered</h6>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($module['topics'] as $i => $topic): ?>
                <li class="list-group-item">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    <?= e($topic) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Need Help -->
        <div class="card bg-light">
            <div class="card-body text-center">
                <i class="bi bi-headset text-primary" style="font-size: 2rem;"></i>
                <h6 class="mt-2">Need Help?</h6>
                <p class="small text-muted mb-2">Check our FAQ or contact support</p>
                <a href="<?= url('/training/faq') ?>" class="btn btn-sm btn-outline-primary">
                    View FAQ
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
