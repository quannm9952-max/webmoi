<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handleLogin(): void
    {
        if (is_logged_in()) {
            redirect('shop.php');
        }

        if (!is_post()) {
            return;
        }

        $_SESSION['_old'] = ['email' => $_POST['email'] ?? ''];

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Vui lòng nhập email và mật khẩu.';
            $_SESSION['_old_login'] = ['email' => $email];
            redirect('login.php');
        }

        $user = (new User($this->pdo))->findByEmail($email);

        if (!$user || !password_verify($password, (string)$user['mat_khau'])) {
            $_SESSION['error'] = 'Email hoặc mật khẩu không chính xác.';
            $_SESSION['_old_login'] = ['email' => $email];
            redirect('login.php');
        }

        if (($user['trang_thai'] ?? '') !== 'hoat_dong') {
            $_SESSION['error'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.';
            $_SESSION['_old_login'] = ['email' => $email];
            redirect('login.php');
        }

        create_login_session($user);

        $redirectTo = $_SESSION['redirect_after_login'] ?? 'shop.php';
        unset($_SESSION['redirect_after_login'], $_SESSION['_old_login']);

        header('Location: ' . $redirectTo);
        exit;
    }

    public function handleRegister(): void
    {
        if (is_logged_in()) {
            redirect('shop.php');
        }

        if (!is_post()) {
            return;
        }

        $data = [
            'ho_ten'        => trim((string)($_POST['ho_ten'] ?? '')),
            'email'         => trim((string)($_POST['email'] ?? '')),
            'so_dien_thoai' => trim((string)($_POST['so_dien_thoai'] ?? '')),
            'mat_khau'      => (string)($_POST['password'] ?? ''),
            'dia_chi'       => trim((string)($_POST['dia_chi'] ?? '')),
            'id_vai_tro'    => 2,
        ];

        if (empty($_POST['agree_terms'])) {
            $_SESSION['error'] = 'Bạn cần đồng ý với điều khoản sử dụng và chính sách bảo mật.';
            $_SESSION['_old_register'] = $data;
            redirect('register.php');
        }

        if ($data['ho_ten'] === '' || $data['email'] === '' || $data['mat_khau'] === '') {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu.';
            $_SESSION['_old_register'] = $data;
            redirect('register.php');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email không hợp lệ hoặc sai định dạng.';
            $_SESSION['_old_register'] = $data;
            redirect('register.php');
        }

        if ($data['so_dien_thoai'] !== '' && !preg_match('/^[0-9]{10}$/', $data['so_dien_thoai'])) {
            $_SESSION['error'] = 'Số điện thoại phải bao gồm đúng 10 chữ số.';
            $_SESSION['_old_register'] = $data;
            redirect('register.php');
        }

        if (strlen($data['mat_khau']) < 6) {
            $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            $_SESSION['_old_register'] = $data;
            redirect('register.php');
        }

        $result = (new User($this->pdo))->create($data);

        if (empty($result['success'])) {
            $_SESSION['error'] = $result['message'] ?? 'Đăng ký thất bại.';
            $_SESSION['_old_register'] = $data;
            redirect('register.php');
        }

        unset($_SESSION['_old_register']);
        $_SESSION['success'] = 'Đăng ký thành công. Bạn có thể đăng nhập.';
        redirect('login.php');
    }

    public function logout(): void
    {
        logout_user();
        redirect('shop.php');
    }
}
