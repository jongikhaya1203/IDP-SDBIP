<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- POE Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <?php
                            $icon = 'bi-file-earmark';
                            $iconColor = 'text-secondary';
                            if (strpos($poe['file_type'], 'pdf') !== false) {
                                $icon = 'bi-file-earmark-pdf';
                                $iconColor = 'text-danger';
                            } elseif (strpos($poe['file_type'], 'image') !== false) {
                                $icon = 'bi-file-earmark-image';
                                $iconColor = 'text-success';
                            } elseif (strpos($poe['file_type'], 'word') !== false || strpos($poe['file_type'], 'document') !== false) {
                                $icon = 'bi-file-earmark-word';
                                $iconColor = 'text-primary';
                            } elseif (strpos($poe['file_type'], 'excel') !== false || strpos($poe['file_type'], 'spreadsheet') !== false) {
                                $icon = 'bi-file-earmark-excel';
                                $iconColor = 'text-success';
                            }
                            ?>
                            <i class="bi <?= $icon ?> <?= $iconColor ?>" style="font-size: 4rem;"></i>
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= htmlspecialchars($poe['original_name']) ?></h4>
                            <p class="text-muted mb-0">
                                <strong><?= htmlspecialchars($poe['kpi_code']) ?></strong> - <?= htmlspecialchars($poe['kpi_name']) ?>
                                <span class="badge bg-secondary ms-2">Q<?= $poe['quarter'] ?></span>
                            </p>
                            <p class="text-muted mb-0 mt-1">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($poe['directorate_name']) ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <a href="/poe/download/<?= $poe['id'] ?>" class="btn btn-primary">
                                <i class="bi bi-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- File Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>File Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">File Type</td>
                                    <td><?= htmlspecialchars($poe['file_type']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">File Size</td>
                                    <td><?= number_format($poe['file_size'] / 1024, 2) ?> KB</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Uploaded By</td>
                                    <td><?= htmlspecialchars($poe['uploader_first'] . ' ' . $poe['uploader_last']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Upload Date</td>
                                    <td><?= date('d M Y H:i', strtotime($poe['upload_date'])) ?></td>
                                </tr>
                                <?php if (!empty($poe['description'])): ?>
                                <tr>
                                    <td class="text-muted">Description</td>
                                    <td><?= htmlspecialchars($poe['description']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Review Status -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Review Status</h5>
                        </div>
                        <div class="card-body">
                            <!-- Manager Review -->
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Manager Review</h6>
                                <?php
                                $mgrBadge = [
                                    'pending' => 'bg-secondary',
                                    'accepted' => 'bg-success',
                                    'rejected' => 'bg-danger'
                                ];
                                ?>
                                <span class="badge <?= $mgrBadge[$poe['manager_status']] ?? 'bg-secondary' ?> fs-6">
                                    <?= ucfirst($poe['manager_status']) ?>
                                </span>
                                <?php if ($poe['manager_reviewer_first']): ?>
                                <p class="mt-2 mb-1 small">
                                    <strong>Reviewed by:</strong> <?= htmlspecialchars($poe['manager_reviewer_first'] . ' ' . $poe['manager_reviewer_last']) ?>
                                </p>
                                <?php endif; ?>
                                <?php if (!empty($poe['manager_feedback'])): ?>
                                <p class="text-muted small mb-0">
                                    <strong>Feedback:</strong> <?= htmlspecialchars($poe['manager_feedback']) ?>
                                </p>
                                <?php endif; ?>
                            </div>

                            <!-- Independent Review -->
                            <div>
                                <h6 class="text-muted mb-2">Independent Review</h6>
                                <span class="badge <?= $mgrBadge[$poe['independent_status']] ?? 'bg-secondary' ?> fs-6">
                                    <?= ucfirst($poe['independent_status']) ?>
                                </span>
                                <?php if ($poe['independent_reviewer_first']): ?>
                                <p class="mt-2 mb-1 small">
                                    <strong>Reviewed by:</strong> <?= htmlspecialchars($poe['independent_reviewer_first'] . ' ' . $poe['independent_reviewer_last']) ?>
                                </p>
                                <?php endif; ?>
                                <?php if (!empty($poe['independent_feedback'])): ?>
                                <p class="text-muted small mb-0">
                                    <strong>Feedback:</strong> <?= htmlspecialchars($poe['independent_feedback']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resubmission Notice -->
            <?php if (!empty($poe['resubmission_required']) && $poe['resubmission_required']): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Resubmission Required</strong>
                <?php if (!empty($poe['resubmission_deadline'])): ?>
                <br>Deadline: <?= date('d M Y', strtotime($poe['resubmission_deadline'])) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="d-flex justify-content-between">
                <a href="/poe" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to List
                </a>
                <?php if (has_role('admin', 'manager', 'independent_assessor')): ?>
                <a href="/poe/review" class="btn btn-primary">
                    <i class="bi bi-clipboard-check me-1"></i>Go to Review Queue
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
