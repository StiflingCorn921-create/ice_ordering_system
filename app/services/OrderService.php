<?php

/**
 * Order business logic: placing orders and changing their status.
 */
class OrderService
{
    /**
     * Place an order from customer input.
     *
     * Prices are ALWAYS calculated here on the server from the products table.
     * (The old process_order.php trusted the total sent by the browser, which anyone could edit.)
     *
     * @param array $input keys: customer_name, contact_number, delivery_address, fulfillment_type,
     *                     payment_method, delivery_date, notes, items => [product_id => qty]
     * @return array ['ok' => bool, 'errors' => string[], 'order' => ?array]
     */
    public static function placeOrder(array $input): array
    {
        $name        = trim($input['customer_name'] ?? '');
        $phoneRaw    = trim($input['contact_number'] ?? '');
        $address     = trim($input['delivery_address'] ?? '');
        $fulfillment = $input['fulfillment_type'] ?? 'Delivery';
        $payment     = $input['payment_method'] ?? 'Cash on Delivery';
        $date        = trim($input['delivery_date'] ?? '');
        $notes       = trim($input['notes'] ?? '');
        $items       = is_array($input['items'] ?? null) ? $input['items'] : [];

        $errors = collect_errors([
            v_required($name, 'Name'),
            v_max_length($name, 100, 'Name'),
            v_phone($phoneRaw),
            v_in($fulfillment, Order::FULFILLMENT_TYPES, 'Fulfillment type'),
            v_in($payment, Order::PAYMENT_METHODS, 'Payment method'),
            v_max_length($notes, 500, 'Notes'),
            $fulfillment === 'Delivery' ? v_required($address, 'Delivery address') : null,
        ]);

        if ($date !== '' && (!strtotime($date) || $date < date('Y-m-d'))) {
            $errors[] = 'Delivery date must be today or later.';
        }

        // Keep only positive quantities
        $wanted = [];
        foreach ($items as $productId => $qty) {
            $qty = (int) $qty;
            if ($qty > 0) {
                $wanted[(int) $productId] = min($qty, 1000);
            }
        }
        if (!$wanted) {
            $errors[] = 'Please add at least one item to your order.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors, 'order' => null];
        }

        $phone = normalize_phone($phoneRaw);
        if ($fulfillment === 'Pickup' && $address === '') {
            $address = 'Store pickup';
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $customer = Customer::findOrCreate($name, $phone, $address);
            $type     = $customer['customer_type']; // Retail unless your aunt marked them as Reseller

            // Lock products and compute prices
            $lines = [];
            $subtotal = 0.0;
            foreach ($wanted as $productId => $qty) {
                $product = Product::findForUpdate($productId);
                if (!$product || !$product['is_active']) {
                    throw new RuntimeException('One of the selected products is no longer available.');
                }
                $unit = Product::priceFor($product, $type);
                $lines[] = [$product, $qty, $unit];
                $subtotal += $qty * $unit;
            }

            $deliveryFee = $fulfillment === 'Delivery' ? (float) config('delivery_fee', 0) : 0.0;

            $orderId = Order::create([
                'order_number'     => Order::generateOrderNumber(),
                'customer_id'      => $customer['id'],
                'customer_name'    => $name,
                'contact_number'   => $phone,
                'delivery_address' => $address,
                'order_type'       => $type,
                'fulfillment_type' => $fulfillment,
                'delivery_date'    => $date !== '' ? $date : null,
                'notes'            => $notes !== '' ? $notes : null,
                'subtotal'         => round($subtotal, 2),
                'delivery_fee'     => $deliveryFee,
                'total_price'      => round($subtotal + $deliveryFee, 2),
                'payment_method'   => $payment,
            ]);

            foreach ($lines as [$product, $qty, $unit]) {
                OrderItem::create($orderId, $product, $qty, $unit);
                InventoryService::adjust((int) $product['id'], -$qty, 'Order Deduction', $orderId);
            }

            $pdo->commit();
        } catch (RuntimeException $e) {
            $pdo->rollBack();
            return ['ok' => false, 'errors' => [$e->getMessage()], 'order' => null];
        } catch (Throwable $e) {
            $pdo->rollBack();
            app_log('placeOrder failed: ' . $e->getMessage());
            return ['ok' => false, 'errors' => ['Something went wrong while saving your order. Please try again.'], 'order' => null];
        }

        $order = Order::find($orderId);
        NotificationService::orderPlaced($order);

        return ['ok' => true, 'errors' => [], 'order' => $order];
    }

    /**
     * Change an order's status. Cancelling returns the items to stock.
     * A cancelled order cannot be re-opened (place a new order instead).
     */
    public static function changeStatus(int $orderId, string $newStatus): array
    {
        if (!in_array($newStatus, Order::STATUSES, true)) {
            return ['ok' => false, 'error' => 'Invalid status.'];
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $order = Order::findForUpdate($orderId);
            if (!$order) {
                throw new RuntimeException('Order not found.');
            }
            if ($order['status'] === 'Cancelled' && $newStatus !== 'Cancelled') {
                throw new RuntimeException('Cancelled orders cannot be re-opened.');
            }
            if ($order['status'] === $newStatus) {
                $pdo->commit();
                return ['ok' => true, 'error' => null];
            }

            if ($newStatus === 'Cancelled') {
                foreach (OrderItem::forOrder($orderId) as $item) {
                    InventoryService::adjust((int) $item['product_id'], (int) $item['quantity'], 'Manual Adjustment', $orderId);
                }
            }

            Order::updateStatus($orderId, $newStatus);
            if ($newStatus === 'Completed') {
                Order::updatePaymentStatus($orderId, 'Paid');
            }

            $pdo->commit();
        } catch (RuntimeException $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        NotificationService::statusChanged(Order::find($orderId));
        return ['ok' => true, 'error' => null];
    }
}

