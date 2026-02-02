<?php
$pageTitle = $title ?? 'Directorate Report';
ob_start();

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
                <li class="breadcrumb-item"><a href="<?= url('/reports/directorate') ?>">Directorate</a></li>
                <li class="breadcrumb-item active"><?= e($directorate['code']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= e($directorate['name']) ?></h1>
        <p class="text-muted mb-0">Financial Year: <?= e($financialYear['year_label'] ?? 'N/A') ?></p>
    </div>
    <div>
        <a href="<?= url('/reports/export/excel') ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export
        </a>
        <a href="<?= url('/reports/directorate') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<!-- Directorate Info Card -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Directorate Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="ps-0">Code:</th>
                        <td><span class="badge bg-primary"><?= e($directorate['code']) ?></span></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Head:</th>
                        <td><?= e(($directorate['first_name'] ?? '') . ' ' . ($directorate['last_name'] ?? 'Not Assigned')) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Budget:</th>
                        <td><strong><?= format_currency($directorate['budget_allocation'] ?? 0) ?></strong></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Total KPIs:</th>
                        <td><?= count($kpis) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">SLA Category Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($slaBreakdown as $sla): ?>
                    <?php
                    $achieveRate = $sla['count'] > 0 ? round(($sla['achieved'] / $sla['count']) * 100, 1) : 0;
                    $slaBadge = match($sla['sla_category']) {
                        'budget' => 'info',
                        'internal_control' => 'warning',
                        'hr_vacancy' => 'secondary',
                        default => 'light'
                    };
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card border-<?= $slaBadge ?>">
                            <div class="card-body text-center">
                                <h6 class="text-uppercase text-muted small">
                                    <?= ucfirst(str_replace('_', ' ', $sla['sla_category'] ?? 'Other')) ?>
                                </h6>
                                <h3 class="mb-0"><?= $sla['achieved'] ?>/<?= $sla['count'] ?></h3>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $slaBadge ?>" style="width: <?= $achieveRate ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $achieveRate ?>% Achieved</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Performance Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">KPI Quarterly Performance</h5>
    </div>
    <div class="card-body">
        <?php if (empty($kpis)): ?>
        <div class="text-center py-5">
            <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No KPIs Found</h5>
            <p class="text-muted">No active KPIs found for this directorate.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>KPI Code</th>
                        <th>KPI Name</th>
                        <th>Strategic Objective</th>
                        <th class="text-center">Q1</th>
                        <th class="text-center">Q2</th>
                        <th class="text-center">Q3</th>
                        <th class="text-center">Q4</th>
                        <th class="text-center">Annual Target</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kpis as $kpi): ?>
                    <tr>
                        <td>
                            <a href="<?= url('/sdbip/kpi/' . $kpi['id']) ?>">
                                <strong><?= e($kpi['kpi_code']) ?></strong>
                            </a>
                        </td>
                        <td><?= e($kpi['kpi_name']) ?></td>
                        <td><small class="text-muted"><?= e($kpi['objective_name']) ?></small></td>
                        <td class="text-center">
                            <?php if ($kpi['q1_actual'] !== null): ?>
                            <div><?= $kpi['q1_actual'] ?></div>
                            <?php if ($kpi['q1_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['q1_rating']) ?> small">
                                <?= number_format($kpi['q1_rating'], 1) ?>
                            </span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['q2_actual'] !== null): ?>
                            <div><?= $kpi['q2_actual'] ?></div>
                            <?php if ($kpi['q2_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['q2_rating']) ?> small">
                                <?= number_format($kpi['q2_rating'], 1) ?>
                            </span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['q3_actual'] !== null): ?>
                            <div><?= $kpi['q3_actual'] ?></div>
                            <?php if ($kpi['q3_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['q3_rating']) ?> small">
                                <?= number_format($kpi['q3_rating'], 1) ?>
                            </span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($kpi['q4_actual'] !== null): ?>
                            <div><?= $kpi['q4_actual'] ?></div>
                            <?php if ($kpi['q4_rating']): ?>
                            <span class="badge bg-<?= $ratingColor($kpi['q4_rating']) ?> small">
                                <?= number_format($kpi['q4_rating'], 1) ?>
                            </span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <strong><?= $kpi['annual_target'] ?></strong>
                            <br><small class="text-muted"><?= e($kpi['unit_of_measure']) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Performance Chart -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Quarterly Rating Trend</h5>
    </div>
    <div class="card-body">
        <canvas id="ratingChart" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php
$q1Ratings = [];
$q2Ratings = [];
$q3Ratings = [];
$q4Ratings = [];
foreach ($kpis as $kpi) {
    if ($kpi['q1_rating']) $q1Ratings[] = $kpi['q1_rating'];
    if ($kpi['q2_rating']) $q2Ratings[] = $kpi['q2_rating'];
    if ($kpi['q3_rating']) $q3Ratings[] = $kpi['q3_rating'];
    if ($kpi['q4_rating']) $q4Ratings[] = $kpi['q4_rating'];
}
$avgQ1 = count($q1Ratings) ? round(array_sum($q1Ratings) / count($q1Ratings), 2) : 0;
$avgQ2 = count($q2Ratings) ? round(array_sum($q2Ratings) / count($q2Ratings), 2) : 0;
$avgQ3 = count($q3Ratings) ? round(array_sum($q3Ratings) / count($q3Ratings), 2) : 0;
$avgQ4 = count($q4Ratings) ? round(array_sum($q4Ratings) / count($q4Ratings), 2) : 0;
?>
new Chart(document.getElementById('ratingChart'), {
    type: 'line',
    data: {
        labels: ['Q1 (Jul-Sep)', 'Q2 (Oct-Dec)', 'Q3 (Jan-Mar)', 'Q4 (Apr-Jun)'],
        datasets: [{
            label: 'Average Rating',
            data: [<?= $avgQ1 ?>, <?= $avgQ2 ?>, <?= $avgQ3 ?>, <?= $avgQ4 ?>],
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.3
        }, {
            label: 'Target (3.0)',
            data: [3, 3, 3, 3],
            borderColor: 'rgba(255, 99, 132, 0.5)',
            borderDash: [5, 5],
            fill: false,
            pointRadius: 0
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 5,
                title: {
                    display: true,
                    text: 'Rating (1-5)'
                }
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
