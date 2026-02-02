<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">SDBIP Overview</h4>
        <p class="text-muted mb-0">Service Delivery and Budget Implementation Plan</p>
    </div>
    <a href="/sdbip/kpis/create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Add KPI
    </a>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card primary">
            <div class="stat-value"><?= (int)($stats['total_objectives'] ?? 0) ?></div>
            <div class="stat-label">Strategic Objectives</div>
            <i class="bi bi-bullseye stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="stat-value"><?= (int)($stats['total_kpis'] ?? 0) ?></div>
            <div class="stat-label">Key Performance Indicators</div>
            <i class="bi bi-graph-up stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card info">
            <div class="stat-value"><?= format_currency($stats['total_budget'] ?? 0) ?></div>
            <div class="stat-label">Total Budget Allocated</div>
            <i class="bi bi-currency-dollar stat-icon"></i>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="/sdbip/objectives" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-diagram-3 fs-4 text-primary"></i>
                </div>
                <div>
                    <h6 class="mb-1">Strategic Objectives</h6>
                    <small class="text-muted">Manage IDP objectives</small>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/sdbip/kpis" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-speedometer2 fs-4 text-success"></i>
                </div>
                <div>
                    <h6 class="mb-1">KPI Management</h6>
                    <small class="text-muted">Define and manage KPIs</small>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/sdbip/targets" class="card text-decoration-none h-100">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-calendar3 fs-4 text-warning"></i>
                </div>
                <div>
                    <h6 class="mb-1">Quarterly Targets</h6>
                    <small class="text-muted">View targets and actuals</small>
                </div>
                <i class="bi bi-chevron-right ms-auto"></i>
            </div>
        </a>
    </div>
</div>

<!-- Objectives by Directorate -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-building me-2"></i>Objectives by Directorate
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Directorate</th>
                        <th class="text-center">Objectives</th>
                        <th class="text-center">KPIs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($objectives as $obj): ?>
                    <tr>
                        <td>
                            <strong><?= e($obj['directorate_code']) ?></strong>
                            <br><small class="text-muted"><?= e($obj['directorate_name']) ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= (int)$obj['objective_count'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><?= (int)$obj['kpi_count'] ?></span>
                        </td>
                        <td>
                            <a href="/sdbip/objectives?directorate=<?= $obj['directorate_code'] ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
