<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

if (is_post()) {
    switch ($_POST['action'] ?? '') {
        case 'update_product':
            ProductController::update();
            break;
        case 'adjust_stock':
            ProductController::adjustStock();
            break;
    }
}

$products = Product::all();
$logs     = InventoryService::recentLogs(25);

$pageTitle = 'Inventory';
$activeNav = 'inventory';
require APP_ROOT . '/views/admin_header.php';
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-bold">Products &amp; Stock</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Product</th><th>Retail ₱</th><th>Reseller ₱</th><th>Active</th><th></th><th>Stock</th><th style="width: 320px">Adjust Stock</th></tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><input form="p<?= (int) $p['id'] ?>" name="name" class="form-control form-control-sm" value="<?= e($p['name']) ?>" required></td>
                    <td><input form="p<?= (int) $p['id'] ?>" name="retail_price" type="number" step="0.01" min="0.01" class="form-control form-control-sm" value="<?= e($p['retail_price']) ?>"></td>
                    <td><input form="p<?= (int) $p['id'] ?>" name="reseller_price" type="number" step="0.01" min="0.01" class="form-control form-control-sm" value="<?= e($p['reseller_price']) ?>"></td>
                    <td><input form="p<?= (int) $p['id'] ?>" name="is_active" type="checkbox" class="form-check-input" value="1" <?= $p['is_active'] ? 'checked' : '' ?>></td>
                    <td>
                        <form method="POST" id="p<?= (int) $p['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_product">
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="hidden" name="description" value="<?= e($p['description']) ?>">
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                    <td>
                        <span class="badge fs-6 <?= $p['stock_quantity'] <= 20 ? 'bg-warning text-dark' : 'bg-success' ?>"><?= (int) $p['stock_quantity'] ?></span>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="adjust_stock">
                            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                            <input type="number" name="quantity" class="form-control form-control-sm" placeholder="Qty" style="width: 80px" required>
                            <select name="reason" class="form-select form-select-sm">
                                <option value="Restock">+ Restock</option>
                                <option value="Damage/Melted">− Melted/Damaged</option>
                                <option value="Manual Adjustment">± Correction</option>
                            </select>
                            <button class="btn btn-sm btn-success">Apply</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header fw-bold">Recent Stock Movements</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>When</th><th>Product</th><th>Change</th><th>Stock</th><th>Reason</th><th>Ref</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="small text-secondary"><?= e(date('M j, g:i A', strtotime($l['created_at']))) ?></td>
                    <td><?= e($l['product_name']) ?></td>
                    <td class="<?= $l['change_qty'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= $l['change_qty'] >= 0 ? '+' : '' ?><?= (int) $l['change_qty'] ?></td>
                    <td class="small"><?= (int) $l['previous_stock'] ?> → <?= (int) $l['current_stock'] ?></td>
                    <td class="small"><?= e($l['reason']) ?></td>
                    <td class="small text-secondary"><?= $l['reference_id'] ? 'Order #' . (int) $l['reference_id'] : '' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/views/admin_footer.php'; ?>
