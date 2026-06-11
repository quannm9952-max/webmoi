<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Brand.php';

class ProductController
{
    public function __construct(private PDO $pdo) {}

    public function shopData(array $query): array
    {
        $pm = new Product($this->pdo);
        $cm = new Category($this->pdo);
        $bm = new Brand($this->pdo);

        $cat = max(0, (int)($query['category'] ?? 0));
        $brand = max(0, (int)($query['brand'] ?? 0));
        $q = trim((string)($query['q'] ?? ''));
        $sort = (string)($query['sort'] ?? 'newest');

        $allowedSorts = ['newest', 'price_asc', 'price_desc'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        if ($q !== '') {
            $products = $pm->search($q, $sort);
            $title = 'Kết quả tìm kiếm: ' . $q;
        } elseif ($cat > 0 && $brand > 0) {
            $products = $pm->getByCategoryAndBrand($cat, $brand, $sort);
            $title = 'Sản phẩm đã lọc';
        } elseif ($cat > 0) {
            $products = $pm->getByCategory($cat, $sort);
            $title = $this->findCategoryName($cat) ?: 'Danh mục';
        } elseif ($brand > 0) {
            $products = $pm->getByBrand($brand, $sort);
            $title = $this->findBrandName($brand) ?: 'Thương hiệu';
        } else {
            $products = $pm->getAll($sort);
            $title = 'Tất cả sản phẩm';
        }

        return [
            'products' => $products,
            'categories' => $cm->getWithCount(),
            'brands' => $bm->getActiveBrands(),
            'cat' => $cat,
            'brand' => $brand,
            'q' => $q,
            'sort' => $sort,
            'heading' => $title,
        ];
    }

    private function findCategoryName(int $id): ?string
    {
        $stmt = $this->pdo->prepare("SELECT ten_danh_muc FROM danh_muc WHERE id_danh_muc = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $name = $stmt->fetchColumn();
        return $name ? (string)$name : null;
    }

    private function findBrandName(int $id): ?string
    {
        $stmt = $this->pdo->prepare("SELECT ten_thuong_hieu FROM thuong_hieu WHERE id_thuong_hieu = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $name = $stmt->fetchColumn();
        return $name ? (string)$name : null;
    }
}
