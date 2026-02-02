<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Strategic Objectives</h4>
    <?php if (has_role('admin', 'director')): ?>
    <a href="/idp/objectives/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add Objective
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
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
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search objectives..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Objectives List -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Objective</th>
                        <th>Directorate</th>
                        <th>National Priority</th>
                        <th class="text-center">KPIs</th>
                        <th class="text-center">Weight</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($objectives)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No strategic objectives found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($objectives as $obj): ?>
                    <tr>
                        <td>
                            <strong class="text-primary"><?= e($obj['objective_code']) ?></strong>
                        </td>
                        <td>
                            <span class="d-block" style="max-width: 300px;"><?= e($obj['objective_name']) ?></span>
                            <?php if ($obj['description']): ?>
                            <small class="text-muted"><?= e(substr($obj['description'], 0, 80)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark"><?= e($obj['directorate_code']) ?></span>
                        </td>
                        <td>
                            <?php if ($obj['national_priority_alignment']): ?>
                            <small><?= e($obj['national_priority_alignment']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= (int)$obj['kpi_count'] ?></span>
                        </td>
                        <td class="text-center">
                            <?= number_format($obj['weight'], 1) ?>%
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/sdbip/kpis?objective=<?= $obj['id'] ?>" class="btn btn-outline-primary" title="View KPIs">
                                    <i class="bi bi-list-check"></i>
                                </a>
                                <?php if (has_role('admin', 'director')): ?>
                                <a href="/idp/objectives/<?= $obj['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
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

<div class="mt-3 text-muted small">
    Showing <?= count($objectives) ?> objectives | FY: <?= e($financialYear['year_label'] ?? '') ?>
</div>
