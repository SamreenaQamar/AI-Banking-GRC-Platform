<?php
/**
 * AI Banking GRC Platform - CSRF Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles CSRF protection:
 * - Generate CSRF tokens
 * - Validate CSRF tokens
 * - Token expiry validation
 * - Invalid request handling
 * - AJAX token support
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Security;
use App\Libraries\Session;
use App\Libraries\Logger;
use App\Libraries\Response;

class CSRFMiddleware
{
    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var array Excluded routes
     */
    private array $excludedRoutes = [];

    /**
     * @var array Excluded methods
     */
    private array $excludedMethods = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * @var int Token lifetime in seconds
     */
    private int $tokenLifetime = 3600;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->security = new Security();
        $this->session = new Session();
        $this->logger = new Logger();
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
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

            // Skip validation for excluded methods
            if (in_array($method, $this->excludedMethods)) {
                return null;
            }

            // Skip validation for excluded routes
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            foreach ($this->excludedRoutes as $route) {
                if (strpos($uri, $route) !== false) {
                    return null;
                }
            }

            // Get token from request
            $token = $this->getTokenFromRequest();

            // Validate token
            if (!$this->security->validateCsrfToken($token)) {
                $this->logger->warning('CSRF validation failed', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    'uri' => $uri,
                    'method' => $method
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('CSRF token validation failed.', [], 403);
                }

                $this->session->flash('error', 'Invalid security token. Please refresh the page and try again.');
                return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }

            // Regenerate token after successful validation (optional)
            // $this->security->generateCsrfToken();

            $this->logger->debug('CSRF validation passed', [
                'uri' => $uri,
                'method' => $method
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('CSRFMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($this->isAjaxRequest()) {
                return Response::error('CSRF validation error occurred.', [], 500);
            }

            $this->session->flash('error', 'An error occurred validating security token.');
            return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
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
        // Generate new token for next request if needed
        if (!isset($_SESSION['csrf_token'])) {
            $this->security->generateCsrfToken();
        }

        $this->logger->debug('CSRFMiddleware terminated');
    }

    /**
     * Get token from request
     * 
     * @return string|null
     */
    private function getTokenFromRequest(): ?string
    {
        // Check POST data
        if (isset($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // Check JSON payload
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true);
            if (isset($data['csrf_token'])) {
                return $data['csrf_token'];
            }
        }

        // Check headers
        $headers = getallheaders();
        if (isset($headers['X-CSRF-TOKEN'])) {
            return $headers['X-CSRF-TOKEN'];
        }

        // Check X-Requested-With for AJAX
        if (isset($headers['X-Requested-With']) && 
            strtolower($headers['X-Requested-With']) === 'xmlhttprequest') {
            return null;
        }

        return null;
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
     * Set excluded methods
     * 
     * @param array $methods
     * @return void
     */
    public function setExcludedMethods(array $methods): void
    {
        $this->excludedMethods = $methods;
    }

    /**
     * Set token lifetime
     * 
     * @param int $lifetime
     * @return void
     */
    public function setTokenLifetime(int $lifetime): void
    {
        $this->tokenLifetime = $lifetime;
    }
}