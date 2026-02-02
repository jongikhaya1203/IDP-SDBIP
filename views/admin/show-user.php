<?php
$pageTitle = $title ?? 'User Details';
ob_start();

$roleBadge = function($role) {
    return match($role) {
        'admin' => 'danger',
        'director' => 'primary',
        'manager' => 'info',
        'independent_assessor' => 'warning',
        default => 'secondary'
    };
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/admin') ?>">Admin</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/users') ?>">Users</a></li>
                <li class="breadcrumb-item active"><?= e($user['username']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h1>
    </div>
    <div>
        <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1)) ?>
                </div>
                <h4><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                <p class="text-muted mb-2"><?= e($user['email']) ?></p>
                <span class="badge bg-<?= $roleBadge($user['role']) ?> fs-6">
                    <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                </span>
                <?php if ($user['is_active']): ?>
                <span class="badge bg-success ms-1">Active</span>
                <?php else: ?>
                <span class="badge bg-danger ms-1">Inactive</span>
                <?php endif; ?>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th class="ps-0">Username:</th>
                        <td><code><?= e($user['username']) ?></code></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Directorate:</th>
                        <td><?= e($user['directorate_name'] ?? 'Not assigned') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Department:</th>
                        <td><?= e($user['department_name'] ?? 'Not assigned') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Created:</th>
                        <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php if ($user['updated_at']): ?>
                    <tr>
                        <th class="ps-0">Updated:</th>
                        <td><?= date('d M Y', strtotime($user['updated_at'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assigned KPIs</h5>
            </div>
            <div class="card-body">
                <?php if (empty($kpis)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No KPIs assigned to this user.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>KPI Code</th>
                                <th>KPI Name</th>
                                <th>Strategic Objective</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kpis as $kpi): ?>
                            <tr>
                                <td><strong><?= e($kpi['kpi_code']) ?></strong></td>
                                <td><?= e($kpi['kpi_name']) ?></td>
                                <td><small class="text-muted"><?= e($kpi['objective_name']) ?></small></td>
                                <td>
                                    <a href="<?= url('/sdbip/kpi/' . $kpi['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        View
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
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
