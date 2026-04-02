<?php
/**
 * BlockedDateModel — manages calendar off-days that cannot be booked.
 */
class BlockedDateModel
{
    /**
     * Get all blocked dates ordered chronologically.
     */
    public static function all(): array
    {
        return Database::query(
            'SELECT * FROM blocked_dates ORDER BY blocked_date ASC'
        );
    }

    /**
     * Get blocked dates within a date range (for calendar rendering).
     */
    public static function inRange(string $from, string $to): array
    {
        return Database::query(
            'SELECT * FROM blocked_dates WHERE blocked_date BETWEEN ? AND ? ORDER BY blocked_date ASC',
            [$from, $to]
        );
    }

    /**
     * Get all blocked date strings (Y-m-d) as a flat array — useful for JS calendars.
     */
    public static function allDates(): array
    {
        $rows = Database::query('SELECT blocked_date FROM blocked_dates ORDER BY blocked_date ASC');
        return array_column($rows, 'blocked_date');
    }

    /**
     * Check whether a specific date is blocked.
     */
    public static function isBlocked(string $date): bool
    {
        $count = (int) Database::queryScalar(
            'SELECT COUNT(*) FROM blocked_dates WHERE blocked_date = ?',
            [$date]
        );
        return $count > 0;
    }

    /**
     * Find a single blocked date record.
     */
    public static function find(string $date): ?array
    {
        return Database::queryOne(
            'SELECT * FROM blocked_dates WHERE blocked_date = ?',
            [$date]
        );
    }

    /**
     * Block a date (insert or update reason).
     */
    public static function block(string $date, string $reason = ''): bool
    {
        // Validate date format
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return false;
        }

        Database::execute(
            'INSERT INTO blocked_dates (blocked_date, reason)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE reason = VALUES(reason)',
            [$date, $reason ?: null]
        );
        return true;
    }

    /**
     * Unblock a date (remove it).
     */
    public static function unblock(string $date): bool
    {
        return Database::execute(
            'DELETE FROM blocked_dates WHERE blocked_date = ?',
            [$date]
        ) > 0;
    }

    /**
     * Block multiple dates at once (e.g. a range).
     * Returns the count of dates blocked.
     */
    public static function blockRange(string $from, string $to, string $reason = ''): int
    {
        $start  = new DateTime($from);
        $end    = new DateTime($to);
        $count  = 0;

        while ($start <= $end) {
            self::block($start->format('Y-m-d'), $reason);
            $start->modify('+1 day');
            $count++;
        }

        return $count;
    }

    /**
     * Get blocked dates for the next N months (for admin calendar default view).
     */
    public static function upcoming(int $months = 3): array
    {
        $from = date('Y-m-d');
        $to   = date('Y-m-d', strtotime("+{$months} months"));
        return self::inRange($from, $to);
    }
}
