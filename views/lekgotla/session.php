<?php
$title = htmlspecialchars($session['session_name']);
$breadcrumbs = [
    ['label' => 'Lekgotla', 'url' => '/lekgotla'],
    ['label' => $session['session_name']]
];
ob_start();
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($session['session_name']) ?></h1>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar me-1"></i><?= date('d F Y', strtotime($session['session_date'])) ?>
                <?php if ($session['venue']): ?>
                    <span class="mx-2">|</span>
                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($session['venue']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div>
            <?php if ($session['status'] !== 'approved'): ?>
                <a href="/lekgotla/session/<?= $session['id'] ?>/add-change" class="btn btn-primary me-2">
                    <i class="bi bi-plus-lg me-1"></i>Add Priority Change
                </a>
            <?php endif; ?>
            <a href="/lekgotla" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- Session Info Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Status</h6>
                    <?php
                    $statusColors = ['draft' => 'secondary', 'in_progress' => 'warning', 'completed' => 'info', 'approved' => 'success'];
                    ?>
                    <span class="badge bg-<?= $statusColors[$session['status']] ?> fs-6">
                        <?= ucfirst(str_replace('_', ' ', $session['status'])) ?>
                    </span>
                    <?php if ($session['resolution_number']): ?>
                        <br><small class="text-muted mt-2 d-block">Resolution: <?= $session['resolution_number'] ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Presided By</h6>
                    <p class="mb-0 fw-medium"><?= htmlspecialchars($session['presided_by']) ?></p>
                    <small class="text-muted">Created by: <?= $session['created_by_name'] ?? 'System' ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Linked Imbizo</h6>
                    <?php if ($session['imbizo_title']): ?>
                        <a href="/imbizo/<?= $session['linked_imbizo_id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($session['imbizo_title']) ?>
                        </a>
                        <br><small class="text-muted"><?= date('d M Y', strtotime($session['imbizo_date'])) ?></small>
                    <?php else: ?>
                        <span class="text-muted">Not linked</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Budget Impact</h6>
                    <?php $netImpact = ($budgetImpact['new_allocations'] ?? 0) - ($budgetImpact['freed_budget'] ?? 0) + ($budgetImpact['adjustments'] ?? 0); ?>
                    <h4 class="mb-0 <?= $netImpact >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $netImpact >= 0 ? '+' : '' ?>R<?= number_format($netImpact, 0) ?>
                    </h4>
                    <small class="text-muted">Net budget change</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Priority Changes -->
        <div class="col-lg-8">
            <!-- Changes Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Priority Changes Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="border-end">
                                <h3 class="text-success mb-0"><?= count($groupedChanges['retain']) ?></h3>
                                <small class="text-muted">Retained</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border-end">
                                <h3 class="text-primary mb-0"><?= count($groupedChanges['new']) ?></h3>
                                <small class="text-muted">New</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border-end">
                                <h3 class="text-warning mb-0"><?= count($groupedChanges['modify']) ?></h3>
                                <small class="text-muted">Modified</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border-end">
                                <h3 class="text-danger mb-0"><?= count($groupedChanges['discard']) ?></h3>
                                <small class="text-muted">Discarded</small>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="text-secondary mb-0"><?= count($groupedChanges['defer']) ?></h3>
                            <small class="text-muted">Deferred</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Priorities -->
            <?php if (!empty($groupedChanges['new'])): ?>
            <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-primary"><i class="bi bi-plus-circle me-2"></i>New Priorities</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($groupedChanges['new'] as $change): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($change['new_priority_name']) ?></h6>
                                <p class="mb-1 text-muted small"><?= htmlspecialchars($change['new_priority_description'] ?? '') ?></p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-info"><?= $change['category_name'] ?? 'Uncategorized' ?></span>
                                    <span class="badge bg-<?= ['critical' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary'][$change['new_priority_level']] ?? 'secondary' ?>">
                                        <?= ucfirst($change['new_priority_level']) ?>
                                    </span>
                                    <span class="badge bg-success">R<?= number_format($change['new_budget'] ?? 0, 0) ?></span>
                                </div>
                                <?php if ($change['imbizo_action']): ?>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-link-45deg"></i> Imbizo: <?= htmlspecialchars($change['imbizo_action']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $change['status'] === 'approved' ? 'success' : ($change['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($change['status']) ?>
                                </span>
                                <?php if ($session['status'] !== 'approved' && $change['status'] === 'proposed'): ?>
                                    <div class="btn-group btn-group-sm mt-2">
                                        <form method="POST" action="/lekgotla/change/<?= $change['id'] ?>/review" class="d-inline">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="/lekgotla/change/<?= $change['id'] ?>/review" class="d-inline">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Discarded Priorities -->
            <?php if (!empty($groupedChanges['discard'])): ?>
            <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-danger"><i class="bi bi-x-circle me-2"></i>Discarded Priorities</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($groupedChanges['discard'] as $change): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <code class="text-danger"><?= $change['priority_code'] ?></code>
                                    <s class="text-muted ms-2"><?= htmlspecialchars($change['priority_name']) ?></s>
                                </h6>
                                <p class="mb-1 text-muted small"><?= htmlspecialchars($change['change_reason'] ?? 'No reason provided') ?></p>
                                <span class="badge bg-danger">Budget Freed: R<?= number_format($change['previous_budget'] ?? 0, 0) ?></span>
                            </div>
                            <span class="badge bg-<?= $change['status'] === 'approved' ? 'success' : 'warning' ?>">
                                <?= ucfirst($change['status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Modified Priorities -->
            <?php if (!empty($groupedChanges['modify'])): ?>
            <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-warning"><i class="bi bi-pencil-square me-2"></i>Modified Priorities</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($groupedChanges['modify'] as $change): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <code><?= $change['priority_code'] ?></code>
                                    <?= htmlspecialchars($change['priority_name']) ?>
                                </h6>
                                <p class="mb-1 text-muted small"><?= htmlspecialchars($change['change_reason'] ?? '') ?></p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-secondary">Previous: R<?= number_format($change['previous_budget'] ?? 0, 0) ?></span>
                                    <i class="bi bi-arrow-right"></i>
                                    <span class="badge bg-primary">New: R<?= number_format($change['new_budget'] ?? 0, 0) ?></span>
                                    <span class="badge bg-<?= ($change['budget_variance'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                        <?= ($change['budget_variance'] ?? 0) >= 0 ? '+' : '' ?>R<?= number_format($change['budget_variance'] ?? 0, 0) ?>
                                    </span>
                                </div>
                            </div>
                            <span class="badge bg-<?= $change['status'] === 'approved' ? 'success' : 'warning' ?>">
                                <?= ucfirst($change['status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Budget Impact -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Budget Impact</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>New Allocations:</span>
                        <strong class="text-success">+R<?= number_format($budgetImpact['new_allocations'] ?? 0, 0) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Budget Freed:</span>
                        <strong class="text-danger">-R<?= number_format($budgetImpact['freed_budget'] ?? 0, 0) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Adjustments:</span>
                        <strong class="text-info">R<?= number_format($budgetImpact['adjustments'] ?? 0, 0) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Net Impact:</strong>
                        <strong class="<?= $netImpact >= 0 ? 'text-success' : 'text-danger' ?> fs-5">
                            <?= $netImpact >= 0 ? '+' : '' ?>R<?= number_format($netImpact, 0) ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Linked Imbizo Actions -->
            <?php if (!empty($imbizoActions)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>Imbizo Commitments</h5>
                </div>
                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($imbizoActions as $action): ?>
                    <div class="list-group-item">
                        <small class="text-muted"><?= $action['directorate_name'] ?? 'Unassigned' ?></small>
                        <p class="mb-0 small"><?= htmlspecialchars(substr($action['action_description'], 0, 100)) ?>...</p>
                        <span class="badge bg-<?= ['critical' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary'][$action['priority']] ?? 'secondary' ?> mt-1">
                            <?= ucfirst($action['priority']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Resolutions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Resolutions</h5>
                    <span class="badge bg-info"><?= count($resolutions) ?></span>
                </div>
                <?php if (empty($resolutions)): ?>
                    <div class="card-body text-center text-muted">
                        <i class="bi bi-file-earmark-text" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No resolutions yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($resolutions as $res): ?>
                        <div class="list-group-item">
                            <strong><?= $res['resolution_number'] ?></strong>
                            <p class="mb-0 small"><?= htmlspecialchars($res['resolution_title']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Approve Session -->
            <?php if ($session['status'] !== 'approved'): ?>
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Finalize Session</h6>
                    <form method="POST" action="/lekgotla/session/<?= $session['id'] ?>/approve">
                        <div class="mb-3">
                            <label class="form-label">Resolution Number</label>
                            <input type="text" name="resolution_number" class="form-control" placeholder="e.g., RES/2024/001" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve session and apply all approved changes?')">
                            <i class="bi bi-check-circle me-1"></i>Approve & Apply Changes
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.border-4 { border-width: 4px !important; }
</style>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
