<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?= e($report['title']) ?></h4>
        <p class="text-muted mb-0">
            Generated <?= format_date($report['created_at'], 'd M Y H:i') ?>
            by <?= e($report['generated_by_first'] . ' ' . $report['generated_by_last']) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports/ai" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer me-1"></i>Print
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Main Report Content -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <?php if ($report['summary']): ?>
                <div class="alert alert-info mb-4">
                    <h6><i class="bi bi-lightbulb me-1"></i>Executive Summary</h6>
                    <p class="mb-0"><?= nl2br(e($report['summary'])) ?></p>
                </div>
                <?php endif; ?>

                <div class="report-content">
                    <?= \Parsedown::instance()->text($report['content']) ?? nl2br(e($report['content'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Report Info -->
        <div class="card mb-4">
            <div class="card-header">Report Details</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th>Financial Year:</th>
                        <td><?= e($report['year_label']) ?></td>
                    </tr>
                    <tr>
                        <th>Quarter:</th>
                        <td><?= quarter_label($report['quarter']) ?></td>
                    </tr>
                    <tr>
                        <th>Report Type:</th>
                        <td><?= ucwords(str_replace('_', ' ', $report['report_type'])) ?></td>
                    </tr>
                    <tr>
                        <th>Scope:</th>
                        <td><?= e($report['directorate_name'] ?? 'Organization-wide') ?></td>
                    </tr>
                    <tr>
                        <th>AI Model:</th>
                        <td><?= e($report['model_used']) ?></td>
                    </tr>
                    <tr>
                        <th>Tokens Used:</th>
                        <td><?= number_format($report['generation_tokens'] ?? 0) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Recommendations -->
        <?php if (!empty($report['recommendations'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-lightbulb me-1"></i>Key Recommendations
            </div>
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <?php foreach ($report['recommendations'] as $rec): ?>
                    <li class="mb-2"><?= e($rec) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <?php endif; ?>

        <!-- Risk Flags -->
        <?php if (!empty($report['risk_flags'])): ?>
        <div class="card">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-1"></i>Risk Flags
            </div>
            <div class="card-body">
                <ul class="mb-0 ps-3">
                    <?php foreach ($report['risk_flags'] as $risk): ?>
                    <li class="mb-2"><?= e($risk) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.report-content h1 { font-size: 1.5rem; margin-top: 1.5rem; }
.report-content h2 { font-size: 1.25rem; margin-top: 1.25rem; color: #2563eb; }
.report-content h3 { font-size: 1.1rem; margin-top: 1rem; }
.report-content ul, .report-content ol { margin-bottom: 1rem; }
.report-content li { margin-bottom: 0.5rem; }
.report-content blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #64748b;
}
@media print {
    .btn, .card-header { display: none !important; }
    .col-lg-4 { display: none; }
    .col-lg-8 { width: 100%; }
}
</style>
