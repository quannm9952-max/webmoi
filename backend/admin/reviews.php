<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/Review.php';

$reviewModel = new Review(db_connect());

if (is_post()) {
    $result = $reviewModel->adminUpdate(
        (int)($_POST['id_danh_gia'] ?? 0),
        (string)($_POST['trang_thai'] ?? 'hien'),
        (string)($_POST['ly_do_an'] ?? ''),
        (string)($_POST['phan_hoi_admin'] ?? '')
    );
    $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
    redirect('admin/reviews.php');
}

$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$reviews = $reviewModel->adminAll($q, $status);
$success = flash('success');
$error = flash('error');
$page_title = 'Feedback khách hàng';
require __DIR__ . '/_layout_start.php';
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1">Feedback khách hàng</h1>
        <p class="text-muted mb-0">Xem feedback cũ, ẩn feedback xấu và phản hồi khách hàng.</p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success rounded-4"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger rounded-4"><?= h($error) ?></div><?php endif; ?>

<div class="bg-white p-4 rounded-4 shadow-sm mb-4">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Tìm kiếm</label>
            <input name="q" value="<?= h($q) ?>" class="form-control" placeholder="Tên khách, sản phẩm, nội dung...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="">Tất cả</option>
                <option value="hien" <?= $status === 'hien' ? 'selected' : '' ?>>Đang hiển thị</option>
                <option value="an" <?= $status === 'an' ? 'selected' : '' ?>>Đã ẩn</option>
                <option value="cho_duyet" <?= $status === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Lọc</button>
        </div>
    </form>
</div>

<div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
    <table class="table align-middle">
        <thead>
        <tr>
            <th>Feedback</th>
            <th>Khách</th>
            <th>Sản phẩm</th>
            <th>Trạng thái</th>
            <th style="width:360px">Xử lý</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reviews as $r): $st = $r['trang_thai'] ?? 'hien'; ?>
            <tr>
                <td>
                    <div class="text-warning fs-5">
                        <?= str_repeat('★', (int)$r['so_sao']) ?><span class="text-muted"><?= str_repeat('☆', 5 - (int)$r['so_sao']) ?></span>
                    </div>
                    <div style="white-space:pre-wrap"><?= h($r['noi_dung']) ?></div>
                    <?php if (!empty($r['phan_hoi_admin'])): ?>
                        <div class="alert alert-primary py-2 mt-2 mb-0"><strong>Admin:</strong> <?= h($r['phan_hoi_admin']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($r['ly_do_an'])): ?>
                        <div class="small text-danger mt-1">Lý do xử lý: <?= h($r['ly_do_an']) ?></div>
                    <?php endif; ?>
                    <div class="small text-muted mt-1">Đơn #<?= (int)$r['id_don_hang'] ?> • <?= h($r['ngay_tao']) ?></div>
                </td>
                <td>
                    <div class="fw-bold"><?= h($r['ho_ten'] ?? 'Khách') ?></div>
                    <div class="small text-muted"><?= h($r['email'] ?? '') ?></div>
                </td>
                <td><?= h($r['ten_san_pham'] ?? '') ?></td>
                <td>
                    <span class="badge bg-<?= $st === 'an' ? 'danger' : ($st === 'cho_duyet' ? 'warning' : 'success') ?>">
                        <?= $st === 'an' ? 'Đã ẩn' : ($st === 'cho_duyet' ? 'Chờ duyệt' : 'Hiển thị') ?>
                    </span>
                </td>
                <td>
                    <form method="post" class="d-grid gap-2">
                        <input type="hidden" name="id_danh_gia" value="<?= (int)$r['id_danh_gia'] ?>">
                        <select name="trang_thai" class="form-select form-select-sm">
                            <option value="hien" <?= $st === 'hien' ? 'selected' : '' ?>>Hiển thị</option>
                            <option value="an" <?= $st === 'an' ? 'selected' : '' ?>>Ẩn feedback xấu</option>
                            <option value="cho_duyet" <?= $st === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                        </select>
                        <input name="ly_do_an" value="<?= h($r['ly_do_an'] ?? '') ?>" class="form-control form-control-sm" placeholder="Lý do xử lý nội bộ">
                        <textarea name="phan_hoi_admin" class="form-control form-control-sm" rows="2" placeholder="Phản hồi công khai của admin"><?= h($r['phan_hoi_admin'] ?? '') ?></textarea>
                        <button class="btn btn-sm btn-primary">Lưu xử lý</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$reviews): ?>
            <tr><td colspan="5" class="text-center text-muted py-5">Chưa có feedback.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>
