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
 * This is the main entry point for the application.
 * Features:
 * - Secure session handling
 * - Global exception handling
 * - Environment-based error reporting
 * - CSRF protection
 * - XSS protection
 * - SQL injection prevention
 * - Security headers
 * - Maintenance mode support
 * - Routing and dispatch
 */

declare(strict_types=1);

// ============================================================
// APPLICATION BOOTSTRAP
// ============================================================

// Define application root path
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Load configuration first
require_once ROOT_PATH . '/config/config.php';

// Set error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Set default timezone
date_default_timezone_set(APP_TIMEZONE);

// ============================================================
// SECURITY HEADERS (Sent before any output)
// ============================================================

if (SECURITY_HEADERS_ENABLED) {
    // Prevent XSS attacks
    header('X-XSS-Protection: ' . XSS_PROTECTION);
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: ' . CONTENT_TYPE_OPTIONS);
    
    // Prevent clickjacking
    header('X-Frame-Options: ' . XFRAME_OPTIONS);
    
    // Referrer policy
    header('Referrer-Policy: ' . REFERRER_POLICY);
    
    // Content Security Policy
    if (CSP_ENABLED) {
        header("Content-Security-Policy: default-src " . CSP_DEFAULT_SRC . "; script-src " . CSP_SCRIPT_SRC . "; style-src " . CSP_STYLE_SRC . "; font-src " . CSP_FONT_SRC . "; img-src " . CSP_IMG_SRC . "; connect-src " . CSP_CONNECT_SRC . ";");
    }
    
    // HSTS - Force HTTPS (only in production)
    if (HSTS_ENABLED) {
        header('Strict-Transport-Security: max-age=' . HSTS_MAX_AGE . '; includeSubDomains; preload');
    }
    
    // Permissions-Policy
    header('Permissions-Policy: ' . PERMISSIONS_POLICY);
}

// ============================================================
// SESSION CONFIGURATION
// ============================================================

// Set secure session parameters before starting session
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie parameters
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => SESSION_PATH,
        'domain' => SESSION_DOMAIN,
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTP_ONLY,
        'samesite' => SESSION_SAME_SITE
    ]);
    
    // Set session name
    session_name(SESSION_NAME);
    
    // Start session
    session_start();
}

// ============================================================
// MAINTENANCE MODE CHECK
// ============================================================

if (MAINTENANCE_MODE) {
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowedIPs = MAINTENANCE_ALLOWED_IPS;
    
    // Allow specific IPs to bypass maintenance mode
    if (!in_array($clientIP, $allowedIPs)) {
        $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
        
        if ($isApi) {
            http_response_code(503);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => MAINTENANCE_MESSAGE,
                'code' => 503
            ]);
            exit;
        }
        
        http_response_code(503);
        require_once VIEW_PATH . '/errors/maintenance.php';
        exit;
    }
}

// ============================================================
// COMPOSER AUTOLOADER
// ============================================================

$composerAutoloader = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoloader)) {
    require_once $composerAutoloader;
}

// ============================================================
// CUSTOM AUTOLOADER
// ============================================================

/**
 * Custom autoloader for PSR-4 compliance
 * Handles App namespace and module autoloading
 */
spl_autoload_register(function ($className) {
    // App namespace
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    
    if (strncmp($prefix, $className, strlen($prefix)) === 0) {
        $relativeClass = substr($className, strlen($prefix));
        $filePath = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }
    
    // Modules namespace
    $prefix = 'Modules\\';
    $baseDir = ROOT_PATH . '/modules/';
    
    if (strncmp($prefix, $className, strlen($prefix)) === 0) {
        $relativeClass = substr($className, strlen($prefix));
        $parts = explode('\\', $relativeClass, 2);
        if (count($parts) === 2) {
            $moduleName = strtolower($parts[0]);
            $classPath = str_replace('\\', '/', $parts[1]);
            $filePath = $baseDir . $moduleName . '/' . $classPath . '.php';
            if (file_exists($filePath)) {
                require_once $filePath;
                return;
            }
        }
    }
});

// ============================================================
// GLOBAL ERROR & EXCEPTION HANDLERS
// ============================================================

/**
 * Global exception handler
 */
set_exception_handler(function ($exception) {
    // Log the exception
    error_log(sprintf(
        "[EXCEPTION] %s in %s:%d\n%s",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    ));
    
    // Determine if API request
    $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isApi || $isAjax) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => DEBUG_MODE ? $exception->getMessage() : 'An unexpected error occurred.',
            'code' => $exception->getCode()
        ]);
        exit;
    }
    
    // Show error page for web requests
    if (DEBUG_MODE) {
        require_once VIEW_PATH . '/errors/500.php';
    } else {
        require_once VIEW_PATH . '/errors/500.php';
    }
    exit;
});

/**
 * Global error handler
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Check if error reporting is disabled
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Log the error
    error_log(sprintf(
        "[ERROR] %s in %s:%d\n",
        $errstr,
        $errfile,
        $errline
    ));
    
    // Throw ErrorException for all errors
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

/**
 * Shutdown function to catch fatal errors
 */
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log(sprintf(
            "[FATAL] %s in %s:%d\n",
            $error['message'],
            $error['file'],
            $error['line']
        ));
        
        // Check if API request
        $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
        if ($isApi) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Internal Server Error',
                'code' => 500
            ]);
            exit;
        }
        
        require_once VIEW_PATH . '/errors/500.php';
    }
});

// ============================================================
// REQUEST & INPUT SANITIZATION
// ============================================================

/**
 * Sanitize input data
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Sanitize all input data
$_GET = sanitizeInput($_GET);
$_POST = sanitizeInput($_POST);

// ============================================================
// LOAD CONFIGURATION
// ============================================================

// Load constants
require_once CONFIG_PATH . '/constants.php';

// Load database configuration
require_once CONFIG_PATH . '/database.php';

// Load routes
$router = null;
if (file_exists(CONFIG_PATH . '/routes.php')) {
    $router = require_once CONFIG_PATH . '/routes.php';
}

// ============================================================
// ROUTING
// ============================================================

// Get the current request URI and method
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string and base path
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($basePath !== '') {
    $requestUri = str_replace($basePath, '', $requestUri);
}
$requestUri = strtok($requestUri, '?') ?: '/';

// Handle 404 if router is not loaded
if (!$router) {
    http_response_code(404);
    require_once VIEW_PATH . '/errors/404.php';
    exit;
}

// ============================================================
// DISPATCH ROUTE
// ============================================================

try {
    // Dispatch the route
    $router->dispatch($requestUri, $requestMethod);
} catch (Exception $e) {
    // Log error
    error_log(sprintf(
        "[ROUTING ERROR] %s\nTrace: %s\n",
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    
    // Handle 404 for unknown routes
    if ($e->getCode() === 404) {
        http_response_code(404);
        require_once VIEW_PATH . '/errors/404.php';
    } else {
        throw $e;
    }
}

// ============================================================
// SESSION CLEANUP
// ============================================================

// Regenerate session ID periodically for security
if (isset($_SESSION['last_regenerated'])) {
    if (time() - $_SESSION['last_regenerated'] > SESSION_REGENERATE_INTERVAL) {
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
    }
} else {
    $_SESSION['last_regenerated'] = time();
}

// ============================================================
// END OF ENTRY POINT
// ============================================================