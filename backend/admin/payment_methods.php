<?php
$page_title = 'Phương thức thanh toán';
require __DIR__ . '/_layout_start.php';

$pdo = db_connect();

if (is_post()) {
    $id = (int)($_POST['id'] ?? 0);

    if ($id) {
        // Cập nhật
        $stmt = $pdo->prepare("
            UPDATE phuong_thuc_thanh_toan
            SET ten_phuong_thuc = :n, mo_ta = :m, trang_thai = :t
            WHERE id_phuong_thuc = :id
        ");
        $stmt->execute([
            ':n'  => $_POST['name']       ?? '',
            ':m'  => $_POST['mo_ta']      ?? '',
            ':t'  => $_POST['trang_thai'] ?? 'hien',
            ':id' => $id,
        ]);
    } else {
        // Thêm mới
        $stmt = $pdo->prepare("
            INSERT INTO phuong_thuc_thanh_toan (ten_phuong_thuc, mo_ta, trang_thai)
            VALUES (:n, :m, :t)
        ");
        $stmt->execute([
            ':n' => $_POST['name']       ?? '',
            ':m' => $_POST['mo_ta']      ?? '',
            ':t' => $_POST['trang_thai'] ?? 'hien',
        ]);
    }

    redirect('admin/payment_methods.php');
}

$rows = $pdo->query("SELECT * FROM phuong_thuc_thanh_toan ORDER BY id_phuong_thuc ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Phương thức thanh toán</h1>
        <p class="text-muted mb-0">Quản lý các phương thức thanh toán có sẵn.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form thêm mới -->
    <div class="col-md-4">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <h5 class="fw-700 mb-3">Thêm phương thức</h5>
            <form method="post">
                <input type="hidden" name="id" value="0">
                <div class="mb-3">
                    <label class="form-label">Tên phương thức</label>
                    <input name="name" class="form-control" placeholder="VD: Tiền mặt, Momo..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea name="mo_ta" class="form-control" rows="2"
                              placeholder="Mô tả ngắn (tùy chọn)"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="hien">Hiện</option>
                        <option value="an">Ẩn</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100">
                    <i class="bi bi-plus-lg me-2"></i>Thêm
                </button>
            </form>
        </div>
    </div>

    <!-- Danh sách -->
    <div class="col-md-8">
        <div class="bg-white p-4 rounded-4 shadow-sm table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="fw-600"><?= h($r['ten_phuong_thuc']) ?></td>
                        <td class="text-muted small"><?= h($r['mo_ta'] ?? '—') ?></td>
                        <td>
                            <span class="badge rounded-pill bg-<?= $r['trang_thai'] === 'hien' ? 'success' : 'secondary' ?>">
                                <?= $r['trang_thai'] === 'hien' ? 'Hiện' : 'Ẩn' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">Chưa có phương thức nào.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
