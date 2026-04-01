<?php
/**
 * UserModel — data access for the `users` table.
 */
class UserModel
{
    /**
     * Find a user by ID.
     */
    public static function find(int $id): ?array
    {
        return Database::queryOne(
            'SELECT id, name, email, role, phone, created_at FROM users WHERE id = ?',
            [$id]
        );
    }

    /**
     * Find a user by email address.
     */
    public static function findByEmail(string $email): ?array
    {
        return Database::queryOne(
            'SELECT * FROM users WHERE email = ?',
            [strtolower(trim($email))]
        );
    }

    /**
     * Return all users (admin use).
     */
    public static function all(): array
    {
        return Database::query(
            'SELECT id, name, email, role, phone, created_at FROM users ORDER BY created_at DESC'
        );
    }

    /**
     * Create a new user. Returns the new user ID.
     */
    public static function create(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        return (int) Database::insert(
            'INSERT INTO users (name, email, password_hash, role, phone)
             VALUES (?, ?, ?, ?, ?)',
            [
                trim($data['name']),
                strtolower(trim($data['email'])),
                $hash,
                $data['role'] ?? 'user',
                $data['phone'] ?? null,
            ]
        );
    }

    /**
     * Update user profile fields.
     */
    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[]  = trim($data['name']);
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = ?';
            $params[]  = trim($data['phone']);
        }
        if (isset($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[]  = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $params[]  = $data['role'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;

        return Database::execute(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $params
        ) > 0;
    }

    /**
     * Delete a user by ID.
     */
    public static function delete(int $id): bool
    {
        return Database::execute('DELETE FROM users WHERE id = ?', [$id]) > 0;
    }

    /**
     * Check if an email is already registered.
     */
    public static function emailExists(string $email): bool
    {
        return (int) Database::queryScalar(
            'SELECT COUNT(*) FROM users WHERE email = ?',
            [strtolower(trim($email))]
        ) > 0;
    }
}
