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

            <!-- Livestream Configuration -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-broadcast me-2"></i>Livestream Configuration</h5>
                    <span class="badge bg-info">Multi-Platform Broadcasting</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">
                        Connect your social media accounts to broadcast the Imbizo session live. Enter your credentials and click Connect to authenticate each platform.
                    </p>

                    <!-- YouTube Configuration -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-youtube text-danger me-2"></i><strong>YouTube Live</strong></span>
                                <span class="badge bg-secondary" id="youtube-status">Not Connected</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="youtube_username" class="form-control form-control-sm"
                                           placeholder="Channel Email / Username" id="youtube-username">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" name="youtube_password" class="form-control form-control-sm"
                                           placeholder="Password / API Key" id="youtube-password">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="connectPlatform('youtube')">
                                        <i class="bi bi-plug me-1"></i>Connect
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="youtube_url" id="youtube-url">
                            <input type="hidden" name="youtube_connected" id="youtube-connected" value="0">
                        </div>
                    </div>

                    <!-- Facebook Configuration -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-facebook text-primary me-2"></i><strong>Facebook Live</strong></span>
                                <span class="badge bg-secondary" id="facebook-status">Not Connected</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="facebook_username" class="form-control form-control-sm"
                                           placeholder="Page Email / Username" id="facebook-username">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" name="facebook_password" class="form-control form-control-sm"
                                           placeholder="Password / Access Token" id="facebook-password">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="connectPlatform('facebook')">
                                        <i class="bi bi-plug me-1"></i>Connect
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="facebook_url" id="facebook-url">
                            <input type="hidden" name="facebook_connected" id="facebook-connected" value="0">
                        </div>
                    </div>

                    <!-- Twitter/X Configuration -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-twitter-x me-2"></i><strong>Twitter/X Spaces</strong></span>
                                <span class="badge bg-secondary" id="twitter-status">Not Connected</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="twitter_username" class="form-control form-control-sm"
                                           placeholder="@username" id="twitter-username">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" name="twitter_password" class="form-control form-control-sm"
                                           placeholder="Password / Bearer Token" id="twitter-password">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-dark btn-sm w-100" onclick="connectPlatform('twitter')">
                                        <i class="bi bi-plug me-1"></i>Connect
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="twitter_url" id="twitter-url">
                            <input type="hidden" name="twitter_connected" id="twitter-connected" value="0">
                        </div>
                    </div>

                    <!-- Municipal Stream Configuration -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-camera-video text-success me-2"></i><strong>Municipal Stream</strong></span>
                                <span class="badge bg-secondary" id="municipal-status">Not Connected</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="municipal_username" class="form-control form-control-sm"
                                           placeholder="Admin Username" id="municipal-username">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" name="municipal_password" class="form-control form-control-sm"
                                           placeholder="Password / Stream Key" id="municipal-password">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success btn-sm w-100" onclick="connectPlatform('municipal')">
                                        <i class="bi bi-plug me-1"></i>Connect
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="municipal_stream_url" id="municipal-url">
                            <input type="hidden" name="municipal_connected" id="municipal-connected" value="0">
                        </div>
                    </div>

                    <!-- Connection Summary -->
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Connected Platforms:</strong>
                                <span id="connected-count">0</span> of 4
                                <div class="small text-muted">At least one platform required for live broadcasting</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media Comments Extraction -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Live Comments Extraction</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="extractComments()">
                        <i class="bi bi-arrow-repeat me-1"></i>Auto-Extract Comments
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Automatically extract and aggregate comments from all connected social media platforms during the live session.
                    </p>

                    <!-- Comments Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <select class="form-select form-select-sm" id="commentPlatformFilter" onchange="filterComments()">
                                <option value="all">All Platforms</option>
                                <option value="youtube">YouTube</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter/X</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Search comments..." id="commentSearch" onkeyup="filterComments()">
                                <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Comments List -->
                    <div id="commentsContainer" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center text-muted py-4" id="noCommentsMsg">
                            <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No comments yet. Connect platforms and click "Auto-Extract" to fetch comments.</p>
                        </div>
                        <div id="commentsList" class="d-none"></div>
                    </div>

                    <!-- Comments Stats -->
                    <div class="row mt-3 pt-3 border-top d-none" id="commentsStats">
                        <div class="col-4 text-center">
                            <h5 class="mb-0 text-danger" id="youtubeCommentCount">0</h5>
                            <small class="text-muted">YouTube</small>
                        </div>
                        <div class="col-4 text-center">
                            <h5 class="mb-0 text-primary" id="facebookCommentCount">0</h5>
                            <small class="text-muted">Facebook</small>
                        </div>
                        <div class="col-4 text-center">
                            <h5 class="mb-0 text-dark" id="twitterCommentCount">0</h5>
                            <small class="text-muted">Twitter/X</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Minutes & Notes Capture -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Minutes & Presentation Notes</h5>
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm me-1" onclick="generateAIMinutes()">
                            <i class="bi bi-robot me-1"></i>AI Generate
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearMinutes()">
                            <i class="bi bi-eraser me-1"></i>Clear
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Minutes Sections -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opening Remarks</label>
                        <textarea name="minutes_opening" class="form-control" rows="2" id="minutesOpening"
                                  placeholder="Mayor's opening address and welcome..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Key Discussion Points</label>
                        <textarea name="minutes_discussion" class="form-control" rows="4" id="minutesDiscussion"
                                  placeholder="Main topics discussed during the session..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Community Concerns Raised</label>
                        <textarea name="minutes_concerns" class="form-control" rows="3" id="minutesConcerns"
                                  placeholder="Issues and concerns raised by community members..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mayoral Commitments</label>
                        <textarea name="minutes_commitments" class="form-control" rows="3" id="minutesCommitments"
                                  placeholder="Promises and commitments made by the Mayor..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Closing Remarks</label>
                        <textarea name="minutes_closing" class="form-control" rows="2" id="minutesClosing"
                                  placeholder="Closing statements and next steps..."></textarea>
                    </div>

                    <!-- Auto-save indicator -->
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="bi bi-cloud-check me-1"></i>Auto-saved</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewMinutes()">
                            <i class="bi bi-eye me-1"></i>Preview Full Minutes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-task me-2"></i>Action Items</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addActionItemModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Action Item
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="actionItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 30%">Action Item</th>
                                    <th style="width: 15%">Priority</th>
                                    <th style="width: 25%">Assignment</th>
                                    <th style="width: 15%">Target Date</th>
                                    <th style="width: 10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="actionItemsBody">
                                <!-- Sample Action Items will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center py-4 d-none" id="noActionItems">
                        <i class="bi bi-clipboard-check text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No action items yet. Click "Add Action Item" to create one.</p>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="row text-center">
                        <div class="col-3">
                            <span class="badge bg-danger rounded-pill" id="highPriorityCount">0</span>
                            <small class="d-block text-muted">High</small>
                        </div>
                        <div class="col-3">
                            <span class="badge bg-warning rounded-pill" id="mediumPriorityCount">0</span>
                            <small class="d-block text-muted">Medium</small>
                        </div>
                        <div class="col-3">
                            <span class="badge bg-info rounded-pill" id="lowPriorityCount">0</span>
                            <small class="d-block text-muted">Low</small>
                        </div>
                        <div class="col-3">
                            <span class="badge bg-success rounded-pill" id="assignedCount">0</span>
                            <small class="d-block text-muted">Assigned</small>
                        </div>
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

<!-- Add Action Item Modal -->
<div class="modal fade" id="addActionItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Action Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Action Item Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="newActionDescription" rows="3" placeholder="Describe the action item..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" id="newActionPriority">
                            <option value="high">High Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" class="form-control" id="newActionTargetDate">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Community Concern (Source)</label>
                    <input type="text" class="form-control" id="newActionConcern" placeholder="Original concern from community...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addActionItem()">
                    <i class="bi bi-plus-lg me-1"></i>Add Action Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Assign Action Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assignActionId">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="assignActionDescription"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Directorate <span class="text-danger">*</span></label>
                    <select class="form-select" id="assignDirectorate" onchange="loadDepartments()">
                        <option value="">Select Directorate...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="assignDepartment" onchange="loadEmployees()">
                        <option value="">Select Department...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Responsible Employee <span class="text-danger">*</span></label>
                    <select class="form-select" id="assignEmployee">
                        <option value="">Select Employee...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assignment Notes</label>
                    <textarea class="form-control" id="assignNotes" rows="2" placeholder="Additional instructions..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmAssignment()">
                    <i class="bi bi-check-lg me-1"></i>Confirm Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Minutes Preview Modal -->
<div class="modal fade" id="minutesPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Minutes Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="minutesPreviewContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadMinutes()">
                    <i class="bi bi-download me-1"></i>Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('wardSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('wardName').value = selected.dataset.name || '';
});

// ============================================
// SAMPLE DATA
// ============================================
const sampleDirectorates = [
    { id: 1, name: 'Infrastructure & Engineering', code: 'INF' },
    { id: 2, name: 'Community Services', code: 'COM' },
    { id: 3, name: 'Corporate Services', code: 'COR' },
    { id: 4, name: 'Finance', code: 'FIN' },
    { id: 5, name: 'Planning & Development', code: 'PLD' },
    { id: 6, name: 'Public Safety', code: 'SAF' }
];

const sampleDepartments = {
    1: [
        { id: 101, name: 'Roads & Stormwater' },
        { id: 102, name: 'Water & Sanitation' },
        { id: 103, name: 'Electricity' },
        { id: 104, name: 'Building Maintenance' }
    ],
    2: [
        { id: 201, name: 'Parks & Recreation' },
        { id: 202, name: 'Libraries' },
        { id: 203, name: 'Community Halls' },
        { id: 204, name: 'Social Development' }
    ],
    3: [
        { id: 301, name: 'Human Resources' },
        { id: 302, name: 'ICT' },
        { id: 303, name: 'Legal Services' },
        { id: 304, name: 'Communications' }
    ],
    4: [
        { id: 401, name: 'Budget & Treasury' },
        { id: 402, name: 'Revenue' },
        { id: 403, name: 'Supply Chain' },
        { id: 404, name: 'Expenditure' }
    ],
    5: [
        { id: 501, name: 'Town Planning' },
        { id: 502, name: 'Housing' },
        { id: 503, name: 'Economic Development' },
        { id: 504, name: 'Environmental Management' }
    ],
    6: [
        { id: 601, name: 'Traffic Services' },
        { id: 602, name: 'Fire & Rescue' },
        { id: 603, name: 'Disaster Management' },
        { id: 604, name: 'Security Services' }
    ]
};

const sampleEmployees = {
    101: [
        { id: 1001, name: 'Thabo Molefe', title: 'Senior Engineer' },
        { id: 1002, name: 'Sarah Johnson', title: 'Project Manager' }
    ],
    102: [
        { id: 1003, name: 'David Nkosi', title: 'Water Services Manager' },
        { id: 1004, name: 'Lindiwe Dlamini', title: 'Senior Technician' }
    ],
    103: [
        { id: 1005, name: 'Johan van der Berg', title: 'Electrical Engineer' },
        { id: 1006, name: 'Nomsa Khumalo', title: 'Network Supervisor' }
    ],
    201: [
        { id: 2001, name: 'Peter Mahlangu', title: 'Parks Supervisor' },
        { id: 2002, name: 'Grace Sithole', title: 'Environmental Officer' }
    ],
    202: [
        { id: 2003, name: 'Mary Botha', title: 'Chief Librarian' }
    ],
    301: [
        { id: 3001, name: 'Amanda Zulu', title: 'HR Manager' },
        { id: 3002, name: 'James Pillay', title: 'Training Officer' }
    ],
    401: [
        { id: 4001, name: 'Michelle Ndlovu', title: 'CFO' },
        { id: 4002, name: 'Robert Mokoena', title: 'Budget Analyst' }
    ],
    501: [
        { id: 5001, name: 'Steven Mabaso', title: 'Town Planner' },
        { id: 5002, name: 'Fatima Adams', title: 'Housing Officer' }
    ],
    601: [
        { id: 6001, name: 'Captain Bongani Cele', title: 'Traffic Chief' },
        { id: 6002, name: 'Fire Chief Lucas Pretorius', title: 'Fire Chief' }
    ]
};

const sampleComments = [
    { platform: 'youtube', user: 'MzansiCitizen', text: 'When will the potholes on Main Road be fixed? Its been 6 months!', time: '2 mins ago', sentiment: 'negative' },
    { platform: 'youtube', user: 'ThaboM', text: 'Thank you Mayor for coming to our ward. We appreciate it!', time: '5 mins ago', sentiment: 'positive' },
    { platform: 'facebook', user: 'Lindiwe Nkosi', text: 'The water pressure in Extension 4 is very low. Please help!', time: '3 mins ago', sentiment: 'negative' },
    { platform: 'facebook', user: 'John Peters', text: 'Great initiative! More of these community engagements please.', time: '7 mins ago', sentiment: 'positive' },
    { platform: 'twitter', user: '@WardCommittee5', text: 'Electricity outages every week in our area. Need urgent attention! #ImbizoWard5', time: '1 min ago', sentiment: 'negative' },
    { platform: 'twitter', user: '@MayorOfficial', text: 'Taking note of all concerns. Action will be taken! #ServiceDelivery', time: '4 mins ago', sentiment: 'positive' },
    { platform: 'youtube', user: 'ConcernedResident', text: 'Street lights not working for 3 months in Section B', time: '8 mins ago', sentiment: 'negative' },
    { platform: 'facebook', user: 'Mama Africa', text: 'Please prioritize the clinic - we need more nurses', time: '6 mins ago', sentiment: 'neutral' }
];

const sampleActionItems = [
    { id: 1, description: 'Repair potholes on Main Road between 5th and 12th Avenue', priority: 'high', targetDate: '2026-02-15', concern: 'Multiple complaints about road damage', assigned: null },
    { id: 2, description: 'Investigate low water pressure in Extension 4 and submit report', priority: 'high', targetDate: '2026-02-10', concern: 'Residents experiencing water supply issues', assigned: null },
    { id: 3, description: 'Replace non-functional street lights in Section B (15 units)', priority: 'medium', targetDate: '2026-02-28', concern: 'Safety concerns due to dark streets', assigned: null },
    { id: 4, description: 'Schedule electricity infrastructure maintenance for Ward 5', priority: 'high', targetDate: '2026-02-12', concern: 'Frequent power outages affecting residents', assigned: { directorate: 'Infrastructure & Engineering', department: 'Electricity', employee: 'Johan van der Berg' } },
    { id: 5, description: 'Coordinate with Health Department on clinic staffing needs', priority: 'medium', targetDate: '2026-03-01', concern: 'Shortage of nursing staff at local clinic', assigned: null }
];

let actionItems = [...sampleActionItems];
let actionItemCounter = sampleActionItems.length;

// ============================================
// PLATFORM CONNECTION
// ============================================
let connectedPlatforms = 0;

function connectPlatform(platform) {
    const username = document.getElementById(`${platform}-username`).value;
    const password = document.getElementById(`${platform}-password`).value;
    const statusBadge = document.getElementById(`${platform}-status`);
    const connectedInput = document.getElementById(`${platform}-connected`);
    const urlInput = document.getElementById(`${platform}-url`);

    // Validate inputs
    if (!username || !password) {
        alert(`Please enter both username and password for ${platform.charAt(0).toUpperCase() + platform.slice(1)}`);
        return;
    }

    // Show connecting state
    statusBadge.textContent = 'Connecting...';
    statusBadge.className = 'badge bg-warning';

    // Simulate API connection (in production, this would be an actual API call)
    setTimeout(() => {
        // Simulate successful connection
        const isConnected = true; // In production: response from API

        if (isConnected) {
            statusBadge.textContent = 'Connected';
            statusBadge.className = 'badge bg-success';
            connectedInput.value = '1';

            // Generate placeholder stream URL based on platform
            const streamUrls = {
                youtube: `https://youtube.com/live/${generateStreamId()}`,
                facebook: `https://facebook.com/live/${generateStreamId()}`,
                twitter: `https://twitter.com/i/spaces/${generateStreamId()}`,
                municipal: `https://stream.municipality.gov.za/live/${generateStreamId()}`
            };
            urlInput.value = streamUrls[platform];

            // Disable inputs after connection
            document.getElementById(`${platform}-username`).readOnly = true;
            document.getElementById(`${platform}-password`).readOnly = true;

            // Change button to disconnect
            const btn = event.target.closest('button');
            btn.innerHTML = '<i class="bi bi-plug-fill me-1"></i>Disconnect';
            btn.onclick = () => disconnectPlatform(platform);

            connectedPlatforms++;
            updateConnectedCount();

        } else {
            statusBadge.textContent = 'Failed';
            statusBadge.className = 'badge bg-danger';
            setTimeout(() => {
                statusBadge.textContent = 'Not Connected';
                statusBadge.className = 'badge bg-secondary';
            }, 2000);
        }
    }, 1500);
}

function disconnectPlatform(platform) {
    const statusBadge = document.getElementById(`${platform}-status`);
    const connectedInput = document.getElementById(`${platform}-connected`);
    const urlInput = document.getElementById(`${platform}-url`);

    // Reset status
    statusBadge.textContent = 'Not Connected';
    statusBadge.className = 'badge bg-secondary';
    connectedInput.value = '0';
    urlInput.value = '';

    // Enable inputs
    document.getElementById(`${platform}-username`).readOnly = false;
    document.getElementById(`${platform}-password`).readOnly = false;
    document.getElementById(`${platform}-password`).value = '';

    // Change button back to connect
    const btn = event.target.closest('button');
    const btnColors = {
        youtube: 'btn-danger',
        facebook: 'btn-primary',
        twitter: 'btn-dark',
        municipal: 'btn-success'
    };
    btn.className = `btn ${btnColors[platform]} btn-sm w-100`;
    btn.innerHTML = '<i class="bi bi-plug me-1"></i>Connect';
    btn.onclick = () => connectPlatform(platform);

    connectedPlatforms--;
    updateConnectedCount();
}

function updateConnectedCount() {
    document.getElementById('connected-count').textContent = connectedPlatforms;
}

function generateStreamId() {
    return Math.random().toString(36).substring(2, 15);
}

// ============================================
// COMMENTS EXTRACTION
// ============================================
let allComments = [];

function extractComments() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Extracting...';

    // Simulate API call to extract comments
    setTimeout(() => {
        allComments = [...sampleComments];
        renderComments(allComments);
        updateCommentStats();

        document.getElementById('noCommentsMsg').classList.add('d-none');
        document.getElementById('commentsList').classList.remove('d-none');
        document.getElementById('commentsStats').classList.remove('d-none');

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Refresh Comments';
    }, 2000);
}

function renderComments(comments) {
    const container = document.getElementById('commentsList');
    container.innerHTML = comments.map(c => `
        <div class="comment-item border-bottom py-2" data-platform="${c.platform}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex">
                    <span class="me-2">
                        ${getPlatformIcon(c.platform)}
                    </span>
                    <div>
                        <strong class="small">${c.user}</strong>
                        <p class="mb-0 small">${c.text}</p>
                    </div>
                </div>
                <div class="text-end">
                    <small class="text-muted">${c.time}</small>
                    <br>
                    ${getSentimentBadge(c.sentiment)}
                </div>
            </div>
            <div class="mt-1">
                <button class="btn btn-outline-primary btn-sm py-0 px-2" onclick="createActionFromComment('${c.text.replace(/'/g, "\\'")}')">
                    <i class="bi bi-plus-lg"></i> Create Action
                </button>
            </div>
        </div>
    `).join('');
}

function getPlatformIcon(platform) {
    const icons = {
        youtube: '<i class="bi bi-youtube text-danger"></i>',
        facebook: '<i class="bi bi-facebook text-primary"></i>',
        twitter: '<i class="bi bi-twitter-x"></i>'
    };
    return icons[platform] || '';
}

function getSentimentBadge(sentiment) {
    const badges = {
        positive: '<span class="badge bg-success">Positive</span>',
        negative: '<span class="badge bg-danger">Negative</span>',
        neutral: '<span class="badge bg-secondary">Neutral</span>'
    };
    return badges[sentiment] || '';
}

function updateCommentStats() {
    document.getElementById('youtubeCommentCount').textContent = allComments.filter(c => c.platform === 'youtube').length;
    document.getElementById('facebookCommentCount').textContent = allComments.filter(c => c.platform === 'facebook').length;
    document.getElementById('twitterCommentCount').textContent = allComments.filter(c => c.platform === 'twitter').length;
}

function filterComments() {
    const platform = document.getElementById('commentPlatformFilter').value;
    const search = document.getElementById('commentSearch').value.toLowerCase();

    let filtered = allComments;

    if (platform !== 'all') {
        filtered = filtered.filter(c => c.platform === platform);
    }

    if (search) {
        filtered = filtered.filter(c => c.text.toLowerCase().includes(search) || c.user.toLowerCase().includes(search));
    }

    renderComments(filtered);
}

function createActionFromComment(text) {
    document.getElementById('newActionDescription').value = `Address community concern: "${text}"`;
    document.getElementById('newActionConcern').value = text;
    document.getElementById('newActionPriority').value = 'medium';
    new bootstrap.Modal(document.getElementById('addActionItemModal')).show();
}

// ============================================
// MINUTES MANAGEMENT
// ============================================
function generateAIMinutes() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';

    setTimeout(() => {
        // Populate with AI-generated sample content
        document.getElementById('minutesOpening').value = `The Mayor welcomed all community members to the Ward 5 Mayoral Imbizo session held at the Community Hall. The Mayor emphasized the municipality's commitment to improving service delivery and addressing community concerns directly.`;

        document.getElementById('minutesDiscussion').value = `1. Infrastructure Challenges
- Road conditions in the area require urgent attention
- Water supply issues affecting multiple sections
- Electricity reliability concerns raised by residents

2. Community Safety
- Street lighting improvements needed in Section B
- Request for increased visible policing

3. Social Services
- Healthcare facility staffing shortages
- Youth development programs requested`;

        document.getElementById('minutesConcerns').value = `• Potholes on Main Road between 5th and 12th Avenue causing vehicle damage
• Low water pressure in Extension 4 affecting daily activities
• Frequent electricity outages disrupting businesses and households
• Non-functional street lights creating safety risks
• Clinic understaffed, long waiting times for patients`;

        document.getElementById('minutesCommitments').value = `The Mayor committed to:
1. Fast-tracking pothole repairs within 2 weeks
2. Deploying a technical team to investigate water pressure issues
3. Scheduling preventive maintenance on electricity infrastructure
4. Replacing all non-functional street lights within 30 days
5. Engaging with the Department of Health on clinic staffing`;

        document.getElementById('minutesClosing').value = `The Mayor thanked all residents for their participation and assured them that their concerns have been noted and will be addressed. A follow-up Imbizo will be scheduled within 60 days to report on progress.`;

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-robot me-1"></i>AI Generate';
    }, 2500);
}

function clearMinutes() {
    if (confirm('Are you sure you want to clear all minutes?')) {
        document.getElementById('minutesOpening').value = '';
        document.getElementById('minutesDiscussion').value = '';
        document.getElementById('minutesConcerns').value = '';
        document.getElementById('minutesCommitments').value = '';
        document.getElementById('minutesClosing').value = '';
    }
}

function previewMinutes() {
    const preview = `
        <div class="border-bottom pb-3 mb-3">
            <h4 class="text-center">MAYORAL IMBIZO MINUTES</h4>
            <p class="text-center text-muted">Ward 5 Community Engagement Session</p>
            <p class="text-center small">${new Date().toLocaleDateString('en-ZA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
        </div>

        <h6 class="text-primary"><i class="bi bi-mic me-1"></i>Opening Remarks</h6>
        <p class="small">${document.getElementById('minutesOpening').value || '<em>Not captured</em>'}</p>

        <h6 class="text-primary mt-3"><i class="bi bi-chat-dots me-1"></i>Key Discussion Points</h6>
        <p class="small" style="white-space: pre-line;">${document.getElementById('minutesDiscussion').value || '<em>Not captured</em>'}</p>

        <h6 class="text-primary mt-3"><i class="bi bi-exclamation-triangle me-1"></i>Community Concerns Raised</h6>
        <p class="small" style="white-space: pre-line;">${document.getElementById('minutesConcerns').value || '<em>Not captured</em>'}</p>

        <h6 class="text-primary mt-3"><i class="bi bi-hand-thumbs-up me-1"></i>Mayoral Commitments</h6>
        <p class="small" style="white-space: pre-line;">${document.getElementById('minutesCommitments').value || '<em>Not captured</em>'}</p>

        <h6 class="text-primary mt-3"><i class="bi bi-flag me-1"></i>Closing Remarks</h6>
        <p class="small">${document.getElementById('minutesClosing').value || '<em>Not captured</em>'}</p>

        <div class="border-top pt-3 mt-3">
            <p class="small text-muted mb-0"><strong>Action Items:</strong> ${actionItems.length} items recorded</p>
            <p class="small text-muted mb-0"><strong>Assigned:</strong> ${actionItems.filter(a => a.assigned).length} items assigned to directorates</p>
        </div>
    `;

    document.getElementById('minutesPreviewContent').innerHTML = preview;
    new bootstrap.Modal(document.getElementById('minutesPreviewModal')).show();
}

function downloadMinutes() {
    alert('Minutes PDF download would be triggered here. In production, this would generate a PDF document.');
}

// ============================================
// ACTION ITEMS MANAGEMENT
// ============================================
function renderActionItems() {
    const tbody = document.getElementById('actionItemsBody');
    const noItems = document.getElementById('noActionItems');

    if (actionItems.length === 0) {
        tbody.innerHTML = '';
        noItems.classList.remove('d-none');
        document.querySelector('#actionItemsTable').closest('.table-responsive').classList.add('d-none');
    } else {
        noItems.classList.add('d-none');
        document.querySelector('#actionItemsTable').closest('.table-responsive').classList.remove('d-none');

        tbody.innerHTML = actionItems.map((item, idx) => `
            <tr>
                <td><strong>${idx + 1}</strong></td>
                <td>
                    <span class="small">${item.description}</span>
                    ${item.concern ? `<br><small class="text-muted"><i class="bi bi-quote me-1"></i>${item.concern.substring(0, 50)}...</small>` : ''}
                </td>
                <td>
                    <span class="badge bg-${item.priority === 'high' ? 'danger' : item.priority === 'medium' ? 'warning' : 'info'}">
                        ${item.priority.charAt(0).toUpperCase() + item.priority.slice(1)}
                    </span>
                </td>
                <td>
                    ${item.assigned ? `
                        <small class="d-block"><strong>${item.assigned.directorate}</strong></small>
                        <small class="d-block text-muted">${item.assigned.department}</small>
                        <small class="text-primary"><i class="bi bi-person me-1"></i>${item.assigned.employee}</small>
                    ` : `
                        <button class="btn btn-outline-success btn-sm" onclick="openAssignModal(${item.id})">
                            <i class="bi bi-person-plus me-1"></i>Assign
                        </button>
                    `}
                </td>
                <td><small>${item.targetDate || 'Not set'}</small></td>
                <td>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeActionItem(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    updateActionStats();
}

function updateActionStats() {
    document.getElementById('highPriorityCount').textContent = actionItems.filter(a => a.priority === 'high').length;
    document.getElementById('mediumPriorityCount').textContent = actionItems.filter(a => a.priority === 'medium').length;
    document.getElementById('lowPriorityCount').textContent = actionItems.filter(a => a.priority === 'low').length;
    document.getElementById('assignedCount').textContent = actionItems.filter(a => a.assigned).length;
}

function addActionItem() {
    const description = document.getElementById('newActionDescription').value;
    const priority = document.getElementById('newActionPriority').value;
    const targetDate = document.getElementById('newActionTargetDate').value;
    const concern = document.getElementById('newActionConcern').value;

    if (!description) {
        alert('Please enter an action item description');
        return;
    }

    actionItemCounter++;
    actionItems.push({
        id: actionItemCounter,
        description,
        priority,
        targetDate,
        concern,
        assigned: null
    });

    renderActionItems();
    bootstrap.Modal.getInstance(document.getElementById('addActionItemModal')).hide();

    // Clear form
    document.getElementById('newActionDescription').value = '';
    document.getElementById('newActionPriority').value = 'medium';
    document.getElementById('newActionTargetDate').value = '';
    document.getElementById('newActionConcern').value = '';
}

function removeActionItem(id) {
    if (confirm('Are you sure you want to remove this action item?')) {
        actionItems = actionItems.filter(a => a.id !== id);
        renderActionItems();
    }
}

// ============================================
// ASSIGNMENT MANAGEMENT
// ============================================
function openAssignModal(actionId) {
    const action = actionItems.find(a => a.id === actionId);
    if (!action) return;

    document.getElementById('assignActionId').value = actionId;
    document.getElementById('assignActionDescription').textContent = action.description;

    // Populate directorates
    const dirSelect = document.getElementById('assignDirectorate');
    dirSelect.innerHTML = '<option value="">Select Directorate...</option>' +
        sampleDirectorates.map(d => `<option value="${d.id}">${d.name}</option>`).join('');

    document.getElementById('assignDepartment').innerHTML = '<option value="">Select Department...</option>';
    document.getElementById('assignEmployee').innerHTML = '<option value="">Select Employee...</option>';
    document.getElementById('assignNotes').value = '';

    new bootstrap.Modal(document.getElementById('assignmentModal')).show();
}

function loadDepartments() {
    const dirId = document.getElementById('assignDirectorate').value;
    const deptSelect = document.getElementById('assignDepartment');
    const empSelect = document.getElementById('assignEmployee');

    deptSelect.innerHTML = '<option value="">Select Department...</option>';
    empSelect.innerHTML = '<option value="">Select Employee...</option>';

    if (dirId && sampleDepartments[dirId]) {
        deptSelect.innerHTML += sampleDepartments[dirId].map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    }
}

function loadEmployees() {
    const deptId = document.getElementById('assignDepartment').value;
    const empSelect = document.getElementById('assignEmployee');

    empSelect.innerHTML = '<option value="">Select Employee...</option>';

    if (deptId && sampleEmployees[deptId]) {
        empSelect.innerHTML += sampleEmployees[deptId].map(e => `<option value="${e.id}">${e.name} - ${e.title}</option>`).join('');
    }
}

function confirmAssignment() {
    const actionId = parseInt(document.getElementById('assignActionId').value);
    const dirSelect = document.getElementById('assignDirectorate');
    const deptSelect = document.getElementById('assignDepartment');
    const empSelect = document.getElementById('assignEmployee');

    if (!dirSelect.value || !deptSelect.value || !empSelect.value) {
        alert('Please select Directorate, Department, and Employee');
        return;
    }

    const action = actionItems.find(a => a.id === actionId);
    if (action) {
        action.assigned = {
            directorate: dirSelect.options[dirSelect.selectedIndex].text,
            department: deptSelect.options[deptSelect.selectedIndex].text,
            employee: empSelect.options[empSelect.selectedIndex].text.split(' - ')[0]
        };
    }

    renderActionItems();
    bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    renderActionItems();
});
</script>

<style>
.comment-item {
    transition: background-color 0.2s;
}
.comment-item:hover {
    background-color: #f8f9fa;
}
#actionItemsTable tbody tr:hover {
    background-color: #f0f7ff;
}
.card-header h5 {
    font-size: 1rem;
}
.modal-body .form-label {
    font-weight: 500;
    font-size: 0.875rem;
}
</style>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
