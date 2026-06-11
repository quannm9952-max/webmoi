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

                        <form method="post" class="review-form d-none">
                            <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
                            <input type="hidden" name="id_san_pham" value="<?= (int)$item['id_san_pham'] ?>">
                            <input type="hidden" name="so_sao" value="<?= (int)($item['so_sao'] ?? 5) ?>">
                            <textarea name="noi_dung" class="d-none"><?= h($item['noi_dung'] ?? '') ?></textarea>
                        </form>

                        <button type="button"
                                class="btn btn-primary btn-open-review"
                                data-product="<?= h($item['ten_san_pham']) ?>"
                                data-stars="<?= (int)($item['so_sao'] ?? 5) ?>"
                                data-content="<?= h($item['noi_dung'] ?? '') ?>">
                            <i class="bi bi-star-fill me-1"></i><?= !empty($item['id_danh_gia']) ? 'Sửa feedback' : 'Đánh giá ngay' ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<style>
.review-stars{display:flex;justify-content:center;gap:8px;margin:12px 0 14px}
.review-star{border:0;background:transparent;font-size:34px;line-height:1;cursor:pointer;color:#cbd5e1;transition:.15s transform,.15s color}
.review-star.active,.review-star:hover{color:#f59e0b;transform:scale(1.08)}
.review-star:focus{outline:none}
.review-popup-product{font-weight:800;color:#0f172a;margin-bottom:6px}
</style>
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

    document.querySelectorAll('.btn-open-review').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = btn.closest('.flex-fill').querySelector('.review-form');
            const starInput = form.querySelector('input[name="so_sao"]');
            const textInput = form.querySelector('textarea[name="noi_dung"]');
            const productName = btn.dataset.product || 'Sản phẩm';
            let selectedStars = parseInt(btn.dataset.stars || starInput.value || '5', 10);
            let oldContent = btn.dataset.content || textInput.value || '';

            const html = `
                <div class="review-popup-product">${productName}</div>
                <div class="text-muted" style="font-size:13px">Chọn số sao và nhập feedback của bạn</div>
                <div class="review-stars" id="reviewStars">
                    ${[1,2,3,4,5].map(i => `<button type="button" class="review-star ${i <= selectedStars ? 'active' : ''}" data-star="${i}">★</button>`).join('')}
                </div>
                <textarea id="reviewContent" class="swal2-textarea" placeholder="Sản phẩm dùng ổn không? Giao hàng, đóng gói thế nào?" style="height:110px;margin:0;width:100%">${oldContent.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')}</textarea>
            `;

            if (typeof Swal === 'undefined') {
                const content = prompt('Nhập feedback:', oldContent);
                if (content && content.trim()) {
                    starInput.value = selectedStars;
                    textInput.value = content.trim();
                    form.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Feedback sản phẩm',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Gửi feedback',
                cancelButtonText: 'Để sau',
                confirmButtonColor: '#2563eb',
                didOpen: () => {
                    const stars = Swal.getHtmlContainer().querySelectorAll('.review-star');
                    const paint = (n) => stars.forEach(st => st.classList.toggle('active', parseInt(st.dataset.star, 10) <= n));
                    stars.forEach(st => {
                        st.addEventListener('mouseenter', () => paint(parseInt(st.dataset.star, 10)));
                        st.addEventListener('click', () => { selectedStars = parseInt(st.dataset.star, 10); paint(selectedStars); });
                    });
                    Swal.getHtmlContainer().querySelector('#reviewStars').addEventListener('mouseleave', () => paint(selectedStars));
                },
                preConfirm: () => {
                    const content = Swal.getHtmlContainer().querySelector('#reviewContent').value.trim();
                    if (!content) {
                        Swal.showValidationMessage('Vui lòng nhập nội dung feedback.');
                        return false;
                    }
                    return { stars: selectedStars, content };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    starInput.value = result.value.stars;
                    textInput.value = result.value.content;
                    form.submit();
                }
            });
        });
    });
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
