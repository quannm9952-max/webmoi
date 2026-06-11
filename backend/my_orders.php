<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/Order.php';
require_login();

$orderModel = new Order(db_connect());

if (is_post() && isset($_POST['cancel_order_id'])) {
    $cancelId = (int)$_POST['cancel_order_id'];
    $result = $orderModel->cancelOrder($cancelId, (int)$_SESSION['id_nguoi_dung']);
    if ($result['success']) {
        $_SESSION['success'] = 'Hủy đơn hàng thành công.';
    } else {
        $_SESSION['error'] = $result['message'] ?? 'Không thể hủy đơn hàng.';
    }
    redirect('my_orders.php');
}

$orders = $orderModel->getOrdersByUser((int)$_SESSION['id_nguoi_dung']);
$page_title = 'Đơn hàng của tôi — TechShop';
$success = flash('success');
$error = flash('error');
require __DIR__ . '/includes/header.php';

function order_status_label(string $status): array
{
    return match ($status) {
        'cho_xac_nhan' => ['Chờ xác nhận', 'warning'],
        'da_xac_nhan' => ['Đã xác nhận', 'info'],
        'dang_giao' => ['Đang giao', 'primary'],
        'da_giao' => ['Đã giao', 'success'],
        'da_huy' => ['Đã hủy', 'danger'],
        default => [$status, 'secondary'],
    };
}
?>
<main class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/account.php">Tài khoản</a></li>
            <li class="breadcrumb-item active">Đơn hàng</li>
        </ol>
    </nav>

    <div class="section-heading mb-4">
        <i class="bi bi-box-seam me-2"></i>Đơn hàng của tôi
        <span class="text-muted fw-normal ms-2" style="font-size:14px">(<?= count($orders) ?> đơn)</span>
    </div>

    <?php if (false): // hide bootstrap alerts ?>
    <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-check-circle-fill"></i><?= h($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill"></i><?= h($error) ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (!$orders): ?>
        <div class="empty-state bg-white rounded-4 shadow-sm">
            <i class="bi bi-box"></i>
            <h5>Bạn chưa có đơn hàng nào</h5>
            <p class="text-muted">Hãy mua sắm để tạo đơn hàng đầu tiên.</p>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary mt-2">
                <i class="bi bi-bag me-2"></i>Mua sắm ngay
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-4 shadow-sm p-4 table-responsive">
            <table class="table align-middle order-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Tổng tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o):
                        [$label, $color] = order_status_label($o['trang_thai_don_hang']);
                    ?>
                    <tr>
                        <td class="fw-800">#<?= (int)$o['id_don_hang'] ?></td>
                        <td><?= h($o['ngay_dat']) ?></td>
                        <td><?= h($o['ten_phuong_thuc'] ?? '—') ?></td>
                        <td><span class="badge rounded-pill bg-<?= $color ?>"><?= h($label) ?></span></td>
                        <td class="text-end fw-800 text-primary"><?= format_price($o['tong_tien']) ?></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <?php if (in_array($o['trang_thai_don_hang'], ['cho_xac_nhan', 'da_xac_nhan'], true)): ?>
                                <form method="post" class="m-0 p-0 cancel-order-form">
                                    <input type="hidden" name="cancel_order_id" value="<?= (int)$o['id_don_hang'] ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-cancel-order">
                                        <i class="bi bi-x-circle me-1"></i>Hủy
                                    </button>
                                </form>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/order_complete.php?id=<?= (int)$o['id_don_hang'] ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Xem
                                </a>
                                <?php if ($o['trang_thai_don_hang'] === 'da_giao'): ?>
                                    <a href="<?= BASE_URL ?>/review.php?order_id=<?= (int)$o['id_don_hang'] ?>"
                                       class="btn btn-sm btn-warning text-dark">
                                        <i class="bi bi-star-fill me-1"></i>Feedback
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
            confirmButtonColor: '#0ea5e9'
        });
    }
    <?php endif; ?>

    function attachCancelEvents() {
        document.querySelectorAll('.btn-cancel-order').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true)); // remove old listener
        });
        
        document.querySelectorAll('.btn-cancel-order').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const form = this.closest('form');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Xác nhận hủy đơn',
                        text: 'Bạn có chắc chắn muốn hủy đơn hàng này không? Hành động này không thể hoàn tác.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Đồng ý hủy',
                        cancelButtonText: 'Bỏ qua'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')) {
                        form.submit();
                    }
                }
            });
        });
    }

    attachCancelEvents();

    let isPolling = false;
    setInterval(async () => {
        if (isPolling) return;
        isPolling = true;
        try {
            const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Network');
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            
            const newTbody = doc.querySelector('table tbody');
            const oldTbody = document.querySelector('table tbody');
            
            if (newTbody && oldTbody && newTbody.innerHTML !== oldTbody.innerHTML) {
                oldTbody.innerHTML = newTbody.innerHTML;
                attachCancelEvents();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Trạng thái đơn hàng vừa được cập nhật!',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                }
            }
        } catch(e) {} finally {
            isPolling = false;
        }
    }, 5000);
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
