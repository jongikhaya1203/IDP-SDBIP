<?php
$pageTitle = $title ?? 'Action Item';
ob_start();
$priorityBadge = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'][$item['priority']] ?? 'secondary';
$statusBadge = [
    'pending' => 'secondary',
    'in_progress' => 'primary',
    'completed' => 'success',
    'overdue' => 'danger',
    'escalated' => 'warning'
][$item['status']] ?? 'secondary';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/imbizo') ?>">Imbizo</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/imbizo/action-items') ?>">Action Items</a></li>
                <li class="breadcrumb-item active"><?= e($item['item_number']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">
            <?= e($item['item_number']) ?>
            <span class="badge bg-<?= $statusBadge ?> ms-2"><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></span>
        </h1>
    </div>
    <a href="<?= url('/imbizo/action-items') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row">
    <!-- Action Item Details -->
    <div class="col-lg-8 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Action Item Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="text-muted">Description</h6>
                    <p class="lead"><?= nl2br(e($item['description'])) ?></p>
                </div>

                <?php if ($item['community_concern']): ?>
                <div class="mb-4">
                    <h6 class="text-muted">Community Concern</h6>
                    <p><?= nl2br(e($item['community_concern'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($item['commitment']): ?>
                <div class="mb-4">
                    <h6 class="text-muted">Mayor's Commitment</h6>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-quote me-1"></i>
                        <?= nl2br(e($item['commitment'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Session:</th>
                                <td>
                                    <a href="<?= url('/imbizo/' . ($item['session_id'] ?? '')) ?>">
                                        <?= e($item['session_title']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= format_date($item['session_date'], 'd F Y') ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Ward:</th>
                                <td><?= e($item['ward_name'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Assigned To:</th>
                                <td>
                                    <span class="badge bg-secondary"><?= e($item['directorate_code'] ?? 'N/A') ?></span>
                                    <?= e($item['directorate_name'] ?? '') ?>
                                    <?php if ($item['assigned_to']): ?>
                                    <br><small class="text-muted"><?= e($item['assigned_to']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Target Date:</th>
                                <td>
                                    <?= $item['target_date'] ? format_date($item['target_date'], 'd F Y') : 'TBD' ?>
                                    <?php if ($item['target_date'] && $item['target_date'] < date('Y-m-d') && $item['status'] !== 'completed'): ?>
                                    <span class="badge bg-danger">Overdue</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Priority:</th>
                                <td><span class="badge bg-<?= $priorityBadge ?>"><?= ucfirst($item['priority']) ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Progress</span>
                        <strong><?= $item['progress_percentage'] ?>%</strong>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-<?= $item['progress_percentage'] >= 100 ? 'success' : 'primary' ?>"
                             style="width: <?= $item['progress_percentage'] ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments/Responses -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-dots me-1"></i> Responses (<?= count($comments) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($comments)): ?>
                <p class="text-muted text-center py-3">No responses yet.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($comments as $comment): ?>
                    <div class="border-start border-3 border-primary ps-3 mb-4">
                        <div class="d-flex justify-content-between">
                            <strong><?= e($comment['author_name']) ?></strong>
                            <small class="text-muted"><?= format_date($comment['created_at'], 'd M Y H:i') ?></small>
                        </div>
                        <span class="badge bg-<?= $comment['comment_type'] === 'completion' ? 'success' : ($comment['comment_type'] === 'escalation' ? 'warning' : 'info') ?> mb-2">
                            <?= ucfirst($comment['comment_type']) ?>
                        </span>
                        <p class="mb-0"><?= nl2br(e($comment['content'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Add Response Form -->
                <hr>
                <h6>Add Response</h6>
                <form action="<?= url('/imbizo/action-items/' . $item['id'] . '/comments') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" required
                                  placeholder="Enter your response..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Response Type</label>
                            <select name="comment_type" class="form-select">
                                <option value="response">Response</option>
                                <option value="update">Progress Update</option>
                                <option value="escalation">Escalation</option>
                                <option value="completion">Mark Complete</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Update Status</label>
                            <select name="new_status" class="form-select">
                                <option value="">No Change</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="escalated">Escalated</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Progress %</label>
                            <input type="number" name="progress_update" class="form-control"
                                   min="0" max="100" placeholder="e.g., 75">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Submit Response
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- POE Sidebar -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-paperclip me-1"></i> Proof of Evidence (<?= count($poeFiles) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($poeFiles)): ?>
                <p class="text-muted text-center py-3">No evidence files uploaded.</p>
                <?php else: ?>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($poeFiles as $poe): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <i class="bi bi-file-earmark-text me-1"></i>
                            <?= e($poe['file_name']) ?>
                            <br><small class="text-muted">
                                By <?= e($poe['uploader_name']) ?><br>
                                <?= format_date($poe['created_at'], 'd M Y') ?>
                            </small>
                        </div>
                        <a href="<?= url('/uploads/' . $poe['file_path']) ?>" target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <!-- Upload POE -->
                <hr>
                <h6>Upload Evidence</h6>
                <form action="<?= url('/imbizo/action-items/' . $item['id'] . '/poe') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <input type="file" name="poe_file" class="form-control" required
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                        <small class="text-muted">PDF, DOC, XLS, Images (max 10MB)</small>
                    </div>

                    <div class="mb-3">
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Description of evidence..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-upload me-1"></i> Upload POE
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
