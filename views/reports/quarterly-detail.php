<?php
$pageTitle = $title ?? 'Quarterly Report';
ob_start();

$statusBadge = function($status) {
    return match($status) {
        'achieved' => 'success',
        'partially_achieved' => 'warning',
        'not_achieved' => 'danger',
        default => 'secondary'
    };
};

$ratingColor = function($rating) {
    if ($rating === null) return 'secondary';
    if ($rating >= 4) return 'success';
    if ($rating >= 3) return 'primary';
    if ($rating >= 2) return 'warning';
    return 'danger';
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/reports') ?>">Reports</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/reports/quarterly') ?>">Quarterly</a></li>
                <li class="breadcrumb-item active">Q<?= e($quarter) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Quarter <?= e($quarter) ?> Performance Report</h1>
        <p class="text-muted mb-0">Financial Year: <?= e($financialYear['year_label'] ?? 'N/A') ?></p>
    </div>
    <div>
        <a href="<?= url('/reports/export/excel') ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <a href="<?= url('/reports/quarterly') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<?php if (empty($groupedKpis)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No Data Available</h5>
        <p class="text-muted">No KPI data found for Quarter <?= e($quarter) ?>.</p>
    </div>
</div>
<?php else: ?>

<!-- Summary Cards -->
<?php
$totalKpis = 0;
$achieved = 0;
$partial = 0;
$notAchieved = 0;
$totalRating = 0;
$ratedCount = 0;

foreach ($groupedKpis as $dirData) {
    foreach ($dirData['kpis'] as $kpi) {
        $totalKpis++;
        if ($kpi['achievement_status'] === 'achieved') $achieved++;
        elseif ($kpi['achievement_status'] === 'partially_achieved') $partial++;
        elseif ($kpi['achievement_status'] === 'not_achieved') $notAchieved++;

        if ($kpi['aggregated_rating']) {
            $totalRating += $kpi['aggregated_rating'];
            $ratedCount++;
        }
    }
}
$avgRating = $ratedCount > 0 ? round($totalRating / $ratedCount, 2) : 0;
$achievementRate = $totalKpis > 0 ? round(($achieved / $totalKpis) * 100, 1) : 0;
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center border-primary">
            <div class="card-body">
                <h2 class="text-primary mb-0"><?= $totalKpis ?></h2>
                <small class="text-muted">Total KPIs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body">
                <h2 class="text-success mb-0"><?= $achieved ?></h2>
                <small class="text-muted">Achieved (<?= $achievementRate ?>%)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-warning">
            <div class="card-body">
                <h2 class="text-warning mb-0"><?= $partial ?></h2>
                <small class="text-muted">Partially Achieved</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-danger">
            <div class="card-body">
                <h2 class="text-danger mb-0"><?= $notAchieved ?></h2>
                <small class="text-muted">Not Achieved</small>
            </div>
        </div>
    </div>
</div>

<!-- KPIs by Directorate -->
<?php foreach ($groupedKpis as $dirId => $dirData): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <span class="badge bg-primary me-2"><?= e($dirData['directorate_code']) ?></span>
            <?= e($dirData['directorate_name']) ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>KPI Code</th>
                        <th>KPI Name</th>
                        <th>SLA Category</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Variance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Self</th>
                        <th class="text-center">Manager</th>
                        <th class="text-center">Independent</th>
                        <th class="text-center">Aggregated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dirData['kpis'] as $kpi): ?>
                    <tr>
                        <td><strong><?= e($kpi['kpi_code']) ?></strong></td>
                        <td><?= e($kpi['kpi_name']) ?></td>
                        <td>
                            <?php
                            $slaBadge = match($kpi['sla_category']) {
                                'budget' => 'info',
                                'internal_control' => 'warning',
                                'hr_vacancy' => 'secondary',
                                default => 'light'
                            };
                            ?>
                            <span class="badge bg-<?= $slaBadge ?>">
                                <?= ucfirst(str_replace('_', ' ', $kpi['sla_category'] ?? 'N/A')) ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $kpi['target_value'] ?? '-' ?></td>
                        <td class="text-center"><?= $kpi['actual_value'] ?? '-' ?></td>
                        <td class="text-center">
                            <?php if ($kpi['variance'] !== null): ?>
                            <span class="text-<?= $kpi['variance'] >= 0 ? 'success' : 'danger' ?>">
                                <?= number_format($kpi['variance'], 1) ?>%
                            </span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['achievement_status']): ?>
                            <span class="badge bg-<?= $statusBadge($kpi['achievement_status']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $kpi['achievement_status'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['self_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['self_rating']) ?>"><?= $kpi['self_rating'] ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['manager_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['manager_rating']) ?>"><?= $kpi['manager_rating'] ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['independent_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['independent_rating']) ?>"><?= $kpi['independent_rating'] ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['aggregated_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['aggregated_rating']) ?> fs-6">
                                <?= number_format($kpi['aggregated_rating'], 2) ?>
                            </span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
