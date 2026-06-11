<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/AdminCatalog.php';

$page_title = 'Sản phẩm';
require __DIR__ . '/_layout_start.php';

$cat      = new AdminCatalog(db_connect());
$products = $cat->products();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Sản phẩm</h1>
        <p class="text-muted mb-0">Quản lý danh sách sản phẩm trong cửa hàng.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/product_form.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Thêm sản phẩm
    </a>
</div>

<div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td class="fw-600"><?= h($p['ten_san_pham']) ?></td>
                <td class="text-primary fw-700"><?= format_price($p['gia']) ?></td>
                <td><?= (int)$p['so_luong_ton'] ?></td>
                <td>
                    <?php if (($p['trang_thai'] ?? '') === 'dang_ban'): ?>
                        <span class="badge bg-success">Đang bán</span>
                    <?php elseif (($p['trang_thai'] ?? '') === 'het_hang'): ?>
                        <span class="badge bg-warning text-dark">Hết hàng</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= h($p['trang_thai'] ?? '') ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/admin/product_form.php?id=<?= (int)$p['id_san_pham'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Sửa
                    </a>

                    <a href="<?= BASE_URL ?>/admin/product_delete.php?id=<?= (int)$p['id_san_pham'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Bạn có chắc muốn xóa/ẩn sản phẩm này không?')">
                        <i class="bi bi-trash me-1"></i>Xóa
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($products)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-5">Chưa có sản phẩm nào.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
