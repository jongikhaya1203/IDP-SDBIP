<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Upload Proof of Evidence</h4>
        <p class="text-muted mb-0">
            <strong><?= e($quarterly['kpi_code']) ?></strong> - Q<?= $quarterly['quarter'] ?>
        </p>
    </div>
    <a href="/assessment/self" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Assessment
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- KPI Info -->
        <div class="card mb-4">
            <div class="card-header">KPI Information</div>
            <div class="card-body">
                <h5><?= e($quarterly['kpi_name']) ?></h5>
                <p class="text-muted"><?= e($quarterly['directorate_name']) ?></p>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-cloud-upload me-2"></i>Upload New Evidence
            </div>
            <div class="card-body">
                <form method="POST" action="/poe/upload/<?= $quarterly['id'] ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="poe_file" class="form-control" required
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                        <div class="form-text">
                            Allowed types: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF
                            <br>Maximum size: <?= UPLOAD_MAX_SIZE / 1048576 ?>MB
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Brief description of this evidence..."></textarea>
                    </div>

                    <?php if (!empty($existingPoe)): ?>
                    <?php
                    $rejectedPoe = array_filter($existingPoe, fn($p) => $p['manager_status'] === 'rejected' || $p['independent_status'] === 'rejected');
                    if (!empty($rejectedPoe)):
                        $lastRejected = reset($rejectedPoe);
                    ?>
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle me-1"></i>Resubmission Required</h6>
                        <p class="mb-2">Previous POE was rejected. Feedback:</p>
                        <blockquote class="mb-2">
                            <?= e($lastRejected['manager_feedback'] ?: $lastRejected['independent_feedback']) ?>
                        </blockquote>
                        <input type="hidden" name="parent_poe_id" value="<?= $lastRejected['id'] ?>">
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Upload Evidence
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Existing POE -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-files me-2"></i>Uploaded Evidence
                <span class="badge bg-primary"><?= count($existingPoe) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($existingPoe)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                    <p>No evidence uploaded yet</p>
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($existingPoe as $poe): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <a href="/poe/<?= $poe['id'] ?>/download" class="text-decoration-none">
                                    <i class="bi bi-file-earmark me-1"></i>
                                    <?= e($poe['original_name']) ?>
                                </a>
                                <br>
                                <small class="text-muted">
                                    v<?= $poe['version'] ?> | <?= format_date($poe['upload_date'], 'd M Y') ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <?php if ($poe['manager_status'] === 'accepted'): ?>
                                <span class="badge bg-success">Manager: Accepted</span>
                                <?php elseif ($poe['manager_status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Manager: Rejected</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Manager: Pending</span>
                                <?php endif; ?>
                                <br>
                                <?php if ($poe['independent_status'] === 'accepted'): ?>
                                <span class="badge bg-success">Independent: Accepted</span>
                                <?php elseif ($poe['independent_status'] === 'rejected'): ?>
                                <span class="badge bg-danger">Independent: Rejected</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Independent: Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
