<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Cart.php';

class CartController
{
    public function __construct(private PDO $pdo)
    {
    }

    private function cartForCurrentUser(): Cart
    {
        require_login();

        if (!check_account_active($this->pdo)) {
            redirect('login.php');
        }

        $cart = new Cart($this->pdo);
        $cart->getOrCreateCartByUserId((int)$_SESSION['id_nguoi_dung']);

        return $cart;
    }

    public function pageData(): array
    {
        $cart = $this->cartForCurrentUser();

        return [
            'items' => $cart->getCartItems(),
            'total' => $cart->getTotalPrice(),
            'cart_count' => $cart->getItemCount(),
        ];
    }

    public function handlePost(): void
    {
        if (!is_post()) {
            return;
        }

        $cart = $this->cartForCurrentUser();
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'update') {
            $cart->updateItem(
                (int)($_POST['id_san_pham'] ?? 0),
                max(1, (int)($_POST['so_luong'] ?? 1))
            );
        } elseif ($action === 'remove') {
            $cart->removeItem((int)($_POST['id_san_pham'] ?? 0));
        } elseif ($action === 'clear') {
            $cart->clearCart();
        }

        redirect('cart.php');
    }

    public function ajaxAdd(array $post): array
    {
        if (!is_logged_in()) {
            return ['success' => false, 'message' => 'Bạn cần đăng nhập để thêm giỏ hàng.'];
        }

        if (!check_account_active($this->pdo)) {
            return ['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.'];
        }

        try {
            $cart = new Cart($this->pdo);
            $cart->getOrCreateCartByUserId((int)$_SESSION['id_nguoi_dung']);

            $ok = $cart->addItem(
                (int)($post['id_san_pham'] ?? 0),
                max(1, (int)($post['so_luong'] ?? 1))
            );

            return [
                'success' => $ok,
                'message' => $ok ? 'Đã thêm sản phẩm vào giỏ hàng!' : 'Không thể thêm sản phẩm.',
                'cart_count' => $cart->getItemCount(),
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
}
