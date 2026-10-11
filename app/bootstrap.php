<?php
/**
 * Bootstrap: every page/API endpoint includes this one file first.
 * It loads config, helpers, models, services and controllers, and starts the session.
 */

define('APP_ROOT', __DIR__);

$GLOBALS['__config'] = require APP_ROOT . '/config/app.php';

function config(string $key, $default = null)
{
    $value = $GLOBALS['__config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

date_default_timezone_set(config('timezone', 'Asia/Manila'));

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/helpers/functions.php';
require_once APP_ROOT . '/helpers/validation.php';
require_once APP_ROOT . '/helpers/auth.php';

// Simple class autoloader for models, services and controllers
spl_autoload_register(function (string $class) {
    foreach (['models', 'services', 'controllers'] as $dir) {
        $file = APP_ROOT . "/$dir/$class.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

