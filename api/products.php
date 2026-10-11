<?php
/**
 * Products API
 *   GET api/products.php          -> active products (public)
 *   GET api/products.php?all=1    -> all products incl. inactive (admin)
 */
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!empty($_GET['all'])) {
    require_login_api();
    json_response(['products' => Product::all()]);
}

// Public view: hide reseller prices and exact stock
$products = array_map(fn($p) => [
    'id'          => (int) $p['id'],
    'name'        => $p['name'],
    'description' => $p['description'],
    'weight_kg'   => (float) $p['weight_kg'],
    'price'       => (float) $p['retail_price'],
    'in_stock'    => $p['stock_quantity'] > 0,
    'image'       => $p['image_path'] ? url($p['image_path']) : null,
], Product::allActive());

json_response(['products' => $products]);

