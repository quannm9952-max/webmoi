<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Order.php';

class AdminOrderController
{
    public const STATUS_LABELS = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan'  => 'Đã xác nhận',
        'dang_giao'    => 'Đang giao',
        'da_giao'      => 'Đã giao',
        'da_huy'       => 'Đã hủy',
    ];

    public const STATUS_BADGES = [
        'cho_xac_nhan' => 'warning',
        'da_xac_nhan'  => 'primary',
        'dang_giao'    => 'info',
        'da_giao'      => 'success',
        'da_huy'       => 'danger',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function handlePost(): void
    {
        require_admin();

        if (!is_post()) {
            return;
        }

        (new Order($this->pdo))->updateStatus(
            (int)($_POST['id_don_hang'] ?? 0),
            (string)($_POST['trang_thai_don_hang'] ?? '')
        );

        $_SESSION['success'] = 'Đã cập nhật trạng thái đơn hàng.';
        redirect('admin/orders.php');
    }

    public function indexData(): array
    {
        require_admin();

        $status = trim((string)($_GET['status'] ?? ''));
        $keyword = trim((string)($_GET['q'] ?? ''));

        $orders = (new Order($this->pdo))->getAllOrders();

        if ($status !== '') {
            $orders = array_values(array_filter($orders, fn($o) => ($o['trang_thai_don_hang'] ?? '') === $status));
        }

        if ($keyword !== '') {
            $kw = mb_strtolower($keyword);
            $orders = array_values(array_filter($orders, function ($o) use ($kw) {
                return str_contains((string)$o['id_don_hang'], $kw)
                    || str_contains(mb_strtolower((string)($o['ho_ten'] ?? '')), $kw)
                    || str_contains(mb_strtolower((string)($o['email'] ?? '')), $kw)
                    || str_contains(mb_strtolower((string)($o['ten_nguoi_nhan'] ?? '')), $kw);
            }));
        }

        return [
            'orders' => $orders,
            'status' => $status,
            'keyword' => $keyword,
            'success' => flash('success'),
            'error' => flash('error'),
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    public static function statusBadge(string $status): string
    {
        return self::STATUS_BADGES[$status] ?? 'secondary';
    }
}
