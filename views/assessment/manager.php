<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Manager Review</h4>
        <p class="text-muted mb-0">Review and rate employee self-assessments</p>
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
                        <?= quarter_label($q) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Directorate</label>
                <select name="directorate" class="form-select">
                    <option value="">All Directorates</option>
                    <?php foreach ($directorates as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $selectedDirectorate == $d['id'] ? 'selected' : '' ?>>
                        <?= e($d['code']) ?> - <?= e($d['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Pending Reviews -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-clipboard-check me-2"></i>Pending Manager Reviews
        <span class="badge bg-warning text-dark ms-2"><?= count($assessments) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($assessments)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
            <p>No pending reviews for the selected quarter</p>
        </div>
        <?php else: ?>
        <div class="accordion" id="reviewAccordion">
            <?php foreach ($assessments as $i => $a): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                            data-bs-toggle="collapse" data-bs-target="#review-<?= $a['quarterly_id'] ?>">
                        <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                            <div>
                                <strong class="text-primary"><?= e($a['kpi_code']) ?></strong>
                                <span class="text-muted mx-2">|</span>
                                <small><?= e(substr($a['kpi_name'], 0, 60)) ?>...</small>
                            </div>
                            <div class="d-flex gap-2">
                                <?= sla_badge($a['sla_category']) ?>
                                <span class="badge bg-<?= $a['pending_poe'] > 0 ? 'warning' : 'success' ?>">
                                    <?= $a['poe_count'] ?> POE
                                </span>
                                <span class="badge bg-info">Self: <?= $a['self_rating'] ?? '-' ?></span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="review-<?= $a['quarterly_id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Performance Data</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Target:</th>
                                        <td><?= e($a['target_value'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Actual:</th>
                                        <td><strong><?= e($a['actual_value'] ?? '-') ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Variance:</th>
                                        <td class="<?= ($a['variance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= $a['variance'] !== null ? number_format($a['variance'], 1) . '%' : '-' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Achievement:</th>
                                        <td><?= achievement_badge($a['achievement_status'] ?? 'pending') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Submitted by:</th>
                                        <td><?= e($a['submitter_name'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>

                                <h6 class="mt-3">Self Assessment</h6>
                                <div class="border rounded p-3 bg-light">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Rating:</span>
                                        <span class="badge bg-<?= rating_color($a['self_rating'] ?? 0) ?> fs-6">
                                            <?= $a['self_rating'] ?? '-' ?> / 5
                                        </span>
                                    </div>
                                    <p class="mb-0 small"><?= e($a['self_comments'] ?? 'No comments provided') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Manager Review</h6>
                                <form method="POST" action="/assessment/manager/<?= $a['quarterly_id'] ?>">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label class="form-label">Manager Rating</label>
                                        <select name="manager_rating" class="form-select" required>
                                            <option value="">Select Rating</option>
                                            <?php foreach (RATING_DESCRIPTIONS as $val => $desc): ?>
                                            <option value="<?= $val ?>" <?= ($a['manager_rating'] ?? '') == $val ? 'selected' : '' ?>>
                                                <?= $val ?> - <?= $desc ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Comments</label>
                                        <textarea name="manager_comments" class="form-control" rows="3"
                                                  placeholder="Provide feedback..."><?= e($a['manager_comments'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" name="action" value="approve" class="btn btn-success flex-grow-1">
                                            <i class="bi bi-check-lg me-1"></i>Approve & Forward
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                                            <i class="bi bi-x-lg me-1"></i>Reject
                                        </button>
                                    </div>
                                </form>

                                <hr>
                                <a href="/poe?quarterly=<?= $a['quarterly_id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-paperclip me-1"></i>View POE (<?= $a['poe_count'] ?>)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
