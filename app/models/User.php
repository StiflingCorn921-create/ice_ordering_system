<?php

class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT id, username, full_name, email, role, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function updatePassword(int $id, string $newPassword): bool
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }
}

