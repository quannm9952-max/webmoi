<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/Favorite.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
    exit;
}

$result = (new Favorite(db_connect()))->toggle(
    (int)$_SESSION['id_nguoi_dung'],
    (int)($_POST['id_san_pham'] ?? 0)
);

if (!isset($result['action'])) {
    $result['action'] = ($result['favorited'] ?? false) ? 'added' : 'removed';
}
if (!isset($result['message'])) {
    $result['message'] = $result['action'] === 'added'
        ? 'Đã thêm vào yêu thích!'
        : 'Đã xóa khỏi yêu thích.';
}

echo json_encode($result);
