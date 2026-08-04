<?php
/**
 * AI Banking GRC Platform - Rate Limit Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles rate limiting:
 * - Login rate limit
 * - API rate limit
 * - IP blocking
 * - Request counter
 * - Time window management
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\RateLimiter;
use App\Libraries\Logger;
use App\Libraries\Response;
use App\Libraries\Session;

class RateLimitMiddleware
{
    /**
     * @var RateLimiter Rate limiter instance
     */
    private RateLimiter $rateLimiter;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var int Default limit
     */
    private int $defaultLimit = 60;

    /**
     * @var int Default window in seconds
     */
    private int $defaultWindow = 60;

    /**
     * @var array Route-specific limits
     */
    private array $routeLimits = [];

    /**
     * @var array Excluded routes
     */
    private array $excludedRoutes = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rateLimiter = new RateLimiter();
        $this->logger = new Logger();
        $this->session = new Session();

        $this->loadRouteLimits();
    }

    /**
     * Handle the request
     * 
     * @param array $params
     * @return mixed
     */
    public function handle(array $params = []): mixed
    {
        try {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

            // Check if route is excluded
            foreach ($this->excludedRoutes as $route) {
                if (strpos($uri, $route) !== false) {
                    return null;
                }
            }

            // Get rate limit for this route
            $limit = $this->getLimitForRoute($uri);
            $window = $this->getWindowForRoute($uri);

            // Create rate limit key
            $key = $this->getRateLimitKey($uri, $ip, $params);

            // Check if rate limited
            if ($this->rateLimiter->isLimited($key, $limit, $window)) {
                $remaining = $this->rateLimiter->remaining($key, $limit);
                $resetTime = $this->rateLimiter->resetTime($key);

                $this->logger->warning('Rate limit exceeded', [
                    'ip' => $ip,
                    'uri' => $uri,
                    'method' => $method,
                    'limit' => $limit,
                    'window' => $window,
                    'reset' => $resetTime
                ]);

                // Set rate limit headers
                header('X-RateLimit-Limit: ' . $limit);
                header('X-RateLimit-Remaining: ' . $remaining);
                header('X-RateLimit-Reset: ' . $resetTime);
                header('Retry-After: ' . max(0, $resetTime - time()));

                if ($this->isAjaxRequest()) {
                    return Response::error('Too many requests. Please try again later.', [], 429);
                }

                $this->session->flash('error', 'Too many requests. Please wait before trying again.');
                return Response::back();
            }

            // Increment rate limit counter
            $count = $this->rateLimiter->increment($key, $window);
            $remaining = max(0, $limit - $count);

            // Set rate limit headers
            header('X-RateLimit-Limit: ' . $limit);
            header('X-RateLimit-Remaining: ' . $remaining);
            header('X-RateLimit-Reset: ' . (time() + $window));

            return null;

        } catch (\Exception $e) {
            $this->logger->error('RateLimitMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Allow request on error (fail open)
            return null;
        }
    }

    /**
     * Terminate the request
     * 
     * @param mixed $response
     * @return void
     */
    public function terminate($response): void
    {
        $this->logger->debug('RateLimitMiddleware terminated');
    }

    /**
     * Load route limits
     * 
     * @return void
     */
    private function loadRouteLimits(): void
    {
        $this->routeLimits = [
            '/login' => ['limit' => 5, 'window' => 300],
            '/register' => ['limit' => 3, 'window' => 3600],
            '/password/forgot' => ['limit' => 3, 'window' => 3600],
            '/api' => ['limit' => 100, 'window' => 60],
            '/api/login' => ['limit' => 10, 'window' => 300],
            '/api/register' => ['limit' => 5, 'window' => 3600],
            '/otp' => ['limit' => 3, 'window' => 300]
        ];
    }

    /**
     * Get limit for route
     * 
     * @param string $uri
     * @return int
     */
    private function getLimitForRoute(string $uri): int
    {
        foreach ($this->routeLimits as $pattern => $config) {
            if (strpos($uri, $pattern) !== false) {
                return $config['limit'] ?? $this->defaultLimit;
            }
        }

        return $this->defaultLimit;
    }

    /**
     * Get window for route
     * 
     * @param string $uri
     * @return int
     */
    private function getWindowForRoute(string $uri): int
    {
        foreach ($this->routeLimits as $pattern => $config) {
            if (strpos($uri, $pattern) !== false) {
                return $config['window'] ?? $this->defaultWindow;
            }
        }

        return $this->defaultWindow;
    }

    /**
     * Get rate limit key
     * 
     * @param string $uri
     * @param string $ip
     * @param array $params
     * @return string
     */
    private function getRateLimitKey(string $uri, string $ip, array $params): string
    {
        // Use user ID if authenticated
        $userId = $this->session->get('user_id', 0);
        $userKey = $userId ? 'user_' . $userId : 'ip_' . md5($ip);

        // Use route specific key
        $routeKey = md5($uri);

        // Add method for API
        if (strpos($uri, '/api') !== false) {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $routeKey .= '_' . $method;
        }

        return 'rate_' . $userKey . '_' . $routeKey;
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Add route limit
     * 
     * @param string $route
     * @param int $limit
     * @param int $window
     * @return void
     */
    public function addRouteLimit(string $route, int $limit, int $window): void
    {
        $this->routeLimits[$route] = ['limit' => $limit, 'window' => $window];
    }

    /**
     * Add excluded route
     * 
     * @param string $route
     * @return void
     */
    public function addExcludedRoute(string $route): void
    {
        $this->excludedRoutes[] = $route;
    }

    /**
     * Set default limit
     * 
     * @param int $limit
     * @param int $window
     * @return void
     */
    public function setDefaultLimit(int $limit, int $window = 60): void
    {
        $this->defaultLimit = $limit;
        $this->defaultWindow = $window;
    }
}