<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Review.php';

$p = (new Product(db_connect()))->getById((int)($_GET['id'] ?? 0));
if (!$p) {
    http_response_code(404);
    require __DIR__ . '/includes/header.php';
    echo '<main class="container py-5 text-center"><i class="bi bi-exclamation-circle display-1 text-muted"></i><h3 class="mt-3">Không tìm thấy sản phẩm</h3><a href="' . BASE_URL . '/shop.php" class="btn btn-primary mt-3">Quay lại cửa hàng</a></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$price       = (!empty($p['gia_giam']) && $p['gia_giam'] < $p['gia_ban']) ? $p['gia_giam'] : $p['gia_ban'];
$hasDiscount = !empty($p['gia_giam']) && $p['gia_giam'] < $p['gia_ban'];
$discountPct = $hasDiscount ? round((1 - $p['gia_giam'] / $p['gia_ban']) * 100) : 0;
$inStock     = (int)$p['so_luong_ton'] > 0;
$stockClass  = (int)$p['so_luong_ton'] <= 5 ? 'low' : '';
$detailDescription = trim((string)($p["mo_ta_chi_tiet"] ?? ""));
$shortDescription  = trim((string)($p["mo_ta_ngan"] ?? ""));
$productCode       = trim((string)($p["ma_san_pham"] ?? ""));
$categoryName      = trim((string)($p["ten_danh_muc"] ?? ""));
$brandName         = trim((string)($p["ten_thuong_hieu"] ?? ""));
$reviewModel       = new Review(db_connect());
$productReviews    = $reviewModel->getPublicByProduct((int)$p['id_san_pham']);
$reviewStats       = $reviewModel->getStatsByProduct((int)$p['id_san_pham']);


$page_title = h($p['ten_san_pham']) . ' — TechShop';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:13px">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/shop.php">Cửa hàng</a></li>
            <?php if (!empty($p['ten_danh_muc'])): ?>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/shop.php?category=<?= (int)$p['id_danh_muc'] ?>">
                    <?= h($p['ten_danh_muc']) ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= h($p['ten_san_pham']) ?></li>
        </ol>
    </nav>

    <div class="bg-white rounded-4 shadow-sm p-4">
        <div class="row g-4 align-items-start">

            <!-- Image -->
            <div class="col-lg-5">
                <div class="product-detail-img">
                    <img src="<?= product_img_url($p['hinh_anh']) ?>"
                         alt="<?= h($p['ten_san_pham']) ?>">
                </div>
            </div>

            <!-- Info -->
            <div class="col-lg-7">
                <div class="p-brand mb-1"><?= h($p['ten_thuong_hieu'] ?? '') ?></div>
                <h1 class="fw-800" style="font-size:clamp(20px,3vw,28px);line-height:1.3"><?= h($p['ten_san_pham']) ?></h1>

                <?php if (!empty($p['mo_ta_ngan'])): ?>
                <p class="text-muted mt-2" style="font-size:15px"><?= h($p['mo_ta_ngan']) ?></p>
                <?php endif; ?>

                <hr class="my-3">

                <!-- Price -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="p-price" style="font-size:32px"><?= format_price($price) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="p-old-price" style="font-size:18px"><?= format_price($p['gia_ban']) ?></span>
                        <span class="badge bg-danger" style="font-size:14px">-<?= $discountPct ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Stock -->
                <div class="mt-3">
                    <?php if ($inStock): ?>
                        <span class="stock-indicator <?= $stockClass ?>">
                            <span class="dot"></span>
                            <?= (int)$p['so_luong_ton'] <= 5
                                ? 'Chỉ còn ' . (int)$p['so_luong_ton'] . ' sản phẩm'
                                : 'Còn hàng (' . (int)$p['so_luong_ton'] . ' sản phẩm)' ?>
                        </span>
                    <?php else: ?>
                        <span class="stock-indicator out"><span class="dot"></span>Hết hàng</span>
                    <?php endif; ?>
                </div>

                <hr class="my-3">

                <!-- Qty + Actions -->
                <?php if ($inStock): ?>
                <div class="d-flex flex-column gap-3 mt-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-600">Số lượng:</span>
                        <div class="qty-selector d-flex align-items-center bg-light border rounded-3" style="height: 44px; padding: 0 5px;">
                            <button type="button" class="btn text-muted qty-btn d-flex align-items-center justify-content-center p-0" data-dir="down" style="width:36px;height:100%;border:none;font-size:20px;">−</button>
                            <input type="number" id="qty-input" value="1" min="1"
                                   max="<?= (int)$p['so_luong_ton'] ?>" class="text-center fw-bold border-0 bg-transparent p-0" style="width:40px;height:100%;-moz-appearance:textfield;outline:none;">
                            <button type="button" class="btn text-muted qty-btn d-flex align-items-center justify-content-center p-0" data-dir="up" style="width:36px;height:100%;border:none;font-size:20px;">+</button>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button class="btn btn-primary btn-lg flex-fill fw-bold text-white ajax-buy-now-btn rounded-3"
                                data-id="<?= (int)$p['id_san_pham'] ?>" style="height: 54px; background: linear-gradient(135deg, #0ea5e9, #3b82f6); border: none; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);">
                            Mua ngay
                        </button>
                        <button class="btn btn-outline-primary btn-lg ajax-cart-btn flex-fill fw-bold rounded-3"
                                data-id="<?= (int)$p['id_san_pham'] ?>" style="height: 54px; border-width: 2px;">
                            <i class="bi bi-cart-plus me-2"></i>Thêm giỏ hàng
                        </button>
                        <button class="btn btn-outline-danger btn-lg ajax-favorite-btn rounded-3"
                                data-id="<?= (int)$p['id_san_pham'] ?>" title="Thêm yêu thích" style="height: 54px; width: 54px; display: flex; align-items: center; justify-content: center; border-width: 2px;">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="mt-4">
                    <button class="btn btn-secondary btn-lg fw-bold rounded-3" disabled style="height: 54px; width: 100%;">
                        <i class="bi bi-x-circle me-2"></i>Hết hàng
                    </button>
                </div>
                <?php endif; ?>

                <!-- Badges -->
                <div class="d-flex gap-2 flex-wrap mt-4">
                    <span class="badge bg-light text-dark border py-2 px-3">
                        <i class="bi bi-shield-check text-success me-1"></i>Bảo hành chính hãng
                    </span>
                    <span class="badge bg-light text-dark border py-2 px-3">
                        <i class="bi bi-truck text-primary me-1"></i>Giao hàng toàn quốc
                    </span>
                    <span class="badge bg-light text-dark border py-2 px-3">
                        <i class="bi bi-arrow-repeat text-warning me-1"></i>Đổi trả 7 ngày
                    </span>
                </div>
            </div>
        </div>

        <hr class="mt-4">

        <!-- Product Detail Description -->
        <section class="product-description mt-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-800 mb-1">Mô tả chi tiết sản phẩm</h5>
                    <p class="text-muted mb-0" style="font-size:14px">
                        Thông tin tổng quan, điểm nổi bật và thông số cơ bản của sản phẩm.
                    </p>
                </div>
                <?php if ($hasDiscount): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">Đang giảm <?= $discountPct ?>%</span>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-light">
                        <div class="fw-bold mb-1"><i class="bi bi-stars text-warning me-1"></i>Tổng quan</div>
                        <div class="text-muted" style="font-size:14px;line-height:1.7">
                            <?= $shortDescription !== '' ? h($shortDescription) : 'Sản phẩm được lựa chọn nhằm đáp ứng nhu cầu học tập, làm việc và giải trí hằng ngày với mức giá phù hợp.' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-light">
                        <div class="fw-bold mb-1"><i class="bi bi-award text-primary me-1"></i>Thương hiệu</div>
                        <div class="text-muted" style="font-size:14px;line-height:1.7">
                            <?= $brandName !== '' ? h($brandName) : 'Sản phẩm chính hãng, có nguồn gốc rõ ràng.' ?><?= $categoryName !== '' ? ' thuộc danh mục ' . h($categoryName) . '.' : '' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-light">
                        <div class="fw-bold mb-1"><i class="bi bi-shield-check text-success me-1"></i>Cam kết</div>
                        <div class="text-muted" style="font-size:14px;line-height:1.7">Bảo hành chính hãng, hỗ trợ đổi trả theo chính sách và giao hàng toàn quốc.</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="border rounded-4 p-4 h-100">
                        <h6 class="fw-bold mb-3"><i class="bi bi-card-text me-2"></i>Thông tin mô tả</h6>
                        <?php if ($detailDescription !== ''): ?>
                            <div style="font-size:15px;line-height:1.9;color:#334155"><?= nl2br(h($detailDescription)) ?></div>
                        <?php else: ?>
                            <div style="font-size:15px;line-height:1.9;color:#334155">
                                <p><strong><?= h($p['ten_san_pham']) ?></strong> là sản phẩm phù hợp cho người dùng đang tìm kiếm một thiết bị công nghệ ổn định, tiện lợi và có tính ứng dụng cao. Sản phẩm hỗ trợ tốt các nhu cầu phổ biến như học tập, làm việc, giải trí, lưu trữ dữ liệu và sử dụng hằng ngày.</p>
                                <p>Với thiết kế hiện đại, dễ sử dụng và mức giá hợp lý, sản phẩm giúp người dùng có trải nghiệm mua sắm hiệu quả hơn. Đây là lựa chọn đáng cân nhắc trong phân khúc <?= $categoryName !== '' ? h($categoryName) : 'sản phẩm điện tử' ?> tại TechShop.</p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-check2-circle me-2"></i>Điểm nổi bật</h6>
                            <div class="row g-2">
                                <div class="col-md-6"><div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Thiết kế hiện đại, phù hợp nhiều nhu cầu sử dụng.</span></div></div>
                                <div class="col-md-6"><div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Sản phẩm chính hãng, thông tin rõ ràng.</span></div></div>
                                <div class="col-md-6"><div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Giá bán minh bạch, hỗ trợ khuyến mãi khi có.</span></div></div>
                                <div class="col-md-6"><div class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Dễ dàng đặt hàng và theo dõi tình trạng đơn hàng.</span></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="border rounded-4 p-4 h-100 bg-light">
                        <h6 class="fw-bold mb-3"><i class="bi bi-list-ul me-2"></i>Thông số sản phẩm</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <?php if ($productCode !== ''): ?><tr><th class="text-muted fw-normal">Mã sản phẩm</th><td class="text-end fw-semibold"><?= h($productCode) ?></td></tr><?php endif; ?>
                                    <tr><th class="text-muted fw-normal">Danh mục</th><td class="text-end fw-semibold"><?= $categoryName !== '' ? h($categoryName) : 'Đang cập nhật' ?></td></tr>
                                    <tr><th class="text-muted fw-normal">Thương hiệu</th><td class="text-end fw-semibold"><?= $brandName !== '' ? h($brandName) : 'Đang cập nhật' ?></td></tr>
                                    <tr><th class="text-muted fw-normal">Giá bán</th><td class="text-end fw-semibold text-primary"><?= format_price($price) ?></td></tr>
                                    <tr><th class="text-muted fw-normal">Tình trạng</th><td class="text-end fw-semibold"><?= $inStock ? 'Còn hàng' : 'Hết hàng' ?></td></tr>
                                    <tr><th class="text-muted fw-normal">Tồn kho</th><td class="text-end fw-semibold"><?= (int)$p['so_luong_ton'] ?> sản phẩm</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-primary mt-3 mb-0" style="font-size:14px"><i class="bi bi-info-circle me-1"></i>Liên hệ TechShop để được tư vấn cấu hình, chính sách bảo hành và ưu đãi mới nhất.</div>
                    </div>
                </div>
            </div>
        </section>
        <hr class="mt-4">

        <section class="product-reviews mt-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-800 mb-1"><i class="bi bi-star-fill text-warning me-2"></i>Đánh giá từ khách hàng</h5>
                    <p class="text-muted mb-0" style="font-size:14px">
                        <?= (int)$reviewStats['total'] ?> feedback • Trung bình <?= h((string)$reviewStats['avg_star']) ?>/5 sao
                    </p>
                </div>
            </div>

            <?php if (!$productReviews): ?>
                <div class="border rounded-4 p-4 text-center text-muted bg-light">
                    Chưa có feedback cho sản phẩm này.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($productReviews as $rv): ?>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100 bg-light">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong><?= h($rv['ho_ten'] ?? 'Khách hàng') ?></strong>
                                    <span class="text-warning">
                                        <?= str_repeat('★', (int)$rv['so_sao']) ?><span class="text-muted"><?= str_repeat('☆', 5 - (int)$rv['so_sao']) ?></span>
                                    </span>
                                </div>
                                <div class="small text-muted mb-2"><?= h($rv['ngay_tao'] ?? '') ?></div>
                                <div style="white-space:pre-wrap"><?= h($rv['noi_dung'] ?? '') ?></div>
                                <?php if (!empty($rv['phan_hoi_admin'])): ?>
                                    <div class="alert alert-primary py-2 mt-3 mb-0" style="font-size:14px">
                                        <strong>TechShop phản hồi:</strong> <?= h($rv['phan_hoi_admin']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
