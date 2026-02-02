<?php
$pageTitle = $title ?? 'Quick Start Guide';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/training') ?>">Training</a></li>
                <li class="breadcrumb-item active">Quick Start</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-rocket-takeoff me-2"></i>Quick Start Guide</h1>
        <p class="text-muted mb-0">Get up and running in minutes</p>
    </div>
    <a href="<?= url('/training') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<!-- Progress Steps -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-1-circle-fill fs-4"></i>
                </div>
                <p class="small mt-2 mb-0">Login</p>
            </div>
            <div class="col">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-2-circle-fill fs-4"></i>
                </div>
                <p class="small mt-2 mb-0">Dashboard</p>
            </div>
            <div class="col">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-3-circle-fill fs-4"></i>
                </div>
                <p class="small mt-2 mb-0">View KPIs</p>
            </div>
            <div class="col">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-4-circle-fill fs-4"></i>
                </div>
                <p class="small mt-2 mb-0">Assessment</p>
            </div>
            <div class="col">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-check-lg fs-4"></i>
                </div>
                <p class="small mt-2 mb-0">Complete!</p>
            </div>
        </div>
    </div>
</div>

<!-- Step by Step Guide -->
<div class="row">
    <div class="col-lg-8">
        <!-- Step 1 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><span class="badge bg-white text-primary me-2">1</span> Login to the System</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <ol>
                            <li>Open your web browser and navigate to the system URL</li>
                            <li>Enter your <strong>username</strong> and <strong>password</strong></li>
                            <li>Click the <strong>"Sign In"</strong> button</li>
                            <li>You'll be redirected to your Dashboard</li>
                        </ol>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            If using LDAP/Active Directory, use your network credentials.
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 5rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><span class="badge bg-white text-primary me-2">2</span> Explore Your Dashboard</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p>Your dashboard shows:</p>
                        <ul>
                            <li><strong>KPI Summary:</strong> Total, achieved, pending KPIs</li>
                            <li><strong>Quarterly Progress:</strong> Current quarter status</li>
                            <li><strong>Recent Activity:</strong> Latest updates and notifications</li>
                            <li><strong>Pending Tasks:</strong> Items requiring your attention</li>
                        </ul>
                        <p class="mb-0">Use the sidebar menu to navigate to different modules.</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-speedometer2 text-primary" style="font-size: 5rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><span class="badge bg-white text-primary me-2">3</span> View Your KPIs</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <ol>
                            <li>Click <strong>"SDBIP"</strong> in the sidebar</li>
                            <li>Select <strong>"KPIs"</strong> to see all Key Performance Indicators</li>
                            <li>Use filters to find specific KPIs by directorate or status</li>
                            <li>Click on a KPI to view details, targets, and history</li>
                        </ol>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-lightbulb me-2"></i>
                            KPIs assigned to you will appear in your personal dashboard.
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-graph-up-arrow text-primary" style="font-size: 5rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><span class="badge bg-white text-primary me-2">4</span> Complete Your Assessment</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <ol>
                            <li>Go to <strong>Assessment → Self Assessment</strong></li>
                            <li>Select the current quarter</li>
                            <li>For each KPI:
                                <ul>
                                    <li>Enter your <strong>actual achievement</strong></li>
                                    <li>Select a <strong>self-rating</strong> (1-5)</li>
                                    <li>Add <strong>comments</strong> to justify your rating</li>
                                    <li>Upload <strong>Proof of Evidence</strong></li>
                                </ul>
                            </li>
                            <li>Click <strong>"Submit for Review"</strong></li>
                        </ol>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-clipboard-check text-primary" style="font-size: 5rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> You're All Set!</h5>
            </div>
            <div class="card-body">
                <p>Congratulations! You now know the basics of using the SDBIP/IDP Management System.</p>
                <h6>Next Steps:</h6>
                <ul>
                    <li>Explore the <a href="<?= url('/training') ?>">Training Modules</a> for in-depth guides</li>
                    <li>Check the <a href="<?= url('/training/faq') ?>">FAQ</a> for common questions</li>
                    <li>Review the <a href="<?= url('/training/glossary') ?>">Glossary</a> for term definitions</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Role Guide -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Your Role</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">What you can do depends on your assigned role:</p>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <span class="badge bg-secondary">Employee</span><br>
                        Self-assessment, POE uploads
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-info">Manager</span><br>
                        + Review team assessments
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-primary">Director</span><br>
                        + Directorate oversight
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-warning text-dark">Assessor</span><br>
                        Independent ratings
                    </li>
                    <li>
                        <span class="badge bg-danger">Admin</span><br>
                        Full system access
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tips -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Quick Tips</h6>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item small">
                    <i class="bi bi-check text-success me-2"></i>
                    Save your work regularly
                </li>
                <li class="list-group-item small">
                    <i class="bi bi-check text-success me-2"></i>
                    Upload clear, dated POE documents
                </li>
                <li class="list-group-item small">
                    <i class="bi bi-check text-success me-2"></i>
                    Submit assessments before deadlines
                </li>
                <li class="list-group-item small">
                    <i class="bi bi-check text-success me-2"></i>
                    Check notifications daily
                </li>
            </ul>
        </div>

        <!-- Help -->
        <div class="card bg-light">
            <div class="card-body text-center">
                <i class="bi bi-question-circle text-primary" style="font-size: 2rem;"></i>
                <h6 class="mt-2">Need Help?</h6>
                <p class="small text-muted mb-2">Check our FAQ or contact your administrator</p>
                <a href="<?= url('/training/faq') ?>" class="btn btn-sm btn-primary">
                    View FAQ
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
