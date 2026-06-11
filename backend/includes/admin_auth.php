<?php
require_once __DIR__ . '/auth.php';

//Kiểm tra có phải admin không
function isAdmin(): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    $ten_vai_tro = strtolower(trim($_SESSION['ten_vai_tro'] ?? ''));
    $admin_roles = ['admin', 'quan_tri', 'superadmin'];
    return in_array($ten_vai_tro, $admin_roles, true);
}

//Bắt buộc admin
function requireAdmin(
    string $login_url = '/login.php',
    string $forbidden_url = '/403.php'
): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . $login_url);
        exit();
    }

    if (!isAdmin()) {
        header('Location: ' . $forbidden_url);
        exit();
    }
}

//Lấy thông tin admin
function getCurrentAdmin(): ?array
{
    if (!isAdmin()) {
        return null;
    }

    return getCurrentUser();
}