<?php

class Order
{
    public const STATUSES = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Completed', 'Cancelled'];
    public const PAYMENT_METHODS = ['Cash on Delivery', 'GCash', 'Cash on Pickup'];
    public const FULFILLMENT_TYPES = ['Delivery', 'Pickup'];

    public static function create(array $d): int
    {
        $stmt = db()->prepare('INSERT INTO orders
            (order_number, customer_id, customer_name, contact_number, delivery_address, order_type,
             fulfillment_type, delivery_date, notes, subtotal, delivery_fee, total_price, payment_method)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $d['order_number'], $d['customer_id'], $d['customer_name'], $d['contact_number'],
            $d['delivery_address'], $d['order_type'], $d['fulfillment_type'], $d['delivery_date'],
            $d['notes'], $d['subtotal'], $d['delivery_fee'], $d['total_price'], $d['payment_method'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findForUpdate(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Public order tracking requires BOTH order number and phone so strangers can't look up orders. */
    public static function findByNumberAndPhone(string $orderNumber, string $phone): ?array
    {
        $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? AND contact_number = ?');
        $stmt->execute([$orderNumber, $phone]);
        return $stmt->fetch() ?: null;
    }

    public static function all(string $status = '', string $search = '', int $limit = 200): array
    {
        $sql = 'SELECT * FROM orders WHERE 1=1';
        $params = [];
        if ($status !== '') {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if ($search !== '') {
            $sql .= ' AND (order_number LIKE ? OR customer_name LIKE ? OR contact_number LIKE ?)';
            array_push($params, "%$search%", "%$search%", "%$search%");
        }
        $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, $limit);

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function recent(int $limit = 5): array
    {
        return self::all('', '', $limit);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public static function updatePaymentStatus(int $id, string $paymentStatus): bool
    {
        $stmt = db()->prepare('UPDATE orders SET payment_status = ? WHERE id = ?');
        return $stmt->execute([$paymentStatus, $id]);
    }

    /** Numbers for the admin dashboard cards. Cancelled orders don't count as revenue. */
    public static function stats(): array
    {
        return db()->query('SELECT
                COUNT(*) AS total_orders,
                SUM(status = "Pending") AS pending_orders,
                SUM(DATE(created_at) = CURDATE()) AS today_orders,
                COALESCE(SUM(CASE WHEN status <> "Cancelled" THEN total_price END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN status <> "Cancelled" AND DATE(created_at) = CURDATE() THEN total_price END), 0) AS today_revenue
            FROM orders')->fetch();
    }

    public static function dailySales(int $days = 14): array
    {
        $stmt = db()->prepare('SELECT DATE(created_at) AS day, COUNT(*) AS orders, SUM(total_price) AS revenue
                               FROM orders
                               WHERE status <> "Cancelled" AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                               GROUP BY DATE(created_at)
                               ORDER BY day DESC');
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}

