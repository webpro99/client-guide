<?php
/**
 * SettingModel — key-value settings stored in the `settings` table.
 */
class SettingModel
{
    /** @var array  In-memory cache to avoid repeated DB reads per request. */
    private static array $cache = [];

    /**
     * Get a single setting value (or a default if not found).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!isset(self::$cache[$key])) {
            $row = Database::queryOne(
                'SELECT setting_value FROM settings WHERE setting_key = ?',
                [$key]
            );
            self::$cache[$key] = $row ? $row['setting_value'] : $default;
        }
        return self::$cache[$key] ?? $default;
    }

    /**
     * Get all settings as an associative array.
     */
    public static function all(): array
    {
        $rows = Database::query('SELECT setting_key, setting_value FROM settings');
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }
        return $map;
    }

    /**
     * Set (upsert) a single setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        Database::execute(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache[$key] = $value;
    }

    /**
     * Set (upsert) multiple settings at once.
     *
     * @param  array $settings  Associative array of key => value
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }
}
