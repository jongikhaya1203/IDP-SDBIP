<?php
$pageTitle = $title ?? 'Edit Project';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/budget') ?>">Budget</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/budget/projects') ?>">Projects</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Edit Project</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= url('/budget/projects/' . $project['id']) ?>" method="POST">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Project Code <span class="text-danger">*</span></label>
                    <input type="text" name="project_code" class="form-control" required value="<?= e($project['project_code']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Directorate <span class="text-danger">*</span></label>
                    <select name="directorate_id" class="form-select" required>
                        <?php foreach ($directorates as $dir): ?>
                        <option value="<?= $dir['id'] ?>" <?= $dir['id'] == $project['directorate_id'] ? 'selected' : '' ?>>
                            <?= e($dir['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="planning" <?= $project['status'] == 'planning' ? 'selected' : '' ?>>Planning</option>
                        <option value="procurement" <?= $project['status'] == 'procurement' ? 'selected' : '' ?>>Procurement</option>
                        <option value="in_progress" <?= $project['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $project['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="on_hold" <?= $project['status'] == 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                        <option value="cancelled" <?= $project['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Project Name <span class="text-danger">*</span></label>
                <input type="text" name="project_name" class="form-control" required value="<?= e($project['project_name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= e($project['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Budget</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="total_budget" class="form-control" min="0" step="0.01" value="<?= $project['total_budget'] ?>">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Completion %</label>
                    <input type="number" name="completion_percentage" class="form-control" min="0" max="100" value="<?= $project['completion_percentage'] ?>">
                </div>
            </div>

            <h5 class="mt-4 mb-3">Quarterly Budget</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q1 Budget</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q1_budget" class="form-control" min="0" step="0.01" value="<?= $project['q1_budget'] ?>">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q2 Budget</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q2_budget" class="form-control" min="0" step="0.01" value="<?= $project['q2_budget'] ?>">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q3 Budget</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q3_budget" class="form-control" min="0" step="0.01" value="<?= $project['q3_budget'] ?>">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q4 Budget</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q4_budget" class="form-control" min="0" step="0.01" value="<?= $project['q4_budget'] ?>">
                    </div>
                </div>
            </div>

            <h5 class="mt-4 mb-3">Quarterly Expenditure</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q1 Spent</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q1_spent" class="form-control" min="0" step="0.01" value="<?= $project['q1_spent'] ?>">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q2 Spent</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q2_spent" class="form-control" min="0" step="0.01" value="<?= $project['q2_spent'] ?>">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q3 Spent</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q3_spent" class="form-control" min="0" step="0.01" value="<?= $project['q3_spent'] ?>">
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Q4 Spent</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" name="q4_spent" class="form-control" min="0" step="0.01" value="<?= $project['q4_spent'] ?>">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="<?= url('/budget/projects') ?>" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
