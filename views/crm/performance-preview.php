<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-graph-up me-2"></i>Performance Preview</h4>
        <p class="text-muted mb-0">Directorate performance status prior to POE assessment</p>
    </div>
    <a href="<?= url('/crm') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to CRM
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Directorate Info -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-building me-2"></i><?= e($directorate['name']) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Director:</strong> <?= e($directorate['director_name'] ?? 'Not assigned') ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?= e($directorate['email'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Financial Year:</strong> <?= e($financialYear['name']) ?></p>
                        <p class="mb-1"><strong>Quarter:</strong> Q<?= $quarter ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Performance Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>KPI Performance Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="mb-0 text-primary"><?= $performance['total_kpis'] ?></h3>
                            <small class="text-muted">Total KPIs</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="mb-0 text-success"><?= $performance['submitted'] ?></h3>
                            <small class="text-muted">Submitted</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="mb-0 text-warning"><?= $performance['pending'] ?></h3>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="mb-0 <?= $performance['submission_rate'] >= 80 ? 'text-success' : ($performance['submission_rate'] >= 50 ? 'text-warning' : 'text-danger') ?>">
                                <?= $performance['submission_rate'] ?>%
                            </h3>
                            <small class="text-muted">Submission Rate</small>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Overall Progress</span>
                        <span><?= $performance['submission_rate'] ?>%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?= $performance['submission_rate'] ?>%">
                            <?= $performance['submitted'] ?> Submitted
                        </div>
                        <div class="progress-bar bg-warning" style="width: <?= 100 - $performance['submission_rate'] ?>%">
                            <?= $performance['pending'] ?> Pending
                        </div>
                    </div>
                </div>

                <!-- Criticality Badge -->
                <div class="alert <?= $performance['criticality'] === 'high' ? 'alert-danger' : ($performance['criticality'] === 'medium' ? 'alert-warning' : 'alert-success') ?>">
                    <i class="bi <?= $performance['criticality'] === 'high' ? 'bi-exclamation-triangle' : ($performance['criticality'] === 'medium' ? 'bi-exclamation-circle' : 'bi-check-circle') ?> me-2"></i>
                    <strong>Criticality Level:</strong>
                    <?= ucfirst($performance['criticality']) ?>
                    <?php if ($performance['criticality'] === 'high'): ?>
                        - Immediate attention required
                    <?php elseif ($performance['criticality'] === 'medium'): ?>
                        - Management attention recommended
                    <?php else: ?>
                        - On track for submission
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SLA Category Breakdown -->
        <?php if (!empty($performance['by_sla_category'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Performance by SLA Category</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>SLA Category</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Submitted</th>
                                <th class="text-center">Pending</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($performance['by_sla_category'] as $category): ?>
                                <tr>
                                    <td><?= e($category['name']) ?></td>
                                    <td class="text-center"><?= $category['total'] ?></td>
                                    <td class="text-center text-success"><?= $category['submitted'] ?></td>
                                    <td class="text-center text-warning"><?= $category['pending'] ?></td>
                                    <td style="width: 200px;">
                                        <?php $catRate = $category['total'] > 0 ? round(($category['submitted'] / $category['total']) * 100) : 0; ?>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?= $catRate >= 80 ? 'bg-success' : ($catRate >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                                 style="width: <?= $catRate ?>%">
                                                <?= $catRate ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending KPIs List -->
        <?php if (!empty($pendingKpis)): ?>
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Pending KPIs (<?= count($pendingKpis) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>KPI</th>
                            <th>Strategic Objective</th>
                            <th>Target</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pendingKpis, 0, 20) as $kpi): ?>
                            <tr>
                                <td>
                                    <strong><?= e($kpi['name']) ?></strong>
                                    <br><small class="text-muted"><?= e($kpi['code'] ?? '') ?></small>
                                </td>
                                <td><small><?= e($kpi['strategic_objective'] ?? 'N/A') ?></small></td>
                                <td><?= e($kpi['target'] ?? '-') ?></td>
                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($pendingKpis) > 20): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    ... and <?= count($pendingKpis) - 20 ?> more pending KPIs
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Deadline Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Deadline Status</h5>
            </div>
            <div class="card-body">
                <?php
                $deadline = $slaConfig['submission_deadline_day'] ?? 15;
                $quarterEndMonths = [3 => 10, 6 => 1, 9 => 4, 12 => 7]; // Mapping based on quarter
                $quarterMonth = match($quarter) {
                    1 => 10, // Q1 (Jul-Sep) -> Oct deadline
                    2 => 1,  // Q2 (Oct-Dec) -> Jan deadline
                    3 => 4,  // Q3 (Jan-Mar) -> Apr deadline
                    4 => 7,  // Q4 (Apr-Jun) -> Jul deadline
                    default => 10
                };
                $deadlineYear = $quarter == 2 ? date('Y') + 1 : date('Y');
                $deadlineDate = new DateTime("$deadlineYear-$quarterMonth-$deadline");
                $today = new DateTime();
                $diff = $today->diff($deadlineDate);
                $daysRemaining = $diff->invert ? -$diff->days : $diff->days;
                ?>

                <div class="text-center mb-3">
                    <h2 class="<?= $daysRemaining < 0 ? 'text-danger' : ($daysRemaining <= 3 ? 'text-warning' : 'text-primary') ?>">
                        <?php if ($daysRemaining < 0): ?>
                            <?= abs($daysRemaining) ?> days overdue
                        <?php elseif ($daysRemaining == 0): ?>
                            Due Today!
                        <?php else: ?>
                            <?= $daysRemaining ?> days remaining
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted mb-0">Deadline: <?= $deadlineDate->format('d M Y') ?></p>
                </div>

                <?php if ($daysRemaining < 0): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Submission is overdue. Escalation may be required.
                    </div>
                <?php elseif ($daysRemaining <= 3): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-clock me-1"></i>
                        Final warning: Very little time remaining.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/crm/send-reminder') ?>" class="mb-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="directorate_id" value="<?= $directorate['id'] ?>">
                    <input type="hidden" name="include_performance" value="1">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-envelope me-1"></i>Send Performance Summary
                    </button>
                </form>

                <?php if ($performance['criticality'] !== 'low' || $daysRemaining < 0): ?>
                <form method="POST" action="<?= url('/crm/escalate') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="directorate_id" value="<?= $directorate['id'] ?>">
                    <input type="hidden" name="level" value="<?= $daysRemaining < -7 ? 'mm' : 'director' ?>">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-arrow-up-circle me-1"></i>
                        Escalate to <?= $daysRemaining < -7 ? 'Municipal Manager' : 'Director' ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Reminders -->
        <?php if (!empty($recentReminders)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Reminders</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($recentReminders as $reminder): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <?php
                                $typeLabels = [
                                    'first_reminder' => '<span class="badge bg-info">1st Reminder</span>',
                                    'second_reminder' => '<span class="badge bg-warning text-dark">2nd Reminder</span>',
                                    'final_warning' => '<span class="badge bg-orange">Final Warning</span>',
                                    'escalation_director' => '<span class="badge bg-danger">Escalation</span>',
                                    'escalation_mm' => '<span class="badge bg-danger">Escalation (MM)</span>',
                                    'performance_report' => '<span class="badge bg-primary">Performance</span>',
                                ];
                                echo $typeLabels[$reminder['reminder_type']] ?? '<span class="badge bg-secondary">' . ucfirst(str_replace('_', ' ', $reminder['reminder_type'])) . '</span>';
                                ?>
                                <br><small class="text-muted"><?= date('d M Y H:i', strtotime($reminder['sent_at'])) ?></small>
                            </div>
                            <?php if ($reminder['status'] === 'sent'): ?>
                                <span class="badge bg-success">Sent</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Failed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bg-orange {
    background-color: #f97316 !important;
    color: white;
}
</style>
