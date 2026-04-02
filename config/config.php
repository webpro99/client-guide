<?php
/**
 * Marrakech Guide — Application Configuration
 *
 * Copy this file to config/config.php and fill in your values.
 * NEVER commit real credentials to version control.
 */

// ---------------------------------------------------------------------------
// Environment
// ---------------------------------------------------------------------------
define('APP_ENV',  getenv('APP_ENV')  ?: 'development'); // 'production' | 'development'
define('APP_URL',  getenv('APP_URL')  ?: 'http://localhost/client-guide');
define('APP_NAME', 'Marrakech Guide');

// ---------------------------------------------------------------------------
// Database
// ---------------------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'marrakech_guide');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------------
define('SESSION_NAME',     'mg_session');
define('SESSION_LIFETIME', 7200);          // 2 hours (seconds)
define('COOKIE_SECURE',    APP_ENV === 'production');
define('COOKIE_SAMESITE',  'Strict');

// ---------------------------------------------------------------------------
// PayPal
// ---------------------------------------------------------------------------
// 'sandbox' for testing, 'live' for production
define('PAYPAL_MODE',      getenv('PAYPAL_MODE')      ?: 'sandbox');
define('PAYPAL_CLIENT_ID', getenv('PAYPAL_CLIENT_ID') ?: '');
define('PAYPAL_SECRET',    getenv('PAYPAL_SECRET')    ?: '');
define('PAYPAL_CURRENCY',  'USD');

$paypalBaseUrl = (PAYPAL_MODE === 'live')
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com';
define('PAYPAL_API_BASE', $paypalBaseUrl);

// ---------------------------------------------------------------------------
// Paths
// ---------------------------------------------------------------------------
define('ROOT_PATH',    dirname(__DIR__));
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('SRC_PATH',     ROOT_PATH . '/src');
define('MODELS_PATH',  ROOT_PATH . '/models');
define('VIEWS_PATH',   ROOT_PATH . '/views');
define('PUBLIC_PATH',  ROOT_PATH . '/public');

// ---------------------------------------------------------------------------
// Error Reporting
// ---------------------------------------------------------------------------
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------------------------
// Autoloader (simple PSR-4-like)
// ---------------------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    $map = [
        'Database'          => SRC_PATH    . '/Database.php',
        'Auth'              => SRC_PATH    . '/Auth.php',
        'CSRF'              => SRC_PATH    . '/CSRF.php',
        'Router'            => SRC_PATH    . '/Router.php',
        'UserModel'         => MODELS_PATH . '/User.php',
        'ServiceModel'      => MODELS_PATH . '/Service.php',
        'BookingModel'      => MODELS_PATH . '/Booking.php',
        'PaymentModel'      => MODELS_PATH . '/Payment.php',
        'SettingModel'      => MODELS_PATH . '/Setting.php',
        'BlockedDateModel'  => MODELS_PATH . '/BlockedDate.php',
    ];

    if (isset($map[$class])) {
        require_once $map[$class];
    }
});

// ---------------------------------------------------------------------------
// Bootstrap: session, helpers
// ---------------------------------------------------------------------------
require_once SRC_PATH . '/helpers.php';

session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => COOKIE_SECURE,
    'httponly' => true,
    'samesite' => COOKIE_SAMESITE,
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
