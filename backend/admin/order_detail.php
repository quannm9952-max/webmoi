<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../controllers/AdminOrderController.php';
require_once __DIR__ . '/../includes/bootstrap.php';
$pdo = db_connect();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    redirect('admin/orders.php');
}

$stmt = $pdo->prepare("
    SELECT dh.*, nd.ho_ten, nd.email, pt.ten_phuong_thuc
    FROM don_hang dh
    JOIN nguoi_dung nd ON nd.id_nguoi_dung = dh.id_nguoi_dung
    LEFT JOIN phuong_thuc_thanh_toan pt ON pt.id_phuong_thuc = dh.id_phuong_thuc
    WHERE dh.id_don_hang = :id
");
$stmt->execute([':id' => $id]);
$o = $stmt->fetch();

if (!$o) {
    redirect('admin/orders.php');
}

$items = (new Order($pdo))->getItems($id);

$page_title = 'Chi tiết đơn #' . $id;
require __DIR__ . '/_layout_start.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
    <div>
        <h1 class="mb-0">Chi tiết đơn hàng <span class="text-primary">#<?= $id ?></span></h1>
    </div>
    <span class="badge rounded-pill bg-<?= AdminOrderController::statusBadge($o['trang_thai_don_hang']) ?> ms-auto">
        <?= h(AdminOrderController::statusLabel($o['trang_thai_don_hang'])) ?>
    </span>
</div>

<div class="row g-4">
    <!-- Thông tin khách hàng -->
    <div class="col-lg-5">
        <div class="bg-white p-4 rounded-4 shadow-sm mb-4">
            <h5 class="fw-700 mb-3"><i class="bi bi-person me-2 text-primary"></i>Thông tin khách hàng</h5>
            <p class="mb-1"><strong>Họ tên:</strong> <?= h($o['ho_ten']) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= h($o['email']) ?></p>
            <p class="mb-1"><strong>Người nhận:</strong> <?= h($o['ten_nguoi_nhan']) ?></p>
            <p class="mb-1"><strong>Điện thoại:</strong> <?= h($o['so_dien_thoai_nhan']) ?></p>
            <p class="mb-1"><strong>Địa chỉ giao:</strong> <?= h($o['dia_chi_giao_hang']) ?></p>
            <?php if (!empty($o['ghi_chu'])): ?>
            <p class="mb-0"><strong>Ghi chú:</strong> <?= h($o['ghi_chu']) ?></p>
            <?php endif; ?>
        </div>

        <div class="bg-white p-4 rounded-4 shadow-sm">
            <h5 class="fw-700 mb-3"><i class="bi bi-wallet2 me-2 text-primary"></i>Thanh toán</h5>
            <p class="mb-1"><strong>Phương thức:</strong> <?= h($o['ten_phuong_thuc'] ?? '—') ?></p>
            <p class="mb-1"><strong>Ngày đặt:</strong> <?= h($o['ngay_dat']) ?></p>
            <p class="mb-0 fw-800 text-primary" style="font-size:20px">
                <?= format_price($o['tong_tien']) ?>
            </p>
        </div>
    </div>

    <!-- Sản phẩm trong đơn -->
    <div class="col-lg-7">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <h5 class="fw-700 mb-3"><i class="bi bi-cart me-2 text-primary"></i>Sản phẩm đặt mua</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">SL</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                        <tr>
                            <td class="fw-600"><?= h($it['ten_san_pham']) ?></td>
                            <td class="text-center"><?= (int)$it['so_luong'] ?></td>
                            <td class="text-end fw-700 text-primary"><?= format_price($it['thanh_tien']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end fw-800">Tổng cộng:</td>
                            <td class="text-end fw-800 text-primary" style="font-size:18px">
                                <?= format_price($o['tong_tien']) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
