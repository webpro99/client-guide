<?php
/**
 * API: /api/auth
 *
 * POST /api/auth/login     → login, returns user info
 * POST /api/auth/logout    → logout
 * POST /api/auth/register  → create account
 * GET  /api/auth/me        → current user info
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/models/User.php';

header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determine the sub-action
if (preg_match('#/api/auth/(login|logout|register|me)#', $uri, $m)) {
    $action = $m[1];
} else {
    jsonError('Unknown auth endpoint.', 404);
}

$httpMethod = method();

switch ($action) {

    // -------------------------------------------------------------------
    case 'login':
        if ($httpMethod !== 'POST') jsonError('Method not allowed.', 405);

        $data     = jsonBody();
        $email    = trim($data['email']    ?? '');
        $password = trim($data['password'] ?? '');

        if (!$email || !$password) {
            jsonError('Email and password are required.', 422);
        }

        $user = Auth::attempt($email, $password);
        if (!$user) {
            jsonError('Invalid email or password.', 401);
        }

        jsonSuccess([
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ], 'Login successful.');
        break;

    // -------------------------------------------------------------------
    case 'logout':
        Auth::logout();
        jsonSuccess([], 'Logged out.');
        break;

    // -------------------------------------------------------------------
    case 'register':
        if ($httpMethod !== 'POST') jsonError('Method not allowed.', 405);

        $data     = jsonBody();
        $name     = trim($data['name']             ?? '');
        $email    = trim($data['email']            ?? '');
        $password = trim($data['password']         ?? '');
        $confirm  = trim($data['password_confirm'] ?? '');
        $phone    = trim($data['phone']            ?? '');

        if (!$name || !$email || !$password) {
            jsonError('Name, email and password are required.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Invalid email address.', 422);
        }

        if (strlen($password) < 8) {
            jsonError('Password must be at least 8 characters.', 422);
        }

        if ($confirm && $password !== $confirm) {
            jsonError('Passwords do not match.', 422);
        }

        if (UserModel::emailExists($email)) {
            jsonError('This email address is already registered.', 409);
        }

        $id   = UserModel::create(['name' => $name, 'email' => $email, 'password' => $password, 'phone' => $phone]);
        $user = UserModel::find($id);
        Auth::login($user);

        jsonSuccess([
            'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']],
        ], 'Account created.', 201);
        break;

    // -------------------------------------------------------------------
    case 'me':
        if (!Auth::check()) {
            jsonError('Not authenticated.', 401);
        }
        $user = UserModel::find(Auth::id());
        jsonSuccess(['user' => $user]);
        break;
}

// Local override for 201 status support
function jsonSuccess(mixed $data = [], string $message = 'OK', int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true, 'message' => $message], (array)$data));
    exit;
}
