<?php
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Setting.php';

/**
 * AuthController — login, register, logout (web pages).
 */
class AuthController
{
    /**
     * GET /login
     */
    public function showLogin(array $params = []): void
    {
        if (Auth::check()) {
            redirect('/');
        }
        view('auth/login', [
            'pageTitle' => 'Login — Marrakech Guide',
            'settings'  => SettingModel::all(),
        ]);
    }

    /**
     * POST /login
     */
    public function login(array $params = []): void
    {
        CSRF::verify();

        $email    = post('email');
        $password = post('password');
        $next     = get('next', '/');

        if (!$email || !$password) {
            flash('error', 'Please enter your email and password.');
            redirect('/login');
        }

        $user = Auth::attempt($email, $password);

        if (!$user) {
            flash('error', 'Invalid email or password.');
            redirect('/login');
        }

        flash('success', 'Welcome back, ' . e($user['name']) . '!');

        // Redirect admin to dashboard, others to $next or home
        if ($user['role'] === 'admin') {
            redirect('/admin');
        }

        redirect(in_array($next, ['/', '/bookings', '/services']) ? $next : '/');
    }

    /**
     * GET /register
     */
    public function showRegister(array $params = []): void
    {
        if (Auth::check()) {
            redirect('/');
        }
        view('auth/register', [
            'pageTitle' => 'Create Account — Marrakech Guide',
            'settings'  => SettingModel::all(),
        ]);
    }

    /**
     * POST /register
     */
    public function register(array $params = []): void
    {
        CSRF::verify();

        $name     = post('name');
        $email    = post('email');
        $password = post('password');
        $confirm  = post('password_confirm');
        $phone    = post('phone');

        // Validation
        if (!$name || !$email || !$password) {
            flash('error', 'All fields are required.');
            redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('/register');
        }

        if (strlen($password) < 8) {
            flash('error', 'Password must be at least 8 characters.');
            redirect('/register');
        }

        if ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect('/register');
        }

        if (UserModel::emailExists($email)) {
            flash('error', 'An account with this email already exists.');
            redirect('/register');
        }

        $id = UserModel::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'phone'    => $phone,
        ]);

        $user = UserModel::find($id);
        Auth::login($user);

        flash('success', 'Welcome to Marrakech Guide, ' . e($name) . '!');
        redirect('/');
    }

    /**
     * GET /logout
     */
    public function logout(array $params = []): void
    {
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect('/login');
    }
}
