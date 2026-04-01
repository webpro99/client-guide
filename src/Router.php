<?php
/**
 * Router — lightweight HTTP router.
 *
 * Usage:
 *   $router = new Router();
 *   $router->get('/',            [HomeController::class, 'index']);
 *   $router->post('/login',      [AuthController::class, 'login']);
 *   $router->get('/services/:id',[ServiceController::class,'show']);
 *   $router->dispatch();
 */
class Router
{
    /** @var array  [ method => [ pattern => callable ] ] */
    private array $routes = [];

    /** Current URI prefix (for grouping). */
    private string $prefix = '';

    // ------------------------------------------------------------------
    // Route registration
    // ------------------------------------------------------------------

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /** Register the same handler for GET and POST. */
    public function any(string $path, callable|array $handler): void
    {
        $this->addRoute('GET',    $path, $handler);
        $this->addRoute('POST',   $path, $handler);
        $this->addRoute('PUT',    $path, $handler);
        $this->addRoute('DELETE', $path, $handler);
    }

    /** Group routes under a common prefix. */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix  = $this->prefix;
        $this->prefix    = $previousPrefix . $prefix;
        $callback($this);
        $this->prefix    = $previousPrefix;
    }

    // ------------------------------------------------------------------
    // Dispatch
    // ------------------------------------------------------------------

    public function dispatch(): void
    {
        $httpMethod = method();                            // GET / POST / ...
        $uri        = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip the base path (e.g. /client-guide) from the URI
        $basePath = parse_url(APP_URL, PHP_URL_PATH) ?? '';
        if ($basePath !== '/' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . ltrim($uri, '/');
        $uri = ($uri === '') ? '/' : rtrim($uri, '/') ?: '/';

        foreach ($this->routes[$httpMethod] ?? [] as $pattern => $handler) {
            $params = $this->match($pattern, $uri);
            if ($params !== false) {
                $this->call($handler, $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if (isAjax()) {
            jsonError('Route not found.', 404);
        }
        view('errors/404');
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $fullPath = $this->prefix . $path;
        $this->routes[$method][$fullPath] = $handler;
    }

    /**
     * Match a URI against a route pattern.
     * Patterns use :param placeholders (e.g. /services/:id).
     *
     * Returns an associative array of captured params, or false on no match.
     */
    private function match(string $pattern, string $uri): array|false
    {
        // Exact match shortcut
        if ($pattern === $uri) {
            return [];
        }

        // Build regex from pattern
        $regex = preg_replace('/\/:([a-z_]+)/', '/(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#i';

        if (preg_match($regex, $uri, $matches)) {
            // Return only named captures
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Call a handler with captured route params.
     *
     * @param callable|array $handler  Closure or [ClassName, 'method']
     * @param array          $params   Named route parameters
     */
    private function call(callable|array $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }

        [$class, $methodName] = $handler;
        $controller = new $class();
        $controller->$methodName($params);
    }
}
