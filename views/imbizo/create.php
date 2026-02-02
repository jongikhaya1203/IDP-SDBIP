<?php
$pageTitle = $title ?? 'Schedule Imbizo';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/imbizo') ?>">Imbizo</a></li>
                <li class="breadcrumb-item active">Schedule New</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Schedule Mayoral Imbizo</h1>
    </div>
</div>

<form action="<?= url('/imbizo') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Session Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Session Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="e.g., Ward 5 Community Engagement - Water Issues">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Brief description of the imbizo session..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="session_date" class="form-control" required
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required value="09:00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="12:00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ward</label>
                            <select name="ward_id" class="form-select" id="wardSelect">
                                <option value="">All Wards / Municipal-wide</option>
                                <?php foreach ($wards as $ward): ?>
                                <option value="<?= $ward['id'] ?>" data-name="<?= e($ward['ward_name']) ?>">
                                    Ward <?= $ward['ward_number'] ?> - <?= e($ward['ward_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="ward_name" id="wardName">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Venue <span class="text-danger">*</span></label>
                            <input type="text" name="venue" class="form-control" required
                                   placeholder="e.g., Community Hall, Ward 5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Livestream Links -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-broadcast me-2"></i>Livestream Configuration</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Add livestream URLs for broadcasting to social media platforms. These links will be displayed
                        during the live session and embedded for viewers.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-youtube text-danger me-1"></i> YouTube Live URL
                        </label>
                        <input type="url" name="youtube_url" class="form-control"
                               placeholder="https://youtube.com/live/...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-facebook text-primary me-1"></i> Facebook Live URL
                        </label>
                        <input type="url" name="facebook_url" class="form-control"
                               placeholder="https://facebook.com/...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-twitter text-info me-1"></i> Twitter/X Space URL
                        </label>
                        <input type="url" name="twitter_url" class="form-control"
                               placeholder="https://twitter.com/...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-camera-video text-success me-1"></i> Municipal Stream URL
                        </label>
                        <input type="url" name="municipal_stream_url" class="form-control"
                               placeholder="https://municipality.gov.za/live/...">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Info Panel -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-1"></i> About Mayoral Imbizo</h6>
                    <p class="small text-muted mb-0">
                        The Mayoral IDP Imbizo is a community engagement platform where the Mayor
                        meets with ward residents to address concerns and make commitments. Action items
                        from these sessions are tracked and assigned to relevant directorates for implementation.
                    </p>
                </div>
            </div>

            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6><i class="bi bi-lightbulb me-1"></i> Features</h6>
                    <ul class="small text-muted mb-0">
                        <li>Multi-platform livestreaming</li>
                        <li>AI-powered minutes capture</li>
                        <li>Action item assignment</li>
                        <li>Directorate response tracking</li>
                        <li>POE attachments</li>
                    </ul>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-calendar-plus me-1"></i> Schedule Imbizo
                </button>
                <a href="<?= url('/imbizo') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('wardSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('wardName').value = selected.dataset.name || '';
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
