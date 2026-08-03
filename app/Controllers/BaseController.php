<?php
/**
 * AI Banking GRC Platform - Base Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This base controller provides common functionality for all controllers:
 * - View rendering with layout support
 * - JSON responses for API endpoints
 * - Authentication and authorization checks
 * - Input validation and sanitization
 * - Flash message handling
 * - CSRF protection
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Validation;
use App\Helpers\CSRF;
use App\Models\User;
use Exception;

class BaseController
{
    /**
     * View data to be passed to templates
     * @var array
     */
    protected array $data = [];
    
    /**
     * Layout file name
     * @var string
     */
    protected string $layout = 'main';
    
    /**
     * Controller name for authorization
     * @var string
     */
    protected string $controllerName = '';
    
    /**
     * Current user instance
     * @var User|null
     */
    protected ?User $currentUser = null;
    
    /**
     * Constructor - Initialize common functionality
     */
    public function __construct()
    {
        $this->initSession();
        $this->loadCurrentUser();
        $this->setupViewData();
        $this->checkMaintenanceMode();
    }
    
    /**
     * Initialize session if not already started
     * 
     * @return void
     */
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Load current authenticated user
     * 
     * @return void
     */
    private function loadCurrentUser(): void
    {
        if (Auth::check()) {
            $this->currentUser = Auth::user();
            $this->data['current_user'] = $this->currentUser;
        }
    }
    
    /**
     * Setup common view data
     * 
     * @return void
     */
    private function setupViewData(): void
    {
        $this->data['app_name'] = APP_NAME;
        $this->data['app_version'] = APP_VERSION;
        $this->data['company_name'] = COMPANY_NAME;
        $this->data['base_url'] = BASE_URL;
        $this->data['assets_url'] = ASSETS_URL;
        $this->data['upload_url'] = UPLOADS_URL;
        $this->data['csrf_token'] = CSRF::getToken();
        $this->data['current_year'] = date('Y');
        $this->data['current_route'] = $_SERVER['REQUEST_URI'] ?? '/';
        $this->data['flash_messages'] = $this->getFlashMessages();
        $this->data['notifications'] = $this->getNotifications();
        $this->data['user_menu'] = $this->getUserMenu();
        $this->data['sidebar_menu'] = $this->getSidebarMenu();
    }
    
    /**
     * Render a view with layout
     * 
     * @param string $view View file name (without extension)
     * @param array $data Additional data to pass to view
     * @param bool $useLayout Whether to use layout
     * @return void
     */
    protected function render(string $view, array $data = [], bool $useLayout = true): void
    {
        // Merge view data
        $viewData = array_merge($this->data, $data);
        
        // Extract data for view
        extract($viewData);
        
        // Build view path
        $viewPath = VIEW_PATH . '/' . $this->getViewPath($view) . '.php';
        
        // Check if view exists
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$viewPath}");
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view
        require_once $viewPath;
        
        // Get view content
        $content = ob_get_clean();
        
        if ($useLayout) {
            // Render with layout
            $layoutPath = VIEW_PATH . '/layouts/' . $this->layout . '.php';
            
            if (!file_exists($layoutPath)) {
                throw new Exception("Layout not found: {$layoutPath}");
            }
            
            // Extract data for layout
            extract($viewData);
            
            // Set content for layout
            $this->data['content'] = $content;
            
            // Include layout
            require_once $layoutPath;
        } else {
            // Output raw content
            echo $content;
        }
    }
    
    /**
     * Render a view without layout
     * 
     * @param string $view View file name
     * @param array $data View data
     * @return void
     */
    protected function renderPartial(string $view, array $data = []): void
    {
        $this->render($view, $data, false);
    }
    
    /**
     * Get view path with subdirectory support
     * 
     * @param string $view
     * @return string
     */
    private function getViewPath(string $view): string
    {
        // If view already contains path separators
        if (strpos($view, '/') !== false) {
            return $view;
        }
        
        // Automatically determine view directory based on controller
        $controllerName = str_replace('Controller', '', $this->controllerName);
        $controllerName = strtolower($controllerName);
        
        // If view is in a subdirectory
        if ($controllerName !== '') {
            return $controllerName . '/' . $view;
        }
        
        return $view;
    }
    
    /**
     * Return JSON response
     * 
     * @param array $data Data to encode
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Return success JSON response
     * 
     * @param string $message Success message
     * @param array $data Additional data
     * @return void
     */
    protected function jsonSuccess(string $message, array $data = []): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Return error JSON response
     * 
     * @param string $message Error message
     * @param array $errors Validation errors
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function jsonError(string $message, array $errors = [], int $statusCode = 400): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $statusCode);
        } else {
            echo '<script>window.location.href="' . $url . '";</script>';
        }
        exit;
    }
    
    /**
     * Redirect to a named route
     * 
     * @param string $routeName Route name
     * @param array $params Route parameters
     * @return void
     */
    protected function redirectToRoute(string $routeName, array $params = []): void
    {
        $url = $this->generateUrl($routeName, $params);
        $this->redirect($url);
    }
    
    /**
     * Generate URL for a named route
     * 
     * @param string $routeName Route name
     * @param array $params Route parameters
     * @return string
     */
    protected function generateUrl(string $routeName, array $params = []): string
    {
        global $router;
        
        if (isset($router) && method_exists($router, 'url')) {
            return $router->url($routeName, $params) ?? BASE_URL . '/' . $routeName;
        }
        
        return BASE_URL . '/' . $routeName;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return Auth::check();
    }
    
    /**
     * Require authentication - redirect to login if not authenticated
     * 
     * @return void
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->setFlashMessage('error', 'Please login to access this page.');
            $this->redirectToRoute('login');
        }
    }
    
    /**
     * Require specific role - redirect if user doesn't have required role
     * 
     * @param string|array $roles Required role(s)
     * @return void
     */
    protected function requireRole($roles): void
    {
        $this->requireAuth();
        
        if (!Auth::hasRole($roles)) {
            $this->setFlashMessage('error', 'You do not have permission to access this page.');
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Require specific permission - redirect if user doesn't have permission
     * 
     * @param string $permission Required permission
     * @return void
     */
    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();
        
        if (!Auth::hasPermission($permission)) {
            $this->setFlashMessage('error', 'You do not have permission to perform this action.');
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token CSRF token
     * @return void
     */
    protected function validateCSRF(string $token): void
    {
        if (!CSRF::validate($token)) {
            $this->jsonError('Invalid CSRF token.', [], 403);
        }
    }
    
    /**
     * Validate request input
     * 
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array Validated data
     * @throws Exception
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = new Validation();
        $validated = $validator->validate($data, $rules);
        
        if (!$validator->passes()) {
            $this->jsonError('Validation failed.', $validator->errors(), 422);
        }
        
        return $validated;
    }
    
    /**
     * Get request input with sanitization
     * 
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed
     */
    protected function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return $this->sanitize($value);
    }
    
    /**
     * Get all request input with sanitization
     * 
     * @return array
     */
    protected function allInput(): array
    {
        $data = array_merge($_GET, $_POST);
        return array_map([$this, 'sanitize'], $data);
    }
    
    /**
     * Sanitize input value
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function sanitize($value)
    {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        
        return $value;
    }
    
    /**
     * Set a flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     * @return void
     */
    protected function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash_messages'][$type][] = $message;
    }
    
    /**
     * Get flash messages
     * 
     * @return array
     */
    private function getFlashMessages(): array
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    
    /**
     * Get user notifications
     * 
     * @return array
     */
    private function getNotifications(): array
    {
        if (!$this->isAuthenticated()) {
            return [];
        }
        
        // Load notifications from service
        // This will be implemented in NotificationService
        return [];
    }
    
    /**
     * Get user menu items
     * 
     * @return array
     */
    protected function getUserMenu(): array
    {
        return [
            'profile' => [
                'label' => 'My Profile',
                'url' => $this->generateUrl('profile.index'),
                'icon' => 'fa-user'
            ],
            'settings' => [
                'label' => 'Settings',
                'url' => $this->generateUrl('settings.index'),
                'icon' => 'fa-cog',
                'permission' => 'settings_view'
            ],
            'logout' => [
                'label' => 'Logout',
                'url' => $this->generateUrl('logout'),
                'icon' => 'fa-sign-out-alt'
            ]
        ];
    }
    
    /**
     * Get sidebar menu items
     * 
     * @return array
     */
    protected function getSidebarMenu(): array
    {
        $menu = [
            'dashboard' => [
                'label' => 'Dashboard',
                'url' => $this->generateUrl('dashboard'),
                'icon' => 'fa-chart-pie',
                'permission' => 'dashboard_view'
            ],
            'compliance' => [
                'label' => 'Compliance',
                'url' => $this->generateUrl('compliance.index'),
                'icon' => 'fa-check-circle',
                'permission' => 'compliance_view'
            ],
            'risk' => [
                'label' => 'Risk Management',
                'url' => $this->generateUrl('risk.index'),
                'icon' => 'fa-shield-alt',
                'permission' => 'risk_view'
            ],
            'audit' => [
                'label' => 'Audit',
                'url' => $this->generateUrl('audit.index'),
                'icon' => 'fa-clipboard-check',
                'permission' => 'audit_view'
            ],
            'policies' => [
                'label' => 'Policies',
                'url' => $this->generateUrl('policies.index'),
                'icon' => 'fa-file-contract',
                'permission' => 'policy_view'
            ],
            'sbp' => [
                'label' => 'SBP Circulars',
                'url' => $this->generateUrl('sbp.index'),
                'icon' => 'fa-newspaper',
                'permission' => 'sbp_view'
            ],
            'ai' => [
                'label' => 'AI Copilot',
                'url' => $this->generateUrl('ai.index'),
                'icon' => 'fa-robot',
                'permission' => 'ai_view'
            ],
            'reports' => [
                'label' => 'Reports',
                'url' => $this->generateUrl('reports.index'),
                'icon' => 'fa-file-alt',
                'permission' => 'report_view'
            ],
            'users' => [
                'label' => 'Users',
                'url' => $this->generateUrl('users.index'),
                'icon' => 'fa-users',
                'permission' => 'user_view'
            ],
            'settings' => [
                'label' => 'Settings',
                'url' => $this->generateUrl('settings.index'),
                'icon' => 'fa-cogs',
                'permission' => 'settings_view'
            ]
        ];
        
        // Filter menu items based on user permissions
        if ($this->isAuthenticated()) {
            foreach ($menu as $key => $item) {
                if (isset($item['permission']) && !Auth::hasPermission($item['permission'])) {
                    unset($menu[$key]);
                }
            }
        } else {
            // Only show public items
            $menu = array_filter($menu, function($item) {
                return !isset($item['permission']);
            });
        }
        
        return $menu;
    }
    
    /**
     * Check maintenance mode
     * 
     * @return void
     */
    private function checkMaintenanceMode(): void
    {
        if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE) {
            $allowedIPs = defined('MAINTENANCE_ALLOWED_IPS') ? MAINTENANCE_ALLOWED_IPS : [];
            $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
            
            if (!in_array($clientIP, $allowedIPs)) {
                // Show maintenance page
                $this->render('errors/maintenance', [], false);
                exit;
            }
        }
    }
    
    /**
     * Set layout
     * 
     * @param string $layout Layout name
     * @return void
     */
    protected function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
    
    /**
     * Set controller name
     * 
     * @param string $name Controller name
     * @return void
     */
    protected function setControllerName(string $name): void
    {
        $this->controllerName = $name;
    }
    
    /**
     * Get current user
     * 
     * @return User|null
     */
    protected function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }
}