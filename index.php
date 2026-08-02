<?php
/**
 * AI Banking GRC Platform - Enterprise Entry Point
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * Security Features:
 * - Session security with strict parameters
 * - XSS protection through output buffering
 * - SQL injection prevention via prepared statements
 * - CSRF token generation
 * - Secure cookie settings
 */

// ============================================================
// SECURITY HEADERS & CONFIGURATION
// ============================================================

// Prevent direct access to sensitive files
define('SECURE_ACCESS', true);

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/error.log');

// Set timezone to Pakistan Standard Time
date_default_timezone_set('Asia/Karachi');

// ============================================================
// SESSION SECURITY CONFIGURATION
// ============================================================

// Start secure session with strict parameters
session_set_cookie_params([
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Strict' // CSRF protection
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['last_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['last_regenerated'] = time();
} elseif (time() - $_SESSION['last_regenerated'] > 1800) { // Regenerate every 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regenerated'] = time();
}

// ============================================================
// AUTO-LOAD CONFIGURATION
// ============================================================

/**
 * Custom Autoloader for PSR-4 Compliance
 * Automatically loads classes from the app directory
 */
spl_autoload_register(function ($className) {
    // Define namespace prefix
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    // Check if class uses the App namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }

    // Get relative class name
    $relativeClass = substr($className, $len);
    $filePath = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Load the file if exists
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

// ============================================================
// ENVIRONMENT CONFIGURATION LOADER
// ============================================================

/**
 * Load Environment Variables
 * Supports both .env file and environment variables
 */
class EnvironmentLoader
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $this->loadEnvironmentFile();
        $this->loadServerEnvironment();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironmentFile()
    {
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    private function loadServerEnvironment()
    {
        $this->config = [
            'APP_ENV' => $_ENV['APP_ENV'] ?? 'production',
            'APP_NAME' => $_ENV['APP_NAME'] ?? 'AI Banking GRC Platform',
            'APP_URL' => $_ENV['APP_URL'] ?? 'http://localhost',
            'DB_HOST' => $_ENV['DB_HOST'] ?? 'localhost',
            'DB_NAME' => $_ENV['DB_NAME'] ?? 'grc_platform',
            'DB_USER' => $_ENV['DB_USER'] ?? 'root',
            'DB_PASS' => $_ENV['DB_PASS'] ?? '',
            'DB_PORT' => $_ENV['DB_PORT'] ?? '3306',
            'HASH_COST' => $_ENV['HASH_COST'] ?? 12,
            'CSRF_TOKEN_NAME' => $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token'
        ];
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}

// Initialize environment
$env = EnvironmentLoader::getInstance();

// ============================================================
// SESSION HELPER FUNCTIONS
// ============================================================

/**
 * Check if user is authenticated
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['authenticated']) && 
           $_SESSION['authenticated'] === true;
}

/**
 * Check if user has specific role
 */
function hasRole(string $role): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCSRFToken($token): bool
{
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

// ============================================================
// ROUTING & CONTROLLER DISPATCHER
// ============================================================

/**
 * Simple Router - Maps URLs to Controllers
 */
class Router
{
    private $routes = [];
    private $currentRoute = null;

    public function __construct()
    {
        $this->loadRoutes();
    }

    private function loadRoutes(): void
    {
        // Public routes (authentication required)
        $this->routes[''] = ['controller' => 'AuthController', 'method' => 'login', 'auth' => false];
        $this->routes['login'] = ['controller' => 'AuthController', 'method' => 'login', 'auth' => false];
        $this->routes['login-submit'] = ['controller' => 'AuthController', 'method' => 'authenticate', 'auth' => false];
        $this->routes['logout'] = ['controller' => 'AuthController', 'method' => 'logout', 'auth' => true];
        $this->routes['register'] = ['controller' => 'AuthController', 'method' => 'register', 'auth' => false];

        // Protected routes (authentication required)
        $this->routes['dashboard'] = ['controller' => 'DashboardController', 'method' => 'index', 'auth' => true];
        
        // User Management
        $this->routes['users'] = ['controller' => 'UserController', 'method' => 'index', 'auth' => true];
        $this->routes['users/create'] = ['controller' => 'UserController', 'method' => 'create', 'auth' => true];
        $this->routes['users/edit'] = ['controller' => 'UserController', 'method' => 'edit', 'auth' => true];
        $this->routes['users/delete'] = ['controller' => 'UserController', 'method' => 'delete', 'auth' => true];

        // Compliance Module
        $this->routes['compliance'] = ['controller' => 'ComplianceController', 'method' => 'index', 'auth' => true];
        $this->routes['compliance/tasks'] = ['controller' => 'ComplianceController', 'method' => 'tasks', 'auth' => true];
        $this->routes['compliance/create'] = ['controller' => 'ComplianceController', 'method' => 'create', 'auth' => true];
        $this->routes['compliance/edit'] = ['controller' => 'ComplianceController', 'method' => 'edit', 'auth' => true];

        // Risk Management
        $this->routes['risk'] = ['controller' => 'RiskController', 'method' => 'index', 'auth' => true];
        $this->routes['risk/register'] = ['controller' => 'RiskController', 'method' => 'register', 'auth' => true];
        $this->routes['risk/assessment'] = ['controller' => 'RiskController', 'method' => 'assessment', 'auth' => true];

        // Audit Module
        $this->routes['audit'] = ['controller' => 'AuditController', 'method' => 'index', 'auth' => true];
        $this->routes['audit/plans'] = ['controller' => 'AuditController', 'method' => 'plans', 'auth' => true];
        $this->routes['audit/findings'] = ['controller' => 'AuditController', 'method' => 'findings', 'auth' => true];

        // Policies Module
        $this->routes['policies'] = ['controller' => 'PolicyController', 'method' => 'index', 'auth' => true];
        $this->routes['policies/create'] = ['controller' => 'PolicyController', 'method' => 'create', 'auth' => true];
        
        // AI Copilot
        $this->routes['ai-copilot'] = ['controller' => 'AICopilotController', 'method' => 'index', 'auth' => true];
        
        // SBP Circulars
        $this->routes['sbp-circulars'] = ['controller' => 'SBPController', 'method' => 'index', 'auth' => true];

        // Reports
        $this->routes['reports'] = ['controller' => 'ReportController', 'method' => 'index', 'auth' => true];
        
        // Notifications
        $this->routes['notifications'] = ['controller' => 'NotificationController', 'method' => 'index', 'auth' => true];
        
        // Settings
        $this->routes['settings'] = ['controller' => 'SettingsController', 'method' => 'index', 'auth' => true];

        // API Routes
        $this->routes['api/users'] = ['controller' => 'ApiController', 'method' => 'getUsers', 'auth' => true];
        $this->routes['api/compliance'] = ['controller' => 'ApiController', 'method' => 'getCompliance', 'auth' => true];
    }

    public function resolve($uri): array
    {
        // Remove query string
        $uri = strtok($uri, '?');
        $uri = trim($uri, '/');

        // If empty URI, load default
        if (empty($uri)) {
            $uri = '';
        }

        // Check if route exists
        if (isset($this->routes[$uri])) {
            return $this->routes[$uri];
        }

        // 404 Not Found
        return ['controller' => 'ErrorController', 'method' => 'notFound', 'auth' => false];
    }
}

// ============================================================
// APPLICATION INITIALIZATION
// ============================================================

// Initialize Router
$router = new Router();

// Get current URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath !== '') {
    $requestUri = str_replace($basePath, '', $requestUri);
}

// Resolve route
$route = $router->resolve($requestUri);

// ============================================================
// AUTHENTICATION REDIRECTION
// ============================================================

$isAuthenticated = isAuthenticated();
$requiresAuth = $route['auth'] ?? false;

// If authenticated and on login page, redirect to dashboard
if ($isAuthenticated && ($route['controller'] === 'AuthController' && $route['method'] === 'login')) {
    header('Location: dashboard');
    exit;
}

// If not authenticated and route requires auth, redirect to login
if (!$isAuthenticated && $requiresAuth) {
    header('Location: login');
    exit;
}

// ============================================================
// CONTROLLER DISPATCHER
// ============================================================

try {
    $controllerName = 'App\\Controllers\\' . $route['controller'];
    $methodName = $route['method'];

    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $methodName)) {
            // If authentication required and user not authenticated, redirect
            if ($requiresAuth && !$isAuthenticated) {
                header('Location: login');
                exit;
            }
            $controller->$methodName();
        } else {
            throw new Exception("Method {$methodName} not found in {$controllerName}");
        }
    } else {
        throw new Exception("Controller {$controllerName} not found");
    }
} catch (Exception $e) {
    // Log error
    error_log("Router Error: " . $e->getMessage());
    
    // Display friendly error page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - <?php echo $env->get('APP_NAME'); ?></title>
        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body { 
                background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                background: #FFFFFF;
                border-radius: 20px;
                padding: 50px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 500px;
                text-align: center;
            }
            .error-icon {
                font-size: 80px;
                color: #DC2626;
                margin-bottom: 20px;
            }
            .error-title {
                color: #0F172A;
                font-weight: 700;
                margin-bottom: 15px;
            }
            .error-message {
                color: #6B7280;
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="error-title">Oops! Something went wrong</h1>
            <p class="error-message">
                We encountered an unexpected error. Please try again or contact support.
            </p>
            <a href="dashboard" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </body>
    </html>
    <?php
}