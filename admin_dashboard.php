<?php
// admin_dashboard.php
$host = 'localhost';
$db   = 'icybiz';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle status update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['new_status'], $_POST['order_id']]);
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch all orders sorted by latest
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IcyBiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="fa-solid fa-gauge-high me-2 text-primary"></i>IcyBiz Admin Dashboard</h2>
                <p class="text-muted mb-0">Manage customer orders, track dispatches, and update delivery statuses.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                    <i class="fa-solid fa-house me-1"></i> Home
                </a>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="card shadow-lg border-0 border-top border-primary border-4 rounded-4 p-4">
            <h4 class="fw-bold mb-3">Incoming Orders</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer / Store</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Type</th>
                            <th>Quantities</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($order['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($order['contact_number'] ?? $order['contact number'] ?? $order['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($order['location'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                            $type = $order['order_type'] ?? 'Retail';
                                            // Custom styling for Retail vs Reseller badges
                                            if ($type === 'Reseller') {
                                                echo '<span class="badge bg-purple text-white" style="background-color: #6f42c1;">Reseller</span>';
                                            } else {
                                                echo '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2">Retail</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <small>
                                            1kg: <?php echo $order['qty_1kg'] ?? 0; ?> | 
                                            3kg: <?php echo $order['qty_3kg'] ?? 0; ?> | 
                                            10kg: <?php echo $order['qty_10kg'] ?? 0; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = $order['status'] ?? 'Pending';
                                            $badgeBg = 'bg-warning text-dark';
                                            if ($status === 'Out for Delivery') $badgeBg = 'bg-info text-dark';
                                            if ($status === 'Completed') $badgeBg = 'bg-success';
                                            if ($status === 'Cancelled') $badgeBg = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badgeBg; ?>"><?php echo $status; ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline-flex gap-1">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="new_status" class="form-select form-select-sm" style="width: 140px;">
                                                <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="Out for Delivery" <?php if($status=='Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                                                <option value="Completed" <?php if($status=='Completed') echo 'selected'; ?>>Completed</option>
                                                <option value="Cancelled" <?php if($status=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>