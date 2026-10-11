<?php
/**
 * Legacy db.php adapter
 * Proxies to new PDO database connection in app/
 */
require_once __DIR__ . '/app/bootstrap.php';
$pdo = db();