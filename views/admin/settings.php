<?php
$pageTitle = $title ?? 'System Settings';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/admin') ?>">Admin</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">System Settings</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Municipality Settings</h5>
            </div>
            <div class="card-body">
                <form action="<?= url('/admin/settings') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Municipality Name</label>
                            <input type="text" name="municipality_name" class="form-control"
                                   value="<?= e((defined('MUNICIPALITY_NAME') ? MUNICIPALITY_NAME : 'Sample Municipality')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Municipality Code</label>
                            <input type="text" name="municipality_code" class="form-control"
                                   value="<?= e((defined('MUNICIPALITY_CODE') ? MUNICIPALITY_CODE : 'DC99')) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Province</label>
                        <select name="province" class="form-select">
                            <?php
                            $provinces = ['Eastern Cape', 'Free State', 'Gauteng', 'KwaZulu-Natal', 'Limpopo', 'Mpumalanga', 'North West', 'Northern Cape', 'Western Cape'];
                            $currentProvince = (defined('PROVINCE') ? PROVINCE : 'Gauteng');
                            foreach ($provinces as $prov): ?>
                            <option value="<?= $prov ?>" <?= $currentProvince == $prov ? 'selected' : '' ?>>
                                <?= $prov ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Performance Rating Weights</h5>
            </div>
            <div class="card-body">
                <form action="<?= url('/admin/settings') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Self Rating Weight</label>
                            <div class="input-group">
                                <input type="number" name="rating_self_weight" class="form-control"
                                       value="<?= (defined('RATING_SELF_WEIGHT') ? RATING_SELF_WEIGHT : 0.20) * 100 ?>" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Manager Rating Weight</label>
                            <div class="input-group">
                                <input type="number" name="rating_manager_weight" class="form-control"
                                       value="<?= (defined('RATING_MANAGER_WEIGHT') ? RATING_MANAGER_WEIGHT : 0.40) * 100 ?>" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Independent Rating Weight</label>
                            <div class="input-group">
                                <input type="number" name="rating_independent_weight" class="form-control"
                                       value="<?= (defined('RATING_INDEPENDENT_WEIGHT') ? RATING_INDEPENDENT_WEIGHT : 0.40) * 100 ?>" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Weights must total 100%. Current formula: <br>
                        <code>Aggregated = (Self x 20%) + (Manager x 40%) + (Independent x 40%)</code>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Weights</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">OpenAI Integration</h5>
            </div>
            <div class="card-body">
                <?php if ((defined('OPENAI_API_KEY') && OPENAI_API_KEY)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    OpenAI API key is configured. AI reports are enabled.
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    OpenAI API key is not configured. AI reports are disabled.
                </div>
                <p>To enable AI-powered reports, add your OpenAI API key to the <code>.env</code> file:</p>
                <pre class="bg-light p-3 rounded">OPENAI_API_KEY=sk-your-api-key-here</pre>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th class="ps-0">PHP Version:</th>
                        <td><?= PHP_VERSION ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Server:</th>
                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Database:</th>
                        <td>MySQL <?= (defined('DB_PORT') ? DB_PORT : '3306') ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Environment:</th>
                        <td>
                            <span class="badge bg-<?= (defined('APP_ENV') ? APP_ENV : 'development') == 'production' ? 'success' : 'warning' ?>">
                                <?= ucfirst((defined('APP_ENV') ? APP_ENV : 'development')) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= url('/admin/users') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> User Management
                </a>
                <a href="<?= url('/admin/directorates') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-building me-2"></i> Directorates
                </a>
                <a href="<?= url('/admin/financial-years') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-calendar3 me-2"></i> Financial Years
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
