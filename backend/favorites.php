<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/Favorite.php';
require_login();

$products   = (new Favorite(db_connect()))->getProductsByUser((int)$_SESSION['id_nguoi_dung']);
$page_title = 'Sản phẩm yêu thích — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/account.php">Tài khoản</a></li>
            <li class="breadcrumb-item active">Yêu thích</li>
        </ol>
    </nav>

    <div class="section-heading mb-4">
        <i class="bi bi-heart me-2"></i>Sản phẩm yêu thích
        <span class="text-muted fw-normal ms-2" style="font-size:14px">(<?= count($products) ?> sản phẩm)</span>
    </div>

    <?php if (empty($products)): ?>
    <div class="empty-state bg-white rounded-4 shadow-sm">
        <i class="bi bi-heart"></i>
        <h5>Chưa có sản phẩm yêu thích</h5>
        <p class="text-muted">Hãy nhấn tim để lưu những sản phẩm bạn thích!</p>
        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary mt-2">
            <i class="bi bi-bag me-2"></i>Khám phá sản phẩm
        </a>
    </div>
    <?php else: ?>
    <div class="row row-cols-2 row-cols-md-4 g-3">
        <?php foreach ($products as $p):
            $price = (!empty($p['gia_giam']) && $p['gia_giam'] < $p['gia_ban']) ? $p['gia_giam'] : $p['gia_ban'];
            $hasDiscount = !empty($p['gia_giam']) && $p['gia_giam'] < $p['gia_ban'];
        ?>
        <div class="col">
            <div class="product-card">
                <?php if ($hasDiscount): ?>
                <span class="p-badge">-<?= round((1 - $p['gia_giam'] / $p['gia_ban']) * 100) ?>%</span>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id_san_pham'] ?>">
                    <div class="p-img-box">
                        <img src="<?= product_img_url($p['hinh_anh']) ?>"
                             alt="<?= h($p['ten_san_pham']) ?>" loading="lazy">
                    </div>
                </a>
                <div class="p-content">
                    <div class="p-brand"><?= h($p['ten_thuong_hieu'] ?? '') ?></div>
                    <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id_san_pham'] ?>" class="p-title">
                        <?= h($p['ten_san_pham']) ?>
                    </a>
                    <div class="mt-auto">
                        <span class="p-price"><?= format_price($price) ?></span>
                        <?php if ($hasDiscount): ?>
                            <span class="p-old-price"><?= format_price($p['gia_ban']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-actions">
                        <button class="btn-add-cart ajax-cart-btn" data-id="<?= (int)$p['id_san_pham'] ?>">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                        <button class="btn-fav active ajax-favorite-btn" data-id="<?= (int)$p['id_san_pham'] ?>" title="Bỏ yêu thích">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
