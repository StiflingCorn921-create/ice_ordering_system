<?php
/**
 * Asymmetric & Session Auth API
 *   GET  api/auth.php                          -> current user or 401
 *   GET  api/auth.php?action=public_key        -> RSA 2048-bit Public Key
 *   POST api/auth.php {action: "login", ...}   -> returns user + RS256 Bearer JWT token
 *   POST api/auth.php {action: "logout"}       -> logs out current session
 */
require_once __DIR__ . '/../app/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (($_GET['action'] ?? '') === 'public_key') {
        $pubKey = TokenService::getPublicKey();
        if (!$pubKey) {
            json_response(['error' => 'Public key not found'], 404);
        }
        json_response([
            'algorithm'  => 'RS256',
            'key_type'   => 'RSA',
            'public_key' => $pubKey,
        ]);
    }

    require_login_api();
    json_response([
        'user'  => current_user(),
        'token' => current_token(),
    ]);
}

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Allow Bearer token authorization OR session CSRF token
$bearer = get_bearer_token();
if (!$bearer) {
    verify_csrf();
}

$input  = json_input();
$action = $input['action'] ?? '';

if ($action === 'login') {
    $user = User::findByUsername(trim($input['username'] ?? ''));
    if (!$user || !password_verify($input['password'] ?? '', $user['password_hash'])) {
        json_response(['error' => 'Invalid username or password.'], 401);
    }
    
    $token = login_user($user);
    json_response([
        'user'       => current_user(),
        'token'      => $token,
        'token_type' => 'Bearer',
        'csrf_token' => csrf_token(),
    ]);
}

if ($action === 'logout') {
    logout_user();
    json_response(['ok' => true]);
}

json_response(['error' => 'Unknown action'], 400);
