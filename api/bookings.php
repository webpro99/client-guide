<?php
/**
 * API: /api/bookings
 *
 * POST /api/bookings           → create a booking (public, AJAX)
 * GET  /api/bookings           → list user's bookings (auth required)
 * GET  /api/bookings/:id       → single booking
 * POST /api/bookings/:id/status → update booking status (admin)
 * DELETE /api/bookings/:id     → delete booking (admin)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/Service.php';
require_once ROOT_PATH . '/models/BlockedDate.php';

header('Content-Type: application/json; charset=utf-8');

$httpMethod = method();
$uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Match /api/bookings/:id/status  or  /api/bookings/:id
preg_match('#/api/bookings/?(\d+)?(/status)?#', $uri, $m);
$id        = isset($m[1]) ? (int)$m[1] : null;
$statusOp  = isset($m[2]) && $m[2] === '/status';

switch (true) {

    // -------------------------------------------------------------------
    // POST /api/bookings  — create a new booking
    // -------------------------------------------------------------------
    case ($httpMethod === 'POST' && !$id):
        $data = jsonBody();

        // Validate required fields
        $required = ['service_id','customer_name','customer_email','customer_phone','booking_date','people'];
        $missing  = array_filter($required, fn($k) => empty($data[$k]));
        if ($missing) {
            jsonError('Missing required fields: ' . implode(', ', $missing), 422);
        }

        if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            jsonError('Invalid email address.', 422);
        }

        $date = DateTime::createFromFormat('Y-m-d', $data['booking_date']);
        if (!$date || $date < new DateTime('today')) {
            jsonError('Booking date must be today or in the future.', 422);
        }

        // Check if date is blocked by admin
        if (BlockedDateModel::isBlocked($data['booking_date'])) {
            $blocked = BlockedDateModel::find($data['booking_date']);
            $msg = 'This date is not available for bookings.';
            if (!empty($blocked['reason'])) {
                $msg .= ' Reason: ' . $blocked['reason'];
            }
            jsonError($msg, 422);
        }

        $service = ServiceModel::find((int)$data['service_id']);
        if (!$service) {
            jsonError('Service not found.', 404);
        }

        $people    = max(1, (int)$data['people']);
        $unitPrice = (float)$service['price'];
        $total     = $unitPrice * $people;

        $bookingId = BookingModel::create([
            'service_id'     => $service['id'],
            'user_id'        => Auth::id(),
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'booking_date'   => $data['booking_date'],
            'people'         => $people,
            'unit_price'     => $unitPrice,
            'total_price'    => $total,
            'notes'          => $data['notes'] ?? '',
        ]);

        $booking = BookingModel::find($bookingId);

        // Store reference in session for guest bookings
        $_SESSION['last_booking_ref'] = $booking['reference'];

        jsonSuccess([
            'booking'   => $booking,
            'reference' => $booking['reference'],
        ], 'Booking created successfully.');
        break;

    // -------------------------------------------------------------------
    // GET /api/bookings  — list authenticated user's bookings
    // -------------------------------------------------------------------
    case ($httpMethod === 'GET' && !$id):
        Auth::requireAuth();
        $bookings = BookingModel::forUser(Auth::id());
        jsonSuccess(['bookings' => $bookings, 'total' => count($bookings)]);
        break;

    // -------------------------------------------------------------------
    // GET /api/bookings/:id  — single booking
    // -------------------------------------------------------------------
    case ($httpMethod === 'GET' && $id && !$statusOp):
        $booking = BookingModel::find($id);
        if (!$booking) jsonError('Booking not found.', 404);

        // Users can only see their own bookings; admins see all
        if (!Auth::isAdmin()) {
            if ((int)($booking['user_id'] ?? -1) !== Auth::id()) {
                jsonError('Forbidden.', 403);
            }
        }
        jsonSuccess(['booking' => $booking]);
        break;

    // -------------------------------------------------------------------
    // POST /api/bookings/:id/status  — update booking status (admin)
    // -------------------------------------------------------------------
    case ($httpMethod === 'POST' && $id && $statusOp):
        Auth::requireAdmin();
        $data = jsonBody();

        if (empty($data['status'])) jsonError('status is required.', 422);

        $ok = BookingModel::updateStatus($id, $data['status'], $data['admin_notes'] ?? null);
        if (!$ok) jsonError('Invalid status or booking not found.', 400);

        $booking = BookingModel::find($id);
        jsonSuccess(['booking' => $booking], 'Status updated.');
        break;

    // -------------------------------------------------------------------
    // DELETE /api/bookings/:id
    // -------------------------------------------------------------------
    case ($httpMethod === 'DELETE' && $id):
        Auth::requireAdmin();
        BookingModel::delete($id);
        jsonSuccess([], 'Booking deleted.');
        break;

    default:
        jsonError('Method not allowed.', 405);
}
