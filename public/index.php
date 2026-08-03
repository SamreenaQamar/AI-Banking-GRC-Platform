<?php
/**
 * AI Banking GRC Platform - Main Front Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This is the main entry point for the application.
 * It handles all incoming requests and routes them to the appropriate controller.
 * 
 * Security Features:
 * - Secure session handling
 * - Global exception handling
 * - Error reporting based on environment
 * - CSRF protection ready
 * - XSS protection
 * - SQL injection prevention
 * - Security headers
 * - Maintenance mode support
 */

declare(strict_types=1);

// ============================================================
// APPLICATION ENVIRONMENT & ERROR REPORTING
// ============================================================

// Define application root path
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Set error reporting based on environment
if (getenv('APP_ENV') === 'development' || $_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    define('DEBUG_MODE', false);
}

// Set default timezone
date_default_timezone_set('Asia/Karachi');

// ============================================================
// SECURITY HEADERS (Sent before any output)
// ============================================================

// Prevent XSS attacks
header('X-XSS-Protection: 1; mode=block');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Prevent clickjacking
header('X-Frame-Options: DENY');

// Referrer policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy (CSP) - Basic
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self';");

// HSTS - Force HTTPS (only in production)
if (!DEBUG_MODE) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Permissions-Policy
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()');

// ============================================================
// SESSION CONFIGURATION
// ============================================================

// Set secure session parameters before starting session
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie parameters
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => !DEBUG_MODE && isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Set session name
    session_name('grc_session');
    
    // Start session
    session_start();
}

// ============================================================
// MAINTENANCE MODE CHECK
// ============================================================

// Check if maintenance mode is enabled
$maintenanceFile = ROOT_PATH . '/storage/maintenance.lock';
if (file_exists($maintenanceFile)) {
    $allowedIPs = ['127.0.0.1', '::1'];
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Allow specific IPs to bypass maintenance mode
    if (!in_array($clientIP, $allowedIPs)) {
        http_response_code(503);
        require_once __DIR__ . '/../app/Views/errors/maintenance.php';
        exit;
    }
}

// ============================================================
// COMPOSER AUTOLOADER (Future support)
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
 */
spl_autoload_register(function ($className) {
    // Project namespace
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    
    // Check if class uses App namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }
    
    // Get relative class name
    $relativeClass = substr($className, $len);
    $filePath = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
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
        "[EXCEPTION] %s in %s:%d\n%s\n",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    ));
    
    // Return JSON error for API requests
    $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
    
    if ($isApi) {
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
        require_once ROOT_PATH . '/app/Views/errors/500.php';
    } else {
        // Log error and show generic error page
        require_once ROOT_PATH . '/app/Views/errors/500.php';
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
        
        require_once ROOT_PATH . '/app/Views/errors/500.php';
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
// APPLICATION BOOTSTRAP
// ============================================================

// Load configuration files
$configPath = ROOT_PATH . '/config';
if (file_exists($configPath . '/config.php')) {
    require_once $configPath . '/config.php';
}

// Load constants
if (file_exists($configPath . '/constants.php')) {
    require_once $configPath . '/constants.php';
}

// Load database configuration
if (file_exists($configPath . '/database.php')) {
    require_once $configPath . '/database.php';
}

// Load routes
$router = null;
if (file_exists($configPath . '/routes.php')) {
    $router = require_once $configPath . '/routes.php';
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
    require_once ROOT_PATH . '/app/Views/errors/404.php';
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
        require_once ROOT_PATH . '/app/Views/errors/404.php';
    } else {
        throw $e;
    }
}

// ============================================================
// SESSION CLEANUP
// ============================================================

// Regenerate session ID periodically for security
if (isset($_SESSION['last_regenerated'])) {
    if (time() - $_SESSION['last_regenerated'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
    }
} else {
    $_SESSION['last_regenerated'] = time();
}