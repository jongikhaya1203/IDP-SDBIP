<?php
$pageTitle = $title ?? 'Mayoral IDP Imbizo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Mayoral IDP Imbizo</h1>
        <p class="text-muted mb-0">Community engagement sessions and action item tracking</p>
    </div>
    <?php if (has_role('admin', 'director')): ?>
    <a href="<?= url('/imbizo/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Schedule Imbizo
    </a>
    <?php endif; ?>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Sessions</h6>
                        <h2 class="mb-0"><?= $stats['total_sessions'] ?></h2>
                    </div>
                    <i class="bi bi-calendar-event" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Action Items</h6>
                        <h2 class="mb-0"><?= $stats['total_actions'] ?></h2>
                    </div>
                    <i class="bi bi-list-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-dark-50">Pending Actions</h6>
                        <h2 class="mb-0"><?= $stats['pending_actions'] ?></h2>
                    </div>
                    <i class="bi bi-clock" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Completed</h6>
                        <h2 class="mb-0"><?= $stats['completed_actions'] ?></h2>
                    </div>
                    <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row mb-4">
    <div class="col-md-6">
        <a href="<?= url('/imbizo/action-items?filter=pending') ?>" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-exclamation-triangle text-warning me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1 text-dark">Pending Action Items</h5>
                    <p class="text-muted mb-0">View and respond to assigned items</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="<?= url('/imbizo/action-items?filter=overdue') ?>" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-alarm text-danger me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1 text-dark">Overdue Items</h5>
                    <p class="text-muted mb-0">Items past their target date</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Sessions List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Imbizo Sessions</h5>
        <a href="<?= url('/imbizo/action-items') ?>" class="btn btn-sm btn-outline-primary">
            View All Action Items
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($sessions)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Imbizo Sessions</h5>
            <p class="text-muted">No sessions have been scheduled yet.</p>
            <?php if (has_role('admin', 'director')): ?>
            <a href="<?= url('/imbizo/create') ?>" class="btn btn-primary">Schedule First Imbizo</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Ward</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Progress</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                    <?php
                    $statusBadge = [
                        'scheduled' => 'primary',
                        'live' => 'danger',
                        'completed' => 'success',
                        'cancelled' => 'secondary'
                    ][$session['status']] ?? 'secondary';
                    $progress = $session['action_count'] > 0
                        ? round(($session['completed_count'] / $session['action_count']) * 100)
                        : 0;
                    ?>
                    <tr>
                        <td>
                            <strong><?= format_date($session['session_date'], 'd M Y') ?></strong><br>
                            <small class="text-muted"><?= date('H:i', strtotime($session['start_time'])) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('/imbizo/' . $session['id']) ?>" class="text-decoration-none">
                                <?= e($session['title']) ?>
                            </a>
                        </td>
                        <td><?= e($session['ward_name'] ?? 'All Wards') ?></td>
                        <td>
                            <span class="badge bg-<?= $statusBadge ?>">
                                <?php if ($session['status'] === 'live'): ?>
                                <i class="bi bi-broadcast me-1"></i>
                                <?php endif; ?>
                                <?= ucfirst($session['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $session['action_count'] ?> items</span>
                        </td>
                        <td style="min-width: 120px;">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: <?= $progress ?>%">
                                    <?= $progress ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="<?= url('/imbizo/' . $session['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($session['status'] === 'scheduled' && has_role('admin', 'director')): ?>
                            <a href="<?= url('/imbizo/' . $session['id'] . '/livestream') ?>" class="btn btn-sm btn-danger">
                                <i class="bi bi-broadcast"></i> Go Live
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
