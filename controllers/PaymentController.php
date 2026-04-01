<?php
require_once ROOT_PATH . '/models/Booking.php';
require_once ROOT_PATH . '/models/Payment.php';
require_once ROOT_PATH . '/models/Setting.php';

/**
 * PaymentController — PayPal REST API integration.
 *
 * Flow:
 *  1. Client calls POST /api/payments/create  { booking_id }
 *     → creates a PayPal Order, returns { id, approveUrl }
 *  2. User approves on PayPal
 *  3. PayPal redirects to GET /paypal/success?token=ORDER_ID&booking_id=X
 *     → captures the order, marks booking as paid
 *  4. On failure PayPal redirects to GET /paypal/cancel?booking_id=X
 */
class PaymentController
{
    // ------------------------------------------------------------------
    // PayPal API helpers
    // ------------------------------------------------------------------

    /**
     * Get a PayPal OAuth access token.
     */
    private function getAccessToken(): string
    {
        $clientId = PAYPAL_CLIENT_ID;
        $secret   = PAYPAL_SECRET;

        if (!$clientId || !$secret) {
            throw new RuntimeException('PayPal credentials not configured.');
        }

        $ch = curl_init(PAYPAL_API_BASE . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_USERPWD        => $clientId . ':' . $secret,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Accept-Language: en_US',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException('PayPal auth failed: ' . $response);
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? throw new RuntimeException('No access token in PayPal response.');
    }

    /**
     * Make an authenticated request to the PayPal API.
     */
    private function paypalRequest(string $method, string $endpoint, array $body = []): array
    {
        $token = $this->getAccessToken();
        $url   = PAYPAL_API_BASE . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'Prefer: return=representation',
            ],
        ]);

        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true) ?? [];
        $data['_http_code'] = $httpCode;
        return $data;
    }

    // ------------------------------------------------------------------
    // Create PayPal Order (called via AJAX)
    // ------------------------------------------------------------------

    public function create(array $params = []): void
    {
        $body      = jsonBody();
        $bookingId = (int)($body['booking_id'] ?? 0);

        $booking = BookingModel::find($bookingId);
        if (!$booking) {
            jsonError('Booking not found.', 404);
        }

        if ($booking['payment_status'] === 'paid') {
            jsonError('This booking has already been paid.', 409);
        }

        try {
            $returnUrl = url('paypal/success') . '?booking_id=' . $bookingId;
            $cancelUrl = url('paypal/cancel')  . '?booking_id=' . $bookingId;

            $response = $this->paypalRequest('POST', '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $booking['reference'],
                    'description'  => 'Marrakech Guide — ' . $booking['service_title'],
                    'amount'       => [
                        'currency_code' => PAYPAL_CURRENCY,
                        'value'         => number_format((float)$booking['total_price'], 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name'          => 'Marrakech Guide',
                    'landing_page'        => 'BILLING',
                    'user_action'         => 'PAY_NOW',
                    'return_url'          => $returnUrl,
                    'cancel_url'          => $cancelUrl,
                ],
            ]);

            if (($response['_http_code'] ?? 0) !== 201) {
                throw new RuntimeException('PayPal order creation failed: ' . json_encode($response));
            }

            // Persist the payment record
            $paypalOrderId = $response['id'];
            $approveUrl    = '';
            foreach ($response['links'] ?? [] as $link) {
                if ($link['rel'] === 'approve') {
                    $approveUrl = $link['href'];
                    break;
                }
            }

            PaymentModel::create([
                'booking_id'      => $bookingId,
                'paypal_order_id' => $paypalOrderId,
                'amount'          => $booking['total_price'],
                'currency'        => PAYPAL_CURRENCY,
                'status'          => 'created',
                'raw_response'    => $response,
            ]);

            jsonSuccess([
                'paypal_order_id' => $paypalOrderId,
                'approve_url'     => $approveUrl,
            ], 'PayPal order created.');

        } catch (RuntimeException $e) {
            error_log('PayPal error: ' . $e->getMessage());
            jsonError('Payment initialization failed. Please try again.', 500);
        }
    }

    // ------------------------------------------------------------------
    // Capture PayPal Order (called from success redirect)
    // ------------------------------------------------------------------

    public function success(array $params = []): void
    {
        $bookingId    = (int) get('booking_id');
        $paypalToken  = get('token');   // PayPal order ID

        $booking = BookingModel::find($bookingId);
        if (!$booking || !$paypalToken) {
            flash('error', 'Invalid payment session.');
            redirect('/bookings');
        }

        try {
            // Capture the PayPal order
            $response = $this->paypalRequest(
                'POST',
                "/v2/checkout/orders/{$paypalToken}/capture"
            );

            $httpCode = $response['_http_code'] ?? 0;

            if ($httpCode === 201 && $response['status'] === 'COMPLETED') {
                // Extract capture details
                $capture   = $response['purchase_units'][0]['payments']['captures'][0] ?? [];
                $captureId = $capture['id'] ?? '';
                $payer     = $response['payer'] ?? [];

                // Update payment record
                $payment = PaymentModel::findByPaypalOrder($paypalToken);
                if ($payment) {
                    PaymentModel::update($payment['id'], [
                        'paypal_capture_id' => $captureId,
                        'status'            => 'captured',
                        'payer_email'       => $payer['email_address'] ?? null,
                        'payer_name'        => ($payer['name']['given_name'] ?? '') . ' ' . ($payer['name']['surname'] ?? ''),
                        'raw_response'      => $response,
                    ]);
                }

                // Mark booking as paid & confirmed
                BookingModel::updatePaymentStatus($bookingId, 'paid');
                BookingModel::updateStatus($bookingId, 'confirmed');

                // Store reference in session for guest bookings
                $_SESSION['last_booking_ref'] = $booking['reference'];

                flash('success', 'Payment successful! Your booking is confirmed.');
                redirect('/bookings');
            } else {
                throw new RuntimeException('Capture failed: ' . json_encode($response));
            }

        } catch (RuntimeException $e) {
            error_log('PayPal capture error: ' . $e->getMessage());
            flash('error', 'Payment capture failed. Please contact us.');
            redirect('/bookings');
        }
    }

    // ------------------------------------------------------------------
    // Cancel redirect
    // ------------------------------------------------------------------

    public function cancel(array $params = []): void
    {
        flash('info', 'Payment was cancelled. Your booking is still pending.');
        redirect('/bookings');
    }
}
