<?php
require_once __DIR__ . '/../app/bootstrap.php';

if (is_logged_in()) {
    redirect('admin/index.php');
}

$error = is_post() ? AuthController::login() : null;
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Yulio's Ice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= url('css/admin.css') ?>">
</head>
<body class="login-page">
    <div class="card login-card shadow-lg border-0 rounded-4 p-4 p-md-5">
        <div class="text-center mb-4">
            <img src="<?= url('assets/images/YuliosGoodLogo.png') ?>" alt="" width="80" height="80" class="rounded-circle mb-3">
            <h4 class="fw-bold">Yulio's Admin</h4>
            <p class="text-secondary small mb-0">Sign in to manage orders</p>
        </div>

        <?php require APP_ROOT . '/views/flash.php'; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus
                       value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username">
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-primary w-100 fw-bold py-2">Log In</button>
        </form>
        <a href="<?= url('public/index.php') ?>" class="d-block text-center small text-secondary mt-3">← Back to store</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

