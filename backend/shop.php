<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/controllers/ProductController.php';

$data = (new ProductController(db_connect()))->shopData($_GET);

$products = $data['products'];
$categories = $data['categories'];
$brands = $data['brands'];
$cat = $data['cat'];
$brand = $data['brand'];
$q = $data['q'];
$sort = $data['sort'];
$heading = $data['heading'];

$page_title = 'Cửa hàng — TechShop';
require_once __DIR__ . '/includes/header.php';
?>
<main class="container py-2">
    <?php require __DIR__ . '/includes/hero.php'; ?>

    <div class="row g-4 mt-2" id="shop-content">

        <!-- Sidebar -->
        <aside class="col-lg-3">
            <div class="filter-sidebar">
                <h6>Danh mục</h6>
                <ul class="filter-list">
                    <li>
                        <a href="<?= BASE_URL ?>/shop.php<?= $brand ? '?brand=' . (int)$brand : '' ?>#shop-content"
                           class="<?= !$cat ? 'active' : '' ?>">
                            Tất cả sản phẩm
                            <span class="filter-badge"><?= array_sum(array_map('intval', array_column($categories, 'so_luong'))) ?></span>
                        </a>
                    </li>

                    <?php foreach ($categories as $c): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/shop.php?category=<?= (int)$c['id_danh_muc'] ?><?= $brand ? '&brand=' . (int)$brand : '' ?>#shop-content"
                           class="<?= $cat === (int)$c['id_danh_muc'] ? 'active' : '' ?>">
                            <?= h($c['ten_danh_muc']) ?>
                            <span class="filter-badge"><?= (int)$c['so_luong'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <h6>Thương hiệu</h6>
                <ul class="filter-list">
                    <?php foreach ($brands as $b): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/shop.php?brand=<?= (int)$b['id'] ?><?= $cat ? '&category=' . (int)$cat : '' ?>#shop-content"
                           class="<?= $brand === (int)$b['id'] ? 'active' : '' ?>">
                            <?= h($b['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Products -->
        <section class="col-lg-9">
            <!-- Sort bar -->
            <div class="sort-bar">
                <div>
                    <span class="section-heading">
                        <?php if ($q): ?>
                            Kết quả cho "<strong><?= h($q) ?></strong>"
                        <?php elseif ($cat): ?>
                            <?= h($heading ?: 'Danh mục') ?>
                        <?php elseif ($brand): ?>
                            <?= h($heading ?: 'Thương hiệu') ?>
                        <?php else: ?>
                            Tất cả sản phẩm
                        <?php endif; ?>
                    </span>
                    <small class="text-muted ms-2">(<?= count($products) ?> sản phẩm)</small>
                </div>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <?php if ($cat): ?><input type="hidden" name="category" value="<?= (int)$cat ?>"><?php endif; ?>
                    <?php if ($brand): ?><input type="hidden" name="brand" value="<?= (int)$brand ?>"><?php endif; ?>
                    <?php if ($q): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Giá thấp → cao</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá cao → thấp</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h5>Không tìm thấy sản phẩm nào</h5>
                <p>Hãy thử tìm kiếm với từ khóa khác.</p>
                <a href="<?= BASE_URL ?>/shop.php#shop-content" class="btn btn-primary mt-2">Xem tất cả</a>
            </div>
            <?php else: ?>
            <div class="row row-cols-2 row-cols-md-3 g-3">
                <?php foreach ($products as $p):
                    $price = (!empty($p['gia_giam']) && $p['gia_giam'] < $p['gia_ban']) ? $p['gia_giam'] : $p['gia_ban'];
                    $hasDiscount = !empty($p['gia_giam']) && $p['gia_giam'] < $p['gia_ban'];
                    $discountPct = $hasDiscount ? round((1 - $p['gia_giam'] / $p['gia_ban']) * 100) : 0;
                ?>
                <div class="col">
                    <div class="product-card">
                        <?php if ($hasDiscount): ?>
                            <span class="p-badge">-<?= $discountPct ?>%</span>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id_san_pham'] ?>">
                            <div class="p-img-box">
                                <img src="<?= product_img_url($p['hinh_anh']) ?>"
                                     alt="<?= h($p['ten_san_pham']) ?>" loading="lazy">
                            </div>
                        </a>
                        <div class="p-content">
                            <div class="p-brand"><?= h($p['ten_thuong_hieu'] ?? '') ?></div>
                            <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$p['id_san_pham'] ?>" class="p-title"><?= h($p['ten_san_pham']) ?></a>
                            <div class="mt-auto">
                                <span class="p-price"><?= format_price($price) ?></span>
                                <?php if ($hasDiscount): ?>
                                    <span class="p-old-price"><?= format_price($p['gia_ban']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="p-actions">
                                <button class="btn-add-cart ajax-cart-btn" data-id="<?= (int)$p['id_san_pham'] ?>">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </button>
                                <button class="btn-fav ajax-favorite-btn" data-id="<?= (int)$p['id_san_pham'] ?>" title="Yêu thích">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
