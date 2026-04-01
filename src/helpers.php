<?php
/**
 * Global helper functions used across the application.
 */

// ---------------------------------------------------------------------------
// Output helpers
// ---------------------------------------------------------------------------

/**
 * Escape HTML (always use for user-supplied data in templates).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Send a JSON response and terminate.
 */
function jsonResponse(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send a JSON error response and terminate.
 */
function jsonError(string $message, int $status = 400, array $extra = []): never
{
    jsonResponse(array_merge(['success' => false, 'error' => $message], $extra), $status);
}

/**
 * Send a JSON success response and terminate.
 */
function jsonSuccess(mixed $data = [], string $message = 'OK'): never
{
    jsonResponse(array_merge(['success' => true, 'message' => $message], (array)$data));
}

// ---------------------------------------------------------------------------
// Routing helpers
// ---------------------------------------------------------------------------

/**
 * Redirect to a URL (relative paths are prepended with APP_URL).
 */
function redirect(string $url, int $code = 302): never
{
    if (!str_starts_with($url, 'http')) {
        $url = rtrim(APP_URL, '/') . '/' . ltrim($url, '/');
    }
    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * Generate an absolute URL from a path.
 */
function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

// ---------------------------------------------------------------------------
// Request helpers
// ---------------------------------------------------------------------------

/**
 * Get a sanitized POST value (or default).
 */
function post(string $key, mixed $default = ''): string
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Get a sanitized GET value (or default).
 */
function get(string $key, mixed $default = ''): string
{
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/**
 * Decode the JSON body from a request (for API endpoints).
 */
function jsonBody(): array
{
    static $body = null;
    if ($body === null) {
        $raw   = file_get_contents('php://input');
        $body  = json_decode($raw, true) ?? [];
    }
    return $body;
}

/**
 * Return true if the request is an AJAX (XHR / fetch) request.
 */
function isAjax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || str_contains($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'application/json')
        || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
}

/**
 * Return the current HTTP method (GET, POST, PUT, DELETE …).
 * Supports _method override for HTML forms.
 */
function method(): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method === 'POST' && isset($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    }
    return $method;
}

// ---------------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------------

/**
 * Format a value as USD currency.
 */
function money(float $value, string $currency = 'USD'): string
{
    return '$' . number_format($value, 2);
}

/**
 * Map a service category name to a Font Awesome icon class.
 */
function getCategoryIcon(string $category): string
{
    return match (strtolower($category)) {
        'cultural'      => 'fa-mosque',
        'adventure'     => 'fa-mountain',
        'food & culture'=> 'fa-utensils',
        'wellness'      => 'fa-spa',
        'nature'        => 'fa-leaf',
        'private'       => 'fa-crown',
        default         => 'fa-compass',
    };
}

/**
 * Generate a unique booking reference: MG-YYYYMMDD-XXXXX
 */
function generateBookingRef(): string
{
    $date   = date('Ymd');
    $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    return "MG-{$date}-{$random}";
}

/**
 * Decode a JSON column from the database (handles null / string / already-array).
 */
function decodeJson(mixed $value): array
{
    if (is_array($value)) {
        return $value;
    }
    if (empty($value)) {
        return [];
    }
    return json_decode($value, true) ?? [];
}

// ---------------------------------------------------------------------------
// Flash messages (one-time session messages)
// ---------------------------------------------------------------------------

/**
 * Store a flash message in the session.
 */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear all flash messages.
 */
function getFlashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

// ---------------------------------------------------------------------------
// View renderer
// ---------------------------------------------------------------------------

/**
 * Render a view file with given variables.
 *
 * @param string $view   Path relative to VIEWS_PATH (e.g. 'frontend/home')
 * @param array  $data   Variables to extract into the view scope
 */
function view(string $view, array $data = []): void
{
    $file = VIEWS_PATH . '/' . $view . '.php';
    if (!file_exists($file)) {
        http_response_code(500);
        die("View not found: {$view}");
    }
    extract($data, EXTR_SKIP);
    require $file;
}

/**
 * Render a partial (no layout).
 */
function partial(string $partial, array $data = []): void
{
    view($partial, $data);
}
