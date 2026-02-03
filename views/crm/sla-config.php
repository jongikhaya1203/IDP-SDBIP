<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-gear me-2"></i>SLA Configuration</h4>
        <p class="text-muted mb-0">Configure reminder schedules, escalation rules, and criticality thresholds</p>
    </div>
    <a href="<?= url('/crm') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to CRM
    </a>
</div>

<form method="POST" action="<?= url('/crm/sla-config/save') ?>">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-6">
            <!-- Reminder Schedule -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Reminder Schedule</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Submission Deadline Day</label>
                        <div class="input-group">
                            <span class="input-group-text">Day</span>
                            <input type="number" name="submission_deadline_day" class="form-control"
                                   value="<?= $slaConfig['submission_deadline_day'] ?>" min="1" max="28" required>
                            <span class="input-group-text">of the month after quarter ends</span>
                        </div>
                        <small class="text-muted">E.g., Q1 (Jul-Sep) deadline would be Oct 15th if set to 15</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">First Reminder</label>
                        <div class="input-group">
                            <input type="number" name="first_reminder_days" class="form-control"
                                   value="<?= $slaConfig['first_reminder_days'] ?>" min="1" max="30" required>
                            <span class="input-group-text">days before deadline</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Second Reminder (Urgent)</label>
                        <div class="input-group">
                            <input type="number" name="second_reminder_days" class="form-control"
                                   value="<?= $slaConfig['second_reminder_days'] ?>" min="1" max="14" required>
                            <span class="input-group-text">days before deadline</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Final Warning</label>
                        <div class="input-group">
                            <input type="number" name="final_warning_days" class="form-control"
                                   value="<?= $slaConfig['final_warning_days'] ?>" min="1" max="7" required>
                            <span class="input-group-text">days before deadline</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Escalation Trigger</label>
                        <div class="input-group">
                            <input type="number" name="escalation_days" class="form-control"
                                   value="<?= $slaConfig['escalation_days'] ?>" min="1" max="14" required>
                            <span class="input-group-text">days after deadline (overdue)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Criticality Thresholds -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Criticality Thresholds</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Define when submissions are flagged as critical based on pending KPI count.</p>

                    <div class="mb-3">
                        <label class="form-label">High Criticality</label>
                        <div class="input-group">
                            <input type="number" name="criticality_high_threshold" class="form-control"
                                   value="<?= $slaConfig['criticality_high_threshold'] ?>" min="1" required>
                            <span class="input-group-text">or more pending KPIs</span>
                        </div>
                        <small class="text-danger">Immediate escalation recommended</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Medium Criticality</label>
                        <div class="input-group">
                            <input type="number" name="criticality_medium_threshold" class="form-control"
                                   value="<?= $slaConfig['criticality_medium_threshold'] ?>" min="1" required>
                            <span class="input-group-text">or more pending KPIs</span>
                        </div>
                        <small class="text-warning">Management attention required</small>
                    </div>

                    <div class="alert alert-light border">
                        <strong>Low Criticality:</strong> Less than <?= $slaConfig['criticality_medium_threshold'] ?> pending KPIs - standard follow-up
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Escalation Settings -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-arrow-up-circle me-2"></i>Escalation Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input type="checkbox" name="escalate_to_mm" class="form-check-input" id="escalateToMM"
                               <?= $slaConfig['escalate_to_mm'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="escalateToMM">
                            <strong>Enable escalation to Municipal Manager</strong>
                            <br><small class="text-muted">MM will be notified when directorates fail to comply</small>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="escalate_to_mayor" class="form-check-input" id="escalateToMayor"
                               <?= $slaConfig['escalate_to_mayor'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="escalateToMayor">
                            <strong>Enable escalation to Executive Mayor</strong>
                            <br><small class="text-muted">For severe non-compliance cases</small>
                        </label>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">CC Emails for All Reminders</label>
                        <input type="text" name="cc_emails" class="form-control"
                               value="<?= e($slaConfig['cc_emails']) ?>"
                               placeholder="email1@example.com, email2@example.com">
                        <small class="text-muted">Comma-separated list of emails to copy on all reminders</small>
                    </div>
                </div>
            </div>

            <!-- Automation Settings -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-robot me-2"></i>Automation Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input type="checkbox" name="auto_send_reminders" class="form-check-input" id="autoSendReminders"
                               <?= $slaConfig['auto_send_reminders'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="autoSendReminders">
                            <strong>Enable automatic reminders</strong>
                            <br><small class="text-muted">System will automatically send reminders based on schedule</small>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="include_performance_summary" class="form-check-input" id="includePerformance"
                               <?= $slaConfig['include_performance_summary'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="includePerformance">
                            <strong>Include performance summary in emails</strong>
                            <br><small class="text-muted">Add directorate's current KPI status to reminder emails</small>
                        </label>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Note:</strong> Automatic reminders require a scheduled task (cron job) to be configured on the server.
                    </div>
                </div>
            </div>

            <!-- Preview Timeline -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Reminder Timeline Preview</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>First Reminder</strong>
                                <br><small class="text-muted"><?= $slaConfig['first_reminder_days'] ?> days before deadline</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <strong>Second Reminder</strong>
                                <br><small class="text-muted"><?= $slaConfig['second_reminder_days'] ?> days before deadline</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-orange"></div>
                            <div class="timeline-content">
                                <strong>Final Warning</strong>
                                <br><small class="text-muted"><?= $slaConfig['final_warning_days'] ?> day(s) before deadline</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <strong>Submission Deadline</strong>
                                <br><small class="text-muted">Day <?= $slaConfig['submission_deadline_day'] ?> of month</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <strong>Escalation</strong>
                                <br><small class="text-muted"><?= $slaConfig['escalation_days'] ?> days after deadline</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <a href="<?= url('/crm') ?>" class="btn btn-light me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Save Configuration
        </button>
    </div>
</form>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e2e8f0;
}
.bg-orange {
    background-color: #f97316 !important;
}
</style>
