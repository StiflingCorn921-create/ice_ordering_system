<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if (is_post() && ($_POST['action'] ?? '') === 'update_status') {
    OrderController::updateStatus();
}

$status = $_GET['status'] ?? '';
$status = in_array($status, Order::STATUSES, true) ? $status : '';
$search = trim($_GET['q'] ?? '');

$orders = Order::all($status, $search);
$items  = OrderItem::forOrders(array_column($orders, 'id'));
$returnTo = 'admin/orders.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

$pageTitle = 'Orders';
$activeNav = 'orders';
require APP_ROOT . '/views/admin_header.php';
?>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" class="form-control" placeholder="Search order #, name or phone" value="<?= e($search) ?>">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All statuses</option>
            <?php foreach (Order::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Filter</button></div>
    <div class="col-md-3 text-md-end small text-secondary align-self-center">
        <i class="fa-solid fa-rotate me-1"></i>Auto-refreshes every 15s
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive" id="orders-table" data-autorefresh="15000">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Order</th><th>Customer</th><th>Items</th><th>Fulfillment</th><th>Total</th><th>Status</th><th style="width: 230px">Update</th></tr>
            </thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="7" class="text-center text-secondary py-4">No orders found.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>
                        <div class="font-monospace small"><?= e($o['order_number']) ?></div>
                        <div class="small text-secondary"><?= e(date('M j, g:i A', strtotime($o['created_at']))) ?></div>
                    </td>
                    <td>
                        <div class="fw-bold"><?= e($o['customer_name']) ?>
                            <?php if ($o['order_type'] === 'Reseller'): ?><span class="badge badge-reseller ms-1">Reseller</span><?php endif; ?>
                        </div>
                        <div class="small"><a href="tel:<?= e($o['contact_number']) ?>"><?= e($o['contact_number']) ?></a></div>
                    </td>
                    <td class="small">
                        <?php foreach ($items[$o['id']] ?? [] as $it): ?>
                            <div><?= (int) $it['quantity'] ?> × <?= e($it['product_name']) ?></div>
                        <?php endforeach; ?>
                        <?php if ($o['notes']): ?><div class="text-warning"><i class="fa-solid fa-note-sticky me-1"></i><?= e($o['notes']) ?></div><?php endif; ?>
                    </td>
                    <td class="small">
                        <div><?= e($o['fulfillment_type']) ?><?= $o['delivery_date'] ? ' · ' . e(date('M j', strtotime($o['delivery_date']))) : '' ?></div>
                        <div class="text-secondary"><?= e($o['delivery_address']) ?></div>
                    </td>
                    <td>
                        <div class="fw-bold"><?= money($o['total_price']) ?></div>
                        <div class="small text-secondary"><?= e($o['payment_method']) ?> · <?= e($o['payment_status']) ?></div>
                    </td>
                    <td><?= status_badge($o['status']) ?></td>
                    <td>
                        <?php if ($o['status'] !== 'Cancelled'): ?>
                        <form method="POST" class="d-flex gap-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach (Order::STATUSES as $s): ?>
                                    <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary" data-confirm-cancel>Save</button>
                        </form>
                        <?php else: ?>
                            <span class="small text-secondary">Stock returned</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/admin_footer.php'; ?>

