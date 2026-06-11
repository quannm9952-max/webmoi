<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class OrderController
{
    public function __construct(private PDO $pdo)
    {
    }

    public function checkoutData(): array
    {
        require_login();

        if (!check_account_active($this->pdo)) {
            redirect('login.php');
        }

        $cart = new Cart($this->pdo);
        $cart->getOrCreateCartByUserId((int)$_SESSION['id_nguoi_dung']);

        $items = $cart->getCartItems();
        if (!$items) {
            redirect('cart.php');
        }

        $order = new Order($this->pdo);

        $user = (new User($this->pdo))->findById((int)$_SESSION['id_nguoi_dung']);

        return [
            'items' => $items,
            'total' => $cart->getTotalPrice(),
            'methods' => $order->getPaymentMethods(),
            'error' => flash('error'),
            'user'  => $user,
        ];
    }

    public function handleCheckoutPost(): void
    {
        if (!is_post()) {
            return;
        }

        require_login();

        if (!check_account_active($this->pdo)) {
            redirect('login.php');
        }

        $result = (new Order($this->pdo))->createFromCart((int)$_SESSION['id_nguoi_dung'], $_POST);

        if (!empty($result['success'])) {
            redirect('order_complete.php?id=' . (int)$result['id_don_hang']);
        }

        $_SESSION['error'] = $result['message'] ?? 'Không thể tạo đơn hàng.';
        redirect('checkout.php');
    }

    public function myOrdersData(): array
    {
        require_login();

        return [
            'orders' => (new Order($this->pdo))->getOrdersByUser((int)$_SESSION['id_nguoi_dung']),
        ];
    }

    public function completeData(int $id): array
    {
        require_login();

        $orderModel = new Order($this->pdo);
        $order = $orderModel->getByIdForUser($id, (int)$_SESSION['id_nguoi_dung']);

        if (!$order) {
            redirect('my_orders.php');
        }

        return [
            'o' => $order,
            'items' => $orderModel->getItems((int)$order['id_don_hang']),
        ];
    }
}
