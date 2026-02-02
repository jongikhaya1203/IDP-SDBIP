<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Independent Assessment</h4>
        <p class="text-muted mb-0">Perform independent verification and final rating</p>
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

<!-- Rating Weights Info -->
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <span>
        <strong>Aggregated Rating Formula:</strong>
        Self (<?= (RATING_SELF_WEIGHT * 100) ?>%) +
        Manager (<?= (RATING_MANAGER_WEIGHT * 100) ?>%) +
        Independent (<?= (RATING_INDEPENDENT_WEIGHT * 100) ?>%)
    </span>
</div>

<!-- Assessments -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-shield-check me-2"></i>Pending Independent Reviews
        <span class="badge bg-primary ms-2"><?= count($assessments) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($assessments)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
            <p>No assessments pending independent review</p>
        </div>
        <?php else: ?>
        <?php foreach ($assessments as $a): ?>
        <div class="border-bottom p-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">
                                <span class="text-primary"><?= e($a['kpi_code']) ?></span>
                                <?= sla_badge($a['sla_category']) ?>
                            </h5>
                            <p class="mb-0"><?= e($a['kpi_name']) ?></p>
                            <small class="text-muted"><?= e($a['directorate_name']) ?></small>
                        </div>
                        <?= achievement_badge($a['achievement_status'] ?? 'pending') ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">Target</small>
                                <strong><?= e($a['target_value'] ?? '-') ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">Actual</small>
                                <strong><?= e($a['actual_value'] ?? '-') ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-muted d-block">Variance</small>
                                <strong class="<?= ($a['variance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $a['variance'] !== null ? number_format($a['variance'], 1) . '%' : '-' ?>
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Comparison -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3">
                                <h6><i class="bi bi-person me-1"></i>Self Assessment</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Rating:</span>
                                    <span class="badge bg-<?= rating_color($a['self_rating'] ?? 0) ?> fs-6">
                                        <?= $a['self_rating'] ?? '-' ?>
                                    </span>
                                </div>
                                <p class="small mb-0 text-muted"><?= e($a['self_comments'] ?? 'No comments') ?></p>
                                <small class="text-muted">By: <?= e($a['submitter_name'] ?? 'N/A') ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light rounded p-3">
                                <h6><i class="bi bi-person-badge me-1"></i>Manager Assessment</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Rating:</span>
                                    <span class="badge bg-<?= rating_color($a['manager_rating'] ?? 0) ?> fs-6">
                                        <?= $a['manager_rating'] ?? '-' ?>
                                    </span>
                                </div>
                                <p class="small mb-0 text-muted"><?= e($a['manager_comments'] ?? 'No comments') ?></p>
                                <small class="text-muted">By: <?= e($a['manager_name'] ?? 'N/A') ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-primary bg-opacity-10 rounded p-3">
                        <h6><i class="bi bi-shield-check me-1"></i>Independent Assessment</h6>
                        <form method="POST" action="/assessment/independent/<?= $a['quarterly_id'] ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Your Rating</label>
                                <select name="independent_rating" class="form-select" required>
                                    <option value="">Select Rating</option>
                                    <?php for ($r = 1; $r <= 5; $r++): ?>
                                    <option value="<?= $r ?>"><?= $r ?> - <?= RATING_DESCRIPTIONS[$r] ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assessment Comments</label>
                                <textarea name="independent_comments" class="form-control" rows="3"
                                          placeholder="Provide your independent assessment..."></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="approve" class="btn btn-success flex-grow-1">
                                    <i class="bi bi-check-lg me-1"></i>Approve
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-outline-danger">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </form>

                        <hr>
                        <a href="/poe?quarterly=<?= $a['quarterly_id'] ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-paperclip me-1"></i>Review POE (<?= $a['poe_count'] ?>)
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
