<?php
/**
 * AI Banking GRC Platform - Session Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles session management:
 * - Start session
 * - Regenerate session ID
 * - Destroy invalid session
 * - Session timeout
 * - Flash messages
 * - Session security
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Session;
use App\Libraries\Security;
use App\Libraries\Logger;
use App\Libraries\Response;

class SessionMiddleware
{
    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var int Session lifetime in seconds
     */
    private int $sessionLifetime = 3600;

    /**
     * @var int Regenerate interval in seconds
     */
    private int $regenerateInterval = 1800;

    /**
     * @var array Excluded routes
     */
    private array $excludedRoutes = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = new Session();
        $this->security = new Security();
        $this->logger = new Logger();

        // Load configuration
        $this->loadConfig();
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

            // Check if session is needed for this route
            if ($this->isRouteExcluded($uri)) {
                return null;
            }

            // Start session if not started
            if (!$this->session->isStarted()) {
                $this->session->start();
            }

            // Set session expiration
            $this->session->setExpiration($this->sessionLifetime);

            // Check session timeout
            if ($this->isSessionExpired()) {
                $this->logger->warning('Session expired', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    'uri' => $uri
                ]);

                $this->destroyInvalidSession();
                return Response::redirect('/login');
            }

            // Regenerate session ID periodically
            if ($this->shouldRegenerate()) {
                $this->session->regenerate();
                $this->session->set('regenerated_at', time());
                $this->logger->debug('Session ID regenerated');
            }

            // Update last activity
            $this->session->set('last_activity', time());

            // Store security info in session
            if (!$this->session->has('ip_address')) {
                $this->session->set('ip_address', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            }
            if (!$this->session->has('user_agent')) {
                $this->session->set('user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
            }

            // Handle flash messages
            if ($this->session->hasFlash()) {
                $_SESSION['_flash'] = $this->session->getFlash();
            }

            $this->logger->debug('Session initialized', [
                'session_id' => $this->session->getId(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('SessionMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Attempt to recover
            try {
                $this->session->regenerate();
                return null;
            } catch (\Exception $re) {
                $this->logger->critical('Session recovery failed: ' . $re->getMessage());
                return Response::error('Session error occurred.', [], 500);
            }
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
        // Save flash messages for next request
        if ($this->session->hasFlash()) {
            $flash = $this->session->getFlash();
            if (!empty($flash)) {
                $_SESSION['_flash'] = $flash;
            }
        }

        // Write session data
        if ($this->session->isStarted()) {
            session_write_close();
        }

        $this->logger->debug('SessionMiddleware terminated');
    }

    /**
     * Load configuration
     * 
     * @return void
     */
    private function loadConfig(): void
    {
        // Load from environment
        if (getenv('SESSION_LIFETIME')) {
            $this->sessionLifetime = (int)getenv('SESSION_LIFETIME');
        }

        if (getenv('SESSION_REGENERATE_INTERVAL')) {
            $this->regenerateInterval = (int)getenv('SESSION_REGENERATE_INTERVAL');
        }

        // Load from config if available
        if (defined('SESSION_LIFETIME')) {
            $this->sessionLifetime = SESSION_LIFETIME;
        }

        if (defined('SESSION_REGENERATE_INTERVAL')) {
            $this->regenerateInterval = SESSION_REGENERATE_INTERVAL;
        }
    }

    /**
     * Check if session is expired
     * 
     * @return bool
     */
    private function isSessionExpired(): bool
    {
        $lastActivity = $this->session->get('last_activity');

        if (!$lastActivity) {
            return false;
        }

        return (time() - $lastActivity) > $this->sessionLifetime;
    }

    /**
     * Check if session should be regenerated
     * 
     * @return bool
     */
    private function shouldRegenerate(): bool
    {
        // Don't regenerate if not authenticated (no user_id)
        if (!$this->session->has('user_id')) {
            return false;
        }

        $regeneratedAt = $this->session->get('regenerated_at', 0);

        return (time() - $regeneratedAt) > $this->regenerateInterval;
    }

    /**
     * Destroy invalid session
     * 
     * @return void
     */
    private function destroyInvalidSession(): void
    {
        // Clear session data
        $this->session->clear();

        // Destroy session
        $this->session->destroy();

        // Clear session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        $this->logger->info('Invalid session destroyed');
    }

    /**
     * Check if route is excluded
     * 
     * @param string $uri
     * @return bool
     */
    private function isRouteExcluded(string $uri): bool
    {
        foreach ($this->excludedRoutes as $route) {
            if (strpos($uri, $route) !== false) {
                return true;
            }
        }
        return false;
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
     * Set session lifetime
     * 
     * @param int $lifetime
     * @return void
     */
    public function setSessionLifetime(int $lifetime): void
    {
        $this->sessionLifetime = $lifetime;
    }

    /**
     * Set regenerate interval
     * 
     * @param int $interval
     * @return void
     */
    public function setRegenerateInterval(int $interval): void
    {
        $this->regenerateInterval = $interval;
    }

    /**
     * Get session status
     * 
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'started' => $this->session->isStarted(),
            'id' => $this->session->getId(),
            'lifetime' => $this->sessionLifetime,
            'last_activity' => $this->session->get('last_activity'),
            'user_id' => $this->session->get('user_id')
        ];
    }
}