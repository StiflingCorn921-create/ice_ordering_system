<?php
/**
 * Orders API
 *   GET  api/orders.php?order_number=ORD-..&phone=09..   -> track one order (public)
 *   GET  api/orders.php?status=Pending&q=...              -> list orders (admin)
 *   POST api/orders.php {action: "create", ...order fields, items: {product_id: qty}}  (public)
 *   POST api/orders.php {action: "status", order_id, status}                          (admin)
 * POST requests need the X-CSRF-Token header.
 */
require_once __DIR__ . '/../app/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['order_number'])) {
        $order = OrderController::track($_GET['order_number'], $_GET['phone'] ?? '');
        $order ? json_response(['order' => $order]) : json_response(['error' => 'Order not found'], 404);
    }

    require_login_api();
    $orders = Order::all($_GET['status'] ?? '', trim($_GET['q'] ?? ''));
    $items  = OrderItem::forOrders(array_column($orders, 'id'));
    foreach ($orders as &$o) {
        $o['items'] = $items[$o['id']] ?? [];
    }
    json_response(['orders' => $orders]);
}

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!get_bearer_token()) {
    verify_csrf();
}
$input  = json_input();
$action = $input['action'] ?? 'create';

if ($action === 'create') {
    $result = OrderService::placeOrder($input);
    $result['ok']
        ? json_response(['order' => $result['order']], 201)
        : json_response(['errors' => $result['errors']], 422);
}

if ($action === 'status') {
    require_login_api();
    $result = OrderService::changeStatus((int) ($input['order_id'] ?? 0), $input['status'] ?? '');
    $result['ok'] ? json_response(['ok' => true]) : json_response(['error' => $result['error']], 422);
}

json_response(['error' => 'Unknown action'], 400);

