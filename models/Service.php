<?php
/**
 * ServiceModel — data access for the `services` table.
 */
class ServiceModel
{
    /**
     * Retrieve all active services, optionally filtered.
     *
     * @param  string $search    Full-text search term
     * @param  string $category  Filter by category
     * @return array
     */
    public static function all(string $search = '', string $category = ''): array
    {
        $sql    = 'SELECT * FROM services WHERE is_active = 1';
        $params = [];

        if ($search !== '') {
            $sql    .= ' AND (title LIKE ? OR description LIKE ? OR tagline LIKE ?)';
            $term    = '%' . $search . '%';
            $params  = array_merge($params, [$term, $term, $term]);
        }

        if ($category !== '') {
            $sql    .= ' AND category = ?';
            $params[] = $category;
        }

        $sql .= ' ORDER BY sort_order ASC, id ASC';

        $rows = Database::query($sql, $params);
        return array_map([self::class, 'decode'], $rows);
    }

    /**
     * Retrieve ALL services (including inactive) for admin use.
     */
    public static function allForAdmin(): array
    {
        $rows = Database::query('SELECT * FROM services ORDER BY sort_order ASC, id ASC');
        return array_map([self::class, 'decode'], $rows);
    }

    /**
     * Find a single service by ID.
     */
    public static function find(int $id): ?array
    {
        $row = Database::queryOne('SELECT * FROM services WHERE id = ?', [$id]);
        return $row ? self::decode($row) : null;
    }

    /**
     * Return services in the same category (for "You May Also Like").
     */
    public static function related(int $excludeId, string $category, int $limit = 3): array
    {
        $rows = Database::query(
            'SELECT * FROM services WHERE is_active = 1 AND id != ? AND category = ?
             ORDER BY RAND() LIMIT ?',
            [$excludeId, $category, $limit]
        );
        return array_map([self::class, 'decode'], $rows);
    }

    /**
     * Return all distinct category names.
     */
    public static function categories(): array
    {
        $rows = Database::query(
            'SELECT DISTINCT category FROM services WHERE is_active = 1 ORDER BY category'
        );
        return array_column($rows, 'category');
    }

    /**
     * Create a new service. Returns the new ID.
     */
    public static function create(array $data): int
    {
        return (int) Database::insert(
            'INSERT INTO services
              (title,tagline,category,fa_icon,price,duration,location,difficulty,
               language,group_type,max_people,rating,reviews,image,description,
               long_desc,highlights,included,not_included,meeting_point,cancel_policy,
               badge,what_to_bring,min_age,is_active,sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            self::buildParams($data)
        );
    }

    /**
     * Update an existing service.
     */
    public static function update(int $id, array $data): bool
    {
        return Database::execute(
            'UPDATE services SET
               title=?,tagline=?,category=?,fa_icon=?,price=?,duration=?,location=?,
               difficulty=?,language=?,group_type=?,max_people=?,rating=?,reviews=?,
               image=?,description=?,long_desc=?,highlights=?,included=?,not_included=?,
               meeting_point=?,cancel_policy=?,badge=?,what_to_bring=?,min_age=?,
               is_active=?,sort_order=?
             WHERE id = ?',
            array_merge(self::buildParams($data), [$id])
        ) > 0;
    }

    /**
     * Delete a service.
     */
    public static function delete(int $id): bool
    {
        return Database::execute('DELETE FROM services WHERE id = ?', [$id]) > 0;
    }

    /**
     * Toggle the is_active flag.
     */
    public static function setActive(int $id, bool $active): bool
    {
        return Database::execute(
            'UPDATE services SET is_active = ? WHERE id = ?',
            [(int)$active, $id]
        ) > 0;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /** Decode JSON columns back to PHP arrays. */
    private static function decode(array $row): array
    {
        foreach (['highlights', 'included', 'not_included'] as $col) {
            $row[$col] = decodeJson($row[$col] ?? null);
        }
        return $row;
    }

    /** Build the ordered parameter list for INSERT/UPDATE. */
    private static function buildParams(array $d): array
    {
        return [
            trim($d['title']       ?? ''),
            trim($d['tagline']     ?? '') ?: null,
            trim($d['category']    ?? 'Cultural'),
            trim($d['fa_icon']     ?? 'fa-compass'),
            (float)($d['price']    ?? 0),
            trim($d['duration']    ?? '') ?: null,
            trim($d['location']    ?? '') ?: null,
            trim($d['difficulty']  ?? 'Easy'),
            trim($d['language']    ?? 'English'),
            in_array($d['group_type'] ?? '', ['private','group']) ? $d['group_type'] : 'group',
            (int)($d['max_people'] ?? 12),
            (float)($d['rating']   ?? 5.0),
            (int)($d['reviews']    ?? 0),
            trim($d['image']       ?? '') ?: null,
            trim($d['description'] ?? '') ?: null,
            trim($d['long_desc']   ?? '') ?: null,
            // JSON-encode array fields if they're already arrays
            is_array($d['highlights']    ?? null) ? json_encode($d['highlights'])    : ($d['highlights']    ?? null),
            is_array($d['included']      ?? null) ? json_encode($d['included'])      : ($d['included']      ?? null),
            is_array($d['not_included']  ?? null) ? json_encode($d['not_included'])  : ($d['not_included']  ?? null),
            trim($d['meeting_point']     ?? '') ?: null,
            trim($d['cancel_policy']     ?? '') ?: null,
            trim($d['badge']             ?? '') ?: null,
            trim($d['what_to_bring']     ?? '') ?: null,
            trim($d['min_age']           ?? '') ?: null,
            isset($d['is_active']) ? (int)(bool)$d['is_active'] : 1,
            (int)($d['sort_order'] ?? 0),
        ];
    }
}
