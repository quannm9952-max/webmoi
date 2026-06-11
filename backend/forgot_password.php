<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/PasswordController.php';

$controller = new PasswordController(db_connect());
$controller->handleForgot();
$data = $controller->forgotViewData();

$error = $data['error'];
$success = $data['success'];
$demoResetLink = $data['demoResetLink'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu — TechShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<main class="auth-page">
    <div class="auth-card">
        <a href="<?= BASE_URL ?>/shop.php" class="brand-logo">
            <span class="brand-icon">T</span>
            <span>Tech<span>Shop</span></span>
        </a>

        <h2>Quên mật khẩu</h2>
        <p class="auth-subtitle mb-4">Nhập email tài khoản để tạo link đặt lại mật khẩu.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= h($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success rounded-4">
                <i class="bi bi-check-circle-fill me-2"></i><?= h($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($demoResetLink): ?>
            <div class="alert alert-info rounded-4">
                <div class="fw-800 mb-2">Link reset demo local:</div>
                <a href="<?= h($demoResetLink) ?>" class="text-break"><?= h($demoResetLink) ?></a>
                <div class="small text-muted mt-2">Khi deploy thật, link này nên được gửi qua email.</div>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-envelope me-1 text-primary"></i>Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>

            <button class="btn btn-primary w-100 btn-lg">
                <i class="bi bi-send me-2"></i>Tạo link đặt lại mật khẩu
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/login.php" class="fw-800">
                <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
            </a>
        </div>
    </div>
</main>
</body>
</html>
