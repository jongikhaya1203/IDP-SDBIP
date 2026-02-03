<?php
$pageTitle = $title ?? 'Integrations';
ob_start();

// Load integration settings
$integrationSettings = $settings ?? [];
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
        <p class="text-muted mb-0">Configure third-party services and API connections</p>
    </div>
</div>

<?php if (isset($_SESSION['flash'])): ?>
<div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<!-- Integration Cards -->
<div class="row">
    <!-- Email (SMTP) Configuration -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 <?= !empty($integrationSettings['smtp_host']) ? 'border-success' : '' ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-envelope-fill text-primary me-2"></i>
                    <strong>Email (SMTP)</strong>
                </div>
                <?php if (!empty($integrationSettings['smtp_host'])): ?>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Configured</span>
                <?php else: ?>
                <span class="badge bg-secondary">Not Configured</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted">Configure SMTP server for sending email notifications, alerts, and reports.</p>

                <form method="POST" action="/cpanel/integrations/smtp">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                            <input type="text" name="smtp_host" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['smtp_host'] ?? '') ?>"
                                   placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Port</label>
                            <select name="smtp_port" class="form-select">
                                <option value="587" <?= ($integrationSettings['smtp_port'] ?? '') == '587' ? 'selected' : '' ?>>587 (TLS)</option>
                                <option value="465" <?= ($integrationSettings['smtp_port'] ?? '') == '465' ? 'selected' : '' ?>>465 (SSL)</option>
                                <option value="25" <?= ($integrationSettings['smtp_port'] ?? '') == '25' ? 'selected' : '' ?>>25 (Plain)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="smtp_username" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['smtp_username'] ?? '') ?>"
                                   placeholder="your@email.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="smtp_password" class="form-control"
                                   value="<?= !empty($integrationSettings['smtp_password']) ? '••••••••' : '' ?>"
                                   placeholder="App password">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Email</label>
                            <input type="email" name="smtp_from_email" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['smtp_from_email'] ?? '') ?>"
                                   placeholder="noreply@municipality.gov.za">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Name</label>
                            <input type="text" name="smtp_from_name" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['smtp_from_name'] ?? '') ?>"
                                   placeholder="SDBIP System">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Encryption</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="smtp_encryption" id="encTls" value="tls"
                                   <?= ($integrationSettings['smtp_encryption'] ?? 'tls') == 'tls' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="encTls">TLS</label>
                            <input type="radio" class="btn-check" name="smtp_encryption" id="encSsl" value="ssl"
                                   <?= ($integrationSettings['smtp_encryption'] ?? '') == 'ssl' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="encSsl">SSL</label>
                            <input type="radio" class="btn-check" name="smtp_encryption" id="encNone" value="none"
                                   <?= ($integrationSettings['smtp_encryption'] ?? '') == 'none' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="encNone">None</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save SMTP Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testSmtp()">
                            <i class="bi bi-send me-1"></i>Test Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- LDAP / Active Directory Configuration -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 <?= !empty($integrationSettings['ldap_enabled']) ? 'border-success' : '' ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-shield-lock-fill text-warning me-2"></i>
                    <strong>LDAP / Active Directory</strong>
                </div>
                <?php if (!empty($integrationSettings['ldap_enabled'])): ?>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Enabled</span>
                <?php else: ?>
                <span class="badge bg-secondary">Disabled</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted">Connect to your organization's Active Directory for single sign-on authentication.</p>

                <form method="POST" action="/cpanel/integrations/ldap">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="ldap_enabled" id="ldapEnabled"
                               <?= !empty($integrationSettings['ldap_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ldapEnabled">Enable LDAP Authentication</label>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">LDAP Host</label>
                            <input type="text" name="ldap_host" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['ldap_host'] ?? '') ?>"
                                   placeholder="ldap://dc.municipality.gov.za">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Port</label>
                            <select name="ldap_port" class="form-select">
                                <option value="389" <?= ($integrationSettings['ldap_port'] ?? '') == '389' ? 'selected' : '' ?>>389 (LDAP)</option>
                                <option value="636" <?= ($integrationSettings['ldap_port'] ?? '') == '636' ? 'selected' : '' ?>>636 (LDAPS)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Base DN</label>
                        <input type="text" name="ldap_base_dn" class="form-control"
                               value="<?= htmlspecialchars($integrationSettings['ldap_base_dn'] ?? '') ?>"
                               placeholder="DC=municipality,DC=gov,DC=za">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bind DN (Service Account)</label>
                        <input type="text" name="ldap_bind_dn" class="form-control"
                               value="<?= htmlspecialchars($integrationSettings['ldap_bind_dn'] ?? '') ?>"
                               placeholder="CN=sdbip_service,OU=ServiceAccounts,DC=municipality,DC=gov,DC=za">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bind Password</label>
                        <input type="password" name="ldap_bind_password" class="form-control"
                               value="<?= !empty($integrationSettings['ldap_bind_password']) ? '••••••••' : '' ?>"
                               placeholder="Service account password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Search Filter</label>
                        <input type="text" name="ldap_user_filter" class="form-control"
                               value="<?= htmlspecialchars($integrationSettings['ldap_user_filter'] ?? '(sAMAccountName=%s)') ?>"
                               placeholder="(sAMAccountName=%s)">
                    </div>

                    <div class="alert alert-info py-2">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Users will be automatically created on first LDAP login. Local accounts remain as fallback.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save LDAP Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testLdap()">
                            <i class="bi bi-plug me-1"></i>Test Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SMS Gateway Configuration -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 <?= !empty($integrationSettings['sms_enabled']) ? 'border-success' : '' ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-phone-fill text-info me-2"></i>
                    <strong>SMS Gateway</strong>
                </div>
                <?php if (!empty($integrationSettings['sms_enabled'])): ?>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Enabled</span>
                <?php else: ?>
                <span class="badge bg-secondary">Disabled</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted">Send SMS notifications for urgent alerts, deadline reminders, and approvals.</p>

                <form method="POST" action="/cpanel/integrations/sms">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="sms_enabled" id="smsEnabled"
                               <?= !empty($integrationSettings['sms_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="smsEnabled">Enable SMS Notifications</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">SMS Provider</label>
                        <select name="sms_provider" class="form-select">
                            <option value="">-- Select Provider --</option>
                            <option value="clickatell" <?= ($integrationSettings['sms_provider'] ?? '') == 'clickatell' ? 'selected' : '' ?>>Clickatell</option>
                            <option value="bulksms" <?= ($integrationSettings['sms_provider'] ?? '') == 'bulksms' ? 'selected' : '' ?>>BulkSMS</option>
                            <option value="africas_talking" <?= ($integrationSettings['sms_provider'] ?? '') == 'africas_talking' ? 'selected' : '' ?>>Africa's Talking</option>
                            <option value="twilio" <?= ($integrationSettings['sms_provider'] ?? '') == 'twilio' ? 'selected' : '' ?>>Twilio</option>
                            <option value="vodacom" <?= ($integrationSettings['sms_provider'] ?? '') == 'vodacom' ? 'selected' : '' ?>>Vodacom Messaging API</option>
                            <option value="custom" <?= ($integrationSettings['sms_provider'] ?? '') == 'custom' ? 'selected' : '' ?>>Custom API</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key / Token</label>
                        <input type="password" name="sms_api_key" class="form-control"
                               value="<?= !empty($integrationSettings['sms_api_key']) ? '••••••••' : '' ?>"
                               placeholder="Your SMS provider API key">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Secret (if required)</label>
                            <input type="password" name="sms_api_secret" class="form-control"
                                   value="<?= !empty($integrationSettings['sms_api_secret']) ? '••••••••' : '' ?>"
                                   placeholder="API Secret">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sender ID</label>
                            <input type="text" name="sms_sender_id" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['sms_sender_id'] ?? '') ?>"
                                   placeholder="SDBIP" maxlength="11">
                        </div>
                    </div>
                    <div class="mb-3" id="customApiUrl" style="display: none;">
                        <label class="form-label">Custom API URL</label>
                        <input type="url" name="sms_api_url" class="form-control"
                               value="<?= htmlspecialchars($integrationSettings['sms_api_url'] ?? '') ?>"
                               placeholder="https://api.provider.com/sms/send">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notification Types</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sms_notify_deadlines" id="smsDeadlines"
                                           <?= !empty($integrationSettings['sms_notify_deadlines']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="smsDeadlines">Deadline Reminders</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sms_notify_approvals" id="smsApprovals"
                                           <?= !empty($integrationSettings['sms_notify_approvals']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="smsApprovals">Approval Requests</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sms_notify_alerts" id="smsAlerts"
                                           <?= !empty($integrationSettings['sms_notify_alerts']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="smsAlerts">Critical Alerts</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sms_notify_imbizo" id="smsImbizo"
                                           <?= !empty($integrationSettings['sms_notify_imbizo']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="smsImbizo">Imbizo Notifications</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save SMS Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testSms()">
                            <i class="bi bi-phone me-1"></i>Send Test SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- OpenAI GPT-4 Configuration -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100 <?= !empty($integrationSettings['openai_api_key']) ? 'border-success' : '' ?>">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-robot text-success me-2"></i>
                    <strong>OpenAI GPT-4</strong>
                </div>
                <?php if (!empty($integrationSettings['openai_api_key'])): ?>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Connected</span>
                <?php else: ?>
                <span class="badge bg-secondary">Not Configured</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted">Enable AI-powered report generation, performance insights, and recommendations.</p>

                <form method="POST" action="/cpanel/integrations/openai">
                    <div class="mb-3">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" name="openai_api_key" id="openaiKey" class="form-control"
                                   value="<?= !empty($integrationSettings['openai_api_key']) ? '••••••••••••••••••••' : '' ?>"
                                   placeholder="sk-...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('openaiKey')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <select name="openai_model" class="form-select">
                                <option value="gpt-4" <?= ($integrationSettings['openai_model'] ?? '') == 'gpt-4' ? 'selected' : '' ?>>GPT-4 (Recommended)</option>
                                <option value="gpt-4-turbo" <?= ($integrationSettings['openai_model'] ?? '') == 'gpt-4-turbo' ? 'selected' : '' ?>>GPT-4 Turbo</option>
                                <option value="gpt-4o" <?= ($integrationSettings['openai_model'] ?? '') == 'gpt-4o' ? 'selected' : '' ?>>GPT-4o (Latest)</option>
                                <option value="gpt-3.5-turbo" <?= ($integrationSettings['openai_model'] ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' ?>>GPT-3.5 Turbo (Faster/Cheaper)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Tokens</label>
                            <input type="number" name="openai_max_tokens" class="form-control"
                                   value="<?= htmlspecialchars($integrationSettings['openai_max_tokens'] ?? '4000') ?>"
                                   min="500" max="8000" step="500">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Temperature (Creativity)</label>
                        <input type="range" name="openai_temperature" class="form-range"
                               min="0" max="1" step="0.1"
                               value="<?= htmlspecialchars($integrationSettings['openai_temperature'] ?? '0.7') ?>"
                               oninput="document.getElementById('tempValue').textContent = this.value">
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Precise (0)</span>
                            <span id="tempValue"><?= $integrationSettings['openai_temperature'] ?? '0.7' ?></span>
                            <span>Creative (1)</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">AI Features</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ai_quarterly_reports" id="aiQuarterly"
                                           <?= ($integrationSettings['ai_quarterly_reports'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aiQuarterly">Quarterly Reports</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ai_recommendations" id="aiRecommend"
                                           <?= ($integrationSettings['ai_recommendations'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aiRecommend">Recommendations</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ai_risk_analysis" id="aiRisk"
                                           <?= ($integrationSettings['ai_risk_analysis'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aiRisk">Risk Analysis</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ai_trend_detection" id="aiTrend"
                                           <?= ($integrationSettings['ai_trend_detection'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aiTrend">Trend Detection</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>OpenAI API usage incurs costs. Monitor your usage at <a href="https://platform.openai.com/usage" target="_blank">OpenAI Dashboard</a>.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save OpenAI Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="testOpenAI()">
                            <i class="bi bi-robot me-1"></i>Test API
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Social Media / Livestream Platforms -->
<div class="card mt-2">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-broadcast me-2"></i>Imbizo Livestream Platforms</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Supported platforms for Mayoral IDP Imbizo livestreaming. URLs are configured per session.</p>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body py-3">
                        <i class="bi bi-youtube text-danger" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-0">YouTube Live</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body py-3">
                        <i class="bi bi-facebook text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-0">Facebook Live</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body py-3">
                        <i class="bi bi-twitter-x" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-0">X (Twitter)</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border text-center">
                    <div class="card-body py-3">
                        <i class="bi bi-camera-video text-info" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-0">Custom RTMP</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Connection Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-lightning me-2"></i>Test Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="testResult" class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Testing...</span>
                    </div>
                    <p class="mb-0">Testing connection...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Show/hide custom API URL based on provider selection
document.querySelector('select[name="sms_provider"]')?.addEventListener('change', function() {
    document.getElementById('customApiUrl').style.display = this.value === 'custom' ? 'block' : 'none';
});

// Test functions
function testSmtp() {
    showTestModal();
    fetch('/cpanel/integrations/test-smtp', { method: 'POST' })
        .then(r => r.json())
        .then(data => showTestResult(data.success, data.message))
        .catch(() => showTestResult(false, 'Connection failed'));
}

function testLdap() {
    showTestModal();
    fetch('/cpanel/integrations/test-ldap', { method: 'POST' })
        .then(r => r.json())
        .then(data => showTestResult(data.success, data.message))
        .catch(() => showTestResult(false, 'Connection failed'));
}

function testSms() {
    const phone = prompt('Enter test phone number (e.g., +27831234567):');
    if (!phone) return;

    showTestModal();
    fetch('/cpanel/integrations/test-sms', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: phone })
    })
        .then(r => r.json())
        .then(data => showTestResult(data.success, data.message))
        .catch(() => showTestResult(false, 'Failed to send test SMS'));
}

function testOpenAI() {
    showTestModal();
    fetch('/cpanel/integrations/test-openai', { method: 'POST' })
        .then(r => r.json())
        .then(data => showTestResult(data.success, data.message))
        .catch(() => showTestResult(false, 'API connection failed'));
}

function showTestModal() {
    document.getElementById('testResult').innerHTML = `
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Testing...</span>
        </div>
        <p class="mb-0">Testing connection...</p>
    `;
    new bootstrap.Modal(document.getElementById('testModal')).show();
}

function showTestResult(success, message) {
    const icon = success ? 'check-circle-fill text-success' : 'x-circle-fill text-danger';
    document.getElementById('testResult').innerHTML = `
        <i class="bi bi-${icon}" style="font-size: 3rem;"></i>
        <p class="mt-3 mb-0">${message}</p>
    `;
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
