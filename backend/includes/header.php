<?php
require_once __DIR__ . '/../models/Cart.php';
$base = rtrim(BASE_URL, '/');
$cartCount = 0;
if (is_logged_in()) {
    try {
        $cart = new Cart(db_connect());
        if ($cart->getOrCreateCartByUserId((int)$_SESSION['id_nguoi_dung'])) {
            $cartCount = $cart->getItemCount();
        }
    } catch (Throwable $e) {
        $cartCount = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= h($page_title ?? APP_NAME) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= asset_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<div class="top-strip">
    <div class="container d-flex justify-content-between flex-wrap gap-2">
        <span><i class="bi bi-lightning-charge-fill text-warning"></i> TechShop Flash Sale — Giảm đến 40%</span>
        <span><i class="bi bi-telephone-fill me-1"></i>1900 1234 &nbsp;|&nbsp; <i class="bi bi-clock me-1"></i>8:00–22:00</span>
    </div>
</div>

<header class="main-header sticky-top">
    <div class="container d-flex align-items-center justify-content-between gap-3">

        <a href="<?= $base ?>/shop.php" class="brand-logo">
            <span class="brand-icon">T</span>
            <span>Tech<span>Shop</span></span>
        </a>

        <form action="<?= $base ?>/shop.php" method="GET" class="search-box d-none d-md-flex">
            <input name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Tìm kiếm sản phẩm, thương hiệu...">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>

        <nav class="header-actions">
            <a href="<?= $base ?>/favorites.php" class="header-icon-btn" title="Yêu thích">
                <i class="bi bi-heart"></i>
            </a>
            <a href="<?= $base ?>/cart.php" class="header-icon-btn" title="Giỏ hàng">
                <i class="bi bi-cart3"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= (int)$cartCount ?></span>
                <?php endif; ?>
            </a>

            <?php if (is_logged_in()): ?>
                <div class="dropdown">
                    <button class="avatar-btn dropdown-toggle-no-caret" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                        <span class="avatar-circle">
                            <?= strtoupper(mb_substr($_SESSION['ho_ten'] ?? 'U', 0, 1, 'UTF-8')) ?>
                        </span>
                        <span class="d-none d-lg-inline fw-600 text-dark" style="font-size:14px;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            <?= h($_SESSION['ho_ten'] ?? '') ?>
                        </span>
                        <i class="bi bi-chevron-down d-none d-lg-inline" style="font-size:11px;color:#64748b"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end account-menu">
                        <div class="account-head">
                            <span class="avatar-circle" style="width:44px;height:44px;font-size:18px">
                                <?= strtoupper(mb_substr($_SESSION['ho_ten'] ?? 'U', 0, 1, 'UTF-8')) ?>
                            </span>
                            <div>
                                <div class="fw-bold lh-1 mb-1"><?= h($_SESSION['ho_ten'] ?? '') ?></div>
                                <div class="text-muted small"><?= h($_SESSION['email'] ?? '') ?></div>
                            </div>
                        </div>
                        <a class="dropdown-item" href="<?= $base ?>/account.php">
                            <i class="bi bi-person-gear"></i> Tài khoản của tôi
                        </a>
                        <a class="dropdown-item" href="<?= $base ?>/my_orders.php">
                            <i class="bi bi-box-seam"></i> Đơn hàng của tôi
                        </a>
                        <a class="dropdown-item" href="<?= $base ?>/favorites.php">
                            <i class="bi bi-heart"></i> Sản phẩm yêu thích
                        </a>
                        <a class="dropdown-item" href="<?= $base ?>/chat.php">
                            <i class="bi bi-chat-dots"></i> Chat hỗ trợ
                        </a>
                        <?php if (is_admin()): ?>
                            <hr class="dropdown-divider my-1">
                            <a class="dropdown-item fw-semibold text-primary" href="<?= $base ?>/admin/index.php">
                                <i class="bi bi-speedometer2"></i> Trang quản trị
                            </a>
                        <?php endif; ?>
                        <hr class="dropdown-divider my-1">
                        <a class="dropdown-item text-danger fw-semibold" href="<?= $base ?>/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= $base ?>/login.php" class="btn btn-primary rounded-pill px-4 fw-600" style="font-size:14px">
                    <i class="bi bi-person me-1"></i>Đăng nhập
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="mobile-search-bar d-md-none">
    <div class="container">
        <form action="<?= $base ?>/shop.php" method="GET" class="search-box">
            <input name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>