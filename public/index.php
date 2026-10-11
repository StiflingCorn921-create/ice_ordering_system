<?php
require_once __DIR__ . '/../app/bootstrap.php';

$products  = Product::allActive();
$pageTitle = "Yulio's Purified Cube Ice - Fresh & Clean Ice Delivery";
$pageCss   = 'home.css';
$hideNav   = true;
require APP_ROOT . '/views/public_header.php';
?>

<!-- Hero -->
<section class="hero-section">
    <div class="container">
        <img src="<?= url('assets/images/YuliosGoodLogo.png') ?>" alt="Yulio's Logo" class="hero-logo rounded-circle shadow-lg mb-4">
        <h1 class="display-3 fw-bold mb-3">Yulio's Purified Cube Ice</h1>
        <p class="lead mb-4 mx-auto" style="max-width: 700px;">
            Crystal clear, safe, and premium-quality ice cubes delivered straight to your sari-sari store, event, or business doorstep.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="<?= url('public/form.php') ?>" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">
                <i class="fa-solid fa-cart-shopping me-2"></i>Place an Order Now
            </a>
            <a href="<?= url('public/dashboard.php') ?>" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold">
                <i class="fa-solid fa-magnifying-glass me-2"></i>Track My Order
            </a>
        </div>
    </div>
    <a href="#products" class="hero-scroll text-white fs-4"><i class="fa-solid fa-chevron-down fa-bounce"></i></a>
</section>

<!-- Products -->
<section id="products" class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Our Ice</h2>
        <p class="text-muted">Purified, hygienically packed, and delivered cold.</p>
    </div>
    <div class="row g-4">
        <?php foreach ($products as $p): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card product-card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                    <?php if ($p['image_path']): ?>
                        <img src="<?= url($p['image_path']) ?>" class="card-img-top" alt="<?= e($p['name']) ?>">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="fw-bold"><?= e($p['name']) ?></h5>
                        <p class="text-muted small flex-grow-1"><?= e($p['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-success"><?= money($p['retail_price']) ?></span>
                            <?php if ($p['stock_quantity'] <= 0): ?>
                                <span class="badge bg-secondary">Sold out</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Features -->
<section class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Why Choose Yulio's?</h2>
        <p class="text-muted">Built for everyday retail, local stores, and cold refreshments.</p>
    </div>
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                <h5 class="fw-bold">Purified &amp; Clean</h5>
                <p class="text-muted small">Processed under strict hygiene guidelines to ensure absolute safety for consumption.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="fs-1 text-warning mb-3"><i class="fa-solid fa-stopwatch"></i></div>
                <h5 class="fw-bold">Long-Lasting Melt</h5>
                <p class="text-muted small">Dense, hard ice cubes designed to keep your beverages cold much longer.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-truck-fast"></i></div>
                <h5 class="fw-bold">Fast Local Delivery</h5>
                <p class="text-muted small">Quick turnaround times right to your store or location when you need a restock.</p>
            </div>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="<?= url('public/form.php') ?>" class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold shadow">
            Proceed to Order Form <i class="fa-solid fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<?php require APP_ROOT . '/views/public_footer.php'; ?>

