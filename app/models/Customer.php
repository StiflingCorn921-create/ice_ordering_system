<?php

class Customer
{
    public static function all(string $search = ''): array
    {
        $sql = 'SELECT c.*,
                       COUNT(o.id) AS order_count,
                       COALESCE(SUM(CASE WHEN o.status <> "Cancelled" THEN o.total_price END), 0) AS total_spent,
                       MAX(o.created_at) AS last_order_at
                FROM customers c
                LEFT JOIN orders o ON o.customer_id = c.id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE c.name LIKE ? OR c.phone LIKE ?';
            $params = ["%$search%", "%$search%"];
        }
        $sql .= ' GROUP BY c.id ORDER BY c.name';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByPhone(string $phone): ?array
    {
        $stmt = db()->prepare('SELECT * FROM customers WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare('INSERT INTO customers (name, phone, email, default_address, customer_type, notes)
                               VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['default_address'],
            $data['customer_type'] ?? 'Retail',
            $data['notes'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function updateType(int $id, string $type): bool
    {
        $stmt = db()->prepare('UPDATE customers SET customer_type = ? WHERE id = ?');
        return $stmt->execute([$type, $id]);
    }

    /**
     * Find a customer by phone, or create one. Customer type (Retail/Reseller) is never
     * set from the public form — only your aunt can promote someone to Reseller.
     */
    public static function findOrCreate(string $name, string $phone, string $address): array
    {
        $existing = self::findByPhone($phone);
        if ($existing) {
            return $existing;
        }
        $id = self::create(['name' => $name, 'phone' => $phone, 'default_address' => $address]);
        return self::find($id);
    }
}

