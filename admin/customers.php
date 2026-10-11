<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if (is_post() && ($_POST['action'] ?? '') === 'set_type') {
    UserController::setCustomerType();
}

$search    = trim($_GET['q'] ?? '');
$customers = Customer::all($search);

$pageTitle = 'Customers';
$activeNav = 'customers';
require APP_ROOT . '/views/admin_header.php';
?>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Search name or phone" value="<?= e($search) ?>"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Search</button></div>
</form>

<div class="alert alert-info small">
    <i class="fa-solid fa-circle-info me-1"></i>
    Customers are created automatically when they order. Mark trusted stores as <strong>Reseller</strong> so they get wholesale prices on their next order.
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Name</th><th>Phone</th><th>Address</th><th>Orders</th><th>Total Spent</th><th>Last Order</th><th>Type</th></tr></thead>
            <tbody>
            <?php if (!$customers): ?>
                <tr><td colspan="7" class="text-center text-secondary py-4">No customers found.</td></tr>
            <?php endif; ?>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td class="fw-bold"><?= e($c['name']) ?></td>
                    <td><a href="tel:<?= e($c['phone']) ?>"><?= e($c['phone']) ?></a></td>
                    <td class="small text-secondary"><?= e($c['default_address']) ?></td>
                    <td><?= (int) $c['order_count'] ?></td>
                    <td><?= money($c['total_spent']) ?></td>
                    <td class="small"><?= $c['last_order_at'] ? e(date('M j, Y', strtotime($c['last_order_at']))) : '—' ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="set_type">
                            <input type="hidden" name="customer_id" value="<?= (int) $c['id'] ?>">
                            <select name="customer_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php foreach (['Retail', 'Reseller'] as $t): ?>
                                    <option <?= $c['customer_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/admin_footer.php'; ?>

