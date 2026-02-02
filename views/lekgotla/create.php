<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Lekgotla Session</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="/lekgotla/store">
                        <div class="mb-3">
                            <label class="form-label">Session Name <span class="text-danger">*</span></label>
                            <input type="text" name="session_name" class="form-control" required
                                   placeholder="e.g., Q2 Mayoral Lekgotla - Priority Review">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Session Date <span class="text-danger">*</span></label>
                                <input type="date" name="session_date" class="form-control" required
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control"
                                       placeholder="e.g., Municipal Council Chambers">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Presided By</label>
                            <input type="text" name="presided_by" class="form-control"
                                   value="Municipal Mayor" placeholder="e.g., Municipal Mayor">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link to Imbizo Session</label>
                            <select name="linked_imbizo_id" class="form-select">
                                <option value="">-- Select Imbizo Session (Optional) --</option>
                                <?php foreach ($imbizoSessions as $imbizo): ?>
                                    <option value="<?= $imbizo['id'] ?>">
                                        <?= htmlspecialchars($imbizo['session_title']) ?>
                                        (<?= date('d M Y', strtotime($imbizo['session_date'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Link this Lekgotla to an Imbizo session to import community commitments</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>What happens next?</strong>
                            <p class="mb-0 mt-2">After creating this session, you can:</p>
                            <ul class="mb-0 mt-1">
                                <li>Review existing IDP priorities and mark them as "Retain", "Modify", or "Discard"</li>
                                <li>Add new priorities based on Imbizo commitments</li>
                                <li>Track budget impact of all changes</li>
                                <li>Generate resolutions for Council approval</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/lekgotla" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Create Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
