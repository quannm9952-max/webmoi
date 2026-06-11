<?php
declare(strict_types=1);

function is_logged_in(): bool
{
    return !empty($_SESSION['id_nguoi_dung']);
}

function current_user(): ?array
{
    return is_logged_in() ? [
        'id_nguoi_dung' => $_SESSION['id_nguoi_dung'],
        'id_vai_tro' => $_SESSION['id_vai_tro'] ?? null,
        'ten_vai_tro' => $_SESSION['ten_vai_tro'] ?? null,
        'ho_ten' => $_SESSION['ho_ten'] ?? null,
        'email' => $_SESSION['email'] ?? null,
    ] : null;
}

function create_login_session(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['id_nguoi_dung'] = (int)$user['id_nguoi_dung'];
    $_SESSION['id_vai_tro'] = (int)$user['id_vai_tro'];
    $_SESSION['ten_vai_tro'] = strtolower((string)$user['ten_vai_tro']);
    $_SESSION['ho_ten'] = (string)$user['ho_ten'];
    $_SESSION['email'] = (string)$user['email'];
    $_SESSION['so_dien_thoai'] = $user['so_dien_thoai'] ?? null;
}

function require_login(): void
{
    sync_user_session();
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('login.php');
    }
}

function check_account_active(PDO $pdo): bool
{
    if (!is_logged_in()) return false;
    try {
        $s = $pdo->prepare("SELECT trang_thai FROM nguoi_dung WHERE id_nguoi_dung = :id LIMIT 1");
        $s->execute([':id' => (int)$_SESSION['id_nguoi_dung']]);
        $row = $s->fetchColumn();
        if ($row !== 'hoat_dong') {
            logout_user();
            $_SESSION['error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.';
            return false;
        }
    } catch (Throwable $e) {
        return false;
    }
    return true;
}

function sync_user_session(): void
{
    if (!is_logged_in()) return;
    try {
        $pdo = db_connect();
        $s = $pdo->prepare("
            SELECT nd.trang_thai, vt.ten_vai_tro, nd.ho_ten, nd.email
            FROM nguoi_dung nd 
            JOIN vai_tro vt ON nd.id_vai_tro = vt.id_vai_tro 
            WHERE nd.id_nguoi_dung = :id LIMIT 1
        ");
        $s->execute([':id' => (int)$_SESSION['id_nguoi_dung']]);
        $row = $s->fetch();
        
        if (!$row || $row['trang_thai'] !== 'hoat_dong') {
            logout_user();
        } else {
            $_SESSION['ten_vai_tro'] = strtolower($row['ten_vai_tro']);
            $_SESSION['ho_ten'] = $row['ho_ten'];
            $_SESSION['email'] = $row['email'];
        }
    } catch (Throwable $e) {
        // Ignore
    }
}

function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['ten_vai_tro'] ?? '') === 'admin';
}

function require_admin(): void
{
    require_login();
    sync_user_session(); // Đảm bảo quyền luôn được cập nhật mới nhất
    
    if (!is_admin()) {
        http_response_code(403);
        die('Bạn không có quyền truy cập khu vực admin.');
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
}
