<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/OrderController.php';

$controller = new OrderController(db_connect());
$controller->handleCheckoutPost();

$data = $controller->checkoutData();
$items = $data['items'];
$total = $data['total'];
$methods = $data['methods'];
$error = $data['error'];
$user_info = $data['user'];

$page_title = 'Thanh toán — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/shop.php">Cửa hàng</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cart.php">Giỏ hàng</a></li>
            <li class="breadcrumb-item active">Thanh toán</li>
        </ol>
    </nav>

    <div class="section-heading mb-4">
        <i class="bi bi-credit-card me-2"></i>Thanh toán
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= h($error) ?>
    </div>
    <?php endif; ?>

    <form method="post">
        <div class="row g-4">
            <!-- Left: Shipping + Payment -->
            <div class="col-lg-7">
                <!-- Shipping Info -->
                <div class="checkout-section mb-4">
                    <h5><i class="bi bi-geo-alt me-2 text-primary"></i>Thông tin giao hàng</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Họ và tên người nhận <span class="text-danger">*</span></label>
                            <input name="ten_nguoi_nhan" class="form-control"
                                   placeholder="Nhập họ tên đầy đủ"
                                   value="<?= h($_SESSION['ho_ten'] ?? $user_info['ho_ten'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input name="so_dien_thoai_nhan" class="form-control"
                                   placeholder="Nhập số điện thoại"
                                   value="<?= h($_SESSION['so_dien_thoai'] ?? $user_info['so_dien_thoai'] ?? '') ?>"
                                   required pattern="[0-9]{10}" maxlength="10" title="Vui lòng nhập đúng 10 chữ số">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea name="dia_chi_giao_hang" class="form-control" rows="2"
                                      placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required><?= h($user_info['dia_chi'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú đơn hàng</label>
                            <textarea name="ghi_chu" class="form-control" rows="2"
                                      placeholder="VD: Giao giờ hành chính, gọi trước khi giao..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="checkout-section">
                    <h5><i class="bi bi-wallet2 me-2 text-primary"></i>Phương thức thanh toán</h5>
                    <?php foreach ($methods as $k => $m): ?>
                    <label class="payment-option <?= $k === 0 ? 'selected' : '' ?>"
                           onclick="document.querySelectorAll('.payment-option').forEach(e=>e.classList.remove('selected'));this.classList.add('selected')">
                        <input type="radio" name="id_phuong_thuc"
                               value="<?= (int)$m['id_phuong_thuc'] ?>"
                               <?= $k === 0 ? 'checked' : '' ?> required>
                        <div>
                            <div class="fw-700"><?= h($m['ten_phuong_thuc']) ?></div>
                            <?php if (!empty($m['mo_ta'])): ?>
                            <small class="text-muted"><?= h($m['mo_ta']) ?></small>
                            <?php endif; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="col-lg-5">
                <div class="cart-summary-card" style="position:sticky;top:100px">
                    <h5 class="fw-800 mb-4">
                        <i class="bi bi-receipt me-2 text-primary"></i>Đơn hàng của bạn
                    </h5>
                    <div style="max-height:320px;overflow-y:auto">
                        <?php foreach ($items as $i): ?>
                        <div class="order-item-row">
                            <div class="d-flex align-items-center gap-2" style="max-width:65%">
                                <img src="<?= product_img_url($i['hinh_anh']) ?>"
                                     style="width:40px;height:40px;object-fit:contain;border-radius:8px;border:1px solid var(--border);flex-shrink:0">
                                <span class="text-truncate"><?= h($i['ten_san_pham']) ?>
                                    <small class="text-muted">x<?= (int)$i['so_luong'] ?></small>
                                </span>
                            </div>
                            <span class="fw-600 text-primary"><?= format_price($i['thanh_tien']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="my-3">
                    <div class="d-flex justify-content-between mb-2" style="font-size:14px">
                        <span class="text-muted">Tạm tính</span>
                        <span class="fw-600"><?= format_price($total) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3" style="font-size:14px">
                        <span class="text-muted">Phí vận chuyển</span>
                        <span class="text-success fw-600">Miễn phí</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-800" style="font-size:16px">Tổng thanh toán</span>
                        <span class="fw-800 text-primary" style="font-size:22px"><?= format_price($total) ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle me-2"></i>Đặt hàng ngay
                    </button>
                    <div class="d-flex align-items-center gap-2 mt-3 text-muted justify-content-center" style="font-size:12px">
                        <i class="bi bi-shield-lock-fill text-success"></i>
                        Thanh toán an toàn và bảo mật
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<?php if ($error): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Không thể đặt hàng',
            text: <?= json_encode($error, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonText: 'Đóng'
        });
    }
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
