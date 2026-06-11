<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/CartController.php';

$controller = new CartController(db_connect());
$controller->handlePost();

$data = $controller->pageData();
$items = $data['items'];
$total = $data['total'];

$page_title = 'Giỏ hàng — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/shop.php">Cửa hàng</a></li>
            <li class="breadcrumb-item active">Giỏ hàng</li>
        </ol>
    </nav>

    <div class="section-heading mb-4">
        <i class="bi bi-cart3 me-2"></i>Giỏ hàng của bạn
        <span class="text-muted fw-normal ms-2" style="font-size:14px">(<?= count($items) ?> sản phẩm)</span>
    </div>

    <?php if (!$items): ?>
        <div class="empty-state bg-white rounded-4 shadow-sm">
            <i class="bi bi-cart-x"></i>
            <h5>Giỏ hàng đang trống</h5>
            <p class="text-muted">Hãy thêm sản phẩm vào giỏ để tiếp tục thanh toán.</p>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary mt-2">
                <i class="bi bi-bag me-2"></i>Tiếp tục mua sắm
            </a>
        </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bg-white rounded-4 shadow-sm overflow-hidden">
                <?php foreach ($items as $i): ?>
                <div class="cart-item-row">
                    <img src="<?= product_img_url($i['hinh_anh'] ?? $i['hinh_anh_chinh']) ?>" alt="<?= h($i['ten_san_pham']) ?>">
                    <div class="cart-item-info">
                        <div class="fw-700"><?= h($i['ten_san_pham']) ?></div>
                        <div class="text-muted small">Mã: <?= h($i['ma_san_pham'] ?? '') ?></div>
                        <div class="text-primary fw-700 mt-1"><?= format_price($i['don_gia']) ?></div>
                    </div>
                    <form method="post" class="cart-qty-form">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_san_pham" value="<?= (int)$i['id_san_pham'] ?>">
                        <input type="number" name="so_luong" min="1" value="<?= (int)$i['so_luong'] ?>" class="form-control">
                        <button class="btn btn-sm btn-outline-primary">Cập nhật</button>
                    </form>
                    <div class="cart-item-total"><?= format_price($i['thanh_tien']) ?></div>
                    <form method="post">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="id_san_pham" value="<?= (int)$i['id_san_pham'] ?>">
                        <button class="btn btn-sm btn-outline-danger" title="Xóa sản phẩm">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cart-summary-card">
                <h5 class="fw-800 mb-3"><i class="bi bi-receipt me-2 text-primary"></i>Tóm tắt đơn hàng</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tạm tính</span>
                    <span class="fw-700"><?= format_price($total) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Phí vận chuyển</span>
                    <span class="text-success fw-700">Miễn phí</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-800">Tổng cộng</span>
                    <span class="fw-800 text-primary fs-4"><?= format_price($total) ?></span>
                </div>
                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-credit-card me-2"></i>Tiến hành thanh toán
                </a>
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline-secondary w-100 mt-2">
                    <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
