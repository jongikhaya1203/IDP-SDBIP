<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Directorate Performance</h4>
        <p class="text-muted mb-0">Organizational performance by directorate</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports/export/excel" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export
        </a>
    </div>
</div>

<!-- Performance Cards -->
<div class="row g-4 mb-4">
    <?php foreach ($directorates as $d):
        $achieveRate = $d['total_kpis'] > 0 ? round(($d['achieved'] / $d['total_kpis']) * 100, 1) : 0;
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= e($d['code']) ?></strong>
                    <br><small class="text-muted"><?= e($d['name']) ?></small>
                </div>
                <span class="badge bg-<?= rating_color($d['avg_rating'] ?? 0) ?> fs-6">
                    <?= number_format($d['avg_rating'] ?? 0, 2) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Achievement</span>
                        <span><?= $d['achieved'] ?>/<?= $d['total_kpis'] ?> KPIs (<?= $achieveRate ?>%)</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= $achieveRate ?>%"></div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <small class="text-muted d-block">Budget</small>
                        <strong><?= format_currency($d['budget_allocation'] ?? 0) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Director</small>
                        <strong><?= e(($d['head_name'] ?? '') . ' ' . ($d['head_surname'] ?? '')) ?: 'N/A' ?></strong>
                    </div>
                </div>

                <hr>
                <a href="/reports/directorate/<?= $d['id'] ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-bar-chart me-1"></i>View Details
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Comparison Chart -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-bar-chart me-2"></i>Directorate Comparison
    </div>
    <div class="card-body">
        <div id="comparisonChart" style="height: 350px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = <?= json_encode($directorates) ?>;

    new ApexCharts(document.querySelector("#comparisonChart"), {
        chart: { type: 'bar', height: 350, toolbar: { show: false } },
        series: [{
            name: 'Average Rating',
            data: data.map(d => parseFloat(d.avg_rating) || 0)
        }],
        xaxis: {
            categories: data.map(d => d.code)
        },
        colors: ['#3b82f6'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: val => val.toFixed(2),
            offsetX: -10,
            style: { colors: ['#fff'] }
        },
        yaxis: { min: 0, max: 5 }
    }).render();
});
</script>
