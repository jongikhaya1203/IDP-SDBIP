<?php
$title = 'Mayoral Lekgotla';
$breadcrumbs = [
    ['label' => 'Lekgotla']
];
ob_start();
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-diagram-3 me-2"></i>Mayoral Lekgotla</h1>
            <p class="text-muted mb-0">Manage IDP Priority Changes based on Mayoral Imbizo Commitments</p>
        </div>
        <div>
            <a href="/lekgotla/comparison" class="btn btn-outline-primary me-2">
                <i class="bi bi-table me-1"></i>Priority Comparison
            </a>
            <a href="/lekgotla/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Lekgotla Session
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Priorities</h6>
                            <h2 class="mb-0"><?= $priorityStats['total'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-list-check fs-1 opacity-50"></i>
                    </div>
                    <small class="text-white-50">
                        <?= $priorityStats['from_lekgotla'] ?? 0 ?> from Lekgotla |
                        <?= $priorityStats['from_imbizo'] ?? 0 ?> from Imbizo
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">On Track</h6>
                            <h2 class="mb-0"><?= ($priorityStats['active'] ?? 0) + ($priorityStats['on_track'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                    <small class="text-white-50">Active priorities progressing well</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-dark-50 mb-1">At Risk</h6>
                            <h2 class="mb-0"><?= $priorityStats['at_risk'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                    <small class="text-dark-50">Require immediate attention</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Budget</h6>
                            <h2 class="mb-0">R<?= number_format(($priorityStats['total_budget'] ?? 0) / 1000000, 1) ?>M</h2>
                        </div>
                        <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                    </div>
                    <small class="text-white-50">
                        R<?= number_format(($priorityStats['total_spent'] ?? 0) / 1000000, 1) ?>M spent
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Impact Summary -->
    <?php if ($budgetImpact && ($budgetImpact['new_count'] > 0 || $budgetImpact['discard_count'] > 0)): ?>
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Lekgotla Budget Impact (This Year)</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="border-end">
                        <h4 class="text-success mb-1">+R<?= number_format($budgetImpact['new_allocations'] ?? 0, 0) ?></h4>
                        <small class="text-muted">New Allocations (<?= $budgetImpact['new_count'] ?? 0 ?> priorities)</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <h4 class="text-danger mb-1">-R<?= number_format($budgetImpact['freed_budget'] ?? 0, 0) ?></h4>
                        <small class="text-muted">Freed Budget (<?= $budgetImpact['discard_count'] ?? 0 ?> discarded)</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <h4 class="text-info mb-1">R<?= number_format($budgetImpact['net_adjustments'] ?? 0, 0) ?></h4>
                        <small class="text-muted">Net Adjustments (<?= $budgetImpact['modify_count'] ?? 0 ?> modified)</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php $netImpact = ($budgetImpact['new_allocations'] ?? 0) - ($budgetImpact['freed_budget'] ?? 0) + ($budgetImpact['net_adjustments'] ?? 0); ?>
                    <h4 class="<?= $netImpact >= 0 ? 'text-primary' : 'text-danger' ?> mb-1">
                        <?= $netImpact >= 0 ? '+' : '' ?>R<?= number_format($netImpact, 0) ?>
                    </h4>
                    <small class="text-muted">Net Budget Impact</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Lekgotla Sessions -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Lekgotla Sessions</h5>
                    <span class="badge bg-primary"><?= count($sessions) ?> sessions</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($sessions)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No Lekgotla sessions yet for this financial year.</p>
                            <a href="/lekgotla/create" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Create First Session
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Session</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-center">Changes</th>
                                        <th class="text-center">Resolutions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $session): ?>
                                    <tr>
                                        <td>
                                            <a href="/lekgotla/session/<?= $session['id'] ?>" class="text-decoration-none fw-medium">
                                                <?= htmlspecialchars($session['session_name']) ?>
                                            </a>
                                            <?php if ($session['resolution_number']): ?>
                                                <br><small class="text-muted">Res: <?= $session['resolution_number'] ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($session['session_date'])) ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'in_progress' => 'warning',
                                                'completed' => 'info',
                                                'approved' => 'success'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statusColors[$session['status']] ?>">
                                                <?= ucfirst(str_replace('_', ' ', $session['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill"><?= $session['change_count'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info rounded-pill"><?= $session['resolution_count'] ?></span>
                                        </td>
                                        <td>
                                            <a href="/lekgotla/session/<?= $session['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Changes -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Changes</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentChanges)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No changes recorded yet</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentChanges as $change): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <?php
                                        $changeIcons = [
                                            'retain' => '<i class="bi bi-check-circle text-success"></i>',
                                            'new' => '<i class="bi bi-plus-circle text-primary"></i>',
                                            'modify' => '<i class="bi bi-pencil-square text-warning"></i>',
                                            'discard' => '<i class="bi bi-x-circle text-danger"></i>',
                                            'defer' => '<i class="bi bi-pause-circle text-secondary"></i>'
                                        ];
                                        echo $changeIcons[$change['change_type']] ?? '';
                                        ?>
                                        <strong class="ms-2">
                                            <?= $change['change_type'] === 'new' ? $change['new_priority_name'] : $change['priority_name'] ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= ucfirst($change['change_type']) ?> •
                                            <?= $change['session_name'] ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?= $change['status'] === 'approved' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($change['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/lekgotla/comparison" class="list-group-item list-group-item-action">
                        <i class="bi bi-table me-2"></i>Priority Comparison Table
                    </a>
                    <a href="/imbizo" class="list-group-item list-group-item-action">
                        <i class="bi bi-megaphone me-2"></i>Mayoral Imbizo Sessions
                    </a>
                    <a href="/idp" class="list-group-item list-group-item-action">
                        <i class="bi bi-diagram-2 me-2"></i>IDP Strategic Objectives
                    </a>
                    <a href="/lekgotla/export" class="list-group-item list-group-item-action">
                        <i class="bi bi-download me-2"></i>Export Comparison Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
