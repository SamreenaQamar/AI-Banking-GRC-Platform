<?php
/**
 * AI Banking GRC Platform - Router Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise routing functionality:
 * - HTTP method routing (GET, POST, PUT, PATCH, DELETE)
 * - Middleware support
 * - Named routes
 * - Route parameters
 * - 404 handling
 * - Route groups
 * - Route caching
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Libraries\Response;
use App\Libraries\Logger;

class Router
{
    /**
     * @var array Registered routes
     */
    private array $routes = [];

    /**
     * @var array Named routes
     */
    private array $namedRoutes = [];

    /**
     * @var array Route groups
     */
    private array $groups = [];

    /**
     * @var array Current route group
     */
    private ?array $currentGroup = null;

    /**
     * @var array Middleware stack
     */
    private array $middleware = [];

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var string Not found handler
     */
    private $notFoundHandler = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Register GET route
     * 
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    public function get(string $uri, $handler, array $options = []): self
    {
        return $this->addRoute('GET', $uri, $handler, $options);
    }

    /**
     * Register POST route
     * 
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    public function post(string $uri, $handler, array $options = []): self
    {
        return $this->addRoute('POST', $uri, $handler, $options);
    }

    /**
     * Register PUT route
     * 
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    public function put(string $uri, $handler, array $options = []): self
    {
        return $this->addRoute('PUT', $uri, $handler, $options);
    }

    /**
     * Register PATCH route
     * 
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    public function patch(string $uri, $handler, array $options = []): self
    {
        return $this->addRoute('PATCH', $uri, $handler, $options);
    }

    /**
     * Register DELETE route
     * 
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    public function delete(string $uri, $handler, array $options = []): self
    {
        return $this->addRoute('DELETE', $uri, $handler, $options);
    }

    /**
     * Register route for all methods
     * 
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    public function any(string $uri, $handler, array $options = []): self
    {
        return $this->addRoute('*', $uri, $handler, $options);
    }

    /**
     * Add route to collection
     * 
     * @param string $method
     * @param string $uri
     * @param string|array $handler
     * @param array $options
     * @return Router
     */
    private function addRoute(string $method, string $uri, $handler, array $options = []): self
    {
        // Apply group prefix if in group
        if ($this->currentGroup) {
            $uri = rtrim($this->currentGroup['prefix'] ?? '', '/') . '/' . ltrim($uri, '/');
            $options['middleware'] = array_merge(
                $this->currentGroup['middleware'] ?? [],
                $options['middleware'] ?? []
            );
        }

        // Normalize URI
        $uri = $this->normalizeUri($uri);

        // Create route
        $route = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
            'options' => $options
        ];

        // Store route
        $this->routes[$method][$uri] = $route;

        // Store named route
        if (isset($options['name'])) {
            $this->namedRoutes[$options['name']] = $route;
        }

        return $this;
    }

    /**
     * Create route group
     * 
     * @param array $attributes
     * @param callable $callback
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
     * Add middleware to route
     * 
     * @param string|array $middleware
     * @return Router
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
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
     * Dispatch route
     * 
     * @param string $uri
     * @param string $method
     * @return mixed
     */
    public function dispatch(string $uri, string $method)
    {
        $uri = $this->normalizeUri($uri);
        $method = strtoupper($method);

        try {
            // Check for exact match
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

            // Handle 404
            return $this->handleNotFound();

        } catch (\Exception $e) {
            $this->logger->error('Route dispatch error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Match route pattern against URI
     * 
     * @param string $pattern
     * @param string $uri
     * @return bool
     */
    private function matchRoute(string $pattern, string $uri): bool
    {
        // Convert pattern to regex
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            $_GET = array_merge($_GET, array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            return true;
        }

        return false;
    }

    /**
     * Execute route
     * 
     * @param array $route
     * @return mixed
     */
    private function executeRoute(array $route)
    {
        // Execute middleware
        $middleware = $route['options']['middleware'] ?? [];
        foreach ($middleware as $m) {
            $this->executeMiddleware($m);
        }

        $handler = $route['handler'];

        // If handler is callable, execute directly
        if (is_callable($handler)) {
            return $handler($_GET);
        }

        // If handler is string with @, instantiate controller
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler);
            $controllerClass = 'App\\Controllers\\' . $controller;

            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                if (method_exists($instance, $method)) {
                    return $instance->$method($_GET);
                }
                throw new \RuntimeException("Method {$method} not found in {$controller}");
            }
            throw new \RuntimeException("Controller {$controller} not found");
        }

        throw new \RuntimeException('Invalid route handler');
    }

    /**
     * Execute middleware
     * 
     * @param string|callable $middleware
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
                    return;
                }
            }
            throw new \RuntimeException("Middleware {$middleware} not found");
        }
    }

    /**
     * Handle 404
     * 
     * @return mixed
     */
    private function handleNotFound()
    {
        if ($this->notFoundHandler) {
            if (is_callable($this->notFoundHandler)) {
                return $this->notFoundHandler();
            }
            return $this->executeRoute(['handler' => $this->notFoundHandler, 'options' => []]);
        }

        Response::error('Route not found', [], 404);
    }

    /**
     * Set 404 handler
     * 
     * @param string|callable $handler
     * @return void
     */
    public function setNotFoundHandler($handler): void
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Generate URL for named route
     * 
     * @param string $name
     * @param array $params
     * @return string|null
     */
    public function url(string $name, array $params = []): ?string
    {
        if (!isset($this->namedRoutes[$name])) {
            return null;
        }

        $uri = $this->namedRoutes[$name]['uri'];
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }

        return BASE_URL . $uri;
    }

    /**
     * Get all routes
     * 
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get named routes
     * 
     * @return array
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * Get route by name
     * 
     * @param string $name
     * @return array|null
     */
    public function getRouteByName(string $name): ?array
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Generate API route prefix
     * 
     * @param string $version
     * @return string
     */
    public function apiPrefix(string $version = 'v1'): string
    {
        return '/api/' . $version;
    }

    /**
     * Restrict routes to authenticated users
     * 
     * @return Router
     */
    public function auth(): self
    {
        return $this->middleware('Auth');
    }

    /**
     * Restrict routes to admin users
     * 
     * @return Router
     */
    public function admin(): self
    {
        return $this->middleware(['Auth', 'Admin']);
    }

    /**
     * Restrict routes to API requests
     * 
     * @return Router
     */
    public function api(): self
    {
        return $this->middleware('Api');
    }
}