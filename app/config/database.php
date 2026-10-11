<?php
/**
 * Database connection (PDO, shared single instance).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host    = 'localhost';
        $dbname  = 'ice_ordering_system';
        $user    = 'root';
        $pass    = '';
        $charset = 'utf8mb4';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            $msg = config('debug') ? $e->getMessage() : 'Please try again later.';
            die('Database connection failed. ' . htmlspecialchars($msg));
        }
    }

    return $pdo;
}

