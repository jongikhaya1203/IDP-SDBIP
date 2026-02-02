<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Performance Dashboard</h4>
        <p class="text-muted mb-0">
            Financial Year: <strong><?= e($financialYear['year_label'] ?? 'N/A') ?></strong>
            | Current Quarter: <strong><?= quarter_label($currentQuarter) ?></strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <select class="form-select form-select-sm" id="quarterFilter" style="width: 150px;">
            <option value="">All Quarters</option>
            <option value="1">Q1 (Jul-Sep)</option>
            <option value="2">Q2 (Oct-Dec)</option>
            <option value="3">Q3 (Jan-Mar)</option>
            <option value="4">Q4 (Apr-Jun)</option>
        </select>
        <a href="/reports/ai" class="btn btn-primary btn-sm">
            <i class="bi bi-robot me-1"></i>AI Report
        </a>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card primary">
            <div class="stat-value"><?= (int)($kpiStats['total_kpis'] ?? 0) ?></div>
            <div class="stat-label">Total KPIs</div>
            <i class="bi bi-bullseye stat-icon"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card success">
            <div class="stat-value">
                <?php
                $achievementRate = $kpiStats['total_kpis'] > 0
                    ? round(($kpiStats['achieved'] / $kpiStats['total_kpis']) * 100, 1)
                    : 0;
                echo $achievementRate . '%';
                ?>
            </div>
            <div class="stat-label">Achievement Rate</div>
            <i class="bi bi-graph-up-arrow stat-icon"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= number_format($kpiStats['avg_rating'] ?? 0, 2) ?></div>
            <div class="stat-label">Average Rating</div>
            <i class="bi bi-star-fill stat-icon"></i>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card info">
            <?php
            $budgetUtil = ($budgetStats['capex_projected'] ?? 0) > 0
                ? round((($budgetStats['capex_actual'] ?? 0) / $budgetStats['capex_projected']) * 100, 1)
                : 0;
            ?>
            <div class="stat-value"><?= $budgetUtil ?>%</div>
            <div class="stat-label">Budget Utilization</div>
            <i class="bi bi-currency-dollar stat-icon"></i>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Quarterly Performance Trend -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2"></i>Quarterly Performance Trend</span>
                <span class="badge bg-light text-dark"><?= e($financialYear['year_label'] ?? '') ?></span>
            </div>
            <div class="card-body">
                <div id="quarterlyTrendChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- KPI Achievement Breakdown -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>KPI Achievement Status
            </div>
            <div class="card-body">
                <div id="achievementPieChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Directorate Performance & Budget -->
<div class="row g-3 mb-4">
    <!-- Directorate Performance -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-building me-2"></i>Directorate Performance
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Directorate</th>
                                <th class="text-center">KPIs</th>
                                <th class="text-center">Achieved</th>
                                <th class="text-center">Rating</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($directoratePerformance as $dir): ?>
                            <tr>
                                <td>
                                    <a href="/reports/directorate/<?= $dir['id'] ?>" class="text-decoration-none">
                                        <strong><?= e($dir['code']) ?></strong>
                                        <br><small class="text-muted"><?= e($dir['name']) ?></small>
                                    </a>
                                </td>
                                <td class="text-center"><?= (int)$dir['total_kpis'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= (int)$dir['achieved'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="rating-value" style="color: <?= $dir['avg_rating'] >= 3 ? '#10b981' : ($dir['avg_rating'] >= 2 ? '#f59e0b' : '#ef4444') ?>">
                                        <?= number_format($dir['avg_rating'] ?? 0, 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="width: 100px;">
                                        <?php $rate = $dir['achievement_rate'] ?? 0; ?>
                                        <div class="progress-bar bg-<?= $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') ?>"
                                             style="width: <?= $rate ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $rate ?>%</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Overview -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-wallet2 me-2"></i>Budget Overview
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <h6 class="text-muted mb-2">Capital Expenditure</h6>
                            <div class="h4 mb-1"><?= format_currency($budgetStats['capex_actual'] ?? 0) ?></div>
                            <small class="text-muted">of <?= format_currency($budgetStats['capex_projected'] ?? 0) ?></small>
                            <div class="progress mt-2">
                                <?php
                                $capexRate = ($budgetStats['capex_projected'] ?? 0) > 0
                                    ? min(100, ($budgetStats['capex_actual'] / $budgetStats['capex_projected']) * 100)
                                    : 0;
                                ?>
                                <div class="progress-bar bg-primary" style="width: <?= $capexRate ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <h6 class="text-muted mb-2">Operating Expenditure</h6>
                            <div class="h4 mb-1"><?= format_currency($budgetStats['opex_actual'] ?? 0) ?></div>
                            <small class="text-muted">of <?= format_currency($budgetStats['opex_projected'] ?? 0) ?></small>
                            <div class="progress mt-2">
                                <?php
                                $opexRate = ($budgetStats['opex_projected'] ?? 0) > 0
                                    ? min(100, ($budgetStats['opex_actual'] / $budgetStats['opex_projected']) * 100)
                                    : 0;
                                ?>
                                <div class="progress-bar bg-info" style="width: <?= $opexRate ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-3">Capital Projects Summary</h6>
                <div class="row text-center">
                    <div class="col-3">
                        <div class="h4 text-primary mb-0"><?= (int)($projectsSummary['total_projects'] ?? 0) ?></div>
                        <small class="text-muted">Projects</small>
                    </div>
                    <div class="col-3">
                        <div class="h4 text-success mb-0"><?= $projectsSummary['avg_completion'] ?? 0 ?>%</div>
                        <small class="text-muted">Avg Completion</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-dark mb-0"><?= format_currency($projectsSummary['total_spent'] ?? 0) ?></div>
                        <small class="text-muted">Total Spent</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLA Category Breakdown & Tasks -->
<div class="row g-3 mb-4">
    <!-- SLA Categories -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-layers me-2"></i>SLA Category Breakdown
            </div>
            <div class="card-body">
                <?php
                $slaLabels = [
                    'budget' => ['Budget Dependent', 'primary'],
                    'internal_control' => ['Internal Control', 'info'],
                    'hr_vacancy' => ['HR/Vacancy', 'warning'],
                    'none' => ['No Dependency', 'secondary']
                ];
                foreach ($slaBreakdown as $sla):
                    $label = $slaLabels[$sla['sla_category']] ?? ['Unknown', 'secondary'];
                    $achieveRate = $sla['count'] > 0 ? round(($sla['achieved'] / $sla['count']) * 100) : 0;
                ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-<?= $label[1] ?> me-2"><?= $sla['count'] ?></span>
                        <?= $label[0] ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress" style="width: 60px; height: 6px;">
                            <div class="progress-bar bg-<?= $label[1] ?>" style="width: <?= $achieveRate ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $achieveRate ?>%</small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-task me-2"></i>Pending Reviews</span>
                <span class="badge bg-warning text-dark"><?= count($pendingTasks) ?></span>
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                <?php if (empty($pendingTasks)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                    No pending reviews
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pendingTasks as $task): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="text-primary"><?= e($task['kpi_code']) ?></strong>
                                <br><small class="text-truncate d-block" style="max-width: 200px;"><?= e($task['kpi_name']) ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $task['status'] === 'submitted' ? 'warning' : 'info' ?>">
                                    <?= ucwords(str_replace('_', ' ', $task['status'])) ?>
                                </span>
                                <br><small class="text-muted">Q<?= $task['quarter'] ?></small>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Recent Activities
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                <?php if (empty($recentActivities)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No recent activities
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($recentActivities as $activity): ?>
                    <li class="list-group-item">
                        <div class="d-flex gap-2">
                            <?php
                            $actionIcons = [
                                'create' => ['plus-circle', 'success'],
                                'update' => ['pencil', 'primary'],
                                'approve' => ['check-circle', 'success'],
                                'reject' => ['x-circle', 'danger']
                            ];
                            $icon = $actionIcons[$activity['action']] ?? ['circle', 'secondary'];
                            ?>
                            <i class="bi bi-<?= $icon[0] ?> text-<?= $icon[1] ?>"></i>
                            <div>
                                <small>
                                    <strong><?= e($activity['first_name'] . ' ' . $activity['last_name']) ?></strong>
                                    <?= $activity['action'] ?>d
                                    <?= str_replace('_', ' ', $activity['table_name']) ?>
                                </small>
                                <br><small class="text-muted"><?= format_date($activity['created_at'], 'd M H:i') ?></small>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Directorate Radar Chart -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-diagram-3 me-2"></i>Directorate Performance Comparison
            </div>
            <div class="card-body">
                <div id="radarChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Budget Utilization by Directorate
            </div>
            <div class="card-body">
                <div id="budgetBarChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quarterly Performance Trend Chart
    const quarterlyData = <?= json_encode($quarterlyData) ?>;
    const quarters = quarterlyData.map(q => 'Q' + q.quarter);
    const ratings = quarterlyData.map(q => parseFloat(q.avg_rating) || 0);
    const achievementRates = quarterlyData.map(q => {
        const total = parseInt(q.total) || 0;
        const achieved = parseInt(q.achieved) || 0;
        return total > 0 ? Math.round((achieved / total) * 100) : 0;
    });

    new ApexCharts(document.querySelector("#quarterlyTrendChart"), {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [
            { name: 'Achievement Rate (%)', type: 'column', data: achievementRates },
            { name: 'Average Rating', type: 'line', data: ratings }
        ],
        xaxis: { categories: quarters.length ? quarters : ['Q1', 'Q2', 'Q3', 'Q4'] },
        yaxis: [
            { title: { text: 'Achievement Rate (%)' }, min: 0, max: 100 },
            { opposite: true, title: { text: 'Rating' }, min: 0, max: 5 }
        ],
        colors: ['#3b82f6', '#10b981'],
        stroke: { width: [0, 3] },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top' }
    }).render();

    // Achievement Pie Chart
    const kpiStats = <?= json_encode($kpiStats) ?>;
    new ApexCharts(document.querySelector("#achievementPieChart"), {
        chart: { type: 'donut', height: 300 },
        series: [
            parseInt(kpiStats.achieved) || 0,
            parseInt(kpiStats.partial) || 0,
            parseInt(kpiStats.not_achieved) || 0
        ],
        labels: ['Achieved', 'Partially Achieved', 'Not Achieved'],
        colors: ['#10b981', '#f59e0b', '#ef4444'],
        legend: { position: 'bottom' },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total KPIs',
                            formatter: () => kpiStats.total_kpis || 0
                        }
                    }
                }
            }
        }
    }).render();

    // Radar Chart for Directorate Comparison
    const directorateData = <?= json_encode($directoratePerformance) ?>;
    new ApexCharts(document.querySelector("#radarChart"), {
        chart: { type: 'radar', height: 350, toolbar: { show: false } },
        series: [{
            name: 'Average Rating',
            data: directorateData.map(d => parseFloat(d.avg_rating) || 0)
        }],
        xaxis: {
            categories: directorateData.map(d => d.code)
        },
        yaxis: { min: 0, max: 5 },
        colors: ['#3b82f6'],
        markers: { size: 4 },
        fill: { opacity: 0.2 }
    }).render();

    // Budget Bar Chart
    fetch('/api/dashboard/charts')
        .then(r => r.json())
        .then(data => {
            const budgetData = data.budgetUtilization || [];
            new ApexCharts(document.querySelector("#budgetBarChart"), {
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                series: [
                    { name: 'Projected', data: budgetData.map(b => parseFloat(b.projected) || 0) },
                    { name: 'Actual', data: budgetData.map(b => parseFloat(b.actual) || 0) }
                ],
                xaxis: { categories: budgetData.map(b => b.code) },
                colors: ['#94a3b8', '#3b82f6'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                legend: { position: 'top' },
                yaxis: {
                    labels: {
                        formatter: val => 'R' + (val / 1000000).toFixed(1) + 'M'
                    }
                }
            }).render();
        });
});
</script>
