<?php
$pageTitle = $title ?? 'Integrations';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/cpanel') ?>">Control Panel</a></li>
                <li class="breadcrumb-item active">Integrations</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-plug me-2"></i>Integrations</h1>
        <p class="text-muted mb-0">Third-party service connections and APIs</p>
    </div>
</div>

<div class="row">
    <?php foreach ($integrations as $integration): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100 <?= $integration['status'] === 'active' ? 'border-success' : '' ?>">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="bg-<?= $integration['status'] === 'active' ? 'success' : 'secondary' ?> text-white rounded p-3 me-3">
                        <i class="bi bi-<?= $integration['icon'] ?>" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="mb-1"><?= e($integration['name']) ?></h5>
                            <?php if ($integration['status'] === 'active'): ?>
                            <span class="badge bg-success">Connected</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Not Configured</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted mb-3"><?= e($integration['description']) ?></p>

                        <?php if ($integration['status'] === 'active'): ?>
                        <div class="alert alert-success py-2 mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            Integration is active and working.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-light py-2 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Set <code><?= e($integration['config_key']) ?></code> in <code>.env</code> file to enable.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Configuration Guide -->
<div class="card mt-2">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Configuration Guide</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>OpenAI (GPT-4) Setup</h6>
                <ol class="small">
                    <li>Create an account at <a href="https://platform.openai.com" target="_blank">platform.openai.com</a></li>
                    <li>Generate an API key in your dashboard</li>
                    <li>Add to your <code>.env</code> file:
                        <pre class="bg-light p-2 rounded mt-1">OPENAI_API_KEY=sk-your-key-here</pre>
                    </li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6>LDAP / Active Directory Setup</h6>
                <ol class="small">
                    <li>Configure your LDAP server details</li>
                    <li>Add to your <code>.env</code> file:
                        <pre class="bg-light p-2 rounded mt-1">LDAP_ENABLED=true
LDAP_HOST=ldap://your-server
LDAP_BASE_DN=DC=domain,DC=com</pre>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Social Media Integration for Imbizo -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-broadcast me-2"></i>Imbizo Livestream Platforms</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Configure social media platforms for Mayoral IDP Imbizo livestreaming.</p>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <i class="bi bi-youtube text-danger" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">YouTube Live</h6>
                        <span class="badge bg-success">Supported</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <i class="bi bi-facebook text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">Facebook Live</h6>
                        <span class="badge bg-success">Supported</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-body text-center">
                        <i class="bi bi-twitter-x" style="font-size: 2rem;"></i>
                        <h6 class="mt-2">X (Twitter) Live</h6>
                        <span class="badge bg-success">Supported</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Livestream URLs are configured per Imbizo session. The system supports embedding from major social platforms.
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
