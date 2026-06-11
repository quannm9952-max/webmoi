<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/Review.php';
require_login();

$uid = (int)$_SESSION['id_nguoi_dung'];
$orderId = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
if ($orderId <= 0) redirect('my_orders.php');

$reviewModel = new Review(db_connect());

if (is_post()) {
    $productId = (int)($_POST['id_san_pham'] ?? 0);
    $stars = (int)($_POST['so_sao'] ?? 5);
    $content = (string)($_POST['noi_dung'] ?? '');
    $result = $reviewModel->save($orderId, $productId, $uid, $stars, $content);
    $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
    redirect('review.php?order_id=' . $orderId);
}

$items = $reviewModel->getReviewableItems($orderId, $uid);
if (!$items) {
    $_SESSION['error'] = 'Đơn hàng chưa giao hoặc không có sản phẩm để đánh giá.';
    redirect('my_orders.php');
}

$success = flash('success');
$error = flash('error');
$page_title = 'Đánh giá đơn hàng — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/account.php">Tài khoản</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/my_orders.php">Đơn hàng</a></li>
            <li class="breadcrumb-item active">Đánh giá</li>
        </ol>
    </nav>

    <div class="section-heading mb-4">
        <i class="bi bi-star me-2"></i>Đánh giá đơn hàng #<?= (int)$orderId ?>
    </div>

    <div class="bg-white rounded-4 shadow-sm p-4">
        <?php foreach ($items as $item): ?>
            <div class="border rounded-4 p-3 mb-3">
                <div class="d-flex gap-3 align-items-start flex-wrap">
                    <img src="<?= product_img_url($item['hinh_anh_chinh'] ?? '') ?>"
                         style="width:80px;height:80px;object-fit:contain;border-radius:12px;border:1px solid var(--border);padding:6px;background:#f8fafc">
                    <div class="flex-fill">
                        <h6 class="fw-800 mb-1"><?= h($item['ten_san_pham']) ?></h6>
                        <div class="text-muted mb-2" style="font-size:13px">Số lượng: <?= (int)$item['so_luong'] ?></div>

                        <?php if (!empty($item['id_danh_gia'])): ?>
                            <div class="alert alert-success py-2 mb-3">
                                Bạn đã đánh giá: <strong><?= (int)$item['so_sao'] ?>/5 sao</strong>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="row g-2">
                            <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
                            <input type="hidden" name="id_san_pham" value="<?= (int)$item['id_san_pham'] ?>">
                            <div class="col-md-3">
                                <label class="form-label fw-700">Số sao</label>
                                <select name="so_sao" class="form-select" required>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>" <?= ((int)($item['so_sao'] ?? 5) === $i) ? 'selected' : '' ?>><?= $i ?> sao</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-700">Nội dung feedback</label>
                                <textarea name="noi_dung" class="form-control" rows="2" required placeholder="Sản phẩm dùng ổn không? Giao hàng, đóng gói thế nào?"><?= h($item['noi_dung'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="bi bi-send me-1"></i>Gửi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($success || $error): ?>
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: '<?= $error ? 'error' : 'success' ?>',
            title: '<?= $error ? 'Lỗi' : 'Thành công' ?>',
            text: <?= json_encode($error ?: $success, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonText: 'Đóng',
            confirmButtonColor: '#2563eb'
        });
    }
    <?php endif; ?>
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
