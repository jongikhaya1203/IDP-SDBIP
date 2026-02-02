<?php
$pageTitle = $title ?? 'Create User';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/admin') ?>">Admin</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/admin/users') ?>">Users</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Create New User</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="<?= url('/admin/users') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="director">Director</option>
                                <option value="independent_assessor">Independent Assessor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Directorate</label>
                            <select name="directorate_id" id="directorate_id" class="form-select">
                                <option value="">-- Select Directorate --</option>
                                <?php foreach ($directorates as $dir): ?>
                                <option value="<?= $dir['id'] ?>"><?= e($dir['code'] . ' - ' . $dir['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dep): ?>
                                <option value="<?= $dep['id'] ?>" data-directorate="<?= $dep['directorate_id'] ?>">
                                    <?= e($dep['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Role Permissions</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong class="text-danger">Admin</strong>
                        <br><small class="text-muted">Full system access, user management</small>
                    </li>
                    <li class="mb-2">
                        <strong class="text-primary">Director</strong>
                        <br><small class="text-muted">Directorate oversight, KPI approval</small>
                    </li>
                    <li class="mb-2">
                        <strong class="text-info">Manager</strong>
                        <br><small class="text-muted">Department management, rating reviews</small>
                    </li>
                    <li class="mb-2">
                        <strong class="text-warning">Independent Assessor</strong>
                        <br><small class="text-muted">Independent ratings, POE review</small>
                    </li>
                    <li>
                        <strong class="text-secondary">Employee</strong>
                        <br><small class="text-muted">Self assessment, POE upload</small>
                    </li>
                </ul>
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
