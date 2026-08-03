<?php
/**
 * AI Banking GRC Platform - Enterprise Configuration
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This is the master configuration file for the enterprise GRC platform.
 * All settings are centralized here with environment-based overrides.
 * 
 * Security Features:
 * - Environment detection
 * - Secure session configuration
 * - Security headers
 * - Rate limiting
 * - CSRF protection
 * - Input validation defaults
 */

declare(strict_types=1);

// ============================================================
// APPLICATION METADATA
// ============================================================

define('APP_NAME', 'AI Banking Governance, Risk & Compliance (GRC) Platform');
define('APP_SHORT_NAME', 'AI Banking GRC Platform');
define('APP_VERSION', '1.0.0');
define('APP_BUILD', '2026.08.02.001');
define('APP_RELEASE_DATE', '2026-08-02');

define('COMPANY_NAME', 'AI Banking GRC Solutions (Pvt) Ltd.');
define('COMPANY_ABBREVIATION', 'GRCS');
define('COMPANY_WEBSITE', 'https://www.grc-platform.com');
define('COMPANY_EMAIL', 'support@grc-platform.com');
define('COMPANY_PHONE', '+92-21-1234567');

// ============================================================
// ENVIRONMENT DETECTION (Enhanced)
// ============================================================

/**
 * Multi-level environment detection with fallback
 */
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$envFile = __DIR__ . '/../.env';

// Load .env file if exists
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Environment detection order: ENV var > .env > host detection
$env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? null;

if (!$env) {
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '::1') !== false) {
        $env = 'development';
    } elseif (strpos($host, 'staging.') !== false || strpos($host, 'test.') !== false || strpos($host, 'dev.') !== false) {
        $env = 'staging';
    } else {
        $env = 'production';
    }
}

define('APP_ENV', $env);

// ============================================================
// BASE URL CONFIGURATION (Enhanced)
// ============================================================

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(dirname($scriptName), '/\\');
$basePath = $basePath === '/' || $basePath === '\\' ? '' : $basePath;

// Allow custom base URL from environment
$customBaseUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?? null;

if ($customBaseUrl) {
    define('BASE_URL', rtrim($customBaseUrl, '/'));
} else {
    define('BASE_URL', $protocol . $host . $basePath);
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('STORAGE_URL', BASE_URL . '/storage');
define('API_URL', BASE_URL . '/api/v1');

// ============================================================
// ERROR REPORTING & DEBUGGING (Enhanced)
// ============================================================

switch (APP_ENV) {
    case 'development':
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        define('DEBUG_MODE', true);
        define('LOG_LEVEL', 'debug');
        define('SHOW_ERROR_DETAILS', true);
        break;
    case 'staging':
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        define('DEBUG_MODE', false);
        define('LOG_LEVEL', 'info');
        define('SHOW_ERROR_DETAILS', false);
        break;
    case 'production':
    default:
        error_reporting(0);
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        define('DEBUG_MODE', false);
        define('LOG_LEVEL', 'error');
        define('SHOW_ERROR_DETAILS', false);
        break;
}

// ============================================================
// TIMEZONE & LOCALE
// ============================================================

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Karachi');
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'Asia/Karachi');
define('DEFAULT_LOCALE', $_ENV['APP_LOCALE'] ?? 'en_PK');
define('DEFAULT_LANGUAGE', $_ENV['APP_LANGUAGE'] ?? 'en');

define('SUPPORTED_LANGUAGES', [
    'en' => 'English',
    'ur' => 'Urdu',
    'en_PK' => 'English (Pakistan)'
]);

// ============================================================
// DATE & TIME FORMATS (ISO Standards)
// ============================================================

define('DATE_FORMAT', 'Y-m-d');
define('DATE_FORMAT_DISPLAY', 'd M Y');
define('DATE_FORMAT_FULL', 'l, d F Y');
define('DATE_FORMAT_ISO', 'Y-m-d\TH:i:sP');
define('TIME_FORMAT', 'H:i:s');
define('TIME_FORMAT_12H', 'h:i A');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DATETIME_FORMAT_DISPLAY', 'd M Y h:i A');
define('DATETIME_FORMAT_ISO', 'Y-m-d\TH:i:sP');
define('DATE_PICKER_FORMAT', 'yyyy-mm-dd');
define('TIME_PICKER_FORMAT', 'HH:mm');

// ============================================================
// CURRENCY & NUMBER FORMATS (Pakistan Standard)
// ============================================================

define('DEFAULT_CURRENCY', $_ENV['DEFAULT_CURRENCY'] ?? 'PKR');
define('CURRENCY_SYMBOL', '₨');
define('CURRENCY_CODE', 'PKR');
define('CURRENCY_POSITION', 'prefix');
define('THOUSAND_SEPARATOR', ',');
define('DECIMAL_SEPARATOR', '.');
define('DECIMAL_PLACES', 2);

// ============================================================
// SESSION CONFIGURATION (Enhanced Security)
// ============================================================

define('SESSION_NAME', 'grc_session');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', $_SERVER['HTTP_HOST'] ?? '');
define('SESSION_SECURE', APP_ENV === 'production' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'));
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Strict');
define('SESSION_GC_MAXLIFETIME', 1440);
define('SESSION_REGENERATE_INTERVAL', 1800);
define('SESSION_USE_COOKIES', true);
define('SESSION_USE_ONLY_COOKIES', true);

// ============================================================
// SECURITY HEADERS & CSP (Enhanced)
// ============================================================

define('SECURITY_HEADERS_ENABLED', true);
define('CSP_ENABLED', APP_ENV === 'production');
define('HSTS_ENABLED', APP_ENV === 'production');
define('HSTS_MAX_AGE', 31536000);
define('XFRAME_OPTIONS', 'DENY');
define('XSS_PROTECTION', '1; mode=block');
define('CONTENT_TYPE_OPTIONS', 'nosniff');
define('REFERRER_POLICY', 'strict-origin-when-cross-origin');
define('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()');

// CSP Directives
define('CSP_DEFAULT_SRC', "'self'");
define('CSP_SCRIPT_SRC', "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com");
define('CSP_STYLE_SRC', "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com");
define('CSP_FONT_SRC', "'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com");
define('CSP_IMG_SRC', "'self' data: https:");
define('CSP_CONNECT_SRC', "'self'");

// ============================================================
// DATABASE CONFIGURATION (From Environment)
// ============================================================

define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'grc_platform');
define('DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?? 'utf8mb4');
define('DB_COLLATION', $_ENV['DB_COLLATION'] ?? getenv('DB_COLLATION') ?? 'utf8mb4_unicode_ci');
define('DB_ENGINE', $_ENV['DB_ENGINE'] ?? getenv('DB_ENGINE') ?? 'InnoDB');

// Database pool settings
define('DB_POOL_SIZE', (int)($_ENV['DB_POOL_SIZE'] ?? getenv('DB_POOL_SIZE') ?? 5));
define('DB_POOL_TIMEOUT', (int)($_ENV['DB_POOL_TIMEOUT'] ?? getenv('DB_POOL_TIMEOUT') ?? 5));

// ============================================================
// FILE UPLOAD CONFIGURATION (Enhanced)
// ============================================================

define('MAX_UPLOAD_SIZE', (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760));
define('MAX_UPLOAD_SIZE_MB', MAX_UPLOAD_SIZE / 1048576);

// Allowed file types with MIME types
define('ALLOWED_FILE_TYPES', [
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls' => 'application/vnd.ms-excel',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'csv' => 'text/csv',
    'txt' => 'text/plain',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    '7z' => 'application/x-7z-compressed'
]);

define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt']);
define('ALLOWED_ARCHIVE_TYPES', ['zip', 'rar', '7z']);
define('MAX_IMAGE_WIDTH', 2048);
define('MAX_IMAGE_HEIGHT', 2048);
define('IMAGE_QUALITY', 85);

// ============================================================
// DEFAULT IMAGES & AVATARS
// ============================================================

define('DEFAULT_USER_AVATAR', 'default-avatar.png');
define('DEFAULT_COMPANY_LOGO', 'company-logo.png');
define('DEFAULT_BANK_LOGO', 'bank-logo.png');
define('DEFAULT_POLICY_COVER', 'policy-cover.png');
define('DEFAULT_REPORT_COVER', 'report-cover.png');
define('DEFAULT_PROFILE_COVER', 'profile-cover.jpg');
define('DEFAULT_FAVICON', 'favicon.ico');

// ============================================================
// PAGINATION CONFIGURATION
// ============================================================

define('PAGINATION_DEFAULT', 15);
define('PAGINATION_PER_PAGE_OPTIONS', [10, 15, 25, 50, 100]);
define('PAGINATION_MAX_PER_PAGE', 100);
define('PAGINATION_LINKS', 5);
define('PAGINATION_QUERY_PARAM', 'page');
define('PAGINATION_PER_PAGE_PARAM', 'per_page');

// ============================================================
// CACHE CONFIGURATION
// ============================================================

define('CACHE_ENABLED', APP_ENV === 'production');
define('CACHE_DRIVER', $_ENV['CACHE_DRIVER'] ?? 'file');
define('CACHE_LIFETIME', (int)($_ENV['CACHE_LIFETIME'] ?? 3600));
define('CACHE_PREFIX', 'grc_');
define('CACHE_PATH', BASE_PATH . '/storage/cache');
define('CACHE_TAG_ENABLED', true);
define('CACHE_COMPRESS', APP_ENV === 'production');

// Redis cache settings (if used)
define('CACHE_REDIS_HOST', $_ENV['REDIS_HOST'] ?? '127.0.0.1');
define('CACHE_REDIS_PORT', (int)($_ENV['REDIS_PORT'] ?? 6379));
define('CACHE_REDIS_PASSWORD', $_ENV['REDIS_PASSWORD'] ?? null);
define('CACHE_REDIS_DATABASE', (int)($_ENV['REDIS_DATABASE'] ?? 0));

// ============================================================
// LOGGING CONFIGURATION (Enhanced)
// ============================================================

define('LOG_ENABLED', true);
define('LOG_PATH', BASE_PATH . '/storage/logs');
define('LOG_FILE', 'grc.log');
define('LOG_ERROR_FILE', 'error.log');
define('LOG_ACCESS_FILE', 'access.log');
define('LOG_ACTIVITY_FILE', 'activity.log');
define('LOG_SECURITY_FILE', 'security.log');
define('LOG_AUDIT_FILE', 'audit.log');
define('LOG_DAILY_ROTATION', true);
define('LOG_MAX_FILES', 30);
define('LOG_MAX_SIZE', 10485760);
define('LOG_JSON_FORMAT', false);
define('LOG_CONTEXT', ['app' => APP_SHORT_NAME, 'env' => APP_ENV]);

// ============================================================
// EMAIL CONFIGURATION
// ============================================================

define('MAIL_DRIVER', $_ENV['MAIL_DRIVER'] ?? 'smtp');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@grc-platform.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_SHORT_NAME);
define('MAIL_SENDMAIL_PATH', $_ENV['MAIL_SENDMAIL_PATH'] ?? '/usr/sbin/sendmail -t -i');

// ============================================================
// AI SERVICES CONFIGURATION
// ============================================================

define('AI_ENABLED', filter_var($_ENV['AI_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('AI_PROVIDER', $_ENV['AI_PROVIDER'] ?? 'openai');
define('AI_API_KEY', $_ENV['AI_API_KEY'] ?? '');
define('AI_API_URL', $_ENV['AI_API_URL'] ?? 'https://api.openai.com/v1');
define('AI_MODEL', $_ENV['AI_MODEL'] ?? 'gpt-4');
define('AI_MAX_TOKENS', (int)($_ENV['AI_MAX_TOKENS'] ?? 4096));
define('AI_TEMPERATURE', (float)($_ENV['AI_TEMPERATURE'] ?? 0.7));
define('AI_TOP_P', (float)($_ENV['AI_TOP_P'] ?? 0.9));
define('AI_FREQUENCY_PENALTY', (float)($_ENV['AI_FREQUENCY_PENALTY'] ?? 0.0));
define('AI_PRESENCE_PENALTY', (float)($_ENV['AI_PRESENCE_PENALTY'] ?? 0.0));
define('AI_TIMEOUT', (int)($_ENV['AI_TIMEOUT'] ?? 30));
define('AI_RETRY_ATTEMPTS', (int)($_ENV['AI_RETRY_ATTEMPTS'] ?? 3));
define('AI_CACHE_ENABLED', filter_var($_ENV['AI_CACHE_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));

// ============================================================
// API CONFIGURATION
// ============================================================

define('API_ENABLED', filter_var($_ENV['API_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('API_RATE_LIMIT', (int)($_ENV['API_RATE_LIMIT'] ?? 60));
define('API_RATE_LIMIT_WINDOW', (int)($_ENV['API_RATE_LIMIT_WINDOW'] ?? 60));
define('API_VERSION', $_ENV['API_VERSION'] ?? 'v1');
define('API_PREFIX', $_ENV['API_PREFIX'] ?? '/api');
define('API_CORS_ENABLED', APP_ENV !== 'production');
define('API_DEBUG', APP_ENV === 'development');

// ============================================================
// QUEUE CONFIGURATION
// ============================================================

define('QUEUE_ENABLED', filter_var($_ENV['QUEUE_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('QUEUE_DRIVER', $_ENV['QUEUE_DRIVER'] ?? 'database');
define('QUEUE_DEFAULT', $_ENV['QUEUE_DEFAULT'] ?? 'default');
define('QUEUE_MAX_ATTEMPTS', (int)($_ENV['QUEUE_MAX_ATTEMPTS'] ?? 3));
define('QUEUE_RETRY_DELAY', (int)($_ENV['QUEUE_RETRY_DELAY'] ?? 5));

// ============================================================
// CSRF CONFIGURATION
// ============================================================

define('CSRF_ENABLED', filter_var($_ENV['CSRF_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');
define('CSRF_TOKEN_LENGTH', (int)($_ENV['CSRF_TOKEN_LENGTH'] ?? 32));
define('CSRF_TOKEN_LIFETIME', (int)($_ENV['CSRF_TOKEN_LIFETIME'] ?? 3600));

// ============================================================
// RECAPTCHA CONFIGURATION
// ============================================================

define('RECAPTCHA_ENABLED', filter_var($_ENV['RECAPTCHA_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('RECAPTCHA_SITE_KEY', $_ENV['RECAPTCHA_SITE_KEY'] ?? '');
define('RECAPTCHA_SECRET_KEY', $_ENV['RECAPTCHA_SECRET_KEY'] ?? '');

// ============================================================
// MAINTENANCE MODE
// ============================================================

define('MAINTENANCE_MODE', filter_var($_ENV['MAINTENANCE_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('MAINTENANCE_MESSAGE', $_ENV['MAINTENANCE_MESSAGE'] ?? 'The AI Banking GRC Platform is currently undergoing maintenance. We will be back shortly.');
define('MAINTENANCE_ALLOWED_IPS', array_map('trim', explode(',', $_ENV['MAINTENANCE_ALLOWED_IPS'] ?? '127.0.0.1,::1')));

// ============================================================
// RATE LIMITING
// ============================================================

define('RATE_LIMIT_ENABLED', filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('RATE_LIMIT_GENERAL', (int)($_ENV['RATE_LIMIT_GENERAL'] ?? 100));
define('RATE_LIMIT_LOGIN', (int)($_ENV['RATE_LIMIT_LOGIN'] ?? 5));
define('RATE_LIMIT_API', (int)($_ENV['RATE_LIMIT_API'] ?? 60));
define('RATE_LIMIT_AUTH', (int)($_ENV['RATE_LIMIT_AUTH'] ?? 10));
define('RATE_LIMIT_WINDOW', (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60));
define('RATE_LIMIT_BLOCK_DURATION', (int)($_ENV['RATE_LIMIT_BLOCK_DURATION'] ?? 300));

// ============================================================
// PERFORMANCE OPTIMIZATIONS
// ============================================================

define('COMPRESS_OUTPUT', APP_ENV === 'production');
define('MINIFY_HTML', APP_ENV === 'production');
define('CACHE_VIEWS', APP_ENV === 'production');
define('DB_QUERY_LOG', APP_ENV === 'development');
define('BENCHMARK_ENABLED', APP_ENV === 'development');
define('PROFILER_ENABLED', APP_ENV === 'development');
define('OPCACHE_ENABLED', APP_ENV === 'production');

// ============================================================
// CONSTANTS FOR PATH MANAGEMENT
// ============================================================

define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', BASE_PATH . DS . 'app');
define('CONTROLLER_PATH', APP_PATH . DS . 'Controllers');
define('MODEL_PATH', APP_PATH . DS . 'Models');
define('VIEW_PATH', APP_PATH . DS . 'Views');
define('HELPER_PATH', APP_PATH . DS . 'Helpers');
define('SERVICE_PATH', APP_PATH . DS . 'Services');
define('MIDDLEWARE_PATH', APP_PATH . DS . 'Middleware');
define('LIBRARY_PATH', APP_PATH . DS . 'Libraries');
define('AI_PATH', APP_PATH . DS . 'AI');
define('CONFIG_PATH', BASE_PATH . DS . 'config');
define('DATABASE_PATH', BASE_PATH . DS . 'database');
define('MODULES_PATH', BASE_PATH . DS . 'modules');
define('ASSETS_PATH', BASE_PATH . DS . 'assets');
define('UPLOADS_PATH', BASE_PATH . DS . 'uploads');
define('STORAGE_PATH', BASE_PATH . DS . 'storage');
define('TESTS_PATH', BASE_PATH . DS . 'tests');
define('DOCS_PATH', BASE_PATH . DS . 'docs');

// ============================================================
// ENVIRONMENT HELPER FUNCTIONS
// ============================================================

/**
 * Get configuration value with dot notation support
 * 
 * @param string $key Configuration key with dot notation
 * @param mixed $default Default value if key not found
 * @return mixed
 */
function config(string $key, $default = null)
{
    static $configCache = null;
    
    if ($configCache === null) {
        $configCache = [
            'app' => [
                'name' => APP_NAME,
                'short_name' => APP_SHORT_NAME,
                'version' => APP_VERSION,
                'build' => APP_BUILD,
                'env' => APP_ENV,
                'debug' => DEBUG_MODE,
                'url' => BASE_URL,
                'timezone' => APP_TIMEZONE,
                'locale' => DEFAULT_LOCALE,
                'currency' => DEFAULT_CURRENCY,
                'maintenance' => MAINTENANCE_MODE
            ],
            'session' => [
                'lifetime' => SESSION_LIFETIME,
                'name' => SESSION_NAME,
                'secure' => SESSION_SECURE,
                'httponly' => SESSION_HTTP_ONLY,
                'samesite' => SESSION_SAME_SITE
            ],
            'upload' => [
                'max_size' => MAX_UPLOAD_SIZE,
                'max_size_mb' => MAX_UPLOAD_SIZE_MB,
                'allowed_types' => ALLOWED_FILE_TYPES,
                'allowed_images' => ALLOWED_IMAGE_TYPES,
                'allowed_documents' => ALLOWED_DOCUMENT_TYPES,
                'max_width' => MAX_IMAGE_WIDTH,
                'max_height' => MAX_IMAGE_HEIGHT
            ],
            'pagination' => [
                'per_page' => PAGINATION_DEFAULT,
                'options' => PAGINATION_PER_PAGE_OPTIONS,
                'max' => PAGINATION_MAX_PER_PAGE,
                'query_param' => PAGINATION_QUERY_PARAM
            ],
            'security' => [
                'csrf' => CSRF_ENABLED,
                'csrf_token_name' => CSRF_TOKEN_NAME,
                'rate_limit' => RATE_LIMIT_GENERAL,
                'headers_enabled' => SECURITY_HEADERS_ENABLED,
                'csp_enabled' => CSP_ENABLED,
                'hsts_enabled' => HSTS_ENABLED
            ],
            'mail' => [
                'driver' => MAIL_DRIVER,
                'host' => MAIL_HOST,
                'port' => MAIL_PORT,
                'username' => MAIL_USERNAME,
                'password' => MAIL_PASSWORD,
                'encryption' => MAIL_ENCRYPTION,
                'from_address' => MAIL_FROM_ADDRESS,
                'from_name' => MAIL_FROM_NAME
            ],
            'ai' => [
                'enabled' => AI_ENABLED,
                'provider' => AI_PROVIDER,
                'model' => AI_MODEL,
                'max_tokens' => AI_MAX_TOKENS,
                'temperature' => AI_TEMPERATURE,
                'timeout' => AI_TIMEOUT,
                'cache' => AI_CACHE_ENABLED
            ],
            'cache' => [
                'enabled' => CACHE_ENABLED,
                'driver' => CACHE_DRIVER,
                'lifetime' => CACHE_LIFETIME,
                'prefix' => CACHE_PREFIX,
                'path' => CACHE_PATH,
                'compress' => CACHE_COMPRESS
            ],
            'log' => [
                'enabled' => LOG_ENABLED,
                'path' => LOG_PATH,
                'level' => LOG_LEVEL,
                'daily_rotation' => LOG_DAILY_ROTATION,
                'max_files' => LOG_MAX_FILES,
                'max_size' => LOG_MAX_SIZE
            ],
            'api' => [
                'enabled' => API_ENABLED,
                'version' => API_VERSION,
                'prefix' => API_PREFIX,
                'rate_limit' => API_RATE_LIMIT,
                'cors' => API_CORS_ENABLED
            ]
        ];
    }
    
    // Parse dot notation
    $segments = explode('.', $key);
    $current = $configCache;
    
    foreach ($segments as $segment) {
        if (!isset($current[$segment])) {
            return $default;
        }
        $current = $current[$segment];
    }
    
    return $current;
}

/**
 * Check if current environment matches
 * 
 * @param string $environment Development, staging, production
 * @return bool
 */
function is_env(string $environment): bool
{
    return strtolower(APP_ENV) === strtolower($environment);
}

/**
 * Check if in development mode
 * 
 * @return bool
 */
function is_development(): bool
{
    return is_env('development');
}

/**
 * Check if in production mode
 * 
 * @return bool
 */
function is_production(): bool
{
    return is_env('production');
}

/**
 * Check if in staging mode
 * 
 * @return bool
 */
function is_staging(): bool
{
    return is_env('staging');
}

/**
 * Get environment name
 * 
 * @return string
 */
function get_environment(): string
{
    return APP_ENV;
}

/**
 * Get application version
 * 
 * @return string
 */
function app_version(): string
{
    return APP_VERSION;
}

/**
 * Get application name
 * 
 * @return string
 */
function app_name(): string
{
    return APP_NAME;
}

// ============================================================
// CONFIGURATION VALIDATION
// ============================================================

// Validate critical configuration
if (empty(DB_HOST) || empty(DB_NAME)) {
    throw new RuntimeException('Database configuration is incomplete. Please check your .env file.');
}

// Create required directories if they don't exist
$requiredDirs = [
    LOG_PATH,
    CACHE_PATH,
    UPLOADS_PATH,
    STORAGE_PATH,
    STORAGE_PATH . '/cache',
    STORAGE_PATH . '/logs',
    STORAGE_PATH . '/temp'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create directory: {$dir}");
        }
    }
}

// Verify directory permissions
foreach ($requiredDirs as $dir) {
    if (!is_writable($dir)) {
        error_log("Directory is not writable: {$dir}");
    }
}

// ============================================================
// END OF CONFIGURATION
// ============================================================