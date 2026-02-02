<?php
$pageTitle = $title ?? 'Budget Projections';
ob_start();
$months = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/budget') ?>">Budget</a></li>
                <li class="breadcrumb-item active">Projections</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Budget Projections</h1>
        <p class="text-muted mb-0">Financial Year: <?= e($financialYear['year_label'] ?? 'N/A') ?></p>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter by Directorate</label>
                <select name="directorate_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Directorates</option>
                    <?php foreach ($directorates as $dir): ?>
                    <option value="<?= $dir['id'] ?>" <?= $selectedDirectorate == $dir['id'] ? 'selected' : '' ?>>
                        <?= e($dir['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <a href="<?= url('/budget/projections') ?>" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($grouped)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No Budget Projections</h5>
        <p class="text-muted">No budget projections found for this financial year.</p>
    </div>
</div>
<?php else: ?>

<?php foreach ($grouped as $dirId => $dirData): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <span class="badge bg-primary me-2"><?= e($dirData['code']) ?></span>
            <?= e($dirData['name']) ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <?php foreach ($months as $i => $month): ?>
                        <th class="text-center"><?= $month ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Revenue Row -->
                    <tr>
                        <td><strong>Projected Revenue</strong></td>
                        <?php
                        $totalProjected = 0;
                        for ($m = 1; $m <= 12; $m++):
                            $val = $dirData['months'][$m]['projected_revenue'] ?? 0;
                            $totalProjected += $val;
                        ?>
                        <td class="text-end"><?= number_format($val/1000000, 1) ?>M</td>
                        <?php endfor; ?>
                        <td class="text-end"><strong><?= number_format($totalProjected/1000000, 1) ?>M</strong></td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Actual Revenue</strong></td>
                        <?php
                        $totalActual = 0;
                        for ($m = 1; $m <= 12; $m++):
                            $val = $dirData['months'][$m]['actual_revenue'] ?? 0;
                            $totalActual += $val;
                        ?>
                        <td class="text-end"><?= number_format($val/1000000, 1) ?>M</td>
                        <?php endfor; ?>
                        <td class="text-end"><strong><?= number_format($totalActual/1000000, 1) ?>M</strong></td>
                    </tr>
                    <!-- OPEX Row -->
                    <tr>
                        <td><strong>Projected OPEX</strong></td>
                        <?php
                        $totalOpexProj = 0;
                        for ($m = 1; $m <= 12; $m++):
                            $val = $dirData['months'][$m]['operating_expenditure_projected'] ?? 0;
                            $totalOpexProj += $val;
                        ?>
                        <td class="text-end"><?= number_format($val/1000000, 1) ?>M</td>
                        <?php endfor; ?>
                        <td class="text-end"><strong><?= number_format($totalOpexProj/1000000, 1) ?>M</strong></td>
                    </tr>
                    <tr class="table-warning">
                        <td><strong>Actual OPEX</strong></td>
                        <?php
                        $totalOpexAct = 0;
                        for ($m = 1; $m <= 12; $m++):
                            $val = $dirData['months'][$m]['operating_expenditure_actual'] ?? 0;
                            $totalOpexAct += $val;
                        ?>
                        <td class="text-end"><?= number_format($val/1000000, 1) ?>M</td>
                        <?php endfor; ?>
                        <td class="text-end"><strong><?= number_format($totalOpexAct/1000000, 1) ?>M</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Variance Summary -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="alert alert-<?= $totalActual >= $totalProjected ? 'success' : 'warning' ?> mb-0">
                    <strong>Revenue Variance:</strong>
                    <?= format_currency($totalActual - $totalProjected) ?>
                    (<?= $totalProjected > 0 ? number_format((($totalActual - $totalProjected) / $totalProjected) * 100, 1) : 0 ?>%)
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-<?= $totalOpexAct <= $totalOpexProj ? 'success' : 'danger' ?> mb-0">
                    <strong>OPEX Variance:</strong>
                    <?= format_currency($totalOpexAct - $totalOpexProj) ?>
                    (<?= $totalOpexProj > 0 ? number_format((($totalOpexAct - $totalOpexProj) / $totalOpexProj) * 100, 1) : 0 ?>%)
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
