<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Proof of Evidence</h4>
        <p class="text-muted mb-0">Manage evidence attachments for KPI assessments</p>
    </div>
    <?php if (has_role('admin', 'manager', 'independent_assessor')): ?>
    <a href="/poe/review" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-1"></i>Review Queue
    </a>
    <?php endif; ?>
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
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="accepted" <?= $selectedStatus === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="rejected" <?= $selectedStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- POE Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>KPI</th>
                        <th>File</th>
                        <th>Uploaded</th>
                        <th>Manager Status</th>
                        <th>Independent Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($poeItems)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-folder2-open fs-1 d-block mb-2"></i>
                            No POE items found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($poeItems as $poe): ?>
                    <tr>
                        <td>
                            <strong class="text-primary"><?= e($poe['kpi_code']) ?></strong>
                            <br><small class="text-muted">Q<?= $poe['quarter'] ?></small>
                        </td>
                        <td>
                            <?php
                            $icon = 'bi-file-earmark';
                            if (strpos($poe['file_type'], 'pdf') !== false) $icon = 'bi-file-earmark-pdf text-danger';
                            elseif (strpos($poe['file_type'], 'image') !== false) $icon = 'bi-file-earmark-image text-success';
                            elseif (strpos($poe['file_type'], 'word') !== false) $icon = 'bi-file-earmark-word text-primary';
                            ?>
                            <i class="bi <?= $icon ?> me-1"></i>
                            <?= e($poe['original_name']) ?>
                            <br><small class="text-muted"><?= round($poe['file_size'] / 1024) ?> KB</small>
                        </td>
                        <td>
                            <?= format_date($poe['upload_date'], 'd M Y') ?>
                            <br><small class="text-muted"><?= e($poe['uploader_name'] . ' ' . $poe['uploader_surname']) ?></small>
                        </td>
                        <td>
                            <?php
                            $mgrBadge = ['pending' => 'secondary', 'accepted' => 'success', 'rejected' => 'danger'];
                            ?>
                            <span class="badge bg-<?= $mgrBadge[$poe['manager_status']] ?? 'secondary' ?>">
                                <?= ucfirst($poe['manager_status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $mgrBadge[$poe['independent_status']] ?? 'secondary' ?>">
                                <?= ucfirst($poe['independent_status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/poe/<?= $poe['id'] ?>/download" class="btn btn-outline-primary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="/poe/<?= $poe['id'] ?>" class="btn btn-outline-secondary" title="Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
