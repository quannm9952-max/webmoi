<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../models/Product.php';

try {
    $id = max(0, (int)($_GET['id'] ?? 0));
    if ($id <= 0) json_response(['success' => false, 'message' => 'Thiếu id sản phẩm'], 400);

    $model = new Product(db_connect());
    $product = $model->getById($id);
    if (!$product) json_response(['success' => false, 'message' => 'Không tìm thấy sản phẩm'], 404);

    $product['image_url'] = absolute_upload_url($product['hinh_anh_chinh'] ?? $product['hinh_anh'] ?? null);
    json_response(['success' => true, 'data' => $product]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => APP_ENV === 'development' ? $e->getMessage() : 'Server error'], 500);
}
