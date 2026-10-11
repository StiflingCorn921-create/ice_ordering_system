<?php

/**
 * All stock changes go through here, so every change is written to inventory_logs.
 */
class InventoryService
{
    public const REASONS = ['Order Deduction', 'Restock', 'Damage/Melted', 'Manual Adjustment'];

    /**
     * Change stock by $change (positive = add, negative = remove).
     * Must be called inside a DB transaction (it locks the product row).
     *
     * @throws RuntimeException when there is not enough stock
     */
    public static function adjust(int $productId, int $change, string $reason, ?int $referenceId = null): int
    {
        $product = Product::findForUpdate($productId);
        if (!$product) {
            throw new RuntimeException('Product not found.');
        }

        $previous = (int) $product['stock_quantity'];
        $current  = $previous + $change;

        if ($current < 0) {
            throw new RuntimeException("Not enough stock for {$product['name']} (only $previous left).");
        }

        Product::setStock($productId, $current);

        $stmt = db()->prepare('INSERT INTO inventory_logs (product_id, change_qty, previous_stock, current_stock, reason, reference_id)
                               VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$productId, $change, $previous, $current, $reason, $referenceId]);

        return $current;
    }

    /** Same as adjust(), but runs in its own transaction (for admin restock/damage forms). */
    public static function adjustStandalone(int $productId, int $change, string $reason): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $newStock = self::adjust($productId, $change, $reason);
            $pdo->commit();
            return $newStock;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function recentLogs(int $limit = 20): array
    {
        $stmt = db()->prepare('SELECT l.*, p.name AS product_name
                               FROM inventory_logs l JOIN products p ON p.id = l.product_id
                               ORDER BY l.created_at DESC, l.id DESC LIMIT ' . max(1, $limit));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function lowStock(int $threshold = 20): array
    {
        $stmt = db()->prepare('SELECT * FROM products WHERE is_active = 1 AND stock_quantity <= ? ORDER BY stock_quantity');
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
}

