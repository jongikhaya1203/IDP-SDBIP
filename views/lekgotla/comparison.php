<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-table me-2"></i>IDP Priority Comparison</h1>
            <p class="text-muted mb-0">Compare continuing, new, and discarded priorities with budget allocations</p>
        </div>
        <div>
            <a href="/lekgotla/export" class="btn btn-outline-success me-2">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <a href="/lekgotla" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Lekgotla
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0 text-success"><?= count($continuing) ?></h3>
                    <small class="text-muted">Continuing Priorities</small>
                    <div class="mt-2">
                        <strong class="text-success">R<?= number_format(array_sum(array_column($continuing, 'budget_allocated')), 0) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary h-100">
                <div class="card-body text-center">
                    <i class="bi bi-plus-circle text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0 text-primary"><?= count($newPriorities) ?></h3>
                    <small class="text-muted">New Priorities (Lekgotla)</small>
                    <div class="mt-2">
                        <strong class="text-primary">R<?= number_format(array_sum(array_column($newPriorities, 'budget_allocated')), 0) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger h-100">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0 text-danger"><?= count($discarded) ?></h3>
                    <small class="text-muted">Discarded Priorities</small>
                    <div class="mt-2">
                        <strong class="text-danger">R<?= number_format(array_sum(array_column($discarded, 'budget_allocated')), 0) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info h-100">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 text-info" style="font-size: 2rem;"></i>
                    <?php
                    $totalBudget = array_sum(array_column($continuing, 'budget_allocated')) +
                                   array_sum(array_column($newPriorities, 'budget_allocated'));
                    ?>
                    <h3 class="mt-2 mb-0 text-info">R<?= number_format($totalBudget / 1000000, 1) ?>M</h3>
                    <small class="text-muted">Active Budget</small>
                    <div class="mt-2">
                        <small class="text-muted"><?= count($continuing) + count($newPriorities) ?> active priorities</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget by Category Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Budget Allocation by Category</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($budgetByCategory as $cat): ?>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 40px; height: 40px; background: <?= $cat['color_code'] ?>; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <span class="text-white fw-bold"><?= $cat['priority_count'] ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?= htmlspecialchars($cat['name']) ?></div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">R<?= number_format($cat['total_allocated'], 0) ?></small>
                                        <small class="text-success"><?= $cat['total_allocated'] > 0 ? round(($cat['total_spent'] / $cat['total_allocated']) * 100) : 0 ?>% spent</small>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar" style="width: <?= $cat['total_allocated'] > 0 ? ($cat['total_spent'] / $cat['total_allocated']) * 100 : 0 ?>%; background: <?= $cat['color_code'] ?>"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Tables -->
    <div class="row">
        <!-- Continuing Priorities -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-success">
                        <i class="bi bi-check-circle me-2"></i>Priorities Still On Track
                    </h5>
                    <span class="badge bg-success"><?= count($continuing) ?> priorities</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Priority Name</th>
                                    <th>Category</th>
                                    <th>Directorate</th>
                                    <th>Level</th>
                                    <th class="text-end">Budget</th>
                                    <th class="text-center">Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($continuing)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No continuing priorities</td></tr>
                                <?php else: ?>
                                <?php foreach ($continuing as $p): ?>
                                <tr>
                                    <td><code><?= $p['priority_code'] ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['priority_name']) ?></strong>
                                        <?php if ($p['source_type'] !== 'original'): ?>
                                            <span class="badge bg-info ms-1"><?= ucfirst($p['source_type']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: <?= $p['color_code'] ?>">
                                            <i class="bi <?= $p['icon'] ?? 'bi-flag' ?> me-1"></i>
                                            <?= $p['category_name'] ?? 'Uncategorized' ?>
                                        </span>
                                    </td>
                                    <td><?= $p['directorate_name'] ?? '-' ?></td>
                                    <td>
                                        <?php
                                        $levelColors = ['critical' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary'];
                                        ?>
                                        <span class="badge bg-<?= $levelColors[$p['priority_level']] ?>">
                                            <?= ucfirst($p['priority_level']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>R<?= number_format($p['budget_allocated'], 0) ?></strong>
                                        <?php if ($p['budget_spent'] > 0): ?>
                                            <br><small class="text-muted">R<?= number_format($p['budget_spent'], 0) ?> spent</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" style="width: 120px;">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: <?= $p['current_progress'] ?>%">
                                                <?= $p['current_progress'] ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = ['active' => 'primary', 'on_track' => 'success', 'at_risk' => 'warning', 'completed' => 'info'];
                                        ?>
                                        <span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>">
                                            <?= ucfirst(str_replace('_', ' ', $p['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total Continuing Budget:</strong></td>
                                    <td class="text-end"><strong class="text-success">R<?= number_format(array_sum(array_column($continuing, 'budget_allocated')), 0) ?></strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Priorities from Lekgotla -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <i class="bi bi-plus-circle me-2"></i>New Priorities Introduced (Mayoral Lekgotla)
                    </h5>
                    <span class="badge bg-primary"><?= count($newPriorities) ?> priorities</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Priority Name</th>
                                    <th>Category</th>
                                    <th>Directorate</th>
                                    <th>Level</th>
                                    <th class="text-end">Budget Allocated</th>
                                    <th>Source</th>
                                    <th>Imbizo Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($newPriorities)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No new priorities from Lekgotla yet</p>
                                        <a href="/lekgotla/create" class="btn btn-sm btn-primary mt-2">
                                            <i class="bi bi-plus-lg me-1"></i>Create Lekgotla Session
                                        </a>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($newPriorities as $p): ?>
                                <tr class="table-primary bg-opacity-10">
                                    <td><code class="text-primary"><?= $p['priority_code'] ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['priority_name']) ?></strong>
                                        <span class="badge bg-primary ms-1">NEW</span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: <?= $p['color_code'] ?>">
                                            <?= $p['category_name'] ?? 'Uncategorized' ?>
                                        </span>
                                    </td>
                                    <td><?= $p['directorate_name'] ?? '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $levelColors[$p['priority_level']] ?? 'secondary' ?>">
                                            <?= ucfirst($p['priority_level']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-primary">R<?= number_format($p['budget_allocated'], 0) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Lekgotla</span>
                                    </td>
                                    <td>
                                        <?php if ($p['source_imbizo_id']): ?>
                                            <a href="/imbizo/<?= $p['source_imbizo_id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-link-45deg"></i> View Imbizo
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($newPriorities)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total New Budget:</strong></td>
                                    <td class="text-end"><strong class="text-primary">R<?= number_format(array_sum(array_column($newPriorities, 'budget_allocated')), 0) ?></strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discarded Priorities -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger">
                        <i class="bi bi-x-circle me-2"></i>Priorities Discarded / Deferred
                    </h5>
                    <span class="badge bg-danger"><?= count($discarded) ?> priorities</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Priority Name</th>
                                    <th>Category</th>
                                    <th>Original Budget</th>
                                    <th>Reason for Removal</th>
                                    <th>Budget Freed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($discarded)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-check-all text-success" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No priorities have been discarded</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($discarded as $p): ?>
                                <tr class="table-danger bg-opacity-10">
                                    <td><code class="text-danger"><s><?= $p['priority_code'] ?></s></code></td>
                                    <td>
                                        <s class="text-muted"><?= htmlspecialchars($p['priority_name']) ?></s>
                                        <span class="badge bg-danger ms-1">DISCARDED</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $p['category_name'] ?? 'Uncategorized' ?>
                                        </span>
                                    </td>
                                    <td><s class="text-muted">R<?= number_format($p['budget_allocated'], 0) ?></s></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php if ($p['description']): ?>
                                                <?= htmlspecialchars(substr($p['description'], 0, 100)) ?>...
                                            <?php else: ?>
                                                No reason specified
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">+R<?= number_format($p['budget_allocated'], 0) ?></strong>
                                        <br><small class="text-muted">Available for reallocation</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($discarded)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total Budget Freed:</strong></td>
                                    <td><strong class="text-success">R<?= number_format(array_sum(array_column($discarded, 'budget_allocated')), 0) ?></strong></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Budget Summary -->
    <div class="card border-0 shadow-sm bg-light">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h6 class="text-muted">Continuing Budget</h6>
                    <h3 class="text-success">R<?= number_format(array_sum(array_column($continuing, 'budget_allocated')), 0) ?></h3>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">New Allocations</h6>
                    <h3 class="text-primary">+ R<?= number_format(array_sum(array_column($newPriorities, 'budget_allocated')), 0) ?></h3>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Total Active Budget</h6>
                    <h3 class="text-dark">R<?= number_format($totalBudget, 0) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-4 {
    border-width: 4px !important;
}
</style>
