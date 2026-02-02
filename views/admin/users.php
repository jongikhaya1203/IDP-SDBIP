<?php
$pageTitle = $title ?? 'User Management';
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
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">User Management</h1>
    </div>
    <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add User
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Users Found</h5>
            <p class="text-muted">No users have been created yet.</p>
            <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add First User
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Directorate</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? '', 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                    <br><small class="text-muted"><?= e($user['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><code><?= e($user['username']) ?></code></td>
                        <td>
                            <span class="badge bg-<?= $roleBadge($user['role']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['directorate_code']): ?>
                            <span class="badge bg-secondary"><?= e($user['directorate_code']) ?></span>
                            <?= e($user['directorate_name']) ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?= date('d M Y', strtotime($user['created_at'])) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('/admin/users/' . $user['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
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
