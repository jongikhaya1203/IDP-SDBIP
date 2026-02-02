<?php
$pageTitle = $title ?? 'Directorates';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/admin') ?>">Admin</a></li>
                <li class="breadcrumb-item active">Directorates</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Directorates</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($directorates)): ?>
        <div class="text-center py-5">
            <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Directorates Found</h5>
            <p class="text-muted">No directorates have been configured.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Directorate Name</th>
                        <th>Head</th>
                        <th class="text-center">Departments</th>
                        <th class="text-center">Users</th>
                        <th class="text-end">Budget Allocation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($directorates as $dir): ?>
                    <tr>
                        <td><span class="badge bg-primary"><?= e($dir['code']) ?></span></td>
                        <td><strong><?= e($dir['name']) ?></strong></td>
                        <td>
                            <?php if ($dir['first_name']): ?>
                            <?= e($dir['first_name'] . ' ' . $dir['last_name']) ?>
                            <?php else: ?>
                            <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $dir['department_count'] ?></td>
                        <td class="text-center"><?= $dir['user_count'] ?></td>
                        <td class="text-end"><?= format_currency($dir['budget_allocation'] ?? 0) ?></td>
                        <td>
                            <?php if ($dir['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
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
