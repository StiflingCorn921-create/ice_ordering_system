<?php

class ProductController
{
    /** Admin: edit product name/prices/active flag. */
    public static function update(): void
    {
        require_login();
        verify_csrf();

        $id = (int) ($_POST['product_id'] ?? 0);
        $data = [
            'name'           => trim($_POST['name'] ?? ''),
            'description'    => trim($_POST['description'] ?? ''),
            'retail_price'   => (float) ($_POST['retail_price'] ?? 0),
            'reseller_price' => (float) ($_POST['reseller_price'] ?? 0),
            'is_active'      => !empty($_POST['is_active']),
        ];

        $errors = collect_errors([v_required($data['name'], 'Product name'), v_max_length($data['name'], 100, 'Product name')]);
        if ($data['retail_price'] <= 0 || $data['reseller_price'] <= 0) {
            $errors[] = 'Prices must be greater than zero.';
        }
        if (!Product::find($id)) {
            $errors[] = 'Product not found.';
        }

        if ($errors) {
            flash('danger', implode(' ', $errors));
        } else {
            Product::update($id, $data);
            flash('success', 'Product updated.');
        }
        redirect('admin/inventory.php');
    }

    /** Admin: restock / record melted ice / manual correction. */
    public static function adjustStock(): void
    {
        require_login();
        verify_csrf();

        $id     = (int) ($_POST['product_id'] ?? 0);
        $qty    = (int) ($_POST['quantity'] ?? 0);
        $reason = $_POST['reason'] ?? '';

        if ($qty === 0 || !in_array($reason, ['Restock', 'Damage/Melted', 'Manual Adjustment'], true)) {
            flash('danger', 'Enter a quantity and choose a reason.');
            redirect('admin/inventory.php');
        }

        // Restock always adds; Damage/Melted always removes; Manual Adjustment uses the sign entered
        if ($reason === 'Restock') {
            $qty = abs($qty);
        } elseif ($reason === 'Damage/Melted') {
            $qty = -abs($qty);
        }

        try {
            $newStock = InventoryService::adjustStandalone($id, $qty, $reason);
            flash('success', "Stock updated. New stock: $newStock.");
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('admin/inventory.php');
    }
}

