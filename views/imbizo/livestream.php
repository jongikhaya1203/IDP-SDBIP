<?php
$pageTitle = $title ?? 'Livestream';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-broadcast text-danger me-2"></i>
            <?= e($session['title']) ?>
        </h1>
        <p class="text-muted mb-0">
            <?= format_date($session['session_date'], 'd F Y') ?> |
            <?= e($session['ward_name'] ?? 'All Wards') ?> |
            <?= e($session['venue']) ?>
        </p>
    </div>
    <div>
        <?php if ($session['status'] === 'scheduled'): ?>
        <button id="startLiveBtn" class="btn btn-danger btn-lg" onclick="startLive()">
            <i class="bi bi-broadcast me-1"></i> Start Live Session
        </button>
        <?php elseif ($session['status'] === 'live'): ?>
        <span class="badge bg-danger fs-5 me-2">
            <i class="bi bi-broadcast me-1"></i> LIVE
        </span>
        <button id="endLiveBtn" class="btn btn-secondary" onclick="endLive()">
            <i class="bi bi-stop-circle me-1"></i> End Session
        </button>
        <?php else: ?>
        <span class="badge bg-success fs-5">Session Completed</span>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Livestream Embed -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Live Broadcast</h5>
            </div>
            <div class="card-body p-0">
                <?php if ($session['youtube_url'] && strpos($session['youtube_url'], 'youtube.com') !== false): ?>
                <?php
                preg_match('/[?&]v=([^&]+)/', $session['youtube_url'], $matches);
                $videoId = $matches[1] ?? '';
                if (strpos($session['youtube_url'], 'live/') !== false) {
                    $videoId = basename(parse_url($session['youtube_url'], PHP_URL_PATH));
                }
                ?>
                <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=1"
                            allowfullscreen allow="autoplay"></iframe>
                </div>
                <?php else: ?>
                <div class="bg-dark text-white text-center py-5" style="min-height: 400px;">
                    <i class="bi bi-camera-video-off" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Live Stream Configured</h4>
                    <p class="text-muted">Add a YouTube Live URL to embed the stream here.</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($session['youtube_url']): ?>
                    <a href="<?= e($session['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-youtube"></i> YouTube
                    </a>
                    <?php endif; ?>
                    <?php if ($session['facebook_url']): ?>
                    <a href="<?= e($session['facebook_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                    <?php endif; ?>
                    <?php if ($session['twitter_url']): ?>
                    <a href="<?= e($session['twitter_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-twitter"></i> Twitter
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Item Capture -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-1"></i> Capture Action Item</h5>
            </div>
            <div class="card-body">
                <form id="actionItemForm">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="ward_name" value="<?= e($session['ward_name'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label">Community Concern</label>
                        <textarea name="community_concern" class="form-control" rows="2"
                                  placeholder="What issue was raised?"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action Item <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required
                                  placeholder="Describe the action to be taken..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mayor's Commitment</label>
                        <textarea name="commitment" class="form-control" rows="2"
                                  placeholder="What did the Mayor commit to?"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assign to Directorate</label>
                        <select name="directorate_id" class="form-select">
                            <option value="">Select Directorate</option>
                            <?php foreach ($directorates as $dir): ?>
                            <option value="<?= $dir['id'] ?>"><?= e($dir['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Target Date</label>
                            <input type="date" name="target_date" class="form-control"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i> Add Action Item
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Captured Action Items -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Captured Action Items</h5>
        <span class="badge bg-primary" id="itemCount">0</span>
    </div>
    <div class="card-body" id="actionItemsList">
        <div class="text-center py-4 text-muted" id="noItemsMsg">
            <i class="bi bi-clipboard"></i> No action items captured yet
        </div>
    </div>
</div>

<script>
const sessionId = <?= $session['id'] ?>;
let actionItems = [];

// Load existing items
loadActionItems();

function loadActionItems() {
    fetch('<?= url('/imbizo/' . $session['id']) ?>')
        .then(r => r.text())
        .then(html => {
            // Parse and update the list
            updateItemCount();
        });
}

function updateItemCount() {
    document.getElementById('itemCount').textContent = actionItems.length;
    if (actionItems.length > 0) {
        document.getElementById('noItemsMsg').style.display = 'none';
    }
}

function addItemToList(item) {
    actionItems.push(item);
    document.getElementById('noItemsMsg').style.display = 'none';

    const list = document.getElementById('actionItemsList');
    const div = document.createElement('div');
    div.className = 'border-bottom pb-3 mb-3';
    div.innerHTML = `
        <div class="d-flex justify-content-between">
            <strong>${item.item_number}</strong>
            <span class="badge bg-${item.priority === 'high' ? 'danger' : item.priority === 'medium' ? 'warning' : 'info'}">${item.priority}</span>
        </div>
        <p class="mb-1">${item.description}</p>
        <small class="text-muted">
            ${item.directorate_name || 'Unassigned'} |
            Target: ${item.target_date || 'TBD'}
        </small>
    `;
    list.appendChild(div);
    updateItemCount();
}

document.getElementById('actionItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('<?= url('/imbizo/') ?>' + sessionId + '/action-items', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            addItemToList(data.item);
            this.reset();
        } else {
            alert('Error: ' + (data.error || 'Failed to add item'));
        }
    })
    .catch(err => alert('Error: ' + err.message));
});

function startLive() {
    fetch('<?= url('/imbizo/') ?>' + sessionId + '/start-live', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error: ' + data.error);
    });
}

function endLive() {
    if (!confirm('End this live session?')) return;
    fetch('<?= url('/imbizo/') ?>' + sessionId + '/end-live', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.href = '<?= url('/imbizo/' . $session['id']) ?>';
        else alert('Error: ' + data.error);
    });
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
