<?php

class Product
{
    public static function allActive(): array
    {
        return db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY weight_kg')->fetchAll();
    }

    public static function all(): array
    {
        return db()->query('SELECT * FROM products ORDER BY is_active DESC, weight_kg')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Lock the row for update (use inside a transaction to avoid overselling). */
    public static function findForUpdate(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM products WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = db()->prepare('UPDATE products
                               SET name = ?, description = ?, retail_price = ?, reseller_price = ?, is_active = ?
                               WHERE id = ?');
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['retail_price'],
            $data['reseller_price'],
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public static function setStock(int $id, int $qty): bool
    {
        $stmt = db()->prepare('UPDATE products SET stock_quantity = ? WHERE id = ?');
        return $stmt->execute([$qty, $id]);
    }

    public static function priceFor(array $product, string $customerType): float
    {
        return (float) ($customerType === 'Reseller' ? $product['reseller_price'] : $product['retail_price']);
    }
}

