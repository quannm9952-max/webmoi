<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/AdminCatalog.php';

$m  = new AdminCatalog(db_connect());
$id = (int)($_GET['id'] ?? 0);
$p  = $id ? $m->findProduct($id) : null;

if (is_post()) {
    if (!empty($_FILES['image']['name'])) {
        $dir = __DIR__ . '/../assets/uploads/products/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $dir . $name);

        $_POST['hinh_anh_chinh'] = 'assets/uploads/products/' . $name;
    }

    try {
        $m->saveProduct($_POST, $id);
        redirect('admin/products.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$page_title = $p ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
require __DIR__ . '/_layout_start.php';

$cats   = $m->categories();
$brands = $m->brands();
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
    <div>
        <h1 class="mb-0"><?= $p ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' ?></h1>
        <p class="text-muted mb-0">Quản lý thông tin sản phẩm trong cửa hàng.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= h($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bg-white p-4 rounded-4 shadow-sm mb-4">
                <h5 class="fw-700 mb-4">Thông tin sản phẩm</h5>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input name="ten_san_pham" class="form-control"
                               placeholder="Nhập tên sản phẩm"
                               value="<?= h($p['ten_san_pham'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mã sản phẩm</label>
                        <input name="ma_san_pham" class="form-control"
                               placeholder="VD: SP001"
                               value="<?= h($p['ma_san_pham'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select name="trang_thai" class="form-select">
                            <option value="dang_ban" <?= ($p['trang_thai'] ?? '') === 'dang_ban' ? 'selected' : '' ?>>Đang bán</option>
                            <option value="het_hang" <?= ($p['trang_thai'] ?? '') === 'het_hang' ? 'selected' : '' ?>>Hết hàng</option>
                            <option value="an" <?= ($p['trang_thai'] ?? '') === 'an' ? 'selected' : '' ?>>Ẩn</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Danh mục</label>
                        <select name="id_danh_muc" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($cats as $c): ?>
                            <option value="<?= (int)$c['id_danh_muc'] ?>"
                                <?= ($p['id_danh_muc'] ?? '') == $c['id_danh_muc'] ? 'selected' : '' ?>>
                                <?= h($c['ten_danh_muc']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Thương hiệu</label>
                        <select name="id_thuong_hieu" class="form-select" required>
                            <option value="">-- Chọn thương hiệu --</option>
                            <?php foreach ($brands as $b): ?>
                            <option value="<?= (int)$b['id_thuong_hieu'] ?>"
                                <?= ($p['id_thuong_hieu'] ?? '') == $b['id_thuong_hieu'] ? 'selected' : '' ?>>
                                <?= h($b['ten_thuong_hieu']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Giá bán (₫) <span class="text-danger">*</span></label>
                        <input name="gia" type="number" min="0" class="form-control"
                               placeholder="0"
                               value="<?= h($p['gia'] ?? 0) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Số lượng tồn kho</label>
                        <input name="so_luong_ton" type="number" min="0" class="form-control"
                               placeholder="0"
                               value="<?= h($p['so_luong_ton'] ?? 0) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Mô tả ngắn</label>
                        <textarea name="mo_ta_ngan" class="form-control" rows="3"
                                  placeholder="Mô tả ngắn hiển thị trên danh sách sản phẩm"><?= h($p['mo_ta_ngan'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="mo_ta_chi_tiet" class="form-control" rows="5"
                                  placeholder="Mô tả chi tiết sản phẩm"><?= h($p['mo_ta_chi_tiet'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="bg-white p-4 rounded-4 shadow-sm">
                <h5 class="fw-700 mb-4">Hình ảnh</h5>

                <?php if (!empty($p['hinh_anh_chinh'])): ?>
                <div class="mb-3 text-center">
                    <img src="<?= BASE_URL ?>/<?= h($p['hinh_anh_chinh']) ?>"
                         class="img-fluid rounded-3"
                         style="max-height:200px;object-fit:contain"
                         alt="Ảnh hiện tại">
                    <p class="text-muted small mt-2">Ảnh hiện tại</p>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Đường dẫn ảnh (URL)</label>
                    <input name="hinh_anh_chinh" class="form-control"
                           placeholder="assets/images/..."
                           value="<?= h($p['hinh_anh_chinh'] ?? 'assets/images/no-image.jpg') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Hoặc tải ảnh lên</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Tải ảnh lên sẽ ghi đè đường dẫn ở trên.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3 mt-3">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg me-2"></i><?= $p ? 'Cập nhật sản phẩm' : 'Thêm sản phẩm' ?>
        </button>
        <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline-secondary btn-lg">Hủy</a>
    </div>
</form>

<?php require __DIR__ . '/_layout_end.php'; ?>
