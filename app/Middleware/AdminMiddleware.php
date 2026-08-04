<?php
/**
 * AI Banking GRC Platform - Admin Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles admin authentication:
 * - Verify admin role
 * - Check admin permissions
 * - Redirect unauthorized users
 * - Log access attempts
 * - Session validation
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Authentication;
use App\Libraries\Authorization;
use App\Libraries\Session;
use App\Libraries\Logger;
use App\Libraries\Response;

class AdminMiddleware
{
    /**
     * @var Authentication Authentication instance
     */
    private Authentication $auth;

    /**
     * @var Authorization Authorization instance
     */
    private Authorization $authorization;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var array Allowed admin roles
     */
    private array $adminRoles = ['admin', 'super_admin'];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Authentication();
        $this->authorization = new Authorization();
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
            // Check if user is authenticated first
            if (!$this->auth->check()) {
                $this->logger->warning('Admin access denied - user not authenticated', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/'
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('Authentication required.', [], 401);
                }

                $this->session->set('intended_url', $_SERVER['REQUEST_URI'] ?? '/');
                return Response::redirect('/login');
            }

            $user = $this->auth->user();
            $userId = $this->auth->id();

            // Check if user has admin role
            if (!$this->authorization->isAdmin($userId)) {
                $this->logger->warning('Admin access denied - insufficient role', [
                    'user_id' => $userId,
                    'username' => $user ? $user->username : 'unknown',
                    'role' => $this->auth->role(),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('Admin access required.', [], 403);
                }

                $this->session->flash('error', 'You do not have admin access to this page.');
                return Response::redirect('/dashboard');
            }

            // Check for specific admin permissions if required
            if (!empty($params['permission'])) {
                if (!$this->authorization->hasPermission($params['permission'])) {
                    $this->logger->warning('Admin access denied - missing permission', [
                        'user_id' => $userId,
                        'permission' => $params['permission'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                    ]);

                    if ($this->isAjaxRequest()) {
                        return Response::error('Permission denied.', [], 403);
                    }

                    $this->session->flash('error', 'You do not have permission to perform this action.');
                    return Response::redirect('/dashboard');
                }
            }

            // Check for specific admin level if required
            if (!empty($params['level'])) {
                $userLevel = $this->authorization->getUserRoleLevel($userId);
                if ($userLevel < (int)$params['level']) {
                    $this->logger->warning('Admin access denied - insufficient level', [
                        'user_id' => $userId,
                        'required_level' => $params['level'],
                        'user_level' => $userLevel
                    ]);

                    if ($this->isAjaxRequest()) {
                        return Response::error('Insufficient admin level.', [], 403);
                    }

                    $this->session->flash('error', 'You do not have sufficient privileges.');
                    return Response::redirect('/dashboard');
                }
            }

            // Log successful admin access
            $this->logger->info('Admin access granted', [
                'user_id' => $userId,
                'username' => $user ? $user->username : 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? '/'
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('AdminMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($this->isAjaxRequest()) {
                return Response::error('Admin verification error occurred.', [], 500);
            }

            $this->session->flash('error', 'An error occurred verifying admin access.');
            return Response::redirect('/dashboard');
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
        $this->logger->debug('AdminMiddleware terminated');
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
     * Set admin roles
     * 
     * @param array $roles
     * @return void
     */
    public function setAdminRoles(array $roles): void
    {
        $this->adminRoles = $roles;
    }
}