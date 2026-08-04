<?php
/**
 * AI Banking GRC Platform - Role Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles role-based access control:
 * - Role validation
 * - Multiple roles support
 * - Dynamic roles
 * - Redirect unauthorized users
 * - Role hierarchy
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Authentication;
use App\Libraries\Authorization;
use App\Libraries\Session;
use App\Libraries\Logger;
use App\Libraries\Response;

class RoleMiddleware
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
     * @var array Role hierarchy
     */
    private array $roleHierarchy = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Authentication();
        $this->authorization = new Authorization();
        $this->session = new Session();
        $this->logger = new Logger();

        $this->loadRoleHierarchy();
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
                $this->logger->warning('Role check failed - user not authenticated', [
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/'
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('Authentication required.', [], 401);
                }

                $this->session->set('intended_url', $_SERVER['REQUEST_URI'] ?? '/');
                return Response::redirect('/login');
            }

            $userId = $this->auth->id();
            $user = $this->auth->user();

            // Get required roles from params
            $requiredRoles = $params['roles'] ?? [];

            // If no roles specified, check route-based roles
            if (empty($requiredRoles)) {
                $uri = $_SERVER['REQUEST_URI'] ?? '/';
                $requiredRoles = $this->getRolesForRoute($uri);
            }

            // If still no roles, allow access
            if (empty($requiredRoles)) {
                return null;
            }

            // Convert to array if string
            if (is_string($requiredRoles)) {
                $requiredRoles = [$requiredRoles];
            }

            // Check if user has any of the required roles
            $hasRole = false;
            foreach ($requiredRoles as $role) {
                if ($this->authorization->userHasRole($userId, $role)) {
                    $hasRole = true;
                    break;
                }
            }

            // Check role hierarchy (higher roles can access lower roles)
            if (!$hasRole) {
                $userLevel = $this->authorization->getUserRoleLevel($userId);
                foreach ($requiredRoles as $role) {
                    $requiredLevel = $this->getRoleLevel($role);
                    if ($requiredLevel !== null && $userLevel >= $requiredLevel) {
                        $hasRole = true;
                        break;
                    }
                }
            }

            if (!$hasRole) {
                $this->logger->warning('Role access denied', [
                    'user_id' => $userId,
                    'username' => $user ? $user->username : 'unknown',
                    'required_roles' => $requiredRoles,
                    'user_role' => $this->auth->role(),
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('Insufficient role permissions.', [], 403);
                }

                $this->session->flash('error', 'You do not have the required role to access this resource.');
                return Response::redirect('/dashboard');
            }

            $this->logger->debug('Role check passed', [
                'user_id' => $userId,
                'username' => $user ? $user->username : 'unknown',
                'roles' => $requiredRoles
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('RoleMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($this->isAjaxRequest()) {
                return Response::error('Role verification error occurred.', [], 500);
            }

            $this->session->flash('error', 'An error occurred verifying role permissions.');
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
        $this->logger->debug('RoleMiddleware terminated');
    }

    /**
     * Load role hierarchy
     * 
     * @return void
     */
    private function loadRoleHierarchy(): void
    {
        $this->roleHierarchy = [
            'super_admin' => 10,
            'admin' => 8,
            'compliance_officer' => 6,
            'risk_manager' => 6,
            'internal_auditor' => 5,
            'department_head' => 4,
            'branch_manager' => 3,
            'user' => 2
        ];
    }

    /**
     * Get roles for route
     * 
     * @param string $uri
     * @return array
     */
    private function getRolesForRoute(string $uri): array
    {
        $routeRoles = [
            '/admin' => ['super_admin', 'admin'],
            '/users' => ['super_admin', 'admin'],
            '/users/create' => ['super_admin', 'admin'],
            '/users/delete' => ['super_admin', 'admin'],
            '/settings' => ['super_admin', 'admin'],
            '/settings/update' => ['super_admin', 'admin'],
            '/compliance/approve' => ['super_admin', 'admin', 'compliance_officer'],
            '/risk/approve' => ['super_admin', 'admin', 'risk_manager'],
            '/audit' => ['super_admin', 'admin', 'internal_auditor'],
            '/audit/create' => ['super_admin', 'admin', 'internal_auditor'],
            '/audit/execute' => ['super_admin', 'admin', 'internal_auditor'],
            '/reports/executive' => ['super_admin', 'admin']
        ];

        // Check exact match
        if (isset($routeRoles[$uri])) {
            return $routeRoles[$uri];
        }

        // Check pattern match
        foreach ($routeRoles as $pattern => $roles) {
            if (strpos($uri, $pattern) === 0) {
                return $roles;
            }
        }

        return [];
    }

    /**
     * Get role level
     * 
     * @param string $role
     * @return int|null
     */
    private function getRoleLevel(string $role): ?int
    {
        return $this->roleHierarchy[$role] ?? null;
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
     * Set role hierarchy
     * 
     * @param array $hierarchy
     * @return void
     */
    public function setRoleHierarchy(array $hierarchy): void
    {
        $this->roleHierarchy = $hierarchy;
    }

    /**
     * Add route roles
     * 
     * @param string $route
     * @param array $roles
     * @return void
     */
    public function addRouteRoles(string $route, array $roles): void
    {
        $this->routeRoles[$route] = $roles;
    }
}