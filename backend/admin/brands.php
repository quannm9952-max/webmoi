<?php
require_once __DIR__ . '/../models/AdminCatalog.php';

$page_title = 'Thương hiệu';
require __DIR__ . '/_layout_start.php';

$m    = new AdminCatalog(db_connect());
$edit = (int)($_GET['edit'] ?? 0);
$row  = $edit ? $m->findBrand($edit) : null;

if (is_post()) {
    $id = (int)($_POST['id'] ?? 0);
    $m->saveBrand([
        'ten_thuong_hieu' => $_POST['name'] ?? '',
        'trang_thai'      => $_POST['trang_thai'] ?? 'hien',
    ], $id);
    redirect('admin/brands.php');
}

$rows = $m->brands();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Thương hiệu</h1>
        <p class="text-muted mb-0">Quản lý danh sách thương hiệu sản phẩm.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form thêm / sửa -->
    <div class="col-md-4">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <h5 class="fw-700 mb-3">
                <?= $row ? 'Sửa thương hiệu' : 'Thêm thương hiệu' ?>
            </h5>
            <form method="post">
                <input type="hidden" name="id" value="<?= h($row['id_thuong_hieu'] ?? 0) ?>">
                <div class="mb-3">
                    <label class="form-label">Tên thương hiệu</label>
                    <input name="name" class="form-control"
                           value="<?= h($row['ten_thuong_hieu'] ?? '') ?>"
                           placeholder="Nhập tên thương hiệu" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="hien" <?= ($row['trang_thai'] ?? '') === 'hien' ? 'selected' : '' ?>>Hiện</option>
                        <option value="an"   <?= ($row['trang_thai'] ?? '') === 'an'   ? 'selected' : '' ?>>Ẩn</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100">
                    <i class="bi bi-check-lg me-2"></i>Lưu
                </button>
                <?php if ($row): ?>
                <a href="<?= BASE_URL ?>/admin/brands.php" class="btn btn-outline-secondary w-100 mt-2">Hủy</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Danh sách -->
    <div class="col-md-8">
        <div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Tên thương hiệu</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="fw-600"><?= h($r['ten_thuong_hieu']) ?></td>
                        <td>
                            <span class="badge rounded-pill bg-<?= $r['trang_thai'] === 'hien' ? 'success' : 'secondary' ?>">
                                <?= $r['trang_thai'] === 'hien' ? 'Hiện' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>/admin/brands.php?edit=<?= (int)$r['id_thuong_hieu'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Sửa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">Chưa có thương hiệu nào.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
