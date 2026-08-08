<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yulio's Purified Cube Ice - Fresh & Clean Ice Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Hero Section with Blurred Background */
        .hero-section {
            position: relative;
            height: 100vh;
            color: white;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            top: -10px; left: -10px; right: -10px; bottom: -10px;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/Yuliosbg1.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(2px);
            z-index: 0;
        }

        .hero-section > * {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-light">

    <!-- 1. Hero Showcase Section -->
    <div class="hero-section d-flex flex-column justify-content-center align-items-center text-center px-3">
        <div class="container">
            <div class="mb-4">
                <img src="assets/images/YuliosGoodLogo.png" alt="Yulio's Logo" class="rounded-circle shadow-lg" style="width: 250px; height: 250px; object-fit: cover;">
            </div>
            <h1 class="display-3 fw-bold mb-3">Yulio's Purified Cube Ice</h1>
            <p class="lead mb-4 mx-auto" style="max-width: 700px;">
                Crystal clear, safe, and premium-quality ice cubes delivered straight to your sari-sari store, event, or business doorstep.
            </p>
            <a href="form.php" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">
                <i class="fa-solid fa-cart-shopping me-2"></i> Place an Order Now
            </a>
        </div>
        <div class="position-absolute bottom-0 mb-4">
            <a href="#features-section" class="text-white fs-4 text-decoration-none">
                <i class="fa-solid fa-chevron-down fa-bounce"></i>
            </a>
        </div>
    </div>

    <!-- 2. Three Features Side-by-Side Section -->
    <div id="features-section" class="container py-5 my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Why Choose Yulio's?</h2>
            <p class="text-muted">Built for everyday retail, local stores, and cold refreshments.</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="card-body">
                        <div class="fs-1 text-primary mb-3"><i class="fa-solid fa-shield-halved"></i></div>
                        <h5 class="fw-bold">Purified & Clean</h5>
                        <p class="text-muted small">Processed under strict hygiene guidelines to ensure absolute safety for consumption.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="card-body">
                        <div class="fs-1 text-warning mb-3"><i class="fa-solid fa-stopwatch"></i></div>
                        <h5 class="fw-bold">Long-Lasting Melt</h5>
                        <p class="text-muted small">Dense, hard ice cubes designed to keep your beverages cold much longer.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="card-body">
                        <div class="fs-1 text-success mb-3"><i class="fa-solid fa-truck-fast"></i></div>
                        <h5 class="fw-bold">Fast Local Delivery</h5>
                        <p class="text-muted small">Quick turnaround times right to your store or location when you need a restock.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Call to Action inside Features -->
        <div class="text-center mt-5">
            <a href="form.php" class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold shadow">
                Proceed to Order Form <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>

</body>
</html>