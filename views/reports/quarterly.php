<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Quarterly Performance Reports</h4>
        <p class="text-muted mb-0">FY <?= e($financialYear['year_label'] ?? '') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports/export/excel" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </a>
        <a href="/reports/ai" class="btn btn-primary">
            <i class="bi bi-robot me-1"></i>AI Report
        </a>
    </div>
</div>

<!-- Quarter Cards -->
<div class="row g-4 mb-4">
    <?php for ($q = 1; $q <= 4; $q++):
        $qData = null;
        foreach ($quarterSummary as $qs) {
            if ($qs['quarter'] == $q) {
                $qData = $qs;
                break;
            }
        }
        $isCurrentQ = ($q == $currentQuarter);
        $achieveRate = $qData && $qData['total_kpis'] > 0
            ? round(($qData['achieved'] / $qData['total_kpis']) * 100, 1)
            : 0;
    ?>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 <?= $isCurrentQ ? 'border-primary' : '' ?>">
            <?php if ($isCurrentQ): ?>
            <div class="card-header bg-primary text-white py-2">
                <small><i class="bi bi-clock me-1"></i>Current Quarter</small>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= quarter_label($q) ?></h5>

                <?php if ($qData): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Achievement Rate</span>
                        <strong><?= $achieveRate ?>%</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-<?= $achieveRate >= 75 ? 'success' : ($achieveRate >= 50 ? 'warning' : 'danger') ?>"
                             style="width: <?= $achieveRate ?>%"></div>
                    </div>
                </div>

                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="h4 text-success mb-0"><?= $qData['achieved'] ?? 0 ?></div>
                        <small class="text-muted">Achieved</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-warning mb-0"><?= $qData['partial'] ?? 0 ?></div>
                        <small class="text-muted">Partial</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-danger mb-0"><?= $qData['not_achieved'] ?? 0 ?></div>
                        <small class="text-muted">Missed</small>
                    </div>
                </div>

                <div class="text-center">
                    <span class="badge bg-<?= rating_color($qData['avg_rating'] ?? 0) ?> fs-5">
                        Rating: <?= number_format($qData['avg_rating'] ?? 0, 2) ?>
                    </span>
                </div>

                <hr>
                <a href="/reports/quarterly/<?= $q ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-eye me-1"></i>View Details
                </a>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    <p>No data available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- Quarterly Trend Chart -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-graph-up me-2"></i>Quarterly Performance Trend
    </div>
    <div class="card-body">
        <div id="trendChart" style="height: 300px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = <?= json_encode($quarterSummary) ?>;

    const quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
    const ratings = [0, 0, 0, 0];
    const achieved = [0, 0, 0, 0];

    data.forEach(d => {
        const idx = d.quarter - 1;
        ratings[idx] = parseFloat(d.avg_rating) || 0;
        achieved[idx] = parseInt(d.achieved) || 0;
    });

    new ApexCharts(document.querySelector("#trendChart"), {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [
            { name: 'Average Rating', data: ratings },
            { name: 'KPIs Achieved', type: 'column', data: achieved }
        ],
        xaxis: { categories: quarters },
        yaxis: [
            { title: { text: 'Rating' }, min: 0, max: 5 },
            { opposite: true, title: { text: 'Achieved' } }
        ],
        colors: ['#3b82f6', '#10b981'],
        stroke: { width: [3, 0] },
        dataLabels: { enabled: false }
    }).render();
});
</script>
