<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if (is_post() && ($_POST['action'] ?? '') === 'change_password') {
    UserController::changePassword();
}

$stats    = Order::stats();
$recent   = Order::recent(8);
$lowStock = InventoryService::lowStock();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$pageJs    = 'dashboard.js';
require APP_ROOT . '/views/admin_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card border-start border-primary">
            <div class="stat-label">Orders Today</div>
            <div class="stat-value"><?= (int) $stats['today_orders'] ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card border-start border-warning">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?= (int) $stats['pending_orders'] ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card border-start border-success">
            <div class="stat-label">Revenue Today</div>
            <div class="stat-value text-success"><?= money($stats['today_revenue']) ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card border-start border-info">
            <div class="stat-label">All-time Revenue</div>
            <div class="stat-value"><?= money($stats['total_revenue']) ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Recent Orders</span>
                <a href="<?= url('admin/orders.php') ?>" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th></tr></thead>
                    <tbody>
                    <?php if (!$recent): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-4">No orders yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent as $o): ?>
                        <tr>
                            <td class="font-monospace small"><?= e($o['order_number']) ?></td>
                            <td><?= e($o['customer_name']) ?></td>
                            <td><?= money($o['total_price']) ?></td>
                            <td><?= status_badge($o['status']) ?></td>
                            <td class="small text-secondary"><?= e(date('M j, g:i A', strtotime($o['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-bold"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Low Stock</div>
            <ul class="list-group list-group-flush">
                <?php if (!$lowStock): ?>
                    <li class="list-group-item text-secondary">All products are well stocked.</li>
                <?php endif; ?>
                <?php foreach ($lowStock as $p): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <?= e($p['name']) ?><span class="badge bg-warning text-dark"><?= (int) $p['stock_quantity'] ?> left</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-bold"><i class="fa-solid fa-robot text-info me-2"></i>Business Assistant</div>
            <div class="card-body">
                <div id="admin-chat-log" class="chat-log mb-2"></div>
                <form id="admin-chat-form" class="d-flex gap-2">
                    <input type="text" id="admin-chat-input" class="form-control form-control-sm" placeholder="e.g. Summarize this week's sales" maxlength="500">
                    <button class="btn btn-sm btn-info"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold">
                <a class="text-reset text-decoration-none" data-bs-toggle="collapse" href="#pwForm"><i class="fa-solid fa-key me-2"></i>Change Password</a>
            </div>
            <div class="collapse" id="pwForm">
                <form method="POST" class="card-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">
                    <input type="password" name="current_password" class="form-control form-control-sm mb-2" placeholder="Current password" required>
                    <input type="password" name="new_password" class="form-control form-control-sm mb-2" placeholder="New password (8+ chars)" minlength="8" required>
                    <input type="password" name="confirm_password" class="form-control form-control-sm mb-2" placeholder="Confirm new password" required>
                    <button class="btn btn-sm btn-primary w-100">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/admin_footer.php'; ?>

