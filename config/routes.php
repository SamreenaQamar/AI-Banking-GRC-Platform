<?php
/**
 * AI Banking GRC Platform - Route Configuration
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
 * - 404 error handling
 * - Route grouping for organization
 */

declare(strict_types=1);

// ============================================================
// ROUTER CLASS DEFINITION
// ============================================================

/**
 * Router class for handling HTTP requests
 * 
 * Implements:
 * - Route registration with HTTP methods
 * - Middleware pipeline
 * - Role-based authorization
 * - Named routes
 * - Route parameters
 * - Route grouping
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
            $uri = rtrim($this->currentGroup['prefix'], '/') . '/' . ltrim($uri, '/');
            $options['middleware'] = array_merge(
                $this->currentGroup['middleware'] ?? [],
                $options['middleware'] ?? []
            );
            $options['roles'] = array_merge(
                $this->currentGroup['roles'] ?? [],
                $options['roles'] ?? []
            );
        }
        
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
        
        $actions = [
            'index' => ['GET', '', 'index'],
            'create' => ['GET', '/create', 'create'],
            'store' => ['POST', '', 'store'],
            'show' => ['GET', '/{id}', 'show'],
            'edit' => ['GET', '/{id}/edit', 'edit'],
            'update' => ['PUT', '/{id}', 'update'],
            'destroy' => ['DELETE', '/{id}', 'destroy'],
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
        $uri = '/' . ltrim($uri, '/');
        
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
     * Match a route pattern against URI
     * 
     * @param string $pattern Route pattern
     * @param string $uri Request URI
     * @return bool
     */
    private function matchRoute(string $pattern, string $uri): bool
    {
        // Convert pattern to regex
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
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
                }
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
            $class = 'App\\Middleware\\' . $middleware;
            if (class_exists($class)) {
                $instance = new $class();
                if (method_exists($instance, 'handle')) {
                    $instance->handle();
                }
            }
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
        
        // Check if user has required role
        return in_array($userRole, $roles);
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
        
        // Default 404 response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found', 'status' => 404]);
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
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized access', 'status' => 401]);
            exit;
        }
        
        // Redirect to login
        header('Location: /login');
        exit;
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
    
    public function getName(): ?string
    {
        return $this->options['name'] ?? null;
    }
}

// ============================================================
// ROUTE DEFINITIONS
// ============================================================

// Initialize router
$router = new Router();

// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================

// Landing page
$router->get('/', 'HomeController@index', ['name' => 'home']);

// Authentication routes
$router->get('/login', 'AuthController@login', ['name' => 'login']);
$router->post('/login', 'AuthController@authenticate', ['name' => 'login.submit']);
$router->get('/logout', 'AuthController@logout', ['name' => 'logout']);
$router->get('/register', 'AuthController@register', ['name' => 'register']);
$router->post('/register', 'AuthController@store', ['name' => 'register.submit']);

// Password reset routes
$router->get('/password/forgot', 'AuthController@forgotPassword', ['name' => 'password.forgot']);
$router->post('/password/forgot', 'AuthController@sendResetLink', ['name' => 'password.email']);
$router->get('/password/reset/{token}', 'AuthController@resetPassword', ['name' => 'password.reset']);
$router->post('/password/reset', 'AuthController@updatePassword', ['name' => 'password.update']);

// Verification routes
$router->get('/verify/{token}', 'AuthController@verifyEmail', ['name' => 'verification.verify']);
$router->post('/verify/resend', 'AuthController@resendVerification', ['name' => 'verification.resend']);

// ============================================================
// AUTHENTICATED ROUTES GROUP
// ============================================================

$router->group(['middleware' => ['Auth'], 'roles' => ['user', 'admin', 'super_admin']], function($router) {
    
    // ============================================================
    // DASHBOARD
    // ============================================================
    $router->get('/dashboard', 'DashboardController@index', ['name' => 'dashboard']);
    $router->get('/dashboard/stats', 'DashboardController@stats', ['name' => 'dashboard.stats']);
    $router->get('/dashboard/charts', 'DashboardController@charts', ['name' => 'dashboard.charts']);
    
    // ============================================================
    // USER MANAGEMENT
    // ============================================================
    $router->group(['prefix' => 'users', 'roles' => ['admin', 'super_admin']], function($router) {
        $router->get('/', 'UserController@index', ['name' => 'users.index']);
        $router->get('/create', 'UserController@create', ['name' => 'users.create']);
        $router->post('/', 'UserController@store', ['name' => 'users.store']);
        $router->get('/{id}', 'UserController@show', ['name' => 'users.show']);
        $router->get('/{id}/edit', 'UserController@edit', ['name' => 'users.edit']);
        $router->put('/{id}', 'UserController@update', ['name' => 'users.update']);
        $router->delete('/{id}', 'UserController@delete', ['name' => 'users.delete']);
        $router->post('/{id}/roles', 'UserController@assignRoles', ['name' => 'users.roles']);
        $router->post('/{id}/status', 'UserController@updateStatus', ['name' => 'users.status']);
        $router->post('/{id}/password', 'UserController@resetPassword', ['name' => 'users.password']);
    });
    
    // Profile routes (accessible by all authenticated users)
    $router->group(['prefix' => 'profile'], function($router) {
        $router->get('/', 'ProfileController@index', ['name' => 'profile.index']);
        $router->put('/', 'ProfileController@update', ['name' => 'profile.update']);
        $router->post('/password', 'ProfileController@changePassword', ['name' => 'profile.password']);
        $router->post('/avatar', 'ProfileController@updateAvatar', ['name' => 'profile.avatar']);
        $router->post('/two-factor', 'ProfileController@twoFactor', ['name' => 'profile.twofactor']);
    });
    
    // ============================================================
    // COMPLIANCE MODULE
    // ============================================================
    $router->group(['prefix' => 'compliance'], function($router) {
        $router->get('/', 'ComplianceController@index', ['name' => 'compliance.index']);
        $router->get('/tasks', 'ComplianceController@tasks', ['name' => 'compliance.tasks']);
        $router->get('/create', 'ComplianceController@create', ['name' => 'compliance.create']);
        $router->post('/', 'ComplianceController@store', ['name' => 'compliance.store']);
        $router->get('/{id}', 'ComplianceController@show', ['name' => 'compliance.show']);
        $router->get('/{id}/edit', 'ComplianceController@edit', ['name' => 'compliance.edit']);
        $router->put('/{id}', 'ComplianceController@update', ['name' => 'compliance.update']);
        $router->delete('/{id}', 'ComplianceController@delete', ['name' => 'compliance.delete']);
        $router->post('/{id}/status', 'ComplianceController@updateStatus', ['name' => 'compliance.status']);
        $router->post('/{id}/evidence', 'ComplianceController@uploadEvidence', ['name' => 'compliance.evidence']);
        $router->get('/frameworks', 'ComplianceController@frameworks', ['name' => 'compliance.frameworks']);
        $router->get('/categories', 'ComplianceController@categories', ['name' => 'compliance.categories']);
        $router->get('/dashboard', 'ComplianceController@dashboard', ['name' => 'compliance.dashboard']);
        $router->get('/export', 'ComplianceController@export', ['name' => 'compliance.export']);
    });
    
    // ============================================================
    // RISK MANAGEMENT MODULE
    // ============================================================
    $router->group(['prefix' => 'risk'], function($router) {
        $router->get('/', 'RiskController@index', ['name' => 'risk.index']);
        $router->get('/register', 'RiskController@register', ['name' => 'risk.register']);
        $router->get('/create', 'RiskController@create', ['name' => 'risk.create']);
        $router->post('/', 'RiskController@store', ['name' => 'risk.store']);
        $router->get('/{id}', 'RiskController@show', ['name' => 'risk.show']);
        $router->get('/{id}/edit', 'RiskController@edit', ['name' => 'risk.edit']);
        $router->put('/{id}', 'RiskController@update', ['name' => 'risk.update']);
        $router->delete('/{id}', 'RiskController@delete', ['name' => 'risk.delete']);
        $router->post('/{id}/assess', 'RiskController@assess', ['name' => 'risk.assess']);
        $router->post('/{id}/mitigate', 'RiskController@mitigate', ['name' => 'risk.mitigate']);
        $router->get('/dashboard', 'RiskController@dashboard', ['name' => 'risk.dashboard']);
        $router->get('/heatmap', 'RiskController@heatmap', ['name' => 'risk.heatmap']);
        $router->get('/export', 'RiskController@export', ['name' => 'risk.export']);
    });
    
    // ============================================================
    // AUDIT MODULE
    // ============================================================
    $router->group(['prefix' => 'audit', 'roles' => ['internal_auditor', 'admin', 'super_admin']], function($router) {
        $router->get('/', 'AuditController@index', ['name' => 'audit.index']);
        $router->get('/plans', 'AuditController@plans', ['name' => 'audit.plans']);
        $router->get('/plans/create', 'AuditController@createPlan', ['name' => 'audit.plan.create']);
        $router->post('/plans', 'AuditController@storePlan', ['name' => 'audit.plan.store']);
        $router->get('/plans/{id}', 'AuditController@showPlan', ['name' => 'audit.plan.show']);
        $router->put('/plans/{id}', 'AuditController@updatePlan', ['name' => 'audit.plan.update']);
        $router->delete('/plans/{id}', 'AuditController@deletePlan', ['name' => 'audit.plan.delete']);
        $router->get('/findings', 'AuditController@findings', ['name' => 'audit.findings']);
        $router->get('/findings/create', 'AuditController@createFinding', ['name' => 'audit.finding.create']);
        $router->post('/findings', 'AuditController@storeFinding', ['name' => 'audit.finding.store']);
        $router->get('/findings/{id}', 'AuditController@showFinding', ['name' => 'audit.finding.show']);
        $router->put('/findings/{id}', 'AuditController@updateFinding', ['name' => 'audit.finding.update']);
        $router->delete('/findings/{id}', 'AuditController@deleteFinding', ['name' => 'audit.finding.delete']);
        $router->post('/findings/{id}/status', 'AuditController@updateFindingStatus', ['name' => 'audit.finding.status']);
        $router->get('/dashboard', 'AuditController@dashboard', ['name' => 'audit.dashboard']);
        $router->get('/schedule', 'AuditController@schedule', ['name' => 'audit.schedule']);
    });
    
    // ============================================================
    // POLICIES MODULE
    // ============================================================
    $router->group(['prefix' => 'policies'], function($router) {
        $router->get('/', 'PolicyController@index', ['name' => 'policies.index']);
        $router->get('/create', 'PolicyController@create', ['name' => 'policies.create']);
        $router->post('/', 'PolicyController@store', ['name' => 'policies.store']);
        $router->get('/{id}', 'PolicyController@show', ['name' => 'policies.show']);
        $router->get('/{id}/edit', 'PolicyController@edit', ['name' => 'policies.edit']);
        $router->put('/{id}', 'PolicyController@update', ['name' => 'policies.update']);
        $router->delete('/{id}', 'PolicyController@delete', ['name' => 'policies.delete']);
        $router->post('/{id}/approve', 'PolicyController@approve', ['name' => 'policies.approve']);
        $router->post('/{id}/acknowledge', 'PolicyController@acknowledge', ['name' => 'policies.acknowledge']);
        $router->get('/versions/{id}', 'PolicyController@versions', ['name' => 'policies.versions']);
        $router->get('/dashboard', 'PolicyController@dashboard', ['name' => 'policies.dashboard']);
    });
    
    // ============================================================
    // SBP CIRCULARS MODULE
    // ============================================================
    $router->group(['prefix' => 'sbp-circulars'], function($router) {
        $router->get('/', 'SBPController@index', ['name' => 'sbp.index']);
        $router->get('/create', 'SBPController@create', ['name' => 'sbp.create']);
        $router->post('/', 'SBPController@store', ['name' => 'sbp.store']);
        $router->get('/{id}', 'SBPController@show', ['name' => 'sbp.show']);
        $router->get('/{id}/edit', 'SBPController@edit', ['name' => 'sbp.edit']);
        $router->put('/{id}', 'SBPController@update', ['name' => 'sbp.update']);
        $router->delete('/{id}', 'SBPController@delete', ['name' => 'sbp.delete']);
        $router->post('/{id}/implement', 'SBPController@implement', ['name' => 'sbp.implement']);
        $router->get('/dashboard', 'SBPController@dashboard', ['name' => 'sbp.dashboard']);
        $router->get('/compliance', 'SBPController@compliance', ['name' => 'sbp.compliance']);
    });
    
    // ============================================================
    // AI COPILOT MODULE
    // ============================================================
    $router->group(['prefix' => 'ai'], function($router) {
        $router->get('/', 'AICopilotController@index', ['name' => 'ai.index']);
        $router->post('/chat', 'AICopilotController@chat', ['name' => 'ai.chat']);
        $router->post('/analyze', 'AICopilotController@analyze', ['name' => 'ai.analyze']);
        $router->post('/recommend', 'AICopilotController@recommend', ['name' => 'ai.recommend']);
        $router->post('/summarize', 'AICopilotController@summarize', ['name' => 'ai.summarize']);
        $router->get('/insights', 'AICopilotController@insights', ['name' => 'ai.insights']);
        $router->get('/dashboard', 'AICopilotController@dashboard', ['name' => 'ai.dashboard']);
        $router->post('/feedback', 'AICopilotController@feedback', ['name' => 'ai.feedback']);
    });
    
    // ============================================================
    // REPORTS MODULE
    // ============================================================
    $router->group(['prefix' => 'reports'], function($router) {
        $router->get('/', 'ReportController@index', ['name' => 'reports.index']);
        $router->get('/create', 'ReportController@create', ['name' => 'reports.create']);
        $router->post('/', 'ReportController@generate', ['name' => 'reports.generate']);
        $router->get('/{id}', 'ReportController@show', ['name' => 'reports.show']);
        $router->delete('/{id}', 'ReportController@delete', ['name' => 'reports.delete']);
        $router->get('/{id}/download', 'ReportController@download', ['name' => 'reports.download']);
        $router->post('/{id}/share', 'ReportController@share', ['name' => 'reports.share']);
        $router->get('/dashboard', 'ReportController@dashboard', ['name' => 'reports.dashboard']);
        $router->get('/templates', 'ReportController@templates', ['name' => 'reports.templates']);
    });
    
    // ============================================================
    // NOTIFICATIONS MODULE
    // ============================================================
    $router->group(['prefix' => 'notifications'], function($router) {
        $router->get('/', 'NotificationController@index', ['name' => 'notifications.index']);
        $router->get('/unread', 'NotificationController@unread', ['name' => 'notifications.unread']);
        $router->post('/{id}/read', 'NotificationController@markRead', ['name' => 'notifications.read']);
        $router->post('/read-all', 'NotificationController@markAllRead', ['name' => 'notifications.read-all']);
        $router->delete('/{id}', 'NotificationController@delete', ['name' => 'notifications.delete']);
    });
    
    // ============================================================
    // SETTINGS MODULE
    // ============================================================
    $router->group(['prefix' => 'settings', 'roles' => ['admin', 'super_admin']], function($router) {
        $router->get('/', 'SettingsController@index', ['name' => 'settings.index']);
        $router->post('/general', 'SettingsController@updateGeneral', ['name' => 'settings.general']);
        $router->post('/security', 'SettingsController@updateSecurity', ['name' => 'settings.security']);
        $router->post('/email', 'SettingsController@updateEmail', ['name' => 'settings.email']);
        $router->post('/ai', 'SettingsController@updateAI', ['name' => 'settings.ai']);
        $router->post('/appearance', 'SettingsController@updateAppearance', ['name' => 'settings.appearance']);
        $router->post('/backup', 'SettingsController@backup', ['name' => 'settings.backup']);
        $router->post('/restore', 'SettingsController@restore', ['name' => 'settings.restore']);
    });
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api', 'middleware' => ['ApiAuth']], function($router) {
    
    // API v1 routes
    $router->group(['prefix' => 'v1'], function($router) {
        
        // Dashboard API
        $router->get('/dashboard/stats', 'ApiController@dashboardStats', ['name' => 'api.dashboard.stats']);
        $router->get('/dashboard/charts', 'ApiController@dashboardCharts', ['name' => 'api.dashboard.charts']);
        $router->get('/dashboard/recent', 'ApiController@recentActivities', ['name' => 'api.dashboard.recent']);
        
        // Compliance API
        $router->get('/compliance', 'ApiController@complianceList', ['name' => 'api.compliance.list']);
        $router->get('/compliance/{id}', 'ApiController@complianceShow', ['name' => 'api.compliance.show']);
        $router->post('/compliance', 'ApiController@complianceStore', ['name' => 'api.compliance.store']);
        $router->put('/compliance/{id}', 'ApiController@complianceUpdate', ['name' => 'api.compliance.update']);
        $router->delete('/compliance/{id}', 'ApiController@complianceDelete', ['name' => 'api.compliance.delete']);
        
        // Risk API
        $router->get('/risk', 'ApiController@riskList', ['name' => 'api.risk.list']);
        $router->get('/risk/{id}', 'ApiController@riskShow', ['name' => 'api.risk.show']);
        $router->post('/risk', 'ApiController@riskStore', ['name' => 'api.risk.store']);
        $router->put('/risk/{id}', 'ApiController@riskUpdate', ['name' => 'api.risk.update']);
        $router->delete('/risk/{id}', 'ApiController@riskDelete', ['name' => 'api.risk.delete']);
        
        // Audit API
        $router->get('/audit', 'ApiController@auditList', ['name' => 'api.audit.list']);
        $router->get('/audit/{id}', 'ApiController@auditShow', ['name' => 'api.audit.show']);
        
        // Reports API
        $router->get('/reports', 'ApiController@reportList', ['name' => 'api.reports.list']);
        $router->post('/reports/generate', 'ApiController@reportGenerate', ['name' => 'api.reports.generate']);
        
        // Users API
        $router->get('/users', 'ApiController@userList', ['name' => 'api.users.list']);
        $router->get('/users/{id}', 'ApiController@userShow', ['name' => 'api.users.show']);
        
        // AI API
        $router->post('/ai/chat', 'ApiController@aiChat', ['name' => 'api.ai.chat']);
        $router->post('/ai/analyze', 'ApiController@aiAnalyze', ['name' => 'api.ai.analyze']);
        
        // System API
        $router->get('/system/health', 'ApiController@systemHealth', ['name' => 'api.system.health']);
        $router->get('/system/info', 'ApiController@systemInfo', ['name' => 'api.system.info']);
    });
});

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
// EXPORT ROUTER
// ============================================================

return $router;