<?php
/**
 * PaymentModel — data access for the `payments` table.
 */
class PaymentModel
{
    /**
     * Find a payment by ID.
     */
    public static function find(int $id): ?array
    {
        return Database::queryOne('SELECT * FROM payments WHERE id = ?', [$id]);
    }

    /**
     * Find a payment by PayPal order ID.
     */
    public static function findByPaypalOrder(string $orderId): ?array
    {
        return Database::queryOne(
            'SELECT * FROM payments WHERE paypal_order_id = ?',
            [$orderId]
        );
    }

    /**
     * Find payment(s) for a booking.
     */
    public static function forBooking(int $bookingId): array
    {
        return Database::query(
            'SELECT * FROM payments WHERE booking_id = ? ORDER BY created_at DESC',
            [$bookingId]
        );
    }

    /**
     * Create a new payment record. Returns the new payment ID.
     */
    public static function create(array $data): int
    {
        return (int) Database::insert(
            'INSERT INTO payments
              (booking_id,paypal_order_id,amount,currency,status,payer_email,payer_name,raw_response)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                (int)$data['booking_id'],
                $data['paypal_order_id'] ?? null,
                (float)$data['amount'],
                $data['currency']   ?? PAYPAL_CURRENCY,
                $data['status']     ?? 'created',
                $data['payer_email'] ?? null,
                $data['payer_name']  ?? null,
                isset($data['raw_response'])
                    ? (is_string($data['raw_response']) ? $data['raw_response'] : json_encode($data['raw_response']))
                    : null,
            ]
        );
    }

    /**
     * Update a payment record after PayPal callback.
     */
    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach (['paypal_capture_id','status','payer_email','payer_name'] as $f) {
            if (isset($data[$f])) {
                $fields[] = "{$f} = ?";
                $params[]  = $data[$f];
            }
        }

        if (isset($data['raw_response'])) {
            $fields[] = 'raw_response = ?';
            $params[]  = is_string($data['raw_response'])
                ? $data['raw_response']
                : json_encode($data['raw_response']);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;

        return Database::execute(
            'UPDATE payments SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $params
        ) > 0;
    }
}
