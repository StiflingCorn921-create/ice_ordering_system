<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_type = $_POST['order_type'];
    $customer_name = trim($_POST['customer_name']);
    $contact_number = trim($_POST['contact_number']);
    $location = trim($_POST['location']);
    
    $qty_1kg = intval($_POST['qty_1kg']);
    $qty_3kg = intval($_POST['qty_3kg']);
    $qty_10kg = intval($_POST['qty_10kg']);
    
    $total_price = floatval($_POST['total_price']);

    $stmt = $conn->prepare("INSERT INTO orders (order_type, customer_name, contact_number, location, qty_1kg, qty_3kg, qty_10kg, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending Confirmation')");
    
    $stmt->bind_param("ssssiiid", $order_type, $customer_name, $contact_number, $location, $qty_1kg, $qty_3kg, $qty_10kg, $total_price);

    if ($stmt->execute()) {
        $safe_name = urlencode($customer_name);
        header("Location: success.php?name=" . $safe_name);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>