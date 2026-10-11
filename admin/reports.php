<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$days  = (int) ($_GET['days'] ?? 14);
$days  = in_array($days, [7, 14, 30, 90], true) ? $days : 14;
$daily = Order::dailySales($days);
$top   = OrderItem::topProducts($days);

$totalRevenue = array_sum(array_column($daily, 'revenue'));
$totalOrders  = array_sum(array_column($daily, 'orders'));
$maxRevenue   = max(array_merge([1], array_column($daily, 'revenue')));

$pageTitle = 'Reports';
$activeNav = 'reports';
require APP_ROOT . '/views/admin_header.php';
?>

<div class="d-flex gap-2 mb-4">
    <?php foreach ([7, 14, 30, 90] as $d): ?>
        <a href="?days=<?= $d ?>" class="btn btn-sm <?= $days === $d ? 'btn-primary' : 'btn-outline-secondary' ?>">Last <?= $d ?> days</a>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat-card border-start border-success"><div class="stat-label">Revenue</div><div class="stat-value text-success"><?= money($totalRevenue) ?></div></div></div>
    <div class="col-md-4"><div class="stat-card border-start border-primary"><div class="stat-label">Orders</div><div class="stat-value"><?= (int) $totalOrders ?></div></div></div>
    <div class="col-md-4"><div class="stat-card border-start border-info"><div class="stat-label">Avg. Order</div><div class="stat-value"><?= money($totalOrders ? $totalRevenue / $totalOrders : 0) ?></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold">Daily Sales</div>
            <div class="card-body">
                <?php if (!$daily): ?><p class="text-secondary mb-0">No sales in this period.</p><?php endif; ?>
                <?php foreach ($daily as $row): ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="small text-secondary" style="width: 70px"><?= e(date('M j', strtotime($row['day']))) ?></span>
                        <div class="flex-grow-1">
                            <div class="bar" style="width: <?= round($row['revenue'] / $maxRevenue * 100) ?>%"></div>
                        </div>
                        <span class="small" style="width: 130px; text-align: right"><?= money($row['revenue']) ?> (<?= (int) $row['orders'] ?>)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold">Top Products</div>
            <table class="table mb-0">
                <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
                <tbody>
                <?php if (!$top): ?><tr><td colspan="3" class="text-secondary">No data.</td></tr><?php endif; ?>
                <?php foreach ($top as $t): ?>
                    <tr><td><?= e($t['product_name']) ?></td><td><?= (int) $t['qty'] ?></td><td><?= money($t['revenue']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/admin_footer.php'; ?>

