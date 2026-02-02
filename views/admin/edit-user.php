<?php
$pageTitle = $title ?? 'Edit User';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/admin') ?>">Admin</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/users') ?>">Users</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Edit User: <?= e($user['username']) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="<?= url('/admin/users/' . $user['id']) ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required value="<?= e($user['first_name']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required value="<?= e($user['last_name']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" disabled value="<?= e($user['username']) ?>">
                            <small class="text-muted">Username cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" minlength="8">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="employee" <?= $user['role'] == 'employee' ? 'selected' : '' ?>>Employee</option>
                                <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
                                <option value="director" <?= $user['role'] == 'director' ? 'selected' : '' ?>>Director</option>
                                <option value="independent_assessor" <?= $user['role'] == 'independent_assessor' ? 'selected' : '' ?>>Independent Assessor</option>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Directorate</label>
                            <select name="directorate_id" id="directorate_id" class="form-select">
                                <option value="">-- Select Directorate --</option>
                                <?php foreach ($directorates as $dir): ?>
                                <option value="<?= $dir['id'] ?>" <?= $user['directorate_id'] == $dir['id'] ? 'selected' : '' ?>>
                                    <?= e($dir['code'] . ' - ' . $dir['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dep): ?>
                                <option value="<?= $dep['id'] ?>" data-directorate="<?= $dep['directorate_id'] ?>"
                                        <?= $user['department_id'] == $dep['id'] ? 'selected' : '' ?>>
                                    <?= e($dep['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active User</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <form action="<?= url('/admin/users/' . $user['id'] . '/delete') ?>" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to deactivate this user?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Deactivate
                            </button>
                        </form>
                        <div class="d-flex gap-2">
                            <a href="<?= url('/admin/users/' . $user['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">User Info</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th class="ps-0">Created:</th>
                        <td><?= date('d M Y H:i', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php if ($user['updated_at']): ?>
                    <tr>
                        <th class="ps-0">Last Updated:</th>
                        <td><?= date('d M Y H:i', strtotime($user['updated_at'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($user['last_login']): ?>
                    <tr>
                        <th class="ps-0">Last Login:</th>
                        <td><?= date('d M Y H:i', strtotime($user['last_login'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('directorate_id').addEventListener('change', function() {
    const dirId = this.value;
    const deptSelect = document.getElementById('department_id');
    const options = deptSelect.querySelectorAll('option');

    options.forEach(opt => {
        if (opt.value === '' || opt.dataset.directorate === dirId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    deptSelect.value = '';
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
