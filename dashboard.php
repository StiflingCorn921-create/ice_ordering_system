<?php
require 'db.php';

$total_orders_query = $conn->query("SELECT COUNT(*) as count FROM orders");
$total_orders = $total_orders_query->fetch_assoc()['count'];

$pending_query = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending Confirmation'");
$pending_orders = $pending_query->fetch_assoc()['count'];

$revenue_query = $conn->query("SELECT SUM(total_price) as total FROM orders");
$total_revenue = $revenue_query->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard - icyBiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fa-solid fa-cube text-info me-2"></i> icyBiz Hub
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" target="_blank" class="btn btn-outline-light btn-sm">
                    <i class="fa-solid fa-store me-1"></i> Open Order Form
                </a>
                <a href="admin_dashboard.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-list-check me-1"></i> Manage Orders
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="fw-bold text-dark">Welcome back, Yulio's Team! 🧊</h2>
                <p class="text-muted">Here is a quick overview of your ice delivery operations today.</p>
            </div>
        </div>

        <!-- Metric Cards Row -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-bold small">Total Orders Placed</h6>
                        <h2 class="fw-bold text-dark mb-0"><?php echo $total_orders; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-bold small">Pending Confirmation</h6>
                        <h2 class="fw-bold text-dark mb-0"><?php echo $pending_orders; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-success border-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-bold small">Total Recorded Revenue</h6>
                        <h2 class="fw-bold text-success mb-0">&#8369;<?php echo number_format($total_revenue, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions / Navigation Panels -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h4 class="fw-bold mb-3"><i class="fa-solid fa-clipboard-list text-primary me-2"></i> Order Management</h4>
                    <p class="text-muted">View incoming requests, update customer delivery statuses, and organize daily logs.</p>
                    <a href="admin_dashboard.php" class="btn btn-dark mt-auto w-100 py-2">Go to Admin Table</a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h4 class="fw-bold mb-3"><i class="fa-solid fa-cart-shopping text-success me-2"></i> Customer Portal</h4>
                    <p class="text-muted">Test out the client-facing ordering page or place an order on behalf of a walk-in buyer.</p>
                    <a href="index.php" target="_blank" class="btn btn-outline-dark mt-auto w-100 py-2">Open Client Form</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>