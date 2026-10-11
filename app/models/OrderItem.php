<?php

class OrderItem
{
    public static function create(int $orderId, array $product, int $qty, float $unitPrice): int
    {
        $stmt = db()->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
                               VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$orderId, $product['id'], $product['name'], $qty, $unitPrice, round($qty * $unitPrice, 2)]);
        return (int) db()->lastInsertId();
    }

    public static function forOrder(int $orderId): array
    {
        $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /** Items for many orders at once, grouped by order_id (avoids one query per order). */
    public static function forOrders(array $orderIds): array
    {
        if (!$orderIds) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = db()->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY id");
        $stmt->execute(array_values($orderIds));

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[$row['order_id']][] = $row;
        }
        return $grouped;
    }

    public static function topProducts(int $days = 30): array
    {
        $stmt = db()->prepare('SELECT oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.total_price) AS revenue
                               FROM order_items oi
                               JOIN orders o ON o.id = oi.order_id
                               WHERE o.status <> "Cancelled" AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                               GROUP BY oi.product_id, oi.product_name
                               ORDER BY qty DESC');
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}

