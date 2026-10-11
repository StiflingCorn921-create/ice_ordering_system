<?php

class OrderController
{
    /** Public order form submit. Returns errors (to re-show the form) or redirects to success page. */
    public static function store(): array
    {
        verify_csrf();

        $result = OrderService::placeOrder($_POST);

        if ($result['ok']) {
            // Remember the last order in the session so success.php can show it without exposing data in the URL
            $_SESSION['last_order_id'] = (int) $result['order']['id'];
            redirect('public/success.php');
        }

        return $result['errors'];
    }

    /** Admin: change order status (from admin/orders.php). */
    public static function updateStatus(): void
    {
        require_login();
        verify_csrf();

        $result = OrderService::changeStatus((int) ($_POST['order_id'] ?? 0), $_POST['status'] ?? '');

        if ($result['ok']) {
            flash('success', 'Order status updated.');
        } else {
            flash('danger', $result['error']);
        }

        $back = $_POST['return_to'] ?? 'admin/orders.php';
        redirect(strpos($back, 'admin/') === 0 ? $back : 'admin/orders.php');
    }

    /** Public tracking lookup. */
    public static function track(string $orderNumber, string $phone): ?array
    {
        $orderNumber = strtoupper(trim($orderNumber));
        $phone = normalize_phone(trim($phone));
        if ($orderNumber === '' || $phone === '') {
            return null;
        }
        $order = Order::findByNumberAndPhone($orderNumber, $phone);
        if ($order) {
            $order['items'] = OrderItem::forOrder((int) $order['id']);
        }
        return $order;
    }
}

