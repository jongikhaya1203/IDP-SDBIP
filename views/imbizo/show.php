<?php
$pageTitle = $title ?? 'Imbizo Session';
ob_start();
$statusBadge = [
    'scheduled' => 'primary',
    'live' => 'danger',
    'completed' => 'success',
    'cancelled' => 'secondary'
][$session['status']] ?? 'secondary';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/imbizo') ?>">Imbizo</a></li>
                <li class="breadcrumb-item active"><?= e($session['title']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">
            <?= e($session['title']) ?>
            <span class="badge bg-<?= $statusBadge ?> ms-2">
                <?php if ($session['status'] === 'live'): ?>
                <i class="bi bi-broadcast me-1"></i>
                <?php endif; ?>
                <?= ucfirst($session['status']) ?>
            </span>
        </h1>
    </div>
    <div>
        <?php if ($session['status'] === 'scheduled' && has_role('admin', 'director')): ?>
        <a href="<?= url('/imbizo/' . $session['id'] . '/livestream') ?>" class="btn btn-danger">
            <i class="bi bi-broadcast me-1"></i> Start Livestream
        </a>
        <?php elseif ($session['status'] === 'live'): ?>
        <a href="<?= url('/imbizo/' . $session['id'] . '/livestream') ?>" class="btn btn-danger">
            <i class="bi bi-broadcast me-1"></i> View Livestream
        </a>
        <?php endif; ?>
        <a href="<?= url('/imbizo') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Session Info -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Session Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th class="ps-0">Date:</th>
                        <td><?= format_date($session['session_date'], 'd F Y') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Time:</th>
                        <td>
                            <?= date('H:i', strtotime($session['start_time'])) ?>
                            <?php if ($session['end_time']): ?>
                            - <?= date('H:i', strtotime($session['end_time'])) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-0">Ward:</th>
                        <td><?= e($session['ward_name'] ?? 'All Wards') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Venue:</th>
                        <td><?= e($session['venue']) ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Created By:</th>
                        <td><?= e($session['first_name'] . ' ' . $session['last_name']) ?></td>
                    </tr>
                </table>

                <?php if ($session['description']): ?>
                <hr>
                <p class="text-muted small mb-0"><?= nl2br(e($session['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Livestream Links -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-broadcast me-1"></i> Livestream Links</h5>
            </div>
            <div class="card-body">
                <?php if ($session['youtube_url']): ?>
                <a href="<?= e($session['youtube_url']) ?>" target="_blank" class="btn btn-outline-danger w-100 mb-2">
                    <i class="bi bi-youtube me-1"></i> YouTube Live
                </a>
                <?php endif; ?>

                <?php if ($session['facebook_url']): ?>
                <a href="<?= e($session['facebook_url']) ?>" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-facebook me-1"></i> Facebook Live
                </a>
                <?php endif; ?>

                <?php if ($session['twitter_url']): ?>
                <a href="<?= e($session['twitter_url']) ?>" target="_blank" class="btn btn-outline-info w-100 mb-2">
                    <i class="bi bi-twitter me-1"></i> Twitter/X
                </a>
                <?php endif; ?>

                <?php if ($session['municipal_stream_url']): ?>
                <a href="<?= e($session['municipal_stream_url']) ?>" target="_blank" class="btn btn-outline-success w-100 mb-2">
                    <i class="bi bi-camera-video me-1"></i> Municipal Stream
                </a>
                <?php endif; ?>

                <?php if (!$session['youtube_url'] && !$session['facebook_url'] && !$session['twitter_url'] && !$session['municipal_stream_url']): ?>
                <p class="text-muted text-center mb-0">No livestream links configured</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Progress Summary</h5>
            </div>
            <div class="card-body">
                <?php
                $total = count($actionItems);
                $completed = count(array_filter($actionItems, fn($i) => $i['status'] === 'completed'));
                $pending = count(array_filter($actionItems, fn($i) => in_array($i['status'], ['pending', 'in_progress'])));
                $overdue = count(array_filter($actionItems, fn($i) => $i['status'] !== 'completed' && $i['target_date'] && $i['target_date'] < date('Y-m-d')));
                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                ?>

                <div class="text-center mb-3">
                    <div class="display-4 text-primary"><?= $progress ?>%</div>
                    <small class="text-muted">Overall Progress</small>
                </div>

                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="h5 mb-0"><?= $total ?></div>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-0 text-success"><?= $completed ?></div>
                        <small class="text-muted">Done</small>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-0 text-warning"><?= $pending ?></div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>

                <?php if ($overdue > 0): ?>
                <div class="alert alert-danger mt-3 mb-0 py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <?= $overdue ?> overdue item(s)
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- AI Minutes -->
<?php if ($session['ai_minutes'] || $session['ai_summary']): ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-robot me-1"></i> AI-Generated Minutes</h5>
        <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>
    <div class="card-body">
        <?php if ($session['ai_summary']): ?>
        <div class="alert alert-info">
            <h6 class="alert-heading">Summary</h6>
            <?= nl2br(e($session['ai_summary'])) ?>
        </div>
        <?php endif; ?>

        <?php if ($session['ai_minutes']): ?>
        <pre class="bg-light p-3 rounded" style="white-space: pre-wrap;"><?= e($session['ai_minutes']) ?></pre>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Action Items -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Action Items (<?= count($actionItems) ?>)</h5>
        <?php if ($session['status'] === 'completed' && has_role('admin', 'director')): ?>
        <button class="btn btn-sm btn-outline-primary" onclick="generateMinutes(<?= $session['id'] ?>)">
            <i class="bi bi-robot me-1"></i> Generate AI Minutes
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($actionItems)): ?>
        <div class="text-center py-4">
            <i class="bi bi-clipboard-check text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mt-2 mb-0">No action items captured for this session.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item #</th>
                        <th>Description</th>
                        <th>Assigned To</th>
                        <th>Target Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actionItems as $item): ?>
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
                        <td>
                            <?= e(substr($item['description'], 0, 80)) ?>
                            <?= strlen($item['description']) > 80 ? '...' : '' ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= e($item['directorate_code'] ?? 'Unassigned') ?></span>
                        </td>
                        <td>
                            <?php if ($item['target_date']): ?>
                            <?= format_date($item['target_date'], 'd M Y') ?>
                            <?php if ($isOverdue): ?>
                            <i class="bi bi-exclamation-triangle text-danger"></i>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">TBD</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $priorityBadge ?>"><?= ucfirst($item['priority']) ?></span></td>
                        <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></span></td>
                        <td style="min-width: 100px;">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: <?= $item['progress_percentage'] ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $item['progress_percentage'] ?>%</small>
                        </td>
                        <td>
                            <a href="<?= url('/imbizo/action-items/' . $item['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
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

<script>
function generateMinutes(sessionId) {
    if (!confirm('Generate AI minutes for this session?')) return;

    fetch('<?= url('/imbizo/') ?>' + sessionId + '/generate-minutes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?= csrf_token() ?>'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Minutes generated successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to generate minutes'));
        }
    })
    .catch(err => alert('Error: ' + err.message));
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
