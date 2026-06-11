<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/models/Favorite.php';
require_login();

$pdo = db_connect();
$um  = new User($pdo);
$u   = $um->findById((int)$_SESSION['id_nguoi_dung']);

if (is_post()) {
    $um->updateProfile((int)$u['id_nguoi_dung'], $_POST);
    $_SESSION['ho_ten'] = $_POST['ho_ten'];
    $_SESSION['success'] = 'Cập nhật thông tin thành công!';
    redirect('account.php');
}

$orders  = (new Order($pdo))->getOrdersByUser((int)$u['id_nguoi_dung']);
$favCount = (new Favorite($pdo))->countByUser((int)$u['id_nguoi_dung']);
$success = flash('success');

$orderStats = array_reduce($orders, function ($carry, $o) {
    $carry[$o['trang_thai_don_hang']] = ($carry[$o['trang_thai_don_hang']] ?? 0) + 1;
    return $carry;
}, []);

$page_title = 'Tài khoản — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item active">Tài khoản của tôi</li>
        </ol>
    </nav>

    <div class="section-heading mb-4">
        <i class="bi bi-person-gear me-2"></i>Tài khoản của tôi
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-check-circle-fill"></i><?= h($success) ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-4">
            <div class="profile-card mb-4">
                <div class="profile-card-header">
                    <div class="profile-avatar">
                        <?= strtoupper(mb_substr($u['ho_ten'] ?? 'U', 0, 1, 'UTF-8')) ?>
                    </div>
                    <div class="fw-800 fs-5"><?= h($u['ho_ten']) ?></div>
                    <div class="opacity-75" style="font-size:13px"><?= h($u['email']) ?></div>
                    <?php if (is_admin()): ?>
                    <span class="badge bg-warning text-dark mt-2">
                        <i class="bi bi-shield-fill me-1"></i>Quản trị viên
                    </span>
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <div class="stat-mini">
                        <i class="bi bi-box-seam text-primary" style="font-size:20px;width:28px"></i>
                        <div>
                            <div class="value"><?= count($orders) ?></div>
                            <div class="label">Đơn hàng</div>
                        </div>
                        <a href="<?= BASE_URL ?>/my_orders.php" class="btn btn-sm btn-outline-primary ms-auto">Xem</a>
                    </div>
                    <div class="stat-mini">
                        <i class="bi bi-heart-fill text-danger" style="font-size:20px;width:28px"></i>
                        <div>
                            <div class="value"><?= (int)$favCount ?></div>
                            <div class="label">Sản phẩm yêu thích</div>
                        </div>
                        <a href="<?= BASE_URL ?>/favorites.php" class="btn btn-sm btn-outline-danger ms-auto">Xem</a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm p-3">
                <a href="<?= BASE_URL ?>/my_orders.php" class="account-menu dropdown-item rounded-3 mb-1">
                    <i class="bi bi-box-seam"></i> Đơn hàng của tôi
                </a>
                <a href="<?= BASE_URL ?>/favorites.php" class="account-menu dropdown-item rounded-3 mb-1">
                    <i class="bi bi-heart"></i> Sản phẩm yêu thích
                </a>
                <?php if (is_admin()): ?>
                <a href="<?= BASE_URL ?>/admin/index.php" class="account-menu dropdown-item rounded-3 mb-1 text-primary fw-semibold">
                    <i class="bi bi-speedometer2"></i> Trang quản trị
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/logout.php" class="account-menu dropdown-item rounded-3 text-danger fw-semibold">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bg-white rounded-4 shadow-sm p-4">
                <h5 class="fw-800 mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Cập nhật thông tin</h5>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="ho_ten">
                                <i class="bi bi-person me-1 text-primary"></i>Họ và tên
                            </label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control"
                                   value="<?= h($u['ho_ten']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-envelope me-1 text-primary"></i>Email
                            </label>
                            <input type="email" class="form-control bg-light"
                                   value="<?= h($u['email']) ?>" disabled>
                            <div class="form-text">Email không thể thay đổi.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="so_dien_thoai">
                                <i class="bi bi-telephone me-1 text-primary"></i>Số điện thoại
                            </label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai"
                                   class="form-control"
                                   value="<?= h($u['so_dien_thoai']) ?>"
                                   placeholder="0901 234 567">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="dia_chi">
                                <i class="bi bi-geo-alt me-1 text-primary"></i>Địa chỉ
                            </label>
                            <textarea id="dia_chi" name="dia_chi" class="form-control" rows="3"
                                      placeholder="Địa chỉ giao hàng mặc định"><?= h($u['dia_chi']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
