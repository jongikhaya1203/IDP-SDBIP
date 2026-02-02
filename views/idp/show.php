<?php
$pageTitle = $title ?? 'Strategic Objective';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/idp') ?>">IDP</a></li>
                <li class="breadcrumb-item active"><?= e($objective['objective_code']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= e($objective['objective_name']) ?></h1>
    </div>
    <div>
        <a href="<?= url('/idp/objectives/' . $objective['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="<?= url('/idp') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Objective Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th class="ps-0">Code:</th>
                        <td><span class="badge bg-primary"><?= e($objective['objective_code']) ?></span></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Financial Year:</th>
                        <td><?= e($objective['year_label'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Directorate:</th>
                        <td><?= e($objective['directorate_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Weight:</th>
                        <td><span class="badge bg-info"><?= number_format($objective['weight'], 1) ?>%</span></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Status:</th>
                        <td>
                            <?php if ($objective['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Description & Alignment</h5>
            </div>
            <div class="card-body">
                <h6>Description</h6>
                <p><?= nl2br(e($objective['description'] ?? 'No description provided.')) ?></p>

                <?php if (!empty($objective['national_priority_alignment'])): ?>
                <h6 class="mt-4">National Priority Alignment</h6>
                <p><?= e($objective['national_priority_alignment']) ?></p>
                <?php endif; ?>

                <?php if (!empty($objective['provincial_priority_alignment'])): ?>
                <h6 class="mt-4">Provincial Priority Alignment</h6>
                <p><?= e($objective['provincial_priority_alignment']) ?></p>
                <?php endif; ?>

                <?php if (!empty($objective['idp_goal'])): ?>
                <h6 class="mt-4">IDP Goal</h6>
                <p><?= e($objective['idp_goal']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Linked KPIs (<?= count($kpis) ?>)</h5>
        <a href="<?= url('/sdbip/kpis/create?objective_id=' . $objective['id']) ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add KPI
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($kpis)): ?>
        <div class="text-center py-4">
            <i class="bi bi-bar-chart text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2 mb-0">No KPIs linked to this objective yet.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>KPI Code</th>
                        <th>KPI Name</th>
                        <th>Unit</th>
                        <th>Annual Target</th>
                        <th>Responsible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kpis as $kpi): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= e($kpi['kpi_code']) ?></span></td>
                        <td><?= e($kpi['kpi_name']) ?></td>
                        <td><?= e($kpi['unit_of_measure']) ?></td>
                        <td><?= e($kpi['annual_target']) ?></td>
                        <td><?= e(($kpi['first_name'] ?? '') . ' ' . ($kpi['last_name'] ?? '')) ?></td>
                        <td>
                            <a href="<?= url('/sdbip/kpis/' . $kpi['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
