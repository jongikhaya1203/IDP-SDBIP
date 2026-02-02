<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">AI Performance Reports</h4>
        <p class="text-muted mb-0">Generate comprehensive AI-powered performance analysis</p>
    </div>
</div>

<div class="row g-4">
    <!-- Generate New Report -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-robot me-2"></i>Generate New Report
            </div>
            <div class="card-body">
                <?php if (!$hasApiKey): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    OpenAI API key not configured. Please set <code>OPENAI_API_KEY</code> in your <code>.env</code> file.
                </div>
                <?php else: ?>
                <form method="POST" action="/reports/ai/generate">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select" required>
                            <option value="quarterly_performance">Quarterly Performance Report</option>
                            <option value="directorate_analysis">Directorate Analysis</option>
                            <option value="budget_analysis">Budget Analysis</option>
                            <option value="risk_assessment">Risk Assessment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quarter</label>
                        <select name="quarter" class="form-select" required>
                            <?php for ($q = 1; $q <= 4; $q++): ?>
                            <option value="<?= $q ?>" <?= $q == current_quarter() ? 'selected' : '' ?>>
                                <?= quarter_label($q) ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Directorate (Optional)</label>
                        <select name="directorate_id" class="form-select">
                            <option value="">Organization-wide Report</option>
                            <?php foreach ($directorates as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['code']) ?> - <?= e($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-lightning me-1"></i>Generate Report
                    </button>
                    <small class="text-muted d-block mt-2">
                        Uses OpenAI <?= OPENAI_MODEL ?> | Generation may take 30-60 seconds
                    </small>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Existing Reports -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-text me-2"></i>Generated Reports
            </div>
            <div class="card-body p-0">
                <?php if (empty($reports)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                    <p>No reports generated yet. Create your first AI report!</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Report</th>
                                <th>Type</th>
                                <th>Scope</th>
                                <th>Generated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                            <tr>
                                <td>
                                    <a href="/reports/ai/<?= $r['id'] ?>" class="text-decoration-none">
                                        <?= e($r['title']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= ucwords(str_replace('_', ' ', $r['report_type'])) ?></span>
                                </td>
                                <td>
                                    <?php if ($r['directorate_name']): ?>
                                    <?= e($r['directorate_name']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">Organization-wide</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= format_date($r['created_at'], 'd M Y H:i') ?></small>
                                    <br><small class="text-muted">by <?= e($r['generated_by_name'] ?? 'System') ?></small>
                                </td>
                                <td>
                                    <a href="/reports/ai/<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
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
    </div>
</div>
