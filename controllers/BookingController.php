<?php
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/Service.php';
require_once ROOT_PATH . '/models/Setting.php';

/**
 * BookingController — user-facing bookings list.
 */
class BookingController
{
    /**
     * GET /bookings — show the current user's bookings.
     * Guests see the booking they just made via session reference.
     */
    public function index(array $params = []): void
    {
        $settings = SettingModel::all();

        if (Auth::check()) {
            $bookings = BookingModel::forUser(Auth::id());
        } else {
            // Guest: show booking stored in session (if any)
            $ref      = $_SESSION['last_booking_ref'] ?? null;
            $bookings = $ref ? array_filter([BookingModel::findByReference($ref)]) : [];
        }

        view('frontend/bookings', [
            'bookings'  => array_values($bookings),
            'settings'  => $settings,
            'pageTitle' => 'My Bookings — Marrakech Guide',
        ]);
    }
}
