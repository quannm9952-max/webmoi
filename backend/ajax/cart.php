<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/CartController.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode((new CartController(db_connect()))->ajaxAdd($_POST), JSON_UNESCAPED_UNICODE);
