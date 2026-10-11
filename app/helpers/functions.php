<?php
/**
 * General helper functions.
 */

/** Escape output for HTML. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Build a URL relative to the project root, e.g. url('admin/orders.php'). */
function url(string $path = ''): string
{
    return rtrim(config('base_url'), '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function money($amount): string
{
    return config('currency', '₱') . number_format((float) $amount, 2);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/* ---------- Flash messages (one-time messages shown after redirect) ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------- CSRF protection ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        die('Your session expired. Please go back, refresh the page and try again.');
    }
}

/* ---------- JSON helpers for api/ ---------- */

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_input(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : $_POST;
}

/* ---------- Simple file logger ---------- */

function app_log(string $message): void
{
    $dir = dirname(APP_ROOT) . '/storage/logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    file_put_contents($dir . '/app.log', '[' . date('Y-m-d H:i:s') . "] $message" . PHP_EOL, FILE_APPEND);
}

/** Bootstrap badge class for an order status. */
function status_badge(string $status): string
{
    $map = [
        'Pending'          => 'bg-warning text-dark',
        'Confirmed'        => 'bg-primary',
        'Preparing'        => 'bg-secondary',
        'Out for Delivery' => 'bg-info text-dark',
        'Completed'        => 'bg-success',
        'Cancelled'        => 'bg-danger',
    ];
    return '<span class="badge ' . ($map[$status] ?? 'bg-light text-dark') . '">' . e($status) . '</span>';
}

