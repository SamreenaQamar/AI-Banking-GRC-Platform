<?php
/**
 * AI Banking GRC Platform - Test Bootstrap
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage tests
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file bootstraps the test environment for PHPUnit.
 */

declare(strict_types=1);

// ============================================================
// SET ERROR REPORTING
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// ============================================================
// DEFINE PATHS
// ============================================================

define('ROOT_PATH', dirname(__DIR__));
define('TEST_PATH', __DIR__);
define('STORAGE_PATH', ROOT_PATH . '/storage');

// ============================================================
// LOAD AUTOLOADER
// ============================================================

// Load Composer autoloader if exists
$composerAutoloader = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoloader)) {
    require_once $composerAutoloader;
}

// Custom autoloader
spl_autoload_register(function ($className) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($className, $len);
    $filePath = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

// ============================================================
// LOAD CONFIGURATION
// ============================================================

// Set test environment
putenv('APP_ENV=testing');

// Load config files
$configPath = ROOT_PATH . '/config';
if (file_exists($configPath . '/config.php')) {
    require_once $configPath . '/config.php';
}
if (file_exists($configPath . '/database.php')) {
    require_once $configPath . '/database.php';
}

// ============================================================
// SET DEFAULT TIMEZONE
// ============================================================

date_default_timezone_set('Asia/Karachi');

// ============================================================
// TEST DATABASE SETUP
// ============================================================

/**
 * Create test database if it doesn't exist
 */
function createTestDatabase(): void
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_DATABASE') ?: 'grc_platform_test';

    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
        $pdo->exec("USE $database");
        
        echo "Test database '$database' ready.\n";
    } catch (PDOException $e) {
        echo "Warning: Could not create test database: " . $e->getMessage() . "\n";
    }
}

// Create test database
createTestDatabase();

// ============================================================
// REGISTER SHUTDOWN FUNCTION
// ============================================================

register_shutdown_function(function () {
    // Clean up any open resources
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
});

// ============================================================
// LOAD TEST HELPERS
// ============================================================

/**
 * Dump variable for debugging
 */
function dump($var): void
{
    echo "\n" . print_r($var, true) . "\n";
}

/**
 * Log test message
 */
function test_log(string $message): void
{
    $logFile = STORAGE_PATH . '/logs/test.log';
    file_put_contents(
        $logFile,
        "[" . date('Y-m-d H:i:s') . "] " . $message . "\n",
        FILE_APPEND
    );
}