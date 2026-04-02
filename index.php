<?php
/**
 * Marrakech Guide — Application Entry Point
 * All web requests are routed through this file (via .htaccess).
 */

require_once __DIR__ . '/config/config.php';

// Load all controllers
require_once ROOT_PATH . '/controllers/HomeController.php';
require_once ROOT_PATH . '/controllers/AuthController.php';
require_once ROOT_PATH . '/controllers/ServiceController.php';
require_once ROOT_PATH . '/controllers/BookingController.php';
require_once ROOT_PATH . '/controllers/PaymentController.php';
require_once ROOT_PATH . '/controllers/AdminController.php';

$router = new Router();

// ── PUBLIC ROUTES ──────────────────────────────────────────────
$router->get('/',                 [HomeController::class, 'index']);
$router->get('/services',         [ServiceController::class, 'index']);
$router->get('/services/:id',     [ServiceController::class, 'show']);
$router->get('/bookings',         [BookingController::class, 'index']);

// ── AUTH ROUTES ────────────────────────────────────────────────
$router->get('/login',            [AuthController::class, 'showLogin']);
$router->post('/login',           [AuthController::class, 'login']);
$router->get('/register',         [AuthController::class, 'showRegister']);
$router->post('/register',        [AuthController::class, 'register']);
$router->get('/logout',           [AuthController::class, 'logout']);

// ── ADMIN ROUTES ───────────────────────────────────────────────
$router->get('/admin',                            [AdminController::class, 'dashboard']);
$router->get('/admin/services',                   [AdminController::class, 'services']);
$router->get('/admin/services/add',               [AdminController::class, 'addServiceForm']);
$router->post('/admin/services/add',              [AdminController::class, 'createService']);
$router->get('/admin/services/:id/edit',          [AdminController::class, 'editServiceForm']);
$router->post('/admin/services/:id/update',       [AdminController::class, 'updateService']);
$router->post('/admin/services/:id/delete',       [AdminController::class, 'deleteService']);
$router->get('/admin/bookings',                   [AdminController::class, 'bookings']);
$router->post('/admin/bookings/:id/status',       [AdminController::class, 'updateBookingStatus']);
$router->post('/admin/bookings/:id/delete',       [AdminController::class, 'deleteBooking']);
$router->get('/admin/users',                      [AdminController::class, 'users']);
$router->get('/admin/settings',                   [AdminController::class, 'settings']);
$router->post('/admin/settings',                  [AdminController::class, 'saveSettings']);
$router->get('/admin/calendar',                   [AdminController::class, 'calendar']);
$router->post('/admin/calendar/block',            [AdminController::class, 'blockDate']);
$router->post('/admin/calendar/unblock',          [AdminController::class, 'unblockDate']);

// ── PAYPAL ROUTES ──────────────────────────────────────────────
$router->get('/paypal/success',   [PaymentController::class, 'success']);
$router->get('/paypal/cancel',    [PaymentController::class, 'cancel']);

$router->dispatch();
