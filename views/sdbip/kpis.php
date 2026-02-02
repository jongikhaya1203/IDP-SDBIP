<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Key Performance Indicators</h4>
    <a href="/sdbip/kpis/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add KPI
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
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
            <div class="col-md-3">
                <label class="form-label">SLA Category</label>
                <select name="sla" class="form-select">
                    <option value="">All Categories</option>
                    <option value="budget" <?= $selectedSla === 'budget' ? 'selected' : '' ?>>Budget</option>
                    <option value="internal_control" <?= $selectedSla === 'internal_control' ? 'selected' : '' ?>>Internal Control</option>
                    <option value="hr_vacancy" <?= $selectedSla === 'hr_vacancy' ? 'selected' : '' ?>>HR Vacancy</option>
                    <option value="none" <?= $selectedSla === 'none' ? 'selected' : '' ?>>No Dependency</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search KPI..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KPIs Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>KPI Name</th>
                        <th>Directorate</th>
                        <th>Annual Target</th>
                        <th>SLA</th>
                        <th>Budget</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kpis)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No KPIs found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($kpis as $kpi): ?>
                    <tr>
                        <td>
                            <a href="/sdbip/kpis/<?= $kpi['id'] ?>" class="fw-bold text-primary">
                                <?= e($kpi['kpi_code']) ?>
                            </a>
                            <?php if ($kpi['is_strategic']): ?>
                            <span class="badge bg-warning text-dark ms-1" title="Strategic KPI">S</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="d-block text-truncate" style="max-width: 250px;" title="<?= e($kpi['kpi_name']) ?>">
                                <?= e($kpi['kpi_name']) ?>
                            </span>
                            <small class="text-muted"><?= e($kpi['objective_code']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark"><?= e($kpi['directorate_code']) ?></span>
                        </td>
                        <td>
                            <?= e($kpi['annual_target']) ?>
                            <small class="text-muted"><?= e($kpi['unit_of_measure']) ?></small>
                        </td>
                        <td><?= sla_badge($kpi['sla_category']) ?></td>
                        <td>
                            <?php if ($kpi['budget_allocated'] > 0): ?>
                            <small><?= format_currency($kpi['budget_allocated']) ?></small>
                            <?php else: ?>
                            <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/sdbip/kpis/<?= $kpi['id'] ?>" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/sdbip/kpis/<?= $kpi['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
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

<div class="mt-3 text-muted small">
    Showing <?= count($kpis) ?> KPIs | FY: <?= e($financialYear['year_label'] ?? '') ?>
</div>
