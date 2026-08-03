<?php
/**
 * AI Banking GRC Platform - Enterprise Route Configuration
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file defines all application routes with:
 * - MVC compatible routing
 * - Named routes for URL generation
 * - Role-based access control
 * - Middleware support
 * - RESTful resource routing
 * - Module-based route grouping
 * - 404 error handling
 * - API versioning
 */

declare(strict_types=1);

// ============================================================
// ROUTER CLASS DEFINITION (Enterprise)
// ============================================================

/**
 * Router class for handling HTTP requests
 * 
 * Implements:
 * - Route registration with HTTP methods
 * - Middleware pipeline
 * - Role-based authorization
 * - Named routes
 * - Route parameters with validation
 * - Route grouping
 * - Resource routing
 * - API versioning
 */
class Router
{
    /**
     * Registered routes
     * @var array
     */
    private array $routes = [];
    
    /**
     * Named routes
     * @var array
     */
    private array $namedRoutes = [];
    
    /**
     * Route groups
     * @var array
     */
    private array $groups = [];
    
    /**
     * Current route group
     * @var array|null
     */
    private ?array $currentGroup = null;
    
    /**
     * Middleware stack
     * @var array
     */
    private array $middleware = [];
    
    /**
     * Route parameters
     * @var array
     */
    private array $params = [];
    
    /**
     * Route parameter patterns
     * @var array
     */
    private array $patterns = [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9-]+',
        'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
        'token' => '[a-f0-9]{32,64}',
        'any' => '[^/]+'
    ];
    
    /**
     * Register a GET route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options (name, middleware, roles)
     * @return Route
     */
    public function get(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('GET', $uri, $handler, $options);
    }
    
    /**
     * Register a POST route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function post(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('POST', $uri, $handler, $options);
    }
    
    /**
     * Register a PUT route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function put(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('PUT', $uri, $handler, $options);
    }
    
    /**
     * Register a DELETE route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function delete(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('DELETE', $uri, $handler, $options);
    }
    
    /**
     * Register a PATCH route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function patch(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('PATCH', $uri, $handler, $options);
    }
    
    /**
     * Register an OPTIONS route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function options(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('OPTIONS', $uri, $handler, $options);
    }
    
    /**
     * Register a HEAD route
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function head(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('HEAD', $uri, $handler, $options);
    }
    
    /**
     * Register routes for all HTTP methods
     * 
     * @param string $uri Route URI
     * @param string|array $handler Controller@method or callable
     * @param array $options Route options
     * @return Route
     */
    public function any(string $uri, $handler, array $options = []): Route
    {
        return $this->addRoute('*', $uri, $handler, $options);
    }
    
    /**
     * Add a route to the collection
     * 
     * @param string $method HTTP method
     * @param string $uri Route URI
     * @param string|array $handler Handler
     * @param array $options Route options
     * @return Route
     */
    private function addRoute(string $method, string $uri, $handler, array $options = []): Route
    {
        // Apply group prefix if in group
        if ($this->currentGroup) {
            $uri = rtrim($this->currentGroup['prefix'] ?? '', '/') . '/' . ltrim($uri, '/');
            $options['middleware'] = array_merge(
                $this->currentGroup['middleware'] ?? [],
                $options['middleware'] ?? []
            );
            $options['roles'] = array_merge(
                $this->currentGroup['roles'] ?? [],
                $options['roles'] ?? []
            );
            $options['permissions'] = array_merge(
                $this->currentGroup['permissions'] ?? [],
                $options['permissions'] ?? []
            );
        }
        
        // Normalize URI
        $uri = $this->normalizeUri($uri);
        
        // Create route object
        $route = new Route($method, $uri, $handler, $options);
        
        // Store route
        $this->routes[$method][$uri] = $route;
        
        // Store named route
        if (isset($options['name'])) {
            $this->namedRoutes[$options['name']] = $route;
        }
        
        return $route;
    }
    
    /**
     * Normalize URI
     * 
     * @param string $uri
     * @return string
     */
    private function normalizeUri(string $uri): string
    {
        // Ensure leading slash
        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }
        
        // Remove trailing slash except for root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }
        
        return $uri;
    }
    
    /**
     * Add a route parameter pattern
     * 
     * @param string $name Parameter name
     * @param string $pattern Regex pattern
     * @return Router
     */
    public function pattern(string $name, string $pattern): Router
    {
        $this->patterns[$name] = $pattern;
        return $this;
    }
    
    /**
     * Create a route group
     * 
     * @param array $attributes Group attributes (prefix, middleware, roles)
     * @param callable $callback Group routes
     * @return void
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousGroup = $this->currentGroup;
        $this->currentGroup = $attributes;
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
    }
    
    /**
     * Register RESTful resource routes
     * 
     * @param string $resource Resource name
     * @param string $controller Controller class
     * @param array $options Additional options
     * @return void
     */
    public function resource(string $resource, string $controller, array $options = []): void
    {
        $baseUri = $resource;
        $name = $options['name'] ?? $resource;
        $only = $options['only'] ?? ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        $except = $options['except'] ?? [];
        
        // Remove except from only
        if (!empty($except)) {
            $only = array_diff($only, $except);
        }
        
        $actions = [
            'index' => ['GET', '', 'index'],
            'create' => ['GET', '/create', 'create'],
            'store' => ['POST', '', 'store'],
            'show' => ['GET', '/{id}', 'show'],
            'edit' => ['GET', '/{id}/edit', 'edit'],
            'update' => ['PUT', '/{id}', 'update'],
            'destroy' => ['DELETE', '/{id}', 'destroy']
        ];
        
        foreach ($actions as $action => [$method, $uri, $handlerMethod]) {
            if (in_array($action, $only)) {
                $routeName = $name . '.' . $action;
                $this->addRoute(
                    $method,
                    $baseUri . $uri,
                    $controller . '@' . $handlerMethod,
                    ['name' => $routeName]
                );
            }
        }
    }
    
    /**
     * Register API resource routes
     * 
     * @param string $resource Resource name
     * @param string $controller Controller class
     * @param array $options Additional options
     * @return void
     */
    public function apiResource(string $resource, string $controller, array $options = []): void
    {
        $baseUri = $resource;
        $name = $options['name'] ?? $resource;
        $only = $options['only'] ?? ['index', 'store', 'show', 'update', 'destroy'];
        $except = $options['except'] ?? [];
        
        // Remove except from only
        if (!empty($except)) {
            $only = array_diff($only, $except);
        }
        
        $actions = [
            'index' => ['GET', '', 'index'],
            'store' => ['POST', '', 'store'],
            'show' => ['GET', '/{id}', 'show'],
            'update' => ['PUT', '/{id}', 'update'],
            'destroy' => ['DELETE', '/{id}', 'destroy']
        ];
        
        foreach ($actions as $action => [$method, $uri, $handlerMethod]) {
            if (in_array($action, $only)) {
                $routeName = $name . '.' . $action;
                $this->addRoute(
                    $method,
                    $baseUri . $uri,
                    $controller . '@' . $handlerMethod,
                    ['name' => $routeName]
                );
            }
        }
    }
    
    /**
     * Dispatch the current request
     * 
     * @param string $uri Request URI
     * @param string $method HTTP method
     * @return mixed
     */
    public function dispatch(string $uri, string $method)
    {
        // Normalize URI
        $uri = $this->normalizeUri($uri);
        
        // Check for OPTIONS request (CORS preflight)
        if ($method === 'OPTIONS') {
            return $this->handleOptions($uri);
        }
        
        // Check for exact match first
        if (isset($this->routes[$method][$uri])) {
            return $this->executeRoute($this->routes[$method][$uri]);
        }
        
        // Check for wildcard routes
        foreach ($this->routes[$method] ?? [] as $routeUri => $route) {
            if ($this->matchRoute($routeUri, $uri)) {
                return $this->executeRoute($route);
            }
        }
        
        // Check for any method routes
        foreach ($this->routes['*'] ?? [] as $routeUri => $route) {
            if ($this->matchRoute($routeUri, $uri)) {
                return $this->executeRoute($route);
            }
        }
        
        // No route found - 404
        return $this->handleNotFound();
    }
    
    /**
     * Handle OPTIONS request (CORS)
     * 
     * @param string $uri
     * @return void
     */
    private function handleOptions(string $uri): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        header('Access-Control-Max-Age: 86400');
        http_response_code(204);
        exit;
    }
    
    /**
     * Match a route pattern against URI
     * 
     * @param string $pattern Route pattern
     * @param string $uri Request URI
     * @return bool
     */
    private function matchRoute(string $pattern, string $uri): bool
    {
        // Convert pattern to regex with parameter patterns
        $regex = preg_replace_callback('/\{([a-zA-Z0-9_]+)(?::([^}]+))?\}/', function($matches) {
            $paramName = $matches[1];
            $pattern = isset($matches[2]) ? $matches[2] : ($this->patterns[$paramName] ?? '[^/]+');
            return '(?P<' . $paramName . '>' . $pattern . ')';
        }, $pattern);
        
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            // Extract parameters
            $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        
        return false;
    }
    
    /**
     * Execute a route
     * 
     * @param Route $route Route object
     * @return mixed
     */
    private function executeRoute(Route $route)
    {
        // Check middleware
        $middleware = $route->getMiddleware();
        foreach ($middleware as $m) {
            $this->executeMiddleware($m);
        }
        
        // Check roles
        $roles = $route->getRoles();
        if (!empty($roles) && !$this->checkRoles($roles)) {
            return $this->handleUnauthorized();
        }
        
        // Check permissions
        $permissions = $route->getPermissions();
        if (!empty($permissions) && !$this->checkPermissions($permissions)) {
            return $this->handleForbidden();
        }
        
        // Execute route handler
        $handler = $route->getHandler();
        
        // If callable, execute directly
        if (is_callable($handler)) {
            return $handler($this->params);
        }
        
        // If string with @, instantiate controller
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerName, $method] = explode('@', $handler);
            $controllerName = 'App\\Controllers\\' . $controllerName;
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $method)) {
                    return $controller->$method($this->params);
                } else {
                    throw new RuntimeException("Method {$method} not found in {$controllerName}");
                }
            } else {
                throw new RuntimeException("Controller {$controllerName} not found");
            }
        }
        
        // Handler not found
        throw new RuntimeException('Invalid route handler');
    }
    
    /**
     * Execute middleware
     * 
     * @param string|callable $middleware Middleware class or callable
     * @return void
     */
    private function executeMiddleware($middleware): void
    {
        if (is_callable($middleware)) {
            $middleware();
            return;
        }
        
        if (is_string($middleware)) {
            // Check if middleware is in App namespace
            $class = 'App\\Middleware\\' . $middleware;
            if (!class_exists($class)) {
                // Check modules middleware
                $class = 'Modules\\' . ucfirst($middleware) . '\\Middleware\\' . $middleware . 'Middleware';
            }
            
            if (class_exists($class)) {
                $instance = new $class();
                if (method_exists($instance, 'handle')) {
                    $instance->handle();
                    return;
                }
            }
            
            throw new RuntimeException("Middleware {$middleware} not found");
        }
    }
    
    /**
     * Check if user has required roles
     * 
     * @param array $roles Required roles
     * @return bool
     */
    private function checkRoles(array $roles): bool
    {
        // If no roles required, allow access
        if (empty($roles)) {
            return true;
        }
        
        // Get current user role from session
        $userRole = $_SESSION['user_role'] ?? null;
        $userLevel = $_SESSION['role_level'] ?? 0;
        
        // Super admin has all access
        if ($userRole === 'super_admin') {
            return true;
        }
        
        // Check if user has required role
        if (in_array($userRole, $roles)) {
            return true;
        }
        
        // Check role hierarchy (higher level roles can access lower level)
        foreach ($roles as $role) {
            $requiredLevel = ROLE_HIERARCHY[$role] ?? 0;
            if ($userLevel >= $requiredLevel) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user has required permissions
     * 
     * @param array $permissions Required permissions
     * @return bool
     */
    private function checkPermissions(array $permissions): bool
    {
        // If no permissions required, allow access
        if (empty($permissions)) {
            return true;
        }
        
        // Get current user permissions from session
        $userPermissions = $_SESSION['user_permissions'] ?? [];
        
        // Super admin has all permissions
        if (in_array('*', $userPermissions)) {
            return true;
        }
        
        // Check if user has required permission
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle 404 Not Found
     * 
     * @return mixed
     */
    private function handleNotFound()
    {
        http_response_code(404);
        
        // Check if custom 404 handler exists
        if (isset($this->routes['*']['/404'])) {
            return $this->executeRoute($this->routes['*']['/404']);
        }
        
        // Check if it's an API request
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Route not found',
                'code' => 404
            ]);
            exit;
        }
        
        // Render 404 view
        require_once VIEW_PATH . '/errors/404.php';
        exit;
    }
    
    /**
     * Handle 401 Unauthorized
     * 
     * @return mixed
     */
    private function handleUnauthorized()
    {
        http_response_code(401);
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized',
                'code' => 401
            ]);
            exit;
        }
        
        // Store intended URL
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login
        header('Location: /login');
        exit;
    }
    
    /**
     * Handle 403 Forbidden
     * 
     * @return mixed
     */
    private function handleForbidden()
    {
        http_response_code(403);
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Forbidden',
                'code' => 403
            ]);
            exit;
        }
        
        // Render 403 view
        require_once VIEW_PATH . '/errors/403.php';
        exit;
    }
    
    /**
     * Check if request is API request
     * 
     * @return bool
     */
    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/') === 0 || 
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    /**
     * Get route by name
     * 
     * @param string $name Route name
     * @return Route|null
     */
    public function getRouteByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }
    
    /**
     * Generate URL by route name
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string|null
     */
    public function url(string $name, array $params = []): ?string
    {
        $route = $this->getRouteByName($name);
        if (!$route) {
            return null;
        }
        
        $url = $route->getUri();
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return BASE_URL . $url;
    }
    
    /**
     * Get all registered routes
     * 
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}

// ============================================================
// ROUTE CLASS
// ============================================================

class Route
{
    private string $method;
    private string $uri;
    private $handler;
    private array $options;
    
    public function __construct(string $method, string $uri, $handler, array $options = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->handler = $handler;
        $this->options = $options;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getUri(): string
    {
        return $this->uri;
    }
    
    public function getHandler()
    {
        return $this->handler;
    }
    
    public function getMiddleware(): array
    {
        return $this->options['middleware'] ?? [];
    }
    
    public function getRoles(): array
    {
        return $this->options['roles'] ?? [];
    }
    
    public function getPermissions(): array
    {
        return $this->options['permissions'] ?? [];
    }
    
    public function getName(): ?string
    {
        return $this->options['name'] ?? null;
    }
    
    public function where(string $param, string $pattern): Route
    {
        $this->uri = str_replace('{' . $param . '}', '{' . $param . ':' . $pattern . '}', $this->uri);
        return $this;
    }
}

// ============================================================
// ROUTE DEFINITIONS
// ============================================================

// Initialize router
$router = new Router();

// Load module routes
$modulesPath = BASE_PATH . '/modules';
$moduleDirs = [
    'authentication', 'dashboard', 'users', 'compliance', 'risk', 
    'audit', 'policies', 'ai', 'reports', 'notifications', 'settings'
];

foreach ($moduleDirs as $module) {
    $routeFile = $modulesPath . '/' . $module . '/routes.php';
    if (file_exists($routeFile)) {
        require_once $routeFile;
    }
}

// ============================================================
// ADDITIONAL ROUTES (Fallback)
// ============================================================

// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================

// Landing page
$router->get('/', 'HomeController@index', ['name' => 'home']);

// Authentication routes (handled by authentication module)
// These are fallbacks if module routes aren't loaded

// ============================================================
// ERROR ROUTES
// ============================================================

// 404 Not Found handler
$router->any('/404', 'ErrorController@notFound', ['name' => 'error.404']);

// 403 Forbidden handler
$router->any('/403', 'ErrorController@forbidden', ['name' => 'error.403']);

// 500 Internal Server Error handler
$router->any('/500', 'ErrorController@serverError', ['name' => 'error.500']);

// ============================================================
// HEALTH CHECK ROUTE (No authentication)
// ============================================================

$router->get('/health', function() {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => APP_VERSION,
        'environment' => APP_ENV
    ]);
    exit;
});

// ============================================================
// EXPORT ROUTER
// ============================================================

return $router;