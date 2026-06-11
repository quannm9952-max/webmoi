<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController(db_connect());
$auth->handleLogin();

$error   = flash('error');
$success = flash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — TechShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-card">
    <a href="<?= BASE_URL ?>/shop.php" class="brand-logo">
        <span class="brand-icon">T</span>
        <span>Tech<span>Shop</span></span>
    </a>

    <h2>Đăng nhập</h2>
    <p class="auth-subtitle">Chào mừng trở lại! Vui lòng đăng nhập để tiếp tục.</p>

    <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i><?= h($success) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i><?= h($error) ?>
    </div>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/google_login.php" class="btn-google mb-3">
        <svg width="18" height="18" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Đăng nhập với Google
    </a>

    <div class="auth-divider">hoặc đăng nhập bằng email</div>

    <form method="post" novalidate>
        <div class="mb-3">
            <label class="form-label" for="email">
                <i class="bi bi-envelope me-1 text-primary"></i>Email
            </label>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="your@email.com"
                   value="<?= old('email') ?>" required autocomplete="email">
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">
                <i class="bi bi-lock me-1 text-primary"></i>Mật khẩu
            </label>
            <div class="position-relative">
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Nhập mật khẩu" required autocomplete="current-password"
                       style="padding-right:44px">
                <button type="button" class="btn position-absolute end-0 top-0 h-100 px-3 text-muted"
                        onclick="togglePwd(this)" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= BASE_URL ?>/forgot_password.php" class="small fw-800">Quên mật khẩu?</a>
            </div>

<button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
        </button>
    </form>

    <div class="text-center mt-4 text-muted" style="font-size:14px">
        Chưa có tài khoản?
        <a href="<?= BASE_URL ?>/register.php" class="fw-700 text-primary">Đăng ký ngay</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd(btn) {
    const input = btn.previousElementSibling;
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
