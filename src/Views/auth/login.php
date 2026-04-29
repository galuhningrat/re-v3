<?php
$appName = defined('APP_NAME') ? APP_NAME : 'Sistem RS';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= $appName ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2e4a 0%, #2d5986 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            background: #1a2e4a;
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 2rem;
            text-align: center;
        }

        .role-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="login-card card">
        <div class="login-header">
            <i class="bi bi-hospital fs-1 mb-2 d-block"></i>
            <h5 class="fw-bold mb-0"><?= $appName ?></h5>
            <small class="opacity-75">Sistem Manajemen Rumah Sakit</small>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-warning d-flex align-items-center gap-2 py-2">
                    <i class="bi bi-lock-fill"></i>
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $baseUrl ?>/login">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control"
                            value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                            placeholder="Masukkan username" autofocus required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passwordInput"
                            class="form-control" placeholder="Masukkan password" required>
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </button>
            </form>

            <!-- Info akun untuk presentasi -->
            <hr class="my-4">
            <p class="text-muted small text-center mb-2">Akun Demo:</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <div class="text-center small">
                    <span class="role-badge bg-danger text-white">Admin</span>
                    <div class="text-muted mt-1">
                        <code>admin</code> / <code>admin123</code>
                    </div>
                </div>
                <div class="text-center small">
                    <span class="role-badge bg-info text-dark">Dokter</span>
                    <div class="text-muted mt-1">
                        <code>dr.andi</code> / <code>dokter123</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>

</html>