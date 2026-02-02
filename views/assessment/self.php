<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Self Assessment</h4>
        <p class="text-muted mb-0">Submit your quarterly performance actuals and self-rating</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-select">
                    <?php for ($q = 1; $q <= 4; $q++): ?>
                    <option value="<?= $q ?>" <?= $selectedQuarter == $q ? 'selected' : '' ?>>
                        <?= quarter_label($q) ?> <?= $q == $currentQuarter ? '(Current)' : '' ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" <?= $selectedStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="submitted" <?= $selectedStatus === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    <option value="rejected" <?= $selectedStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Assessments -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>KPI</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Self Rating</th>
                        <th>Status</th>
                        <th>POE</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($assessments)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No KPIs found for assessment
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($assessments as $a): ?>
                    <tr id="row-<?= $a['quarterly_id'] ?>">
                        <td>
                            <strong class="text-primary"><?= e($a['kpi_code']) ?></strong>
                            <br><small class="text-muted"><?= e(substr($a['kpi_name'], 0, 50)) ?>...</small>
                            <br><?= sla_badge($a['sla_category']) ?>
                        </td>
                        <td><?= e($a['target_value'] ?? '-') ?></td>
                        <td>
                            <?php if ($a['status'] === 'draft'): ?>
                            <input type="text" class="form-control form-control-sm actual-input"
                                   data-id="<?= $a['quarterly_id'] ?>"
                                   value="<?= e($a['actual_value'] ?? '') ?>"
                                   placeholder="Enter actual">
                            <?php else: ?>
                            <strong><?= e($a['actual_value'] ?? '-') ?></strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($a['status'] === 'draft'): ?>
                            <select class="form-select form-select-sm rating-select" data-id="<?= $a['quarterly_id'] ?>">
                                <option value="">Select</option>
                                <?php for ($r = 1; $r <= 5; $r++): ?>
                                <option value="<?= $r ?>" <?= $a['self_rating'] == $r ? 'selected' : '' ?>><?= $r ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php else: ?>
                            <span class="badge bg-<?= rating_color($a['self_rating'] ?? 0) ?>"><?= $a['self_rating'] ?? '-' ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusBadges = [
                                'draft' => 'secondary',
                                'submitted' => 'info',
                                'rejected' => 'danger'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusBadges[$a['status']] ?? 'secondary' ?>">
                                <?= ucfirst($a['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/poe/upload/<?= $a['quarterly_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-paperclip"></i>
                                <?= (int)$a['poe_count'] ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($a['status'] === 'draft'): ?>
                            <button class="btn btn-sm btn-success submit-btn" data-id="<?= $a['quarterly_id'] ?>">
                                <i class="bi bi-check-lg"></i> Submit
                            </button>
                            <?php elseif ($a['status'] === 'rejected'): ?>
                            <button class="btn btn-sm btn-warning resubmit-btn" data-id="<?= $a['quarterly_id'] ?>">
                                <i class="bi bi-arrow-repeat"></i> Resubmit
                            </button>
                            <?php else: ?>
                            <span class="text-muted small">Submitted</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="submitForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Submit Self Assessment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Actual Value</label>
                        <input type="text" name="actual_value" id="modalActual" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Self Rating (1-5)</label>
                        <select name="self_rating" id="modalRating" class="form-select" required>
                            <option value="">Select Rating</option>
                            <?php foreach (RATING_DESCRIPTIONS as $val => $desc): ?>
                            <option value="<?= $val ?>"><?= $val ?> - <?= $desc ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments / Justification</label>
                        <textarea name="self_comments" id="modalComments" class="form-control" rows="3"
                                  placeholder="Provide justification for your rating..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Assessment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('submitModal'));

    document.querySelectorAll('.submit-btn, .resubmit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const row = document.getElementById('row-' + id);
            const actual = row.querySelector('.actual-input')?.value || '';
            const rating = row.querySelector('.rating-select')?.value || '';

            document.getElementById('submitForm').action = '/assessment/self/' + id;
            document.getElementById('modalActual').value = actual;
            document.getElementById('modalRating').value = rating;
            document.getElementById('modalComments').value = '';

            modal.show();
        });
    });
});
</script>
