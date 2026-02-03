<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <!-- Assessment Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-1">
                                <span class="badge bg-primary me-2"><?= htmlspecialchars($assessment['kpi_code']) ?></span>
                                <?= htmlspecialchars($assessment['kpi_name']) ?>
                            </h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars($assessment['objective_name']) ?>
                            </p>
                            <p class="text-muted mb-0 mt-1">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($assessment['directorate_name']) ?>
                                <span class="badge bg-secondary ms-2">Q<?= $assessment['quarter'] ?></span>
                            </p>
                        </div>
                        <div class="col-auto text-end">
                            <?php
                            $statusColors = [
                                'draft' => 'secondary',
                                'submitted' => 'info',
                                'manager_review' => 'warning',
                                'independent_review' => 'primary',
                                'approved' => 'success',
                                'rejected' => 'danger'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$assessment['status']] ?? 'secondary' ?> fs-5">
                                <?= ucfirst(str_replace('_', ' ', $assessment['status'])) ?>
                            </span>
                            <?php if ($assessment['aggregated_rating']): ?>
                            <div class="mt-2">
                                <span class="text-muted">Aggregated Rating:</span>
                                <span class="badge bg-primary fs-5"><?= number_format($assessment['aggregated_rating'], 2) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- KPI Details -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>KPI Details</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Unit of Measure</td>
                                    <td><?= htmlspecialchars($assessment['unit_of_measure'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">SLA Category</td>
                                    <td>
                                        <?php
                                        $slaBadges = [
                                            'budget' => 'primary',
                                            'internal_control' => 'info',
                                            'hr_vacancy' => 'warning',
                                            'none' => 'secondary'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $slaBadges[$assessment['sla_category']] ?? 'secondary' ?>">
                                            <?= ucfirst(str_replace('_', ' ', $assessment['sla_category'] ?? 'None')) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Target</td>
                                    <td><strong><?= htmlspecialchars($assessment['target_value'] ?? 'N/A') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Actual</td>
                                    <td><strong><?= htmlspecialchars($assessment['actual_value'] ?? 'N/A') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Variance</td>
                                    <td>
                                        <?php
                                        $variance = $assessment['variance'] ?? 0;
                                        $varianceClass = $variance >= 0 ? 'text-success' : 'text-danger';
                                        ?>
                                        <span class="<?= $varianceClass ?> fw-bold">
                                            <?= $variance >= 0 ? '+' : '' ?><?= number_format($variance, 2) ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php if (!empty($assessment['data_source'])): ?>
                                <tr>
                                    <td class="text-muted">Data Source</td>
                                    <td><?= htmlspecialchars($assessment['data_source']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                            <?php if (!empty($assessment['description'])): ?>
                            <hr>
                            <p class="text-muted mb-0"><strong>Description:</strong></p>
                            <p><?= htmlspecialchars($assessment['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Ratings Summary -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-star me-2"></i>Rating Summary</h5>
                        </div>
                        <div class="card-body">
                            <!-- Self Assessment -->
                            <div class="mb-4 p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-person me-1"></i>Self Assessment (20%)
                                </h6>
                                <?php if ($assessment['self_rating']): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="progress flex-grow-1 me-3" style="height: 25px;">
                                        <div class="progress-bar bg-info" role="progressbar"
                                             style="width: <?= ($assessment['self_rating'] / 5) * 100 ?>%">
                                            <?= $assessment['self_rating'] ?>/5
                                        </div>
                                    </div>
                                </div>
                                <?php if ($assessment['submitter_first']): ?>
                                <small class="text-muted">By: <?= htmlspecialchars($assessment['submitter_first'] . ' ' . $assessment['submitter_last']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($assessment['self_comments'])): ?>
                                <p class="mt-2 mb-0 small"><?= htmlspecialchars($assessment['self_comments']) ?></p>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">Not submitted</span>
                                <?php endif; ?>
                            </div>

                            <!-- Manager Assessment -->
                            <div class="mb-4 p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-person-badge me-1"></i>Manager Assessment (40%)
                                </h6>
                                <?php if ($assessment['manager_rating']): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="progress flex-grow-1 me-3" style="height: 25px;">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                             style="width: <?= ($assessment['manager_rating'] / 5) * 100 ?>%">
                                            <?= $assessment['manager_rating'] ?>/5
                                        </div>
                                    </div>
                                </div>
                                <?php if ($assessment['manager_first']): ?>
                                <small class="text-muted">By: <?= htmlspecialchars($assessment['manager_first'] . ' ' . $assessment['manager_last']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($assessment['manager_comments'])): ?>
                                <p class="mt-2 mb-0 small"><?= htmlspecialchars($assessment['manager_comments']) ?></p>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">Not reviewed</span>
                                <?php endif; ?>
                            </div>

                            <!-- Independent Assessment -->
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-2">
                                    <i class="bi bi-shield-check me-1"></i>Independent Assessment (40%)
                                </h6>
                                <?php if ($assessment['independent_rating']): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="progress flex-grow-1 me-3" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: <?= ($assessment['independent_rating'] / 5) * 100 ?>%">
                                            <?= $assessment['independent_rating'] ?>/5
                                        </div>
                                    </div>
                                </div>
                                <?php if ($assessment['independent_first']): ?>
                                <small class="text-muted">By: <?= htmlspecialchars($assessment['independent_first'] . ' ' . $assessment['independent_last']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($assessment['independent_comments'])): ?>
                                <p class="mt-2 mb-0 small"><?= htmlspecialchars($assessment['independent_comments']) ?></p>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted">Not reviewed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proof of Evidence -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Proof of Evidence</h5>
                    <a href="/poe/upload/<?= $assessment['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-upload me-1"></i>Upload POE
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($poeItems)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                        No evidence uploaded yet
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Manager</th>
                                    <th>Independent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($poeItems as $poe): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $icon = 'bi-file-earmark';
                                        if (strpos($poe['file_type'] ?? '', 'pdf') !== false) $icon = 'bi-file-earmark-pdf text-danger';
                                        elseif (strpos($poe['file_type'] ?? '', 'image') !== false) $icon = 'bi-file-earmark-image text-success';
                                        ?>
                                        <i class="bi <?= $icon ?> me-1"></i>
                                        <?= htmlspecialchars($poe['original_name'] ?? $poe['file_name'] ?? 'Unknown') ?>
                                    </td>
                                    <td><?= htmlspecialchars($poe['uploader_name'] ?? 'Unknown') ?></td>
                                    <td><?= date('d M Y', strtotime($poe['upload_date'])) ?></td>
                                    <td>
                                        <?php
                                        $mgrBadge = ['pending' => 'secondary', 'accepted' => 'success', 'rejected' => 'danger'];
                                        ?>
                                        <span class="badge bg-<?= $mgrBadge[$poe['manager_status']] ?? 'secondary' ?>">
                                            <?= ucfirst($poe['manager_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $mgrBadge[$poe['independent_status']] ?? 'secondary' ?>">
                                            <?= ucfirst($poe['independent_status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/poe/<?= $poe['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/poe/download/<?= $poe['id'] ?>" class="btn btn-sm btn-outline-primary">
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

            <!-- Actions -->
            <div class="d-flex justify-content-between">
                <a href="/assessment/self" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Assessments
                </a>
                <div>
                    <?php if ($assessment['status'] === 'draft'): ?>
                    <a href="/assessment/self" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Continue Self Assessment
                    </a>
                    <?php elseif ($assessment['status'] === 'submitted' && has_role('manager', 'admin')): ?>
                    <a href="/assessment/manager" class="btn btn-warning">
                        <i class="bi bi-clipboard-check me-1"></i>Manager Review
                    </a>
                    <?php elseif ($assessment['status'] === 'manager_review' && has_role('independent_assessor', 'admin')): ?>
                    <a href="/assessment/independent" class="btn btn-success">
                        <i class="bi bi-shield-check me-1"></i>Independent Review
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
