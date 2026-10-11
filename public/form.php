<?php
require_once __DIR__ . '/../app/bootstrap.php';

$errors = [];
if (is_post()) {
    $errors = OrderController::store(); // redirects to success.php when the order is saved
}

$old         = is_post() ? $_POST : [];
$oldItems    = $old['items'] ?? [];
$products    = Product::allActive();
$deliveryFee = (float) config('delivery_fee', 0);

$pageTitle = "Place Order - Yulio's Ice";
$pageCss   = 'form.css';
$pageJs    = 'form.js';
$bodyClass = 'form-page';
require APP_ROOT . '/views/public_header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center g-4">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 border-top border-primary border-4 rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold"><i class="fa-solid fa-snowflake text-primary me-2"></i>Customer Order Portal</h3>
                    <p class="text-muted">Choose your ice, tell us where to bring it, and we'll confirm by text.</p>
                </div>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="order-form" novalidate>
                    <?= csrf_field() ?>

                    <h5 class="fw-bold mb-3">1. Select Quantities</h5>
                    <div class="row g-3 mb-4">
                        <?php foreach ($products as $p): $soldOut = $p['stock_quantity'] <= 0; ?>
                            <div class="col-md-6">
                                <div class="product-line border rounded-3 p-3 <?= $soldOut ? 'opacity-50' : '' ?>">
                                    <div class="d-flex justify-content-between">
                                        <label class="fw-bold" for="qty_<?= (int) $p['id'] ?>"><?= e($p['name']) ?></label>
                                        <span class="text-success fw-bold"><?= money($p['retail_price']) ?></span>
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        <?= $soldOut ? 'Sold out today' : (int) $p['stock_quantity'] . ' available' ?>
                                    </small>
                                    <input type="number" class="form-control qty-input" id="qty_<?= (int) $p['id'] ?>"
                                           name="items[<?= (int) $p['id'] ?>]" min="0" max="<?= max(0, (int) $p['stock_quantity']) ?>"
                                           value="<?= (int) ($oldItems[$p['id']] ?? 0) ?>"
                                           data-price="<?= e($p['retail_price']) ?>" <?= $soldOut ? 'disabled' : '' ?>>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h5 class="fw-bold mb-3">2. Your Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_name" class="form-label fw-bold">Name / Store Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" maxlength="100" required
                                   value="<?= e($old['customer_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_number" class="form-label fw-bold">Mobile Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="09171234567" required
                                   value="<?= e($old['contact_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fulfillment_type" class="form-label fw-bold">Delivery or Pickup</label>
                            <select class="form-select" id="fulfillment_type" name="fulfillment_type" data-delivery-fee="<?= e($deliveryFee) ?>">
                                <?php foreach (Order::FULFILLMENT_TYPES as $f): ?>
                                    <option value="<?= e($f) ?>" <?= ($old['fulfillment_type'] ?? '') === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="delivery_date" class="form-label fw-bold">Preferred Date <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date" min="<?= date('Y-m-d') ?>"
                                   value="<?= e($old['delivery_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3" id="address-group">
                        <label for="delivery_address" class="form-label fw-bold">Delivery Address / Landmark</label>
                        <input type="text" class="form-control" id="delivery_address" name="delivery_address"
                               value="<?= e($old['delivery_address'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label fw-bold">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <?php foreach (Order::PAYMENT_METHODS as $m): ?>
                                    <option value="<?= e($m) ?>" <?= ($old['payment_method'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label fw-bold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" class="form-control" id="notes" name="notes" maxlength="500" placeholder="e.g. Deliver before 10 AM"
                                   value="<?= e($old['notes'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="bg-body-tertiary p-3 rounded-3 my-4">
                        <div class="d-flex justify-content-between"><span>Subtotal</span><span id="display_subtotal">₱0.00</span></div>
                        <div class="d-flex justify-content-between"><span>Delivery fee</span><span id="display_fee">₱0.00</span></div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Estimated Total</span>
                            <span id="display_total" class="fw-bold fs-4 text-success">₱0.00</span>
                        </div>
                        <small class="text-muted">Registered resellers automatically get wholesale prices; the final total is shown after you submit.</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-bold py-3">Submit Order</button>
                        <a href="<?= url('public/index.php') ?>" class="btn btn-outline-secondary">Back to Home</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- AI ice calculator -->
        <div class="col-lg-4">
            <div class="card shadow border-0 rounded-4 p-4 chat-card">
                <h5 class="fw-bold"><i class="fa-solid fa-robot text-primary me-2"></i>Not sure how much ice?</h5>
                <p class="text-muted small">Ask our assistant, e.g. "Birthday party for 30 people, how much ice?"</p>
                <div id="chat-log" class="chat-log mb-3"></div>
                <form id="chat-form" class="d-flex gap-2">
                    <input type="text" id="chat-input" class="form-control" placeholder="Ask a question..." maxlength="500" autocomplete="off">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/public_footer.php'; ?>

