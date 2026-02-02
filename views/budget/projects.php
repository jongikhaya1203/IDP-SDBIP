<?php
$pageTitle = $title ?? 'Capital Projects';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/budget') ?>">Budget</a></li>
                <li class="breadcrumb-item active">Capital Projects</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Capital Projects</h1>
        <p class="text-muted mb-0">Financial Year: <?= e($financialYear['year_label'] ?? 'N/A') ?></p>
    </div>
    <?php if (has_role('admin', 'director', 'manager')): ?>
    <a href="<?= url('/budget/projects/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Project
    </a>
    <?php endif; ?>
</div>

<?php if (empty($projects)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No Capital Projects</h5>
        <p class="text-muted">No capital projects found for this financial year.</p>
    </div>
</div>
<?php else: ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Project Name</th>
                        <th>Directorate</th>
                        <th class="text-end">Budget</th>
                        <th class="text-end">Spent</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <?php
                    $totalSpent = ($project['q1_spent'] ?? 0) + ($project['q2_spent'] ?? 0) +
                                  ($project['q3_spent'] ?? 0) + ($project['q4_spent'] ?? 0);
                    $spentPct = $project['total_budget'] > 0 ? ($totalSpent / $project['total_budget']) * 100 : 0;
                    $statusBadge = [
                        'planning' => 'secondary',
                        'procurement' => 'info',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'on_hold' => 'warning',
                        'cancelled' => 'danger'
                    ][$project['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><strong><?= e($project['project_code']) ?></strong></td>
                        <td><?= e($project['project_name']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($project['directorate_code']) ?></span></td>
                        <td class="text-end"><?= format_currency($project['total_budget']) ?></td>
                        <td class="text-end">
                            <?= format_currency($totalSpent) ?>
                            <br><small class="text-muted"><?= number_format($spentPct, 1) ?>%</small>
                        </td>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $project['status'])) ?></span></td>
                        <td style="min-width: 120px;">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-<?= $project['completion_percentage'] >= 100 ? 'success' : 'primary' ?>"
                                     style="width: <?= $project['completion_percentage'] ?>%">
                                    <?= $project['completion_percentage'] ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="<?= url('/budget/projects/' . $project['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (has_role('admin', 'director', 'manager')): ?>
                            <a href="<?= url('/budget/projects/' . $project['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
