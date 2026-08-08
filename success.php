<?php
// Optional: Grab the name or total if you want to personalize it
$customer_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - IcyBiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Atmospheric Blurred Background */
        body {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/YuliosCpic1.jpg');
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
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Solid white card with blue top border accent -->
                <div class="card shadow-lg border-0 border-top border-primary border-4 p-5 bg-white rounded-4">
                    <div class="mb-4">
                        <span class="display-3">🧊</span>
                    </div>
                    <h2 class="fw-bold text-success mb-3">Order Placed Successfully!</h2>
                    <p class="text-muted mb-4">
                        Thank you, <strong><?php echo $customer_name; ?></strong>! Your order has been received. We will review it and send a confirmation text shortly.
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary btn-lg fw-bold">Make Another Purchase</a>
                        <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>