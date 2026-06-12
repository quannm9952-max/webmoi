<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../models/User.php';

try {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true);

    $action = $_GET['action'] ?? '';

    if ($action === 'login') {
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if ($email === '' || $password === '') {
            json_response([
                'success' => false,
                'message' => 'Vui lòng nhập email và mật khẩu.'
            ], 400);
        }

        $user = (new User(db_connect()))->findByEmail($email);

        if (!$user || !password_verify($password, (string)$user['mat_khau'])) {
            json_response([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.'
            ], 401);
        }

        if (($user['trang_thai'] ?? '') !== 'hoat_dong') {
            json_response([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa.'
            ], 403);
        }

        create_login_session($user);

        json_response([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'user' => [
                'id_nguoi_dung' => $user['id_nguoi_dung'] ?? null,
                'ho_ten' => $user['ho_ten'] ?? '',
                'email' => $user['email'] ?? '',
                'id_vai_tro' => $user['id_vai_tro'] ?? null,
            ]
        ]);
    }

    json_response([
        'success' => false,
        'message' => 'Action không hợp lệ.'
    ], 400);

} catch (Throwable $e) {
    json_response([
        'success' => false,
        'message' => APP_ENV === 'development' ? $e->getMessage() : 'Server error'
    ], 500);
}