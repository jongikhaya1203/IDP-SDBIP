<?php
$pageTitle = $title ?? 'Project Details';
ob_start();
$totalSpent = ($project['q1_spent'] ?? 0) + ($project['q2_spent'] ?? 0) +
              ($project['q3_spent'] ?? 0) + ($project['q4_spent'] ?? 0);
$totalBudget = ($project['q1_budget'] ?? 0) + ($project['q2_budget'] ?? 0) +
               ($project['q3_budget'] ?? 0) + ($project['q4_budget'] ?? 0);
$statusBadge = [
    'planning' => 'secondary',
    'procurement' => 'info',
    'in_progress' => 'primary',
    'completed' => 'success',
    'on_hold' => 'warning',
    'cancelled' => 'danger'
][$project['status']] ?? 'secondary';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/budget') ?>">Budget</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/budget/projects') ?>">Projects</a></li>
                <li class="breadcrumb-item active"><?= e($project['project_code']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= e($project['project_name']) ?></h1>
    </div>
    <div>
        <?php if (has_role('admin', 'director', 'manager')): ?>
        <a href="<?= url('/budget/projects/' . $project['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('/budget/projects') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Project Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th class="ps-0">Code:</th>
                        <td><span class="badge bg-primary"><?= e($project['project_code']) ?></span></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Directorate:</th>
                        <td><?= e($project['directorate_name']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Status:</th>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $project['status'])) ?></span></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Total Budget:</th>
                        <td><strong><?= format_currency($project['total_budget']) ?></strong></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Total Spent:</th>
                        <td><?= format_currency($totalSpent) ?></td>
                    </tr>
                </table>

                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Completion</span>
                        <strong><?= $project['completion_percentage'] ?>%</strong>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?= $project['completion_percentage'] ?>%">
                            <?= $project['completion_percentage'] ?>%
                        </div>
                    </div>
                </div>

                <?php if ($project['description']): ?>
                <hr>
                <h6>Description</h6>
                <p class="text-muted mb-0"><?= nl2br(e($project['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Quarterly Budget & Expenditure</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-center">Q1 (Jul-Sep)</th>
                                <th class="text-center">Q2 (Oct-Dec)</th>
                                <th class="text-center">Q3 (Jan-Mar)</th>
                                <th class="text-center">Q4 (Apr-Jun)</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>Budget</th>
                                <td class="text-center"><?= format_currency($project['q1_budget']) ?></td>
                                <td class="text-center"><?= format_currency($project['q2_budget']) ?></td>
                                <td class="text-center"><?= format_currency($project['q3_budget']) ?></td>
                                <td class="text-center"><?= format_currency($project['q4_budget']) ?></td>
                                <td class="text-center"><strong><?= format_currency($totalBudget) ?></strong></td>
                            </tr>
                            <tr class="table-info">
                                <th>Spent</th>
                                <td class="text-center"><?= format_currency($project['q1_spent']) ?></td>
                                <td class="text-center"><?= format_currency($project['q2_spent']) ?></td>
                                <td class="text-center"><?= format_currency($project['q3_spent']) ?></td>
                                <td class="text-center"><?= format_currency($project['q4_spent']) ?></td>
                                <td class="text-center"><strong><?= format_currency($totalSpent) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Variance</th>
                                <?php for ($q = 1; $q <= 4; $q++):
                                    $budget = $project["q{$q}_budget"] ?? 0;
                                    $spent = $project["q{$q}_spent"] ?? 0;
                                    $variance = $budget - $spent;
                                ?>
                                <td class="text-center">
                                    <span class="text-<?= $variance >= 0 ? 'success' : 'danger' ?>">
                                        <?= format_currency($variance) ?>
                                    </span>
                                </td>
                                <?php endfor; ?>
                                <td class="text-center">
                                    <strong class="text-<?= ($totalBudget - $totalSpent) >= 0 ? 'success' : 'danger' ?>">
                                        <?= format_currency($totalBudget - $totalSpent) ?>
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Budget vs Spent Chart -->
                <canvas id="budgetChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('budgetChart'), {
    type: 'bar',
    data: {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [
            {
                label: 'Budget',
                data: [<?= $project['q1_budget'] ?>, <?= $project['q2_budget'] ?>, <?= $project['q3_budget'] ?>, <?= $project['q4_budget'] ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Spent',
                data: [<?= $project['q1_spent'] ?>, <?= $project['q2_spent'] ?>, <?= $project['q3_spent'] ?>, <?= $project['q4_spent'] ?>],
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
