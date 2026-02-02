<?php
$pageTitle = $title ?? 'Action Items';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/imbizo') ?>">Imbizo</a></li>
                <li class="breadcrumb-item active">Action Items</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Imbizo Action Items</h1>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status Filter</label>
                <select name="filter" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Items</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending / In Progress</option>
                    <option value="overdue" <?= $filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Directorate</label>
                <select name="directorate_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Directorates</option>
                    <?php foreach ($directorates as $dir): ?>
                    <option value="<?= $dir['id'] ?>" <?= $selectedDirectorate == $dir['id'] ? 'selected' : '' ?>>
                        <?= e($dir['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <a href="<?= url('/imbizo/action-items') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Action Items List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Action Items (<?= count($items) ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <i class="bi bi-clipboard-check text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Action Items</h5>
            <p class="text-muted">No action items match your filter criteria.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item #</th>
                        <th>Description</th>
                        <th>Session</th>
                        <th>Directorate</th>
                        <th>Target Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Responses</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <?php
                    $priorityBadge = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'][$item['priority']] ?? 'secondary';
                    $statusBadge = [
                        'pending' => 'secondary',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'overdue' => 'danger',
                        'escalated' => 'warning'
                    ][$item['status']] ?? 'secondary';
                    $isOverdue = $item['status'] !== 'completed' && $item['target_date'] && $item['target_date'] < date('Y-m-d');
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                        <td><strong><?= e($item['item_number']) ?></strong></td>
                        <td style="max-width: 250px;">
                            <?= e(substr($item['description'], 0, 60)) ?>
                            <?= strlen($item['description']) > 60 ? '...' : '' ?>
                        </td>
                        <td>
                            <a href="<?= url('/imbizo/' . ($item['session_id'] ?? '')) ?>" class="text-decoration-none">
                                <?= e(substr($item['session_title'] ?? '', 0, 30)) ?>
                            </a>
                            <br><small class="text-muted"><?= format_date($item['session_date'], 'd M Y') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= e($item['directorate_code'] ?? 'N/A') ?></span>
                        </td>
                        <td>
                            <?php if ($item['target_date']): ?>
                            <?= format_date($item['target_date'], 'd M Y') ?>
                            <?php if ($isOverdue): ?>
                            <br><span class="badge bg-danger">Overdue</span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">TBD</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $priorityBadge ?>"><?= ucfirst($item['priority']) ?></span></td>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></span></td>
                        <td>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-chat-dots me-1"></i><?= $item['comment_count'] ?>
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-paperclip me-1"></i><?= $item['poe_count'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= url('/imbizo/action-items/' . $item['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i> View
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

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
