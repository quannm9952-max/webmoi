<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/Order.php';
require_login();

$om = new Order(db_connect());
$o  = $om->getByIdForUser((int)($_GET['id'] ?? 0), (int)$_SESSION['id_nguoi_dung']);
if (!$o) redirect('my_orders.php');

$items      = $om->getItems((int)$o['id_don_hang']);
$page_title = 'Đặt hàng thành công — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="bg-white rounded-4 shadow-sm p-5 text-center">
                <div style="width:80px;height:80px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px">
                    ✅
                </div>
                <h2 class="fw-900 text-dark mb-2">Đặt hàng thành công!</h2>
                <p class="text-muted mb-4">
                    Cảm ơn bạn đã tin tưởng TechShop. Đơn hàng <strong>#<?= (int)$o['id_don_hang'] ?></strong> của bạn đã được ghi nhận.
                </p>

                <div class="bg-light rounded-3 p-3 mb-4 text-start">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="text-muted" style="font-size:12px">Mã đơn</div>
                            <div class="fw-700">#<?= (int)$o['id_don_hang'] ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:12px">Tổng tiền</div>
                            <div class="fw-700 text-primary"><?= format_price($o['tong_tien']) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:12px">Người nhận</div>
                            <div class="fw-600" style="font-size:14px"><?= h($o['ten_nguoi_nhan'] ?? '—') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:12px">SĐT</div>
                            <div class="fw-600" style="font-size:14px"><?= h($o['so_dien_thoai_nhan'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($items)): ?>
                <div class="text-start mb-4">
                    <h6 class="fw-700 mb-3">Sản phẩm đã đặt</h6>
                    <?php foreach ($items as $item): ?>
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <img src="<?= product_img_url($item['hinh_anh'] ?? $item['hinh_anh_chinh']) ?>"
                             style="width:48px;height:48px;object-fit:contain;border-radius:8px;border:1px solid var(--border);padding:4px;background:#f8fafc;flex-shrink:0">
                        <div class="flex-fill">
                            <div style="font-size:13px;font-weight:600"><?= h($item['ten_san_pham']) ?></div>
                            <div class="text-muted" style="font-size:12px">x<?= (int)$item['so_luong'] ?></div>
                        </div>
                        <div class="fw-700 text-primary" style="font-size:14px"><?= format_price($item['thanh_tien']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?= BASE_URL ?>/my_orders.php" class="btn btn-primary">
                        <i class="bi bi-box-seam me-2"></i>Theo dõi đơn hàng
                    </a>
                    <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline-secondary">
                        <i class="bi bi-bag me-2"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
