<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Quarterly Targets & Actuals</h4>
    <a href="/assessment/self" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-1"></i>Start Assessment
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-select">
                    <option value="1" <?= $selectedQuarter == 1 ? 'selected' : '' ?>>Q1 (Jul-Sep)</option>
                    <option value="2" <?= $selectedQuarter == 2 ? 'selected' : '' ?>>Q2 (Oct-Dec)</option>
                    <option value="3" <?= $selectedQuarter == 3 ? 'selected' : '' ?>>Q3 (Jan-Mar)</option>
                    <option value="4" <?= $selectedQuarter == 4 ? 'selected' : '' ?>>Q4 (Apr-Jun)</option>
                </select>
            </div>
            <div class="col-md-4">
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
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i>Apply Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Targets Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>KPI</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Variance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ratings</th>
                        <th class="text-center">Aggregated</th>
                        <th>Review Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targets)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No targets found for selected quarter
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($targets as $t): ?>
                    <tr>
                        <td>
                            <a href="/sdbip/kpis/<?= $t['kpi_id'] ?>" class="fw-bold text-primary">
                                <?= e($t['kpi_code']) ?>
                            </a>
                            <br><small class="text-muted"><?= e($t['directorate_code']) ?></small>
                            <?= sla_badge($t['sla_category']) ?>
                        </td>
                        <td class="text-center"><?= e($t['target_value'] ?? '-') ?></td>
                        <td class="text-center">
                            <?php if ($t['actual_value']): ?>
                            <strong><?= e($t['actual_value']) ?></strong>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($t['variance'] !== null): ?>
                            <span class="<?= $t['variance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $t['variance'] >= 0 ? '+' : '' ?><?= number_format($t['variance'], 1) ?>%
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= achievement_badge($t['achievement_status'] ?? 'pending') ?></td>
                        <td class="text-center">
                            <small>
                                S: <?= $t['self_rating'] ?? '-' ?> |
                                M: <?= $t['manager_rating'] ?? '-' ?> |
                                I: <?= $t['independent_rating'] ?? '-' ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <?php if ($t['aggregated_rating']): ?>
                            <span class="rating-value" style="color: <?= getRatingColor($t['aggregated_rating']) ?>">
                                <?= number_format($t['aggregated_rating'], 2) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusBadges = [
                                'draft' => 'secondary',
                                'submitted' => 'info',
                                'manager_review' => 'warning',
                                'independent_review' => 'primary',
                                'approved' => 'success',
                                'rejected' => 'danger'
                            ];
                            $badge = $statusBadges[$t['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badge ?>">
                                <?= ucwords(str_replace('_', ' ', $t['status'] ?? 'draft')) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function getRatingColor(rating) {
    if (rating >= 4) return '#10b981';
    if (rating >= 3) return '#f59e0b';
    if (rating >= 2) return '#f97316';
    return '#ef4444';
}
</script>
