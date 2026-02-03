<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-envelope-paper me-2"></i>CRM Portal - Reminder Management</h4>
        <p class="text-muted mb-0">Manage quarterly submission reminders, escalations, and SLA tracking</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/crm/sla-config') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>SLA Config
        </a>
        <a href="<?= url('/crm/logs') ?>" class="btn btn-outline-primary">
            <i class="bi bi-journal-text me-1"></i>View All Logs
        </a>
    </div>
</div>

<!-- Submission Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Total KPIs</h6>
                        <h2 class="mb-0"><?= number_format($submissionStats['total_kpis'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-bullseye fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Submitted</h6>
                        <h2 class="mb-0"><?= number_format($submissionStats['submitted'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="opacity-75 mb-1">Pending</h6>
                        <h2 class="mb-0"><?= number_format($submissionStats['pending'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1">Approved</h6>
                        <h2 class="mb-0"><?= number_format($submissionStats['approved'] ?? 0) ?></h2>
                    </div>
                    <i class="bi bi-award fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Directorate Status & Actions -->
    <div class="col-lg-8">
        <!-- Overdue Alert -->
        <?php if (!empty($overdueSubmissions)): ?>
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>
                    <strong>Overdue Submissions Detected!</strong>
                    <p class="mb-0"><?= count($overdueSubmissions) ?> directorate(s) have overdue submissions for <?= quarter_label($currentQuarter) ?>.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Directorate Submission Status -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Directorate Submission Status - <?= quarter_label($currentQuarter) ?></h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bulkReminderModal">
                    <i class="bi bi-send me-1"></i>Bulk Reminder
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Directorate</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Submitted</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Rate</th>
                                <th class="text-center">Criticality</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($directorateStats as $dir): ?>
                            <?php
                                $total = (int)$dir['total_kpis'];
                                $submitted = (int)$dir['submitted'];
                                $pending = (int)$dir['pending'];
                                $rate = $total > 0 ? round(($submitted / $total) * 100, 1) : 0;

                                // Determine criticality
                                if ($pending >= ($slaConfig['criticality_high_threshold'] ?? 5)) {
                                    $criticality = 'HIGH';
                                    $criticalityClass = 'danger';
                                } elseif ($pending >= ($slaConfig['criticality_medium_threshold'] ?? 3)) {
                                    $criticality = 'MEDIUM';
                                    $criticalityClass = 'warning';
                                } elseif ($pending > 0) {
                                    $criticality = 'LOW';
                                    $criticalityClass = 'info';
                                } else {
                                    $criticality = 'NONE';
                                    $criticalityClass = 'success';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= e($dir['code']) ?></strong> - <?= e($dir['name']) ?>
                                    <?php if ($dir['head_name']): ?>
                                    <br><small class="text-muted"><i class="bi bi-person me-1"></i><?= e($dir['head_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $total ?></td>
                                <td class="text-center text-success"><?= $submitted ?></td>
                                <td class="text-center">
                                    <?php if ($pending > 0): ?>
                                    <span class="text-danger fw-bold"><?= $pending ?></span>
                                    <?php else: ?>
                                    <span class="text-success">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px; min-width: 60px;">
                                        <div class="progress-bar bg-<?= $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') ?>"
                                             style="width: <?= $rate ?>%"><?= $rate ?>%</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $criticalityClass ?>"><?= $criticality ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/crm/performance-preview?directorate_id=' . $dir['id']) ?>"
                                           class="btn btn-outline-secondary" title="Preview Performance">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($pending > 0): ?>
                                        <button type="button" class="btn btn-outline-primary btn-send-reminder"
                                                data-directorate-id="<?= $dir['id'] ?>"
                                                data-directorate-name="<?= e($dir['name']) ?>"
                                                data-pending="<?= $pending ?>"
                                                title="Send Reminder">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                        <?php if ($criticality === 'HIGH'): ?>
                                        <button type="button" class="btn btn-outline-danger btn-escalate"
                                                data-directorate-id="<?= $dir['id'] ?>"
                                                data-directorate-name="<?= e($dir['name']) ?>"
                                                title="Escalate">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- SLA Summary -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>SLA Configuration</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>First Reminder</span>
                        <strong><?= $slaConfig['first_reminder_days'] ?> days before</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Second Reminder</span>
                        <strong><?= $slaConfig['second_reminder_days'] ?> days before</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Escalation</span>
                        <strong><?= $slaConfig['escalation_days'] ?> days overdue</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Submission Deadline</span>
                        <strong>Day <?= $slaConfig['submission_deadline_day'] ?></strong>
                    </li>
                </ul>
                <a href="<?= url('/crm/sla-config') ?>" class="btn btn-outline-dark btn-sm w-100 mt-3">
                    <i class="bi bi-gear me-1"></i>Configure SLA
                </a>
            </div>
        </div>

        <!-- Recent Reminder Logs -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Recent Reminders</h6>
                <a href="<?= url('/crm/logs') ?>" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentLogs)): ?>
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No reminders sent yet
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    <?php foreach (array_slice($recentLogs, 0, 10) as $log): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-<?= $log['status'] === 'sent' ? 'success' : 'danger' ?> me-1">
                                    <?= ucfirst($log['status']) ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <?= ucwords(str_replace('_', ' ', $log['reminder_type'])) ?>
                                </span>
                            </div>
                            <small class="text-muted"><?= format_date($log['sent_at'], 'd M H:i') ?></small>
                        </div>
                        <div class="mt-1">
                            <strong><?= e($log['directorate_name'] ?? 'N/A') ?></strong>
                            <br><small class="text-muted"><?= e($log['recipient_email']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Send Reminder Modal -->
<div class="modal fade" id="sendReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/crm/send-reminder') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="directorate_id" id="reminderDirectorateId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>Send Reminder</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" id="reminderInfo">
                        <strong id="reminderDirectorateName"></strong> has <strong id="reminderPendingCount"></strong> pending KPI submissions.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reminder Type</label>
                        <select name="reminder_type" class="form-select" required>
                            <option value="submission_reminder">Standard Reminder</option>
                            <option value="first_reminder">First Reminder (14 days)</option>
                            <option value="second_reminder">Second Reminder (Urgent)</option>
                            <option value="final_warning">Final Warning</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Custom Message (Optional)</label>
                        <textarea name="custom_message" class="form-control" rows="3"
                                  placeholder="Add any additional message..."></textarea>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="include_performance" class="form-check-input" id="includePerformance" checked>
                        <label class="form-check-label" for="includePerformance">
                            Include performance summary in email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Send Reminder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Escalation Modal -->
<div class="modal fade" id="escalationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/crm/escalate') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="directorate_id" id="escalateDirectorateId">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Escalate Non-Submission</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning:</strong> You are about to escalate <strong id="escalateDirectorateName"></strong> for non-compliance with submission requirements.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Escalation Level</label>
                        <select name="escalation_level" class="form-select" required>
                            <option value="director">Directorate Head Only</option>
                            <option value="mm">Municipal Manager</option>
                            <?php if ($slaConfig['escalate_to_mayor']): ?>
                            <option value="mayor">Executive Mayor</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <p class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        An escalation notice will be sent to the selected recipients with the current submission status and criticality assessment.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>Escalate Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Reminder Modal -->
<div class="modal fade" id="bulkReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/crm/send-bulk-reminders') ?>">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-send me-2"></i>Send Bulk Reminders</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Target Group</label>
                        <select name="target_group" class="form-select" required>
                            <option value="pending">Directorates with Pending Submissions</option>
                            <option value="overdue">Overdue Directorates Only</option>
                            <option value="all">All Directorates</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reminder Type</label>
                        <select name="reminder_type" class="form-select" required>
                            <option value="submission_reminder">Standard Reminder</option>
                            <option value="first_reminder">First Reminder</option>
                            <option value="second_reminder">Second Reminder (Urgent)</option>
                            <option value="final_warning">Final Warning</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-1"></i>
                        This will send reminders to all directorates in the selected group. Each email will include the directorate's current performance summary.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>Send to All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Send Reminder Button Handler
    document.querySelectorAll('.btn-send-reminder').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('reminderDirectorateId').value = this.dataset.directorateId;
            document.getElementById('reminderDirectorateName').textContent = this.dataset.directorateName;
            document.getElementById('reminderPendingCount').textContent = this.dataset.pending;
            new bootstrap.Modal(document.getElementById('sendReminderModal')).show();
        });
    });

    // Escalate Button Handler
    document.querySelectorAll('.btn-escalate').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('escalateDirectorateId').value = this.dataset.directorateId;
            document.getElementById('escalateDirectorateName').textContent = this.dataset.directorateName;
            new bootstrap.Modal(document.getElementById('escalationModal')).show();
        });
    });
});
</script>
