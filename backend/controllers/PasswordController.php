<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class PasswordController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handleForgot(): array
    {
        if (is_logged_in()) {
            redirect('shop.php');
        }

        $demoResetLink = null;

        if (!is_post()) {
            return [
                'error' => flash('error'),
                'success' => flash('success'),
                'demoResetLink' => null,
            ];
        }

        $email = trim((string)($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Vui lòng nhập email hợp lệ.';
            redirect('forgot_password.php');
        }

        $user = (new User($this->pdo))->findByEmail($email);

        // Không tiết lộ email có tồn tại hay không.
        $_SESSION['success'] = 'Nếu email tồn tại trong hệ thống, link đặt lại mật khẩu đã được tạo.';

        if ($user && ($user['trang_thai'] ?? '') === 'hoat_dong') {
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);

            $this->pdo->prepare("
                UPDATE dat_lai_mat_khau
                SET da_su_dung = 1
                WHERE id_nguoi_dung = :uid AND da_su_dung = 0
            ")->execute([
                ':uid' => (int)$user['id_nguoi_dung'],
            ]);

            $stmt = $this->pdo->prepare("
                INSERT INTO dat_lai_mat_khau(id_nguoi_dung, token_hash, het_han)
                VALUES(:uid, :hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))
            ");
            $stmt->execute([
                ':uid' => (int)$user['id_nguoi_dung'],
                ':hash' => $tokenHash,
            ]);

            $demoResetLink = rtrim(BASE_URL, '/') . '/reset_password.php?token=' . urlencode($rawToken);

            // Demo đồ án local: lưu link vào session để hiển thị.
            // Khi có mail thật, thay đoạn này bằng gửi email.
            $_SESSION['demo_reset_link'] = $demoResetLink;
        }

        redirect('forgot_password.php');
    }

    public function forgotViewData(): array
    {
        $demoResetLink = $_SESSION['demo_reset_link'] ?? null;
        unset($_SESSION['demo_reset_link']);

        return [
            'error' => flash('error'),
            'success' => flash('success'),
            'demoResetLink' => is_string($demoResetLink) ? $demoResetLink : null,
        ];
    }

    public function handleReset(): array
    {
        if (is_logged_in()) {
            redirect('shop.php');
        }

        $token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));

        if ($token === '') {
            $_SESSION['error'] = 'Token đặt lại mật khẩu không hợp lệ.';
            redirect('forgot_password.php');
        }

        $reset = $this->findValidResetToken($token);

        if (!$reset) {
            return [
                'error' => 'Link đặt lại mật khẩu không hợp lệ, đã hết hạn hoặc đã được sử dụng.',
                'success' => null,
                'token' => '',
                'valid' => false,
            ];
        }

        if (!is_post()) {
            return [
                'error' => flash('error'),
                'success' => null,
                'token' => $token,
                'valid' => true,
            ];
        }

        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirm'] ?? '');

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            redirect('reset_password.php?token=' . urlencode($token));
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Mật khẩu xác nhận không khớp.';
            redirect('reset_password.php?token=' . urlencode($token));
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->prepare("
                UPDATE nguoi_dung
                SET mat_khau = :password
                WHERE id_nguoi_dung = :uid
            ")->execute([
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':uid' => (int)$reset['id_nguoi_dung'],
            ]);

            $this->pdo->prepare("
                UPDATE dat_lai_mat_khau
                SET da_su_dung = 1
                WHERE id_token = :id
            ")->execute([
                ':id' => (int)$reset['id_token'],
            ]);

            $this->pdo->commit();

            $_SESSION['success'] = 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập.';
            redirect('login.php');
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            $_SESSION['error'] = 'Không thể đặt lại mật khẩu. Vui lòng thử lại.';
            redirect('reset_password.php?token=' . urlencode($token));
        }
    }

    private function findValidResetToken(string $rawToken): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/i', $rawToken)) {
            return null;
        }

        $hash = hash('sha256', $rawToken);

        $stmt = $this->pdo->prepare("
            SELECT rt.*, nd.email, nd.ho_ten
            FROM dat_lai_mat_khau rt
            JOIN nguoi_dung nd ON nd.id_nguoi_dung = rt.id_nguoi_dung
            WHERE rt.token_hash = :hash
              AND rt.da_su_dung = 0
              AND rt.het_han >= NOW()
              AND nd.trang_thai = 'hoat_dong'
            LIMIT 1
        ");
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
