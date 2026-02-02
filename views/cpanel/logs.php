<?php
$pageTitle = $title ?? 'System Logs';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/cpanel') ?>">Control Panel</a></li>
                <li class="breadcrumb-item active">Logs</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-journal-text me-2"></i>Audit Logs</h1>
        <p class="text-muted mb-0">System activity and change history</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
        <div class="text-center py-5">
            <i class="bi bi-journal text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Audit Logs</h5>
            <p class="text-muted">No system activity has been logged yet.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <small><?= date('d M Y H:i:s', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                            <?php if ($log['username']): ?>
                            <span class="badge bg-secondary"><?= e($log['username']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">System</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $actionBadge = match($log['action']) {
                                'create', 'insert' => 'success',
                                'update' => 'info',
                                'delete' => 'danger',
                                'login' => 'primary',
                                'logout' => 'secondary',
                                default => 'light'
                            };
                            ?>
                            <span class="badge bg-<?= $actionBadge ?>">
                                <?= strtoupper(e($log['action'])) ?>
                            </span>
                        </td>
                        <td><code><?= e($log['table_name']) ?></code></td>
                        <td><?= e($log['record_id'] ?? '-') ?></td>
                        <td>
                            <?php if ($log['old_values'] || $log['new_values']): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal" data-bs-target="#logModal<?= $log['id'] ?>">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="logModal<?= $log['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Change Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Old Values</h6>
                                                    <pre class="bg-light p-2 rounded small"><?= e($log['old_values'] ?: 'N/A') ?></pre>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>New Values</h6>
                                                    <pre class="bg-light p-2 rounded small"><?= e($log['new_values'] ?: 'N/A') ?></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">-</span>
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

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
