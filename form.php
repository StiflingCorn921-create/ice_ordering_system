<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - IcyBiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Atmospheric Blurred Background */
        body {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/YuliosCpic2.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            backdrop-filter: blur(2px);
            z-index: -1;
        }
    </style>
</head>
<body class="py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Solid white form card with blue top border accent -->
                <div class="card shadow-lg border-0 border-top border-primary border-4 rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark"><i class="fa-solid fa-snowflake text-primary me-2"></i>Customer Order Portal</h3>
                        <p class="text-muted">Fill out the quantities below and submit your request to our dispatch team.</p>
                    </div>

                    <form action="process_order.php" method="POST">
                        <div class="mb-3">
                            <label for="order_type" class="form-label fw-bold">Order Type</label>
                            <select class="form-select" id="order_type" name="order_type" required>
                                <option value="Retail">Retail</option>
                                <option value="Reseller">Reseller</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label fw-bold">Name / Store Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contact_number" class="form-label fw-bold">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label fw-bold">Delivery Address / Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3">Select Quantities</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">1kg Pack</label>
                                <input type="number" class="form-control qty-input" id="qty_1kg" name="qty_1kg" value="0" min="0" data-price="25">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">3kg Pack</label>
                                <input type="number" class="form-control qty-input" id="qty_3kg" name="qty_3kg" value="0" min="0" data-price="70">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-bold">10kg Sack</label>
                                <input type="number" class="form-control qty-input" id="qty_10kg" name="qty_10kg" value="0" min="0" data-price="220">
                            </div>
                        </div>

                        <!-- Total Price Display Box -->
                        <div class="bg-light p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Estimated Total:</span>
                            <span id="display_total" class="fw-bold fs-4 text-success">&#8369;0.00</span>
                        </div>
                        <input type="hidden" id="total_price" name="total_price" value="0">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold py-3">Submit Order & Send via SMS</button>
                            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Pricing Script -->
    <script>
        const inputs = document.querySelectorAll('.qty-input');
        const displayTotal = document.getElementById('display_total');
        const hiddenTotal = document.getElementById('total_price');

        function calculateTotal() {
            let total = 0;
            inputs.forEach(input => {
                let qty = parseInt(input.value) || 0;
                let price = parseFloat(input.getAttribute('data-price'));
                total += qty * price;
            });
            displayTotal.innerText = '₱' + total.toFixed(2);
            hiddenTotal.value = total.toFixed(2);
        }

        inputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
        });
    </script>

</body>
</html>