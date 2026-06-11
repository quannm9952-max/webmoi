<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class AdminUserController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handlePost(): void
    {
        require_admin();

        if (!is_post()) {
            return;
        }

        $action = (string)($_POST['_action'] ?? 'update');

        if ($action === 'create') {
            $data = [
                'ho_ten'        => trim((string)($_POST['ho_ten'] ?? '')),
                'email'         => trim((string)($_POST['email'] ?? '')),
                'so_dien_thoai' => trim((string)($_POST['so_dien_thoai'] ?? '')),
                'mat_khau'      => (string)($_POST['mat_khau'] ?? ''),
                'dia_chi'       => trim((string)($_POST['dia_chi'] ?? '')),
                'id_vai_tro'    => (int)($_POST['id_vai_tro'] ?? 2),
            ];

            if ($data['ho_ten'] === '' || $data['email'] === '' || $data['mat_khau'] === '') {
                $_SESSION['error'] = 'Vui lòng nhập đầy đủ họ tên, email và mật khẩu.';
                redirect('admin/users.php');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email không hợp lệ.';
                redirect('admin/users.php');
            }
            if (strlen($data['mat_khau']) < 6) {
                $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
                redirect('admin/users.php');
            }
            if ($data['so_dien_thoai'] !== '' && !preg_match('/^[0-9]{10}$/', $data['so_dien_thoai'])) {
                $_SESSION['error'] = 'Số điện thoại phải gồm đúng 10 chữ số.';
                redirect('admin/users.php');
            }

            $result = (new User($this->pdo))->create($data);
            if ($result['success']) {
                $_SESSION['success'] = 'Tạo người dùng thành công!';
            } else {
                $_SESSION['error'] = $result['message'] ?? 'Tạo thất bại.';
            }
            redirect('admin/users.php');
        }

        // Default: update role/status
        $id = (int)($_POST['id_nguoi_dung'] ?? 0);
        $targetUser = (new User($this->pdo))->findById($id);
        $isAjax = isset($_POST['_ajax']);

        if (!$targetUser) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại.']); exit; }
            $_SESSION['error'] = 'Người dùng không tồn tại.';
            redirect('admin/users.php');
        }

        if ($id === (int)$_SESSION['id_nguoi_dung']) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Bạn không thể tự sửa quyền của chính mình.']); exit; }
            $_SESSION['error'] = 'Bạn không thể tự sửa quyền/trạng thái của chính mình.';
            redirect('admin/users.php');
        }

        // Bảo vệ Super Admin (ID = 1)
        if ($id === 1) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Không thể thay đổi quyền hoặc khóa Super Admin tối cao.']); exit; }
            $_SESSION['error'] = 'Không thể thay đổi quyền hoặc khóa Super Admin tối cao.';
            redirect('admin/users.php');
        }

        // Admin thường không thể sửa Admin khác
        if ((int)$_SESSION['id_nguoi_dung'] !== 1 && (int)$targetUser['id_vai_tro'] === 1) {
            if ($isAjax) { echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thay đổi thông tin của một Admin khác.']); exit; }
            $_SESSION['error'] = 'Bạn không có quyền thay đổi thông tin của một Admin khác. Chỉ Super Admin (chủ web) mới có quyền này.';
            redirect('admin/users.php');
        }

        (new User($this->pdo))->updateRoleAndStatus(
            $id,
            (int)($_POST['id_vai_tro'] ?? 2),
            (string)($_POST['trang_thai'] ?? 'hoat_dong')
        );

        if ($isAjax) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật quyền/trạng thái thành công!']);
            exit;
        }

        $_SESSION['success'] = 'Đã cập nhật người dùng.';
        redirect('admin/users.php');
    }

    public function indexData(): array
    {
        require_admin();

        $keyword = trim((string)($_GET['q'] ?? ''));
        $users = (new User($this->pdo))->allUsers();

        if ($keyword !== '') {
            $kw = mb_strtolower($keyword);
            $users = array_values(array_filter($users, function ($u) use ($kw) {
                return str_contains(mb_strtolower((string)($u['ho_ten'] ?? '')), $kw)
                    || str_contains(mb_strtolower((string)($u['email'] ?? '')), $kw)
                    || str_contains(mb_strtolower((string)($u['ten_vai_tro'] ?? '')), $kw);
            }));
        }

        return [
            'users' => $users,
            'keyword' => $keyword,
            'success' => flash('success'),
            'error' => flash('error'),
        ];
    }
}
