<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController(db_connect());
$auth->handleRegister();

$error = flash('error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký — TechShop</title>
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

    <h2>Tạo tài khoản</h2>
    <p class="auth-subtitle">Đăng ký để mua sắm và theo dõi đơn hàng dễ dàng hơn.</p>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i><?= h($error) ?>
    </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label class="form-label" for="ho_ten">
                <i class="bi bi-person me-1 text-primary"></i>Họ và tên <span class="text-danger">*</span>
            </label>
            <input type="text" id="ho_ten" name="ho_ten" class="form-control"
                   placeholder="Nhập Họ và Tên" value="<?= h($_SESSION['_old_register']['ho_ten'] ?? '') ?>" required autocomplete="name">
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">
                <i class="bi bi-envelope me-1 text-primary"></i>Email <span class="text-danger">*</span>
            </label>
            <input type="email" id="email" name="email" class="form-control <?= str_contains($error, 'Email') ? 'is-invalid' : '' ?>"
                   placeholder="Nhập email của bạn" value="<?= h($_SESSION['_old_register']['email'] ?? '') ?>" required autocomplete="email">
        </div>
        <div class="mb-3">
            <label class="form-label" for="so_dien_thoai">
                <i class="bi bi-telephone me-1 text-primary"></i>Số điện thoại
            </label>
            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control <?= str_contains($error, 'Số điện thoại') ? 'is-invalid' : '' ?>"
                   placeholder="Nhập số điện thoại" value="<?= h($_SESSION['_old_register']['so_dien_thoai'] ?? '') ?>" autocomplete="tel" pattern="[0-9]{10}" maxlength="10" title="Vui lòng nhập đúng 10 chữ số">
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">
                <i class="bi bi-lock me-1 text-primary"></i>Mật khẩu <span class="text-danger">*</span>
            </label>
            <div class="position-relative">
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Ít nhất 6 ký tự" required minlength="6"
                       autocomplete="new-password" style="padding-right:44px">
                <button type="button" class="btn position-absolute end-0 top-0 h-100 px-3 text-muted"
                        onclick="togglePwd(this)" tabindex="-1">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label" for="dia_chi">
                <i class="bi bi-geo-alt me-1 text-primary"></i>Địa chỉ
            </label>
            <textarea id="dia_chi" name="dia_chi" class="form-control" rows="2"
                      placeholder="Địa chỉ của bạn"><?= h($_SESSION['_old_register']['dia_chi'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-person-plus me-2"></i>Đăng ký ngay
        </button>
    
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="agree_terms" value="1" id="agree_terms" required>
                <label class="form-check-label small" for="agree_terms">
                    Tôi đồng ý với
                    <a href="<?= BASE_URL ?>/terms.php" target="_blank" class="fw-800">Điều khoản sử dụng</a>
                    và
                    <a href="<?= BASE_URL ?>/privacy.php" target="_blank" class="fw-800">Chính sách bảo mật</a>.
                </label>
            </div>

</form>

    <div class="text-center mt-4 text-muted" style="font-size:14px">
        Đã có tài khoản?
        <a href="<?= BASE_URL ?>/login.php" class="fw-700 text-primary">Đăng nhập</a>
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

document.addEventListener("DOMContentLoaded", function() {
    <?php if ($error && str_contains($error, 'Email')): ?>
        document.getElementById('email').focus();
    <?php elseif ($error && str_contains($error, 'Số điện thoại')): ?>
        document.getElementById('so_dien_thoai').focus();
    <?php elseif ($error && str_contains($error, 'Mật khẩu')): ?>
        document.getElementById('password').focus();
    <?php endif; ?>
});
</script>
</body>
</html>
