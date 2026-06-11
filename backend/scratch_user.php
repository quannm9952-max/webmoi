<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/User.php';

$pdo = db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $u = new User($pdo);
    $res = $u->create([
        'ho_ten' => 'Test User',
        'email' => 'test@test.com',
        'mat_khau' => '123456',
        'so_dien_thoai' => '',
        'dia_chi' => '',
        'id_vai_tro' => 2
    ]);
    var_dump($res);
} catch (Exception $e) {
    echo $e->getMessage();
}
