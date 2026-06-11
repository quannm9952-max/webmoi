<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/AdminCatalog.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $m = new AdminCatalog(db_connect());
    $m->deleteProduct($id);
}

redirect('admin/products.php');
