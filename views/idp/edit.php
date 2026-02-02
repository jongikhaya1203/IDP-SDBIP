<?php
$pageTitle = $title ?? 'Edit Strategic Objective';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/idp') ?>">IDP</a></li>
                <li class="breadcrumb-item active">Edit Objective</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Edit Strategic Objective</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= url('/idp/objectives/' . $objective['id']) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Objective Code <span class="text-danger">*</span></label>
                    <input type="text" name="objective_code" class="form-control" required
                           value="<?= e($objective['objective_code']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" name="weight" class="form-control" required
                           min="0" max="100" step="0.1" value="<?= e($objective['weight']) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Objective Name <span class="text-danger">*</span></label>
                <input type="text" name="objective_name" class="form-control" required
                       value="<?= e($objective['objective_name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($objective['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Directorate <span class="text-danger">*</span></label>
                <select name="directorate_id" class="form-select" required>
                    <option value="">Select Directorate</option>
                    <?php foreach ($directorates as $dir): ?>
                    <option value="<?= $dir['id'] ?>" <?= $dir['id'] == $objective['directorate_id'] ? 'selected' : '' ?>>
                        <?= e($dir['name']) ?> (<?= e($dir['code']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">National Priority Alignment</label>
                    <input type="text" name="national_priority_alignment" class="form-control"
                           value="<?= e($objective['national_priority_alignment'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Provincial Priority Alignment</label>
                    <input type="text" name="provincial_priority_alignment" class="form-control"
                           value="<?= e($objective['provincial_priority_alignment'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">IDP Goal</label>
                <input type="text" name="idp_goal" class="form-control" value="<?= e($objective['idp_goal'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                           <?= $objective['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <form action="<?= url('/idp/objectives/' . $objective['id'] . '/delete') ?>" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this objective?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
                <div class="d-flex gap-2">
                    <a href="<?= url('/idp') ?>" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
