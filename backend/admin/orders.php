<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/AdminOrderController.php';

$controller = new AdminOrderController(db_connect());
$controller->handlePost();

$data = $controller->indexData();
$orders = $data['orders'];
$status = $data['status'];
$keyword = $data['keyword'];
$success = $data['success'];
$error = $data['error'];

$page_title = 'Đơn hàng';
require __DIR__ . '/_layout_start.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1">Quản lý đơn hàng</h1>
        <p class="text-muted mb-0">Theo dõi và cập nhật trạng thái đơn hàng.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success rounded-4"><?= h($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger rounded-4"><?= h($error) ?></div>
<?php endif; ?>

<div class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Tìm kiếm</label>
            <input name="q" value="<?= h($keyword) ?>" class="form-control" placeholder="Mã đơn, tên khách, email...">
        </div>
        <div class="col-md-4">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <?php foreach (AdminOrderController::STATUS_LABELS as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= $status === $key ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">
                <i class="bi bi-search me-2"></i>Lọc
            </button>
        </div>
    </form>
</div>

<div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Khách</th>
                <th>Tổng</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="fw-800">#<?= (int)$o['id_don_hang'] ?></td>
                    <td>
                        <div class="fw-700"><?= h($o['ho_ten'] ?? $o['ten_nguoi_nhan'] ?? '') ?></div>
                        <div class="text-muted small"><?= h($o['email'] ?? '') ?></div>
                    </td>
                    <td class="fw-800 text-primary"><?= format_price($o['tong_tien']) ?></td>
                    <td>
                        <span class="badge rounded-pill bg-<?= AdminOrderController::statusBadge($o['trang_thai_don_hang']) ?>">
                            <?= h(AdminOrderController::statusLabel($o['trang_thai_don_hang'])) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <form method="post" class="d-inline-flex gap-2 align-items-center">
                            <input type="hidden" name="id_don_hang" value="<?= (int)$o['id_don_hang'] ?>">
                            <select name="trang_thai_don_hang" class="form-select form-select-sm" style="width:155px">
                                <?php foreach (AdminOrderController::STATUS_LABELS as $key => $label): ?>
                                    <option value="<?= h($key) ?>" <?= $o['trang_thai_don_hang'] === $key ? 'selected' : '' ?>>
                                        <?= h($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary">Lưu</button>
                            <a class="btn btn-sm btn-outline-secondary"
                               href="<?= BASE_URL ?>/admin/order_detail.php?id=<?= (int)$o['id_don_hang'] ?>">
                                Chi tiết
                            </a>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">Không có đơn hàng phù hợp.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($success || $error): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: '<?= $error ? 'error' : 'success' ?>',
            title: '<?= $error ? 'Không thể xử lý' : 'Thành công' ?>',
            text: <?= json_encode($error ?: $success, JSON_UNESCAPED_UNICODE) ?>,
            confirmButtonText: 'Đóng'
        });
    }
});
</script>
<?php endif; ?>

<script>
// Tự động cập nhật dữ liệu Real-time (Mỗi 5 giây)
document.addEventListener('DOMContentLoaded', function() {
    let isPolling = false;
    setInterval(async () => {
        if (isPolling) return;
        isPolling = true;
        try {
            const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Network response was not ok');
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            
            const newTbody = doc.querySelector('table tbody');
            const oldTbody = document.querySelector('table tbody');
            
            if (newTbody && oldTbody && newTbody.innerHTML !== oldTbody.innerHTML) {
                oldTbody.innerHTML = newTbody.innerHTML;
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Có đơn hàng mới hoặc trạng thái vừa thay đổi!',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            }
        } catch(e) {
            // Im lặng nếu lỗi mạng (để không làm phiền admin)
        } finally {
            isPolling = false;
        }
    }, 5000);
});
</script>

<?php require __DIR__ . '/_layout_end.php'; ?>
