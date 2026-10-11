<?php
require_once __DIR__ . '/../app/bootstrap.php';

// The order ID comes from the session (set right after placing the order), not from the URL,
// so nobody can view other people's orders by changing a number in the address bar.
$order = isset($_SESSION['last_order_id']) ? Order::find((int) $_SESSION['last_order_id']) : null;
if (!$order) {
    redirect('public/index.php');
}
$items = OrderItem::forOrder((int) $order['id']);

$pageTitle = "Order Placed - Yulio's Ice";
$bodyClass = 'form-page';
$pageCss   = 'form.css';
require APP_ROOT . '/views/public_header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 border-top border-primary border-4 p-4 p-md-5 rounded-4 text-center">
                <div class="display-3 mb-3">🧊</div>
                <h2 class="fw-bold text-success mb-2">Order Placed Successfully!</h2>
                <p class="text-muted">
                    Thank you, <strong><?= e($order['customer_name']) ?></strong>! We'll review your order and text you a confirmation shortly.
                </p>

                <div class="bg-body-tertiary rounded-3 p-3 my-3">
                    <div class="small text-muted">Your order number</div>
                    <div class="fs-4 fw-bold font-monospace"><?= e($order['order_number']) ?></div>
                    <div class="small text-muted">Save this to track your order.</div>
                </div>

                <table class="table table-sm text-start">
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= (int) $item['quantity'] ?> × <?= e($item['product_name']) ?></td>
                            <td class="text-end"><?= money($item['total_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr><td>Delivery fee</td><td class="text-end"><?= money($order['delivery_fee']) ?></td></tr>
                    <tr class="fw-bold"><td>Total (<?= e($order['payment_method']) ?>)</td><td class="text-end text-success"><?= money($order['total_price']) ?></td></tr>
                </table>

                <div class="d-grid gap-2">
                    <a href="<?= url('public/dashboard.php?order=' . urlencode($order['order_number'])) ?>" class="btn btn-primary btn-lg fw-bold">Track This Order</a>
                    <a href="<?= url('public/form.php') ?>" class="btn btn-outline-secondary">Make Another Order</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/public_footer.php'; ?>

