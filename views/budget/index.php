<?php
$pageTitle = $title ?? 'Budget Overview';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Budget Overview</h1>
        <p class="text-muted mb-0">Financial Year: <?= e($financialYear['year_label'] ?? 'N/A') ?></p>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50">Total Projects</h6>
                <h2 class="mb-0"><?= $projectsSummary['total_projects'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50">Capital Budget</h6>
                <h2 class="mb-0"><?= format_currency($projectsSummary['total_budget'] ?? 0) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h6 class="text-dark-50">Capital Spent</h6>
                <h2 class="mb-0"><?= format_currency($projectsSummary['total_spent'] ?? 0) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h6 class="text-white-50">Avg Completion</h6>
                <h2 class="mb-0"><?= number_format($projectsSummary['avg_completion'] ?? 0, 1) ?>%</h2>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row mb-4">
    <div class="col-md-6">
        <a href="<?= url('/budget/projections') ?>" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-graph-up-arrow text-primary me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1 text-dark">Budget Projections</h5>
                    <p class="text-muted mb-0">Monthly revenue and expenditure projections</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="<?= url('/budget/projects') ?>" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-building text-success me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1 text-dark">Capital Projects</h5>
                    <p class="text-muted mb-0">Infrastructure and development projects</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Directorate Budget Summary -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Budget by Directorate</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Directorate</th>
                        <th class="text-end">Allocation</th>
                        <th class="text-end">Projected Revenue</th>
                        <th class="text-end">Actual Revenue</th>
                        <th class="text-end">Projected OPEX</th>
                        <th class="text-end">Actual OPEX</th>
                        <th>Variance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $row): ?>
                    <?php
                    $revenueVariance = ($row['total_actual_revenue'] ?? 0) - ($row['total_projected_revenue'] ?? 0);
                    $opexVariance = ($row['total_opex_actual'] ?? 0) - ($row['total_opex_projected'] ?? 0);
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($row['code']) ?></strong>
                            <br><small class="text-muted"><?= e($row['name']) ?></small>
                        </td>
                        <td class="text-end"><?= format_currency($row['budget_allocation']) ?></td>
                        <td class="text-end"><?= format_currency($row['total_projected_revenue']) ?></td>
                        <td class="text-end"><?= format_currency($row['total_actual_revenue']) ?></td>
                        <td class="text-end"><?= format_currency($row['total_opex_projected']) ?></td>
                        <td class="text-end"><?= format_currency($row['total_opex_actual']) ?></td>
                        <td>
                            <span class="badge bg-<?= $opexVariance <= 0 ? 'success' : 'danger' ?>">
                                <?= $opexVariance <= 0 ? 'Under' : 'Over' ?> Budget
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
