<?php
$pageTitle = $title ?? 'New Capital Project';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/budget') ?>">Budget</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/budget/projects') ?>">Projects</a></li>
                <li class="breadcrumb-item active">New Project</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">New Capital Project</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= url('/budget/projects') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Project Code <span class="text-danger">*</span></label>
                    <input type="text" name="project_code" class="form-control" required placeholder="e.g., CP-001">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Directorate <span class="text-danger">*</span></label>
                    <select name="directorate_id" class="form-select" required>
                        <option value="">Select Directorate</option>
                        <?php foreach ($directorates as $dir): ?>
                        <option value="<?= $dir['id'] ?>"><?= e($dir['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Project Name <span class="text-danger">*</span></label>
                <input type="text" name="project_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Total Budget <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">R</span>
                    <input type="number" name="total_budget" class="form-control" required min="0" step="0.01">
                </div>
            </div>

            <h5 class="mt-4 mb-3">Quarterly Budget Allocation</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q1 (Jul-Sep)</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q1_budget" class="form-control" min="0" step="0.01" value="0">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q2 (Oct-Dec)</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q2_budget" class="form-control" min="0" step="0.01" value="0">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q3 (Jan-Mar)</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q3_budget" class="form-control" min="0" step="0.01" value="0">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q4 (Apr-Jun)</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q4_budget" class="form-control" min="0" step="0.01" value="0">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="<?= url('/budget/projects') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Project</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
