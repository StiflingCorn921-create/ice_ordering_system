<?php
/**
 * Public layout header.
 * Optional variables: $pageTitle, $pageCss (filename in css/), $bodyClass, $hideNav
 */
$pageTitle = $pageTitle ?? config('name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
    <?php if (!empty($pageCss)): ?>
        <link rel="stylesheet" href="<?= url('css/' . $pageCss) ?>">
    <?php endif; ?>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <script>window.APP = { baseUrl: <?= json_encode(config('base_url')) ?> };</script>
</head>
<body class="<?= e($bodyClass ?? 'bg-light') ?>">

<?php if (empty($hideNav)): ?>
<nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= url('public/index.php') ?>">
            <img src="<?= url('assets/images/YuliosGoodLogo.png') ?>" alt="" class="rounded-circle me-2" width="36" height="36">
            Yulio's Ice
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url('public/index.php') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('public/form.php') ?>">Order</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('public/dashboard.php') ?>">Track Order</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php require APP_ROOT . '/views/flash.php'; ?>

