<?php
/**
 * Customers API (admin only)
 *   GET api/customers.php?q=search
 */
require_once __DIR__ . '/../app/bootstrap.php';

require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

json_response(['customers' => Customer::all(trim($_GET['q'] ?? ''))]);

