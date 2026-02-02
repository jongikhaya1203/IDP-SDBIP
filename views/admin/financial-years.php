<?php
$pageTitle = $title ?? 'Financial Years';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/admin') ?>">Admin</a></li>
                <li class="breadcrumb-item active">Financial Years</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Financial Years</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($years)): ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar3 text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Financial Years</h5>
            <p class="text-muted">No financial years have been configured.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Financial Year</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Current</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($years as $year): ?>
                    <tr class="<?= $year['is_current'] ? 'table-primary' : '' ?>">
                        <td><strong><?= e($year['year_label']) ?></strong></td>
                        <td><?= date('d M Y', strtotime($year['start_date'])) ?></td>
                        <td><?= date('d M Y', strtotime($year['end_date'])) ?></td>
                        <td>
                            <?php
                            $statusBadge = match($year['status']) {
                                'planning' => 'secondary',
                                'active' => 'success',
                                'closed' => 'dark',
                                default => 'light'
                            };
                            ?>
                            <span class="badge bg-<?= $statusBadge ?>"><?= ucfirst($year['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($year['is_current']): ?>
                            <span class="badge bg-primary">Current</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">SA Municipal Financial Year</h6>
    </div>
    <div class="card-body">
        <p class="mb-0">
            South African municipalities operate on a financial year from <strong>1 July to 30 June</strong>.
            The quarters are:
        </p>
        <ul class="mt-2 mb-0">
            <li><strong>Q1:</strong> July - September</li>
            <li><strong>Q2:</strong> October - December</li>
            <li><strong>Q3:</strong> January - March</li>
            <li><strong>Q4:</strong> April - June</li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
