<?php
/**
 * AI Banking GRC Platform - Permission Middleware
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Middleware
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This middleware handles permission-based access control:
 * - Permission checking
 * - Module access
 * - Route permission
 * - RBAC validation
 * - Dynamic permission checking
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Libraries\Authentication;
use App\Libraries\Authorization;
use App\Libraries\Session;
use App\Libraries\Logger;
use App\Libraries\Response;

class PermissionMiddleware
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
     * @var array Permission mapping for routes
     */
    private array $routePermissions = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Authentication();
        $this->authorization = new Authorization();
        $this->session = new Session();
        $this->logger = new Logger();

        $this->loadRoutePermissions();
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
                $this->logger->warning('Permission check failed - user not authenticated', [
                    'uri' => $_SERVER['REQUEST_URI'] ?? '/'
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('Authentication required.', [], 401);
                }

                $this->session->set('intended_url', $_SERVER['REQUEST_URI'] ?? '/');
                return Response::redirect('/login');
            }

            $userId = $this->auth->id();
            $uri = $_SERVER['REQUEST_URI'] ?? '/';

            // Get required permission
            $permission = $params['permission'] ?? $this->getPermissionForRoute($uri);

            if (!$permission) {
                // No permission required for this route
                return null;
            }

            // Check if user has permission
            if (!$this->authorization->userHasPermission($userId, $permission)) {
                $this->logger->warning('Permission denied', [
                    'user_id' => $userId,
                    'permission' => $permission,
                    'uri' => $uri,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
                ]);

                if ($this->isAjaxRequest()) {
                    return Response::error('Permission denied.', [], 403);
                }

                $this->session->flash('error', 'You do not have permission to access this resource.');
                return Response::redirect('/dashboard');
            }

            // Check module access if specified
            if (!empty($params['module'])) {
                if (!$this->authorization->userHasModuleAccess($userId, $params['module'])) {
                    $this->logger->warning('Module access denied', [
                        'user_id' => $userId,
                        'module' => $params['module'],
                        'uri' => $uri
                    ]);

                    if ($this->isAjaxRequest()) {
                        return Response::error('Module access denied.', [], 403);
                    }

                    $this->session->flash('error', 'You do not have access to this module.');
                    return Response::redirect('/dashboard');
                }
            }

            // Check feature access if specified
            if (!empty($params['feature']) && !empty($params['module'])) {
                if (!$this->authorization->userHasFeatureAccess($userId, $params['feature'], $params['module'])) {
                    $this->logger->warning('Feature access denied', [
                        'user_id' => $userId,
                        'feature' => $params['feature'],
                        'module' => $params['module'],
                        'uri' => $uri
                    ]);

                    if ($this->isAjaxRequest()) {
                        return Response::error('Feature access denied.', [], 403);
                    }

                    $this->session->flash('error', 'You do not have access to this feature.');
                    return Response::redirect('/dashboard');
                }
            }

            $this->logger->debug('Permission check passed', [
                'user_id' => $userId,
                'permission' => $permission,
                'uri' => $uri
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('PermissionMiddleware error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($this->isAjaxRequest()) {
                return Response::error('Permission check error occurred.', [], 500);
            }

            $this->session->flash('error', 'An error occurred checking permissions.');
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
        $this->logger->debug('PermissionMiddleware terminated');
    }

    /**
     * Load route permissions mapping
     * 
     * @return void
     */
    private function loadRoutePermissions(): void
    {
        $this->routePermissions = [
            // User Management
            '/users' => 'user_view',
            '/users/create' => 'user_create',
            '/users/edit' => 'user_update',
            '/users/delete' => 'user_delete',
            '/users/roles' => 'user_role_assign',
            
            // Compliance
            '/compliance' => 'compliance_view',
            '/compliance/create' => 'compliance_create',
            '/compliance/edit' => 'compliance_update',
            '/compliance/delete' => 'compliance_delete',
            '/compliance/approve' => 'compliance_approve',
            
            // Risk Management
            '/risk' => 'risk_view',
            '/risk/create' => 'risk_create',
            '/risk/edit' => 'risk_update',
            '/risk/delete' => 'risk_delete',
            '/risk/assess' => 'risk_assess',
            
            // Audit
            '/audit' => 'audit_view',
            '/audit/create' => 'audit_create',
            '/audit/edit' => 'audit_update',
            '/audit/delete' => 'audit_delete',
            '/audit/execute' => 'audit_execute',
            
            // Policies
            '/policies' => 'policy_view',
            '/policies/create' => 'policy_create',
            '/policies/edit' => 'policy_update',
            '/policies/delete' => 'policy_delete',
            '/policies/approve' => 'policy_approve',
            
            // SBP Circulars
            '/sbp' => 'sbp_view',
            '/sbp/create' => 'sbp_create',
            '/sbp/edit' => 'sbp_update',
            '/sbp/delete' => 'sbp_delete',
            '/sbp/implement' => 'sbp_implement',
            
            // Reports
            '/reports' => 'report_view',
            '/reports/create' => 'report_create',
            '/reports/export' => 'report_export',
            '/reports/schedule' => 'report_schedule',
            
            // AI
            '/ai/chat' => 'ai_chat',
            '/ai/policy' => 'ai_policy_generate',
            '/ai/risk' => 'ai_risk_analyze',
            '/ai/gap' => 'ai_gap_analyze',
            
            // Settings
            '/settings' => 'settings_view',
            '/settings/update' => 'settings_update',
            '/settings/backup' => 'settings_backup',
            '/settings/restore' => 'settings_restore'
        ];
    }

    /**
     * Get permission for route
     * 
     * @param string $uri
     * @return string|null
     */
    private function getPermissionForRoute(string $uri): ?string
    {
        // Check exact match
        if (isset($this->routePermissions[$uri])) {
            return $this->routePermissions[$uri];
        }

        // Check pattern match
        foreach ($this->routePermissions as $pattern => $permission) {
            if (strpos($uri, $pattern) === 0) {
                return $permission;
            }
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
     * Add route permission mapping
     * 
     * @param string $route
     * @param string $permission
     * @return void
     */
    public function addRoutePermission(string $route, string $permission): void
    {
        $this->routePermissions[$route] = $permission;
    }

    /**
     * Set route permissions
     * 
     * @param array $permissions
     * @return void
     */
    public function setRoutePermissions(array $permissions): void
    {
        $this->routePermissions = array_merge($this->routePermissions, $permissions);
    }
}