<?php
/**
 * Admin layout header (sidebar + top bar).
 * Variables: $pageTitle, $activeNav ('dashboard'|'orders'|'inventory'|'customers'|'reports'), $pageJs
 */
$pageTitle = $pageTitle ?? 'Admin';
$activeNav = $activeNav ?? '';
$navItems = [
    'dashboard' => ['admin/index.php', 'fa-gauge-high', 'Dashboard'],
    'orders'    => ['admin/orders.php', 'fa-clipboard-list', 'Orders'],
    'inventory' => ['admin/inventory.php', 'fa-boxes-stacked', 'Inventory'],
    'customers' => ['admin/customers.php', 'fa-users', 'Customers'],
    'reports'   => ['admin/reports.php', 'fa-chart-line', 'Reports'],
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Yulio's Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('css/admin.css') ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <script>window.APP = { baseUrl: <?= json_encode(config('base_url')) ?> };</script>
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <img src="<?= url('assets/images/YuliosGoodLogo.png') ?>" alt="" width="40" height="40" class="rounded-circle">
            <span>Yulio's Admin</span>
        </div>
        <nav class="nav flex-column">
            <?php foreach ($navItems as $key => [$href, $icon, $label]): ?>
                <a class="nav-link <?= $activeNav === $key ? 'active' : '' ?>" href="<?= url($href) ?>">
                    <i class="fa-solid <?= $icon ?> fa-fw me-2"></i><?= $label ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <a class="nav-link" href="<?= url('public/index.php') ?>" target="_blank"><i class="fa-solid fa-store fa-fw me-2"></i>View Store</a>
            <a class="nav-link text-danger" href="<?= url('admin/logout.php') ?>"><i class="fa-solid fa-right-from-bracket fa-fw me-2"></i>Logout</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <h1 class="h4 mb-0"><?= e($pageTitle) ?></h1>
            <span class="text-secondary small"><i class="fa-solid fa-user me-1"></i><?= e(current_user()['full_name'] ?? '') ?></span>
        </header>
        <?php require APP_ROOT . '/views/flash.php'; ?>
        <div class="admin-content">

