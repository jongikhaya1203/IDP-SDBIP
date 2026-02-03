<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-journal-text me-2"></i>Reminder Logs</h4>
        <p class="text-muted mb-0">View history of all sent reminders and escalations</p>
    </div>
    <a href="<?= url('/crm') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to CRM
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/crm/logs') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Directorate</label>
                <select name="directorate_id" class="form-select">
                    <option value="">All Directorates</option>
                    <?php foreach ($directorates as $dir): ?>
                        <option value="<?= $dir['id'] ?>" <?= ($filters['directorate_id'] ?? '') == $dir['id'] ? 'selected' : '' ?>>
                            <?= e($dir['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Reminder Type</label>
                <select name="reminder_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="first_reminder" <?= ($filters['reminder_type'] ?? '') == 'first_reminder' ? 'selected' : '' ?>>First Reminder</option>
                    <option value="second_reminder" <?= ($filters['reminder_type'] ?? '') == 'second_reminder' ? 'selected' : '' ?>>Second Reminder</option>
                    <option value="final_warning" <?= ($filters['reminder_type'] ?? '') == 'final_warning' ? 'selected' : '' ?>>Final Warning</option>
                    <option value="escalation_director" <?= ($filters['reminder_type'] ?? '') == 'escalation_director' ? 'selected' : '' ?>>Escalation (Director)</option>
                    <option value="escalation_mm" <?= ($filters['reminder_type'] ?? '') == 'escalation_mm' ? 'selected' : '' ?>>Escalation (MM)</option>
                    <option value="escalation_mayor" <?= ($filters['reminder_type'] ?? '') == 'escalation_mayor' ? 'selected' : '' ?>>Escalation (Mayor)</option>
                    <option value="performance_report" <?= ($filters['reminder_type'] ?? '') == 'performance_report' ? 'selected' : '' ?>>Performance Report</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="sent" <?= ($filters['status'] ?? '') == 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= ($filters['status'] ?? '') == 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i>Apply Filters
                </button>
                <a href="<?= url('/crm/logs') ?>" class="btn btn-light">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0"><?= $stats['total'] ?? 0 ?></h3>
                <small class="text-muted">Total Reminders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="text-success mb-0"><?= $stats['sent'] ?? 0 ?></h3>
                <small class="text-muted">Successfully Sent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h3 class="text-danger mb-0"><?= $stats['failed'] ?? 0 ?></h3>
                <small class="text-muted">Failed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0"><?= $stats['escalations'] ?? 0 ?></h3>
                <small class="text-muted">Escalations</small>
            </div>
        </div>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i>Reminder History</span>
        <span class="badge bg-secondary"><?= $pagination['total'] ?? 0 ?> records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date/Time</th>
                    <th>Directorate</th>
                    <th>Type</th>
                    <th>Recipient</th>
                    <th>Status</th>
                    <th>Sent By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            No reminder logs found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <strong><?= date('d M Y', strtotime($log['sent_at'])) ?></strong>
                                <br><small class="text-muted"><?= date('H:i', strtotime($log['sent_at'])) ?></small>
                            </td>
                            <td><?= e($log['directorate_name'] ?? 'N/A') ?></td>
                            <td>
                                <?php
                                $typeBadges = [
                                    'first_reminder' => 'bg-info',
                                    'second_reminder' => 'bg-warning text-dark',
                                    'final_warning' => 'bg-orange',
                                    'escalation_director' => 'bg-danger',
                                    'escalation_mm' => 'bg-danger',
                                    'escalation_mayor' => 'bg-dark',
                                    'performance_report' => 'bg-primary',
                                    'submission_reminder' => 'bg-secondary'
                                ];
                                $typeLabels = [
                                    'first_reminder' => 'First Reminder',
                                    'second_reminder' => 'Second Reminder',
                                    'final_warning' => 'Final Warning',
                                    'escalation_director' => 'Escalation (Director)',
                                    'escalation_mm' => 'Escalation (MM)',
                                    'escalation_mayor' => 'Escalation (Mayor)',
                                    'performance_report' => 'Performance Report',
                                    'submission_reminder' => 'Submission Reminder'
                                ];
                                $badge = $typeBadges[$log['reminder_type']] ?? 'bg-secondary';
                                $label = $typeLabels[$log['reminder_type']] ?? ucfirst(str_replace('_', ' ', $log['reminder_type']));
                                ?>
                                <span class="badge <?= $badge ?>"><?= $label ?></span>
                                <?php if ($log['is_bulk']): ?>
                                    <span class="badge bg-light text-dark">Bulk</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= e($log['recipient_email']) ?></small>
                            </td>
                            <td>
                                <?php if ($log['status'] === 'sent'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>
                                <?php elseif ($log['status'] === 'failed'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?= e($log['sender_name'] ?? 'System') ?></small>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#viewLogModal"
                                        data-log-id="<?= $log['id'] ?>"
                                        data-subject="<?= e($log['subject']) ?>"
                                        data-message="<?= e($log['message']) ?>"
                                        data-snapshot="<?= e($log['performance_snapshot'] ?? '') ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('/crm/logs?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1]))) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('/crm/logs?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= url('/crm/logs?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1]))) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- View Log Modal -->
<div class="modal fade" id="viewLogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope-open me-2"></i>Reminder Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject</label>
                    <div id="logSubject" class="form-control-plaintext border rounded p-2 bg-light"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Message</label>
                    <div id="logMessage" class="form-control-plaintext border rounded p-3 bg-light" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;"></div>
                </div>
                <div id="logSnapshotSection" class="mb-3" style="display: none;">
                    <label class="form-label fw-bold">Performance Snapshot at Time of Sending</label>
                    <div id="logSnapshot" class="border rounded p-3 bg-light"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.bg-orange {
    background-color: #f97316 !important;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewLogModal = document.getElementById('viewLogModal');
    viewLogModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const subject = button.getAttribute('data-subject');
        const message = button.getAttribute('data-message');
        const snapshot = button.getAttribute('data-snapshot');

        document.getElementById('logSubject').textContent = subject;
        document.getElementById('logMessage').textContent = message;

        const snapshotSection = document.getElementById('logSnapshotSection');
        const snapshotDiv = document.getElementById('logSnapshot');

        if (snapshot && snapshot !== 'null' && snapshot !== '') {
            try {
                const snapshotData = JSON.parse(snapshot);
                let snapshotHtml = '<div class="row">';
                if (snapshotData.total_kpis !== undefined) {
                    snapshotHtml += `
                        <div class="col-md-4 mb-2">
                            <div class="p-2 bg-white rounded">
                                <small class="text-muted">Total KPIs</small>
                                <div class="fw-bold">${snapshotData.total_kpis}</div>
                            </div>
                        </div>`;
                }
                if (snapshotData.submitted !== undefined) {
                    snapshotHtml += `
                        <div class="col-md-4 mb-2">
                            <div class="p-2 bg-white rounded">
                                <small class="text-muted">Submitted</small>
                                <div class="fw-bold text-success">${snapshotData.submitted}</div>
                            </div>
                        </div>`;
                }
                if (snapshotData.pending !== undefined) {
                    snapshotHtml += `
                        <div class="col-md-4 mb-2">
                            <div class="p-2 bg-white rounded">
                                <small class="text-muted">Pending</small>
                                <div class="fw-bold text-warning">${snapshotData.pending}</div>
                            </div>
                        </div>`;
                }
                if (snapshotData.submission_rate !== undefined) {
                    snapshotHtml += `
                        <div class="col-12">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: ${snapshotData.submission_rate}%">
                                    ${snapshotData.submission_rate}% Complete
                                </div>
                            </div>
                        </div>`;
                }
                snapshotHtml += '</div>';
                snapshotDiv.innerHTML = snapshotHtml;
                snapshotSection.style.display = 'block';
            } catch (e) {
                snapshotSection.style.display = 'none';
            }
        } else {
            snapshotSection.style.display = 'none';
        }
    });
});
</script>
