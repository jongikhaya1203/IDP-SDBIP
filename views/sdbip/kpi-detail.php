<?php
$pageTitle = $title ?? 'KPI Details';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/sdbip') ?>">SDBIP</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/sdbip/kpis') ?>">KPIs</a></li>
                <li class="breadcrumb-item active"><?= e($kpi['kpi_code'] ?? 'KPI') ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><?= e($kpi['kpi_name'] ?? 'KPI Details') ?></h1>
    </div>
    <div>
        <?php if (has_role('admin', 'director', 'manager')): ?>
        <a href="<?= url('/sdbip/kpis/' . $kpi['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('/sdbip/kpis') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- KPI Details Card -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">KPI Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th class="ps-0 w-40">Code:</th>
                        <td><span class="badge bg-primary"><?= e($kpi['kpi_code'] ?? '') ?></span></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Strategic Objective:</th>
                        <td><?= e($kpi['objective_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Directorate:</th>
                        <td><?= e($kpi['directorate_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Unit of Measure:</th>
                        <td><?= e($kpi['unit_of_measure'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">SLA Category:</th>
                        <td><?= sla_badge($kpi['sla_category'] ?? 'none') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Responsible:</th>
                        <td><?= e(($kpi['responsible_first_name'] ?? '') . ' ' . ($kpi['responsible_last_name'] ?? '')) ?: 'Not Assigned' ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Status:</th>
                        <td>
                            <?php if ($kpi['is_active'] ?? true): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <?php if (!empty($kpi['description'])): ?>
                <hr>
                <h6>Description</h6>
                <p class="text-muted"><?= nl2br(e($kpi['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Targets Card -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Targets</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-6">
                        <div class="border rounded p-3">
                            <div class="text-muted small">Baseline</div>
                            <div class="h4 mb-0"><?= e($kpi['baseline'] ?? '0') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 bg-primary bg-opacity-10">
                            <div class="text-muted small">Annual Target</div>
                            <div class="h4 mb-0 text-primary"><?= e($kpi['annual_target'] ?? '0') ?></div>
                        </div>
                    </div>
                </div>

                <h6>Quarterly Targets</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Q1</th>
                                <th>Q2</th>
                                <th>Q3</th>
                                <th>Q4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= e($kpi['q1_target'] ?? '-') ?></td>
                                <td><?= e($kpi['q2_target'] ?? '-') ?></td>
                                <td><?= e($kpi['q3_target'] ?? '-') ?></td>
                                <td><?= e($kpi['q4_target'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($kpi['budget_required'])): ?>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="text-muted small">Budget Required</div>
                        <div class="fw-bold"><?= format_currency($kpi['budget_required']) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Budget Allocated</div>
                        <div class="fw-bold"><?= format_currency($kpi['budget_allocated'] ?? 0) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quarterly Performance -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Quarterly Performance</h5>
    </div>
    <div class="card-body">
        <?php if (empty($actuals)): ?>
        <div class="text-center py-4">
            <i class="bi bi-graph-up text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2 mb-0">No quarterly data captured yet.</p>
            <a href="<?= url('/assessment/self') ?>" class="btn btn-primary mt-3">
                <i class="bi bi-clipboard-check me-1"></i> Submit Self Assessment
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Quarter</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Variance</th>
                        <th>Self Rating</th>
                        <th>Manager Rating</th>
                        <th>Independent Rating</th>
                        <th>Final</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actuals as $actual): ?>
                    <tr>
                        <td><?= quarter_label($actual['quarter']) ?></td>
                        <td><?= e($kpi['q' . $actual['quarter'] . '_target'] ?? '-') ?></td>
                        <td><?= e($actual['actual_value'] ?? '-') ?></td>
                        <td>
                            <?php
                            $variance = $actual['variance'] ?? 0;
                            $varClass = $variance >= 0 ? 'text-success' : 'text-danger';
                            $varSign = $variance >= 0 ? '+' : '';
                            ?>
                            <span class="<?= $varClass ?>"><?= $varSign . $variance ?></span>
                        </td>
                        <td>
                            <?php if (isset($actual['self_rating'])): ?>
                            <span class="badge bg-<?= rating_color($actual['self_rating']) ?>"><?= $actual['self_rating'] ?>/5</span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($actual['manager_rating'])): ?>
                            <span class="badge bg-<?= rating_color($actual['manager_rating']) ?>"><?= $actual['manager_rating'] ?>/5</span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($actual['independent_rating'])): ?>
                            <span class="badge bg-<?= rating_color($actual['independent_rating']) ?>"><?= $actual['independent_rating'] ?>/5</span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($actual['aggregated_rating'])): ?>
                            <span class="badge bg-<?= rating_color($actual['aggregated_rating']) ?>"><?= number_format($actual['aggregated_rating'], 2) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= achievement_badge($actual['achievement_status'] ?? 'pending') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- POE Section -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Proof of Evidence (POE)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($poeFiles)): ?>
        <div class="text-center py-4">
            <i class="bi bi-file-earmark text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2 mb-0">No evidence files uploaded yet.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Quarter</th>
                        <th>Uploaded</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($poeFiles as $poe): ?>
                    <tr>
                        <td>
                            <i class="bi bi-file-earmark-text me-1"></i>
                            <?= e($poe['file_name']) ?>
                        </td>
                        <td><?= quarter_label($poe['quarter'] ?? 0) ?></td>
                        <td><?= format_date($poe['created_at'] ?? '', 'd M Y') ?></td>
                        <td>
                            <?php
                            $status = $poe['manager_status'] ?? 'pending';
                            $statusBadge = [
                                'pending' => 'warning',
                                'accepted' => 'success',
                                'rejected' => 'danger'
                            ][$status] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $statusBadge ?>"><?= ucfirst($status) ?></span>
                        </td>
                        <td>
                            <a href="<?= url('/poe/' . $poe['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
