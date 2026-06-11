<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/AdminCatalog.php';

$page_title = 'Khuyến mãi';
require __DIR__ . '/_layout_start.php';

$m = new AdminCatalog(db_connect());
$promotions = $m->promotions();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Khuyến mãi</h1>
        <p class="text-muted mb-0">Tạo chương trình giảm giá và chọn sản phẩm áp dụng.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/promotion_form.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Thêm khuyến mãi
    </a>
</div>

<div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Tên khuyến mãi</th>
                <th>Giảm</th>
                <th>Ngày bắt đầu</th>
                <th>Ngày kết thúc</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promotions as $km): ?>
            <tr>
                <td class="fw-600"><?= h($km['ten_khuyen_mai']) ?></td>
                <td class="text-danger fw-bold">-<?= h($km['phan_tram_giam']) ?>%</td>
                <td><?= h($km['ngay_bat_dau']) ?></td>
                <td><?= h($km['ngay_ket_thuc']) ?></td>
                <td>
                    <?php if (($km['trang_thai'] ?? '') === 'dang_dien_ra'): ?>
                        <span class="badge bg-success">Đang diễn ra</span>
                    <?php elseif (($km['trang_thai'] ?? '') === 'ket_thuc'): ?>
                        <span class="badge bg-secondary">Kết thúc</span>
                    <?php else: ?>
                        <span class="badge bg-dark"><?= h($km['trang_thai'] ?? '') ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/admin/promotion_form.php?id=<?= (int)$km['id_khuyen_mai'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Sửa
                    </a>

                    <a href="<?= BASE_URL ?>/admin/promotion_delete.php?id=<?= (int)$km['id_khuyen_mai'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Bạn có chắc muốn xóa/ẩn khuyến mãi này không?')">
                        <i class="bi bi-trash me-1"></i>Xóa
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($promotions)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-5">Chưa có khuyến mãi nào.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
