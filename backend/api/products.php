<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../controllers/ProductController.php';

try {
    $controller = new ProductController(db_connect());
    $data = $controller->shopData($_GET);

    foreach ($data['products'] as &$product) {
        $product['image_url'] = absolute_upload_url($product['hinh_anh_chinh'] ?? $product['hinh_anh'] ?? null);
    }

    json_response(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => APP_ENV === 'development' ? $e->getMessage() : 'Server error'], 500);
}
