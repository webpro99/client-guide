<?php
/**
 * Auth — session-based authentication helpers.
 */
class Auth
{
    private const SESSION_KEY = 'mg_user';

    // ------------------------------------------------------------------
    // Login / Logout
    // ------------------------------------------------------------------

    /**
     * Attempt to log in with email + password.
     * Returns the user array on success, false on failure.
     */
    public static function attempt(string $email, string $password): array|false
    {
        $user = Database::queryOne(
            'SELECT id, name, email, password_hash, role, phone FROM users WHERE email = ?',
            [strtolower(trim($email))]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Rehash if the cost factor has changed
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::execute(
                'UPDATE users SET password_hash = ? WHERE id = ?',
                [$newHash, $user['id']]
            );
        }

        self::login($user);
        return $user;
    }

    /**
     * Store user data in the session.
     */
    public static function login(array $user): void
    {
        // Rotate session ID to prevent fixation
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
    }

    /**
     * Destroy the session (logout).
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    // ------------------------------------------------------------------
    // Checks
    // ------------------------------------------------------------------

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION[self::SESSION_KEY]['role'] ?? '') === 'admin';
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION[self::SESSION_KEY]['id'] ?? null;
    }

    // ------------------------------------------------------------------
    // Route guards
    // ------------------------------------------------------------------

    /**
     * Redirect to login if not authenticated.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            redirect('/login?next=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }

    /**
     * Redirect to home if not admin.
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            if (!self::check()) {
                redirect('/login?next=' . urlencode($_SERVER['REQUEST_URI']));
            }
            redirect('/');
        }
    }
}
