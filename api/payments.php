<?php
/**
 * API: /api/payments
 *
 * POST /api/payments/create   → initiate PayPal payment
 * POST /api/payments/capture  → capture a PayPal order (Smart Button flow)
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/Payment.php';
require_once ROOT_PATH . '/controllers/PaymentController.php';

header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#/api/payments/(create|capture)#', $uri, $m)) {
    $action = $m[1];
} else {
    jsonError('Unknown payment endpoint.', 404);
}

if (method() !== 'POST') {
    jsonError('Method not allowed.', 405);
}

$controller = new PaymentController();

if ($action === 'create') {
    $controller->create([]);
} elseif ($action === 'capture') {
    // Smart Button flow: client sends { paypal_order_id, booking_id }
    $body      = jsonBody();
    $orderId   = $body['paypal_order_id'] ?? '';
    $bookingId = (int)($body['booking_id'] ?? 0);

    if (!$orderId || !$bookingId) {
        jsonError('paypal_order_id and booking_id are required.', 422);
    }

    // Inject into GET params so PaymentController::success() works
    $_GET['token']      = $orderId;
    $_GET['booking_id'] = $bookingId;

    $controller->success([]);
}
