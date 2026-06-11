<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pdo = db_connect();
$users = $pdo->query("SELECT id_nguoi_dung, email, ho_ten, id_vai_tro FROM nguoi_dung ORDER BY id_nguoi_dung ASC LIMIT 5")->fetchAll();
print_r($users);
