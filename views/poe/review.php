<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">POE Review Queue</h4>
        <p class="text-muted mb-0">
            <?= $isIndependent ? 'Independent' : 'Manager' ?> review of Proof of Evidence attachments
        </p>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-select">
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                    <option value="<?= $q ?>" <?= $selectedQuarter == $q ? 'selected' : '' ?>>
                        <?= quarter_label($q) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- POE Items -->
<div class="row g-4">
    <?php if (empty($poeItems)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                <p>No POE items pending review</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($poeItems as $poe): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary"><?= e($poe['kpi_code']) ?></span>
                <span class="badge bg-secondary">Q<?= $poe['quarter'] ?></span>
            </div>
            <div class="card-body">
                <h6 class="card-title text-truncate" title="<?= e($poe['kpi_name']) ?>">
                    <?= e(substr($poe['kpi_name'], 0, 50)) ?>...
                </h6>

                <div class="d-flex align-items-center mb-3">
                    <?php
                    $icon = 'bi-file-earmark';
                    if (strpos($poe['file_type'], 'pdf') !== false) $icon = 'bi-file-earmark-pdf text-danger';
                    elseif (strpos($poe['file_type'], 'image') !== false) $icon = 'bi-file-earmark-image text-success';
                    elseif (strpos($poe['file_type'], 'word') !== false) $icon = 'bi-file-earmark-word text-primary';
                    elseif (strpos($poe['file_type'], 'excel') !== false || strpos($poe['file_type'], 'spreadsheet') !== false) $icon = 'bi-file-earmark-excel text-success';
                    ?>
                    <i class="bi <?= $icon ?> fs-2 me-3"></i>
                    <div>
                        <strong class="d-block"><?= e($poe['original_name']) ?></strong>
                        <small class="text-muted">
                            <?= round($poe['file_size'] / 1024) ?> KB |
                            Uploaded <?= format_date($poe['upload_date'], 'd M Y H:i') ?>
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Uploaded by:</small>
                    <br><?= e($poe['uploader_name'] . ' ' . $poe['uploader_surname']) ?>
                </div>

                <?php if ($poe['description']): ?>
                <p class="small text-muted mb-3"><?= e($poe['description']) ?></p>
                <?php endif; ?>

                <?= sla_badge($poe['sla_category']) ?>

                <hr>

                <div class="d-flex gap-2 mb-3">
                    <a href="/poe/<?= $poe['id'] ?>/download" class="btn btn-outline-primary btn-sm flex-grow-1">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    <a href="/poe/<?= $poe['id'] ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>

                <!-- Review Form -->
                <form method="POST" action="/poe/<?= $poe['id'] ?>/accept" id="accept-form-<?= $poe['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="reviewer_type" value="<?= $isIndependent ? 'independent' : 'manager' ?>">
                </form>
                <form method="POST" action="/poe/<?= $poe['id'] ?>/reject" id="reject-form-<?= $poe['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="reviewer_type" value="<?= $isIndependent ? 'independent' : 'manager' ?>">
                    <input type="hidden" name="feedback" id="feedback-<?= $poe['id'] ?>" value="">
                </form>

                <div class="d-flex gap-2">
                    <button type="submit" form="accept-form-<?= $poe['id'] ?>" class="btn btn-success flex-grow-1">
                        <i class="bi bi-check-lg me-1"></i>Accept
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-<?= $poe['id'] ?>">
                        <i class="bi bi-x-lg me-1"></i>Reject
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal-<?= $poe['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject POE</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Reason for Rejection</label>
                            <textarea class="form-control" id="reject-reason-<?= $poe['id'] ?>" rows="3"
                                      placeholder="Please provide feedback for the user..." required></textarea>
                        </div>
                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            User will be notified and given <?= POE_RESUBMISSION_DAYS ?> days to resubmit.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="submitReject(<?= $poe['id'] ?>)">
                            Confirm Rejection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function submitReject(poeId) {
    const feedback = document.getElementById('reject-reason-' + poeId).value;
    if (!feedback.trim()) {
        alert('Please provide a reason for rejection');
        return;
    }
    document.getElementById('feedback-' + poeId).value = feedback;
    document.getElementById('reject-form-' + poeId).submit();
}
</script>
