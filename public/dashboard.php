<?php
/**
 * Customer "dashboard": track an order using order number + mobile number.
 */
require_once __DIR__ . '/../app/bootstrap.php';

$orderNumber = trim($_GET['order'] ?? '');
$phone       = trim($_GET['phone'] ?? '');
$searched    = $orderNumber !== '' && $phone !== '';
$order       = $searched ? OrderController::track($orderNumber, $phone) : null;

$steps = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Completed'];

$pageTitle = "Track Order - Yulio's Ice";
$pageCss   = 'dashboard.css';
$pageJs    = 'dashboard.js';
require APP_ROOT . '/views/public_header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
                <h3 class="fw-bold mb-3"><i class="fa-solid fa-magnifying-glass text-primary me-2"></i>Track Your Order</h3>
                <form method="GET" class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="order" class="form-control" placeholder="Order number (ORD-...)" value="<?= e($orderNumber) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <input type="tel" name="phone" class="form-control" placeholder="Mobile number" value="<?= e($phone) ?>" required>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn btn-primary fw-bold">Track</button>
                    </div>
                </form>
            </div>

            <?php if ($searched && !$order): ?>
                <div class="alert alert-warning">No order found with that order number and mobile number.</div>
            <?php elseif ($order): ?>
                <div class="card shadow-sm border-0 rounded-4 p-4" id="order-result" data-status="<?= e($order['status']) ?>">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small">Order</div>
                            <div class="fs-5 fw-bold font-monospace"><?= e($order['order_number']) ?></div>
                            <div class="small text-muted"><?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></div>
                        </div>
                        <?= status_badge($order['status']) ?>
                    </div>

                    <?php if ($order['status'] === 'Cancelled'): ?>
                        <div class="alert alert-danger mb-3">This order was cancelled. Please contact us if this is a mistake.</div>
                    <?php else: $current = array_search($order['status'], $steps, true); ?>
                        <ol class="progress-steps mb-4">
                            <?php foreach ($steps as $i => $step): ?>
                                <li class="<?= $i <= $current ? 'done' : '' ?>"><?= e($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <table class="table table-sm">
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= (int) $item['quantity'] ?> × <?= e($item['product_name']) ?></td>
                                <td class="text-end"><?= money($item['total_price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr><td>Delivery fee</td><td class="text-end"><?= money($order['delivery_fee']) ?></td></tr>
                        <tr class="fw-bold"><td>Total</td><td class="text-end"><?= money($order['total_price']) ?></td></tr>
                    </table>
                    <div class="small text-muted">
                        <?= e($order['fulfillment_type']) ?> · <?= e($order['delivery_address']) ?> · <?= e($order['payment_method']) ?> (<?= e($order['payment_status']) ?>)
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/public_footer.php'; ?>

