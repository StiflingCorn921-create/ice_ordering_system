<?php

class AuthController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

    /** Handles POST from admin/login.php. Returns an error message, or redirects on success. */
    public static function login(): ?string
    {
        verify_csrf();

        $lockedUntil = $_SESSION['login_locked_until'] ?? 0;
        if ($lockedUntil > time()) {
            return 'Too many failed attempts. Please wait a few minutes and try again.';
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $username !== '' ? User::findByUsername($username) : null;

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
                $_SESSION['login_locked_until'] = time() + self::LOCK_SECONDS;
                $_SESSION['login_attempts'] = 0;
            }
            return 'Invalid username or password.';
        }

        // Upgrade hash automatically if PHP's default algorithm changes in the future
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], $password);
        }

        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
        login_user($user);
        flash('success', 'Welcome back, ' . $user['full_name'] . '!');
        redirect('admin/index.php');
        return null;
    }

    public static function logout(): void
    {
        logout_user();
        session_start();
        flash('success', 'You have been logged out.');
        redirect('admin/login.php');
    }
}

