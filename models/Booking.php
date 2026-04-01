<?php
/**
 * BookingModel — data access for the `bookings` table.
 */
class BookingModel
{
    /**
     * Get all bookings with service title (admin).
     *
     * @param  string $search  Search by name, email, reference, service title
     * @param  string $status  Filter by status
     * @return array
     */
    public static function allForAdmin(string $search = '', string $status = ''): array
    {
        $sql = 'SELECT b.*, s.title AS service_title
                FROM bookings b
                JOIN services s ON b.service_id = s.id';
        $params = [];
        $where  = [];

        if ($search !== '') {
            $term = '%' . $search . '%';
            $where[] = '(b.customer_name LIKE ? OR b.customer_email LIKE ?
                         OR b.reference LIKE ? OR s.title LIKE ?)';
            $params  = array_merge($params, [$term, $term, $term, $term]);
        }

        if ($status !== '') {
            $where[]  = 'b.status = ?';
            $params[] = $status;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY b.created_at DESC';
        return Database::query($sql, $params);
    }

    /**
     * Get bookings for a specific user (by user_id or email).
     */
    public static function forUser(int $userId): array
    {
        return Database::query(
            'SELECT b.*, s.title AS service_title, s.image, s.category
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC',
            [$userId]
        );
    }

    /**
     * Find a single booking by ID.
     */
    public static function find(int $id): ?array
    {
        return Database::queryOne(
            'SELECT b.*, s.title AS service_title
             FROM bookings b JOIN services s ON b.service_id = s.id
             WHERE b.id = ?',
            [$id]
        );
    }

    /**
     * Find by booking reference (e.g. MG-20240101-XXXXX).
     */
    public static function findByReference(string $ref): ?array
    {
        return Database::queryOne(
            'SELECT b.*, s.title AS service_title
             FROM bookings b JOIN services s ON b.service_id = s.id
             WHERE b.reference = ?',
            [$ref]
        );
    }

    /**
     * Create a new booking. Returns the new booking ID.
     */
    public static function create(array $data): int
    {
        $reference = generateBookingRef();

        // Ensure unique reference (retry on collision)
        $attempts = 0;
        while (self::findByReference($reference) && $attempts < 5) {
            $reference = generateBookingRef();
            $attempts++;
        }

        return (int) Database::insert(
            'INSERT INTO bookings
              (reference,service_id,user_id,customer_name,customer_email,customer_phone,
               booking_date,people,unit_price,total_price,status,payment_status,notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $reference,
                (int)$data['service_id'],
                isset($data['user_id']) ? (int)$data['user_id'] : null,
                trim($data['customer_name']),
                strtolower(trim($data['customer_email'])),
                trim($data['customer_phone']),
                $data['booking_date'],
                (int)$data['people'],
                (float)$data['unit_price'],
                (float)$data['total_price'],
                $data['status']         ?? 'pending',
                $data['payment_status'] ?? 'unpaid',
                trim($data['notes']     ?? '') ?: null,
            ]
        );
    }

    /**
     * Update the booking status (confirmed / cancelled / pending).
     */
    public static function updateStatus(int $id, string $status, ?string $adminNotes = null): bool
    {
        $allowed = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        return Database::execute(
            'UPDATE bookings SET status = ?, admin_notes = ? WHERE id = ?',
            [$status, $adminNotes, $id]
        ) > 0;
    }

    /**
     * Update the payment status (unpaid / paid / refunded).
     */
    public static function updatePaymentStatus(int $id, string $paymentStatus): bool
    {
        return Database::execute(
            'UPDATE bookings SET payment_status = ? WHERE id = ?',
            [$paymentStatus, $id]
        ) > 0;
    }

    /**
     * Delete a booking.
     */
    public static function delete(int $id): bool
    {
        return Database::execute('DELETE FROM bookings WHERE id = ?', [$id]) > 0;
    }

    // ------------------------------------------------------------------
    // Aggregates (for admin dashboard KPIs)
    // ------------------------------------------------------------------

    public static function totalCount(): int
    {
        return (int) Database::queryScalar('SELECT COUNT(*) FROM bookings');
    }

    public static function pendingCount(): int
    {
        return (int) Database::queryScalar(
            "SELECT COUNT(*) FROM bookings WHERE status = 'pending'"
        );
    }

    public static function totalRevenue(): float
    {
        return (float) Database::queryScalar(
            "SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE payment_status = 'paid'"
        );
    }

    /**
     * Get the 5 most recent bookings for the dashboard.
     */
    public static function recent(int $limit = 5): array
    {
        return Database::query(
            'SELECT b.*, s.title AS service_title
             FROM bookings b JOIN services s ON b.service_id = s.id
             ORDER BY b.created_at DESC LIMIT ?',
            [$limit]
        );
    }
}
