<?php
/**
 * AI chat API (local Ollama)
 *   POST api/ai.php {message, mode: "customer" | "admin"}
 * "admin" mode requires login. Needs the X-CSRF-Token header.
 */
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!get_bearer_token()) {
    verify_csrf();
}
$input   = json_input();
$message = trim((string) ($input['message'] ?? ''));
$mode    = ($input['mode'] ?? 'customer') === 'admin' ? 'admin' : 'customer';

if ($message === '' || mb_strlen($message) > 500) {
    json_response(['error' => 'Message must be 1-500 characters.'], 422);
}

// Simple per-session rate limit: 10 messages per minute (the local GPU is precious)
$now = time();
$_SESSION['ai_hits'] = array_values(array_filter($_SESSION['ai_hits'] ?? [], fn($t) => $t > $now - 60));
if (count($_SESSION['ai_hits']) >= 10) {
    json_response(['error' => 'Too many messages. Please wait a minute.'], 429);
}
$_SESSION['ai_hits'][] = $now;

$sessionId = session_id();
$loggedIn  = is_logged_in();
// Release the session lock: the AI call can take many seconds and would otherwise block other tabs
session_write_close();

if ($mode === 'admin' && !$loggedIn) {
    json_response(['error' => 'Unauthorized'], 401);
}

$result = $mode === 'admin'
    ? AIService::adminChat($message, $sessionId)
    : AIService::customerChat($message, $sessionId);

json_response($result, $result['ok'] ? 200 : 503);
