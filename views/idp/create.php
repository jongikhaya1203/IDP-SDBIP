<?php
$pageTitle = $title ?? 'Create Strategic Objective';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/idp') ?>">IDP</a></li>
                <li class="breadcrumb-item active">Create Objective</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Create Strategic Objective</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= url('/idp/objectives') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="financial_year_id" value="<?= $financialYear['id'] ?? '' ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Objective Code <span class="text-danger">*</span></label>
                    <input type="text" name="objective_code" class="form-control" required
                           placeholder="e.g., SO-001" value="<?= old('objective_code') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" name="weight" class="form-control" required
                           min="0" max="100" step="0.1" value="<?= old('weight', '0') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Objective Name <span class="text-danger">*</span></label>
                <input type="text" name="objective_name" class="form-control" required
                       value="<?= old('objective_name') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Directorate <span class="text-danger">*</span></label>
                <select name="directorate_id" class="form-select" required>
                    <option value="">Select Directorate</option>
                    <?php foreach ($directorates as $dir): ?>
                    <option value="<?= $dir['id'] ?>"><?= e($dir['name']) ?> (<?= e($dir['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">National Priority Alignment</label>
                    <input type="text" name="national_priority_alignment" class="form-control"
                           placeholder="e.g., NDP Priority 1" value="<?= old('national_priority_alignment') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Provincial Priority Alignment</label>
                    <input type="text" name="provincial_priority_alignment" class="form-control"
                           value="<?= old('provincial_priority_alignment') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">IDP Goal</label>
                <input type="text" name="idp_goal" class="form-control" value="<?= old('idp_goal') ?>">
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('/idp') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Objective</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
