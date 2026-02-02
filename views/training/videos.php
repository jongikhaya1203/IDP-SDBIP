<?php
$pageTitle = $title ?? 'Training Videos';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/training') ?>">Training</a></li>
                <li class="breadcrumb-item active">Videos</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-play-circle me-2"></i>Training Videos</h1>
        <p class="text-muted mb-0">Watch step-by-step tutorials</p>
    </div>
    <a href="<?= url('/training') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<?php if (empty($videos)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-camera-video-off text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No Videos Available</h5>
        <p class="text-muted">Training videos will be added soon.</p>
    </div>
</div>
<?php else: ?>

<div class="row">
    <?php foreach ($videos as $video): ?>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="position-relative">
                <img src="<?= e($video['thumbnail']) ?>" class="card-img-top" alt="<?= e($video['title']) ?>"
                     style="height: 180px; object-fit: cover;">
                <div class="position-absolute top-50 start-50 translate-middle">
                    <a href="<?= e($video['url']) ?>" class="btn btn-danger btn-lg rounded-circle" target="_blank">
                        <i class="bi bi-play-fill"></i>
                    </a>
                </div>
                <span class="position-absolute bottom-0 end-0 badge bg-dark m-2">
                    <i class="bi bi-clock me-1"></i><?= e($video['duration']) ?>
                </span>
            </div>
            <div class="card-body">
                <span class="badge bg-primary mb-2"><?= e($video['category']) ?></span>
                <h5 class="card-title"><?= e($video['title']) ?></h5>
                <p class="card-text text-muted small"><?= e($video['description']) ?></p>
            </div>
            <div class="card-footer bg-transparent">
                <a href="<?= e($video['url']) ?>" class="btn btn-outline-danger w-100" target="_blank">
                    <i class="bi bi-play-fill me-1"></i> Watch Video
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Note:</strong> Video links will open in a new tab. Ensure you have a stable internet connection for streaming.
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
