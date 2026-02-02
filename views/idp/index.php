<?php
$pageTitle = $title ?? 'IDP Strategic Objectives';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">IDP Strategic Objectives</h1>
        <p class="text-muted mb-0">Financial Year: <?= e($financialYear['year_label'] ?? 'N/A') ?></p>
    </div>
    <a href="<?= url('/idp/objectives/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Objective
    </a>
</div>

<?php if (empty($objectives)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-bullseye text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3">No Strategic Objectives</h5>
        <p class="text-muted">No strategic objectives have been created for this financial year.</p>
        <a href="<?= url('/idp/objectives/create') ?>" class="btn btn-primary">Create First Objective</a>
    </div>
</div>
<?php else: ?>

<div class="row">
    <?php foreach ($objectives as $obj): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="badge bg-primary"><?= e($obj['objective_code']) ?></span>
                <span class="badge bg-secondary"><?= e($obj['directorate_code'] ?? 'N/A') ?></span>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= e($obj['objective_name']) ?></h5>
                <p class="card-text text-muted small"><?= e(substr($obj['description'] ?? '', 0, 100)) ?>...</p>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small">
                        <i class="bi bi-bar-chart me-1"></i> <?= $obj['kpi_count'] ?> KPIs
                    </span>
                    <span class="badge bg-info"><?= number_format($obj['weight'], 1) ?>%</span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="btn-group w-100">
                    <a href="<?= url('/idp/objectives/' . $obj['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                    <a href="<?= url('/idp/objectives/' . $obj['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Objectives Summary</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Objective</th>
                        <th>Directorate</th>
                        <th>Weight</th>
                        <th>KPIs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($objectives as $obj): ?>
                    <tr>
                        <td><span class="badge bg-primary"><?= e($obj['objective_code']) ?></span></td>
                        <td><?= e($obj['objective_name']) ?></td>
                        <td><?= e($obj['directorate_name'] ?? 'N/A') ?></td>
                        <td><?= number_format($obj['weight'], 1) ?>%</td>
                        <td><?= $obj['kpi_count'] ?></td>
                        <td>
                            <a href="<?= url('/idp/objectives/' . $obj['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= url('/idp/objectives/' . $obj['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
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
