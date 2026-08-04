<?php
/**
 * AI Banking GRC Platform - Authentication Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles authentication verification:
 * - Check user login status
 * - Session validation
 * - Remember me functionality
 * - Redirect guests to login
 * - Authentication logging
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Authentication;
use App\Libraries\Session;
use App\Libraries\Logger;
use App\Libraries\Response;

class AuthMiddleware
{
    /**
     * @var Authentication Authentication instance
     */
    private Authentication $auth;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var bool Whether to allow AJAX requests without redirect
     */
    private bool $allowAjax = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Authentication();
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
            // Check if user is authenticated
            if (!$this->auth->check()) {
                // Log authentication failure
                $this->logger->warning('Authentication failed - user not logged in', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/'
                ]);

                // Check if request is AJAX
                if ($this->isAjaxRequest()) {
                    return Response::error('Authentication required. Please login.', [], 401);
                }

                // Store intended URL for redirect after login
                $this->session->set('intended_url', $_SERVER['REQUEST_URI'] ?? '/');

                // Redirect to login page
                return Response::redirect('/login');
            }

            // Verify session integrity
            if (!$this->validateSession()) {
                $this->logger->warning('Invalid session detected', [
                    'user_id' => $this->auth->id(),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                $this->auth->logout();

                if ($this->isAjaxRequest()) {
                    return Response::error('Session expired. Please login again.', [], 401);
                }

                $this->session->flash('error', 'Your session has expired. Please login again.');
                return Response::redirect('/login');
            }

            // Get current user for logging
            $user = $this->auth->user();

            // Log successful authentication
            if ($user) {
                $this->logger->debug('Authentication passed', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/'
                ]);
            }

            // Continue to next middleware/controller
            return null;

        } catch (\Exception $e) {
            $this->logger->error('AuthMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($this->isAjaxRequest()) {
                return Response::error('Authentication error occurred.', [], 500);
            }

            $this->session->flash('error', 'An authentication error occurred. Please try again.');
            return Response::redirect('/login');
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
        // Log any termination events if needed
        $this->logger->debug('AuthMiddleware terminated');
    }

    /**
     * Validate session integrity
     * 
     * @return bool
     */
    private function validateSession(): bool
    {
        // Check if session has user ID
        if (!$this->session->has('user_id')) {
            return false;
        }

        // Verify user exists in database
        $userId = $this->session->get('user_id');
        $user = $this->auth->user();

        if (!$user || $user->id != $userId) {
            return false;
        }

        // Check session IP match (optional security)
        $sessionIp = $this->session->get('ip_address');
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($sessionIp && $sessionIp !== $currentIp) {
            $this->logger->warning('Session IP mismatch', [
                'session_ip' => $sessionIp,
                'current_ip' => $currentIp,
                'user_id' => $userId
            ]);
            // Don't reject, just log - can be configured to reject
        }

        // Check session user agent match (optional security)
        $sessionUserAgent = $this->session->get('user_agent');
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($sessionUserAgent && $sessionUserAgent !== $currentUserAgent) {
            $this->logger->warning('Session user agent mismatch', [
                'session_agent' => $sessionUserAgent,
                'current_agent' => $currentUserAgent,
                'user_id' => $userId
            ]);
            // Don't reject, just log - can be configured to reject
        }

        return true;
    }

    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        if (!$this->allowAjax) {
            return false;
        }

        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Set whether to allow AJAX requests
     * 
     * @param bool $allow
     * @return void
     */
    public function setAllowAjax(bool $allow): void
    {
        $this->allowAjax = $allow;
    }
}