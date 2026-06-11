<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/PasswordController.php';

$data = (new PasswordController(db_connect()))->handleReset();

$error = $data['error'];
$token = $data['token'];
$valid = $data['valid'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu — TechShop</title>
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

        <h2>Đặt lại mật khẩu</h2>
        <p class="auth-subtitle mb-4">Tạo mật khẩu mới cho tài khoản của bạn.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= h($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($valid): ?>
            <form method="post">
                <input type="hidden" name="token" value="<?= h($token) ?>">

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock me-1 text-primary"></i>Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-shield-lock me-1 text-primary"></i>Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirm" class="form-control" minlength="6" required>
                </div>

                <button class="btn btn-primary w-100 btn-lg">
                    <i class="bi bi-check2-circle me-2"></i>Cập nhật mật khẩu
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/login.php" class="fw-800">
                <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
            </a>
        </div>
    </div>
</main>
</body>
</html>
