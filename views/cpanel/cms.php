<?php
$pageTitle = $title ?? 'CMS Portal';
$breadcrumbs = [
    ['label' => 'Control Panel', 'url' => '/cpanel'],
    ['label' => 'CMS Portal']
];
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="bi bi-palette me-2"></i>CMS Portal</h1>
        <p class="text-muted mb-0">Customize branding, logo, and site appearance</p>
    </div>
    <a href="/cpanel" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Control Panel
    </a>
</div>

<form action="/cpanel/cms/update" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Left Column - Branding -->
        <div class="col-lg-8">
            <!-- Logo & Favicon -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i>Logo & Favicon</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Logo Upload -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Site Logo</label>
                            <div class="border rounded p-3 text-center bg-light mb-3" style="min-height: 150px;">
                                <?php if (!empty($settings['logo'])): ?>
                                    <img src="<?= $settings['logo'] ?>" alt="Current Logo" class="img-fluid" style="max-height: 120px;">
                                <?php else: ?>
                                    <div class="py-4">
                                        <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mb-0 mt-2">No logo uploaded</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="logo" class="form-control mb-2" accept="image/*" id="logoInput">
                            <small class="text-muted d-block">Recommended: 200x60px, PNG or SVG</small>
                            <?php if (!empty($settings['logo'])): ?>
                                <a href="/cpanel/cms/remove-logo" class="btn btn-outline-danger btn-sm mt-2" onclick="return confirm('Remove current logo?')">
                                    <i class="bi bi-trash me-1"></i>Remove Logo
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Favicon Upload -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Favicon</label>
                            <div class="border rounded p-3 text-center bg-light mb-3" style="min-height: 150px;">
                                <?php if (!empty($settings['favicon'])): ?>
                                    <img src="<?= $settings['favicon'] ?>" alt="Current Favicon" style="width: 64px; height: 64px;">
                                <?php else: ?>
                                    <div class="py-4">
                                        <i class="bi bi-app text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mb-0 mt-2">No favicon uploaded</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="favicon" class="form-control mb-2" accept=".ico,.png,.svg">
                            <small class="text-muted d-block">Recommended: 32x32px or 64x64px, ICO or PNG</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Site Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-card-text me-2"></i>Site Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Site Name <span class="text-danger">*</span></label>
                            <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name']) ?>" required>
                            <small class="text-muted">Displayed in browser tab and header</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Organization Name</label>
                            <input type="text" name="organization_name" class="form-control" value="<?= e($settings['organization_name']) ?>">
                            <small class="text-muted">Municipality or organization name</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Site Tagline</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline']) ?>">
                        <small class="text-muted">Brief description shown below the logo</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Dashboard Title</label>
                        <input type="text" name="dashboard_title" class="form-control" value="<?= e($settings['dashboard_title']) ?>">
                        <small class="text-muted">Main title displayed on the dashboard page</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Footer Text</label>
                        <input type="text" name="footer_text" class="form-control" value="<?= e($settings['footer_text']) ?>">
                        <small class="text-muted">Copyright or footer message</small>
                    </div>
                </div>
            </div>

            <!-- Theme Colors -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-palette2 me-2"></i>Theme Colors</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Primary Color</label>
                            <div class="input-group">
                                <input type="color" name="primary_color" class="form-control form-control-color" value="<?= e($settings['primary_color']) ?>" id="primaryColor">
                                <input type="text" class="form-control" value="<?= e($settings['primary_color']) ?>" id="primaryColorText" readonly>
                            </div>
                            <small class="text-muted">Main brand color for buttons and accents</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= e($settings['secondary_color']) ?>" id="secondaryColor">
                                <input type="text" class="form-control" value="<?= e($settings['secondary_color']) ?>" id="secondaryColorText" readonly>
                            </div>
                            <small class="text-muted">Secondary color for text and borders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Preview -->
        <div class="col-lg-4">
            <!-- Live Preview -->
            <div class="card mb-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Live Preview</h5>
                </div>
                <div class="card-body p-0">
                    <!-- Preview Header -->
                    <div class="p-3 border-bottom" id="previewHeader" style="background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);">
                        <div class="d-flex align-items-center">
                            <div id="previewLogo" class="me-2">
                                <?php if (!empty($settings['logo'])): ?>
                                    <img src="<?= $settings['logo'] ?>" alt="Logo" style="max-height: 40px;">
                                <?php else: ?>
                                    <i class="bi bi-building text-white" style="font-size: 1.5rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="text-white fw-bold small" id="previewSiteName"><?= e($settings['site_name']) ?></div>
                                <div class="text-white-50" style="font-size: 0.7rem;" id="previewTagline"><?= e($settings['site_tagline']) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Dashboard -->
                    <div class="p-3">
                        <h6 class="mb-3" id="previewDashboardTitle">
                            <span class="preview-primary-color" style="color: <?= e($settings['primary_color']) ?>;">
                                <?= e($settings['dashboard_title']) ?>
                            </span>
                        </h6>

                        <!-- Sample Stats -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="rounded p-2 text-white text-center preview-primary-bg" style="background-color: <?= e($settings['primary_color']) ?>;">
                                    <div style="font-size: 1.2rem; font-weight: bold;">24</div>
                                    <div style="font-size: 0.65rem;">KPIs</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="rounded p-2 text-white text-center" style="background-color: #10b981;">
                                    <div style="font-size: 1.2rem; font-weight: bold;">85%</div>
                                    <div style="font-size: 0.65rem;">Progress</div>
                                </div>
                            </div>
                        </div>

                        <!-- Sample Button -->
                        <button class="btn btn-sm w-100 text-white preview-primary-bg" style="background-color: <?= e($settings['primary_color']) ?>; border: none;">
                            <i class="bi bi-plus me-1"></i>Sample Button
                        </button>
                    </div>

                    <!-- Preview Footer -->
                    <div class="p-2 bg-light border-top text-center">
                        <small class="text-muted" id="previewFooter" style="font-size: 0.65rem;"><?= e($settings['footer_text']) ?></small>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card bg-light">
                <div class="card-body">
                    <h6><i class="bi bi-lightbulb me-1"></i>Tips</h6>
                    <ul class="small text-muted mb-0">
                        <li>Use PNG or SVG for logos with transparency</li>
                        <li>Keep logo dimensions around 200x60px</li>
                        <li>Choose colors that meet accessibility standards</li>
                        <li>Changes take effect immediately after saving</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>Changes will apply across the entire application
                        </span>
                        <div>
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-lg me-1"></i>Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Live preview updates
document.querySelector('input[name="site_name"]').addEventListener('input', function() {
    document.getElementById('previewSiteName').textContent = this.value || 'Site Name';
});

document.querySelector('input[name="site_tagline"]').addEventListener('input', function() {
    document.getElementById('previewTagline').textContent = this.value || 'Tagline';
});

document.querySelector('input[name="dashboard_title"]').addEventListener('input', function() {
    document.getElementById('previewDashboardTitle').innerHTML = '<span class="preview-primary-color" style="color: ' + document.getElementById('primaryColor').value + ';">' + (this.value || 'Dashboard') + '</span>';
});

document.querySelector('input[name="footer_text"]').addEventListener('input', function() {
    document.getElementById('previewFooter').textContent = this.value || 'Footer Text';
});

// Color pickers
document.getElementById('primaryColor').addEventListener('input', function() {
    document.getElementById('primaryColorText').value = this.value;
    document.querySelectorAll('.preview-primary-bg').forEach(el => {
        el.style.backgroundColor = this.value;
    });
    document.querySelectorAll('.preview-primary-color').forEach(el => {
        el.style.color = this.value;
    });
});

document.getElementById('secondaryColor').addEventListener('input', function() {
    document.getElementById('secondaryColorText').value = this.value;
});

// Logo preview
document.getElementById('logoInput').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewLogo').innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview" style="max-height: 40px;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
