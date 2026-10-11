<?php
/**
 * Session-based authentication helpers for the admin area.
 */

function get_bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
        return $matches[1];
    }
    return null;
}

function current_user(): ?array
{
    // 1. Check native session first
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    // 2. Check for Asymmetric RS256 Bearer Token
    $bearer = get_bearer_token();
    if ($bearer) {
        $payload = TokenService::verifyToken($bearer);
        if ($payload) {
            return [
                'id'        => (int) ($payload['sub'] ?? 0),
                'username'  => $payload['username'] ?? '',
                'full_name' => $payload['full_name'] ?? ($payload['username'] ?? ''),
                'role'      => $payload['role'] ?? 'staff',
            ];
        }
    }

    return null;
}

function current_token(): ?string
{
    if (!empty($_SESSION['asymmetric_token'])) {
        return $_SESSION['asymmetric_token'];
    }
    return get_bearer_token();
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function login_user(array $user): string
{
    // New session ID on login prevents session fixation attacks
    session_regenerate_id(true);
    
    // Generate asymmetric RS256 token
    $token = TokenService::createToken([
        'sub'       => (int) $user['id'],
        'username'  => $user['username'],
        'full_name' => $user['full_name'],
        'role'      => $user['role'],
    ]);

    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'username'  => $user['username'],
        'full_name' => $user['full_name'],
        'role'      => $user['role'],
    ];
    $_SESSION['asymmetric_token'] = $token;

    return $token;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Put at the top of every admin page. Redirects to login if not signed in. */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please log in first.');
        redirect('admin/login.php');
    }
}

/** For API endpoints: returns 401 JSON instead of redirecting. */
function require_login_api(): void
{
    if (!is_logged_in()) {
        json_response(['error' => 'Unauthorized'], 401);
    }
}

function require_role(string $role): void
{
    require_login();
    if (current_user()['role'] !== $role) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

