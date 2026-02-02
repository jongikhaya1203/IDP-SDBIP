<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 2rem;
            text-align: center;
            color: #fff;
        }
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .login-header p {
            color: #94a3b8;
            font-size: 0.875rem;
            margin: 0;
        }
        .login-body {
            padding: 2rem;
        }
        .form-floating > .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            height: 56px;
        }
        .form-floating > .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .form-floating > label {
            padding: 1rem 0.875rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
        .municipality-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .login-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: #64748b;
            font-size: 0.75rem;
        }
        .sa-flag {
            display: inline-flex;
            gap: 2px;
            margin-right: 0.5rem;
        }
        .sa-flag span {
            width: 4px;
            height: 12px;
            display: inline-block;
        }
        .sa-flag .red { background: #DE3831; }
        .sa-flag .white { background: #fff; border: 1px solid #ddd; }
        .sa-flag .blue { background: #002395; }
        .sa-flag .green { background: #007A4D; }
        .sa-flag .yellow { background: #FFB612; }
        .sa-flag .black { background: #000; }

        .ldap-badge {
            background: #f0fdf4;
            color: #166534;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="municipality-badge">
                <span class="sa-flag">
                    <span class="red"></span>
                    <span class="white"></span>
                    <span class="green"></span>
                    <span class="yellow"></span>
                    <span class="black"></span>
                    <span class="blue"></span>
                </span>
                <?= MUNICIPALITY_CODE ?>
            </div>
            <h1><i class="bi bi-building me-2"></i><?= MUNICIPALITY_NAME ?></h1>
            <p>Service Delivery & Budget Implementation Plan</p>
        </div>

        <div class="login-body">
            <?php if ($error = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2">
                <i class="bi bi-exclamation-circle me-2"></i><?= e($error) ?>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($success = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show py-2">
                <i class="bi bi-check-circle me-2"></i><?= e($success) ?>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <?= csrf_field() ?>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="Username" value="<?= e(old('username')) ?>" required autofocus>
                    <label for="username"><i class="bi bi-person me-2"></i>Username</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Password" required>
                    <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>

                <?php if (LDAP_ENABLED): ?>
                <div class="text-center">
                    <span class="ldap-badge">
                        <i class="bi bi-shield-check"></i>
                        Active Directory Authentication Enabled
                    </span>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="login-footer">
            <p class="mb-1">SDBIP & IDP Management System</p>
            <p class="mb-0">Compliant with MFMA & National Treasury Regulations</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
