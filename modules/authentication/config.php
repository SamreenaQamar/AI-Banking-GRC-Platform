<?php
/**
 * Authentication Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the authentication module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('AUTH_MODULE_NAME', 'Authentication');
define('AUTH_MODULE_VERSION', '1.0.0');
define('AUTH_MODULE_AUTHOR', 'GRC Platform Team');
define('AUTH_MODULE_DESCRIPTION', 'User authentication and session management module');

// ============================================================
// AUTHENTICATION SETTINGS
// ============================================================

// Session configuration
define('AUTH_SESSION_LIFETIME', 3600); // 1 hour
define('AUTH_SESSION_REFRESH_INTERVAL', 1800); // 30 minutes

// Login settings
define('AUTH_MAX_LOGIN_ATTEMPTS', 5);
define('AUTH_LOCKOUT_DURATION', 15); // minutes
define('AUTH_REMEMBER_ME_LIFETIME', 2592000); // 30 days

// Password settings
define('AUTH_PASSWORD_MIN_LENGTH', 8);
define('AUTH_PASSWORD_MAX_LENGTH', 100);
define('AUTH_PASSWORD_HASH_COST', 12);
define('AUTH_PASSWORD_REQUIRE_UPPERCASE', true);
define('AUTH_PASSWORD_REQUIRE_LOWERCASE', true);
define('AUTH_PASSWORD_REQUIRE_NUMBERS', true);
define('AUTH_PASSWORD_REQUIRE_SPECIAL', true);

// Two-factor authentication
define('AUTH_2FA_ENABLED', true);
define('AUTH_2FA_ISSUER', 'AI Banking GRC Platform');
define('AUTH_2FA_ALGORITHM', 'sha1');
define('AUTH_2FA_PERIOD', 30); // seconds
define('AUTH_2FA_DIGITS', 6);

// Email verification
define('AUTH_EMAIL_VERIFICATION_REQUIRED', true);
define('AUTH_EMAIL_VERIFICATION_EXPIRY', 24); // hours
define('AUTH_RESET_PASSWORD_EXPIRY', 1); // hour

// ============================================================
// ROUTE CONFIGURATION
// ============================================================

define('AUTH_ROUTES', [
    'login' => [
        'uri' => '/login',
        'method' => 'GET',
        'controller' => 'AuthController',
        'action' => 'login'
    ],
    'login_submit' => [
        'uri' => '/login',
        'method' => 'POST',
        'controller' => 'AuthController',
        'action' => 'authenticate'
    ],
    'logout' => [
        'uri' => '/logout',
        'method' => 'GET',
        'controller' => 'AuthController',
        'action' => 'logout'
    ],
    'register' => [
        'uri' => '/register',
        'method' => 'GET',
        'controller' => 'AuthController',
        'action' => 'register'
    ],
    'register_submit' => [
        'uri' => '/register',
        'method' => 'POST',
        'controller' => 'AuthController',
        'action' => 'store'
    ],
    'forgot_password' => [
        'uri' => '/password/forgot',
        'method' => 'GET',
        'controller' => 'AuthController',
        'action' => 'forgotPassword'
    ],
    'reset_password' => [
        'uri' => '/password/reset',
        'method' => 'POST',
        'controller' => 'AuthController',
        'action' => 'sendResetLink'
    ],
    'verify_email' => [
        'uri' => '/verify/{token}',
        'method' => 'GET',
        'controller' => 'AuthController',
        'action' => 'verifyEmail'
    ],
    'two_factor' => [
        'uri' => '/2fa',
        'method' => 'GET',
        'controller' => 'AuthController',
        'action' => 'twoFactor'
    ],
    'two_factor_verify' => [
        'uri' => '/2fa/verify',
        'method' => 'POST',
        'controller' => 'AuthController',
        'action' => 'verifyTwoFactor'
    ]
]);

// ============================================================
// PERMISSION CONFIGURATION
// ============================================================

define('AUTH_PERMISSIONS', [
    'auth_login' => 'Can login to the system',
    'auth_register' => 'Can register new account',
    'auth_reset_password' => 'Can reset password',
    'auth_change_password' => 'Can change own password',
    'auth_view_profile' => 'Can view own profile',
    'auth_update_profile' => 'Can update own profile',
    'auth_2fa_enable' => 'Can enable two-factor authentication',
    'auth_2fa_disable' => 'Can disable two-factor authentication',
    'auth_view_sessions' => 'Can view active sessions',
    'auth_terminate_sessions' => 'Can terminate sessions'
]);

// ============================================================
// MIDDLEWARE CONFIGURATION
// ============================================================

define('AUTH_MIDDLEWARE', [
    'auth' => [
        'class' => 'AuthMiddleware',
        'priority' => 10,
        'description' => 'Ensures user is authenticated'
    ],
    'guest' => [
        'class' => 'GuestMiddleware',
        'priority' => 10,
        'description' => 'Redirects authenticated users away from guest pages'
    ],
    'verified' => [
        'class' => 'VerifiedMiddleware',
        'priority' => 20,
        'description' => 'Ensures user email is verified'
    ],
    '2fa' => [
        'class' => 'TwoFactorMiddleware',
        'priority' => 30,
        'description' => 'Ensures two-factor authentication is completed'
    ]
]);

// ============================================================
// VALIDATION RULES
// ============================================================

define('AUTH_VALIDATION_RULES', [
    'login' => [
        'username' => 'required|min:3|max:50',
        'password' => 'required|min:8|max:100'
    ],
    'register' => [
        'username' => 'required|min:3|max:50|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|max:100|confirmed',
        'first_name' => 'required|min:2|max:50',
        'last_name' => 'required|min:2|max:50'
    ],
    'reset_password' => [
        'email' => 'required|email|exists:users'
    ],
    'change_password' => [
        'current_password' => 'required|min:8',
        'new_password' => 'required|min:8|max:100|confirmed'
    ]
]);

// ============================================================
// MESSAGE CONFIGURATION
// ============================================================

define('AUTH_MESSAGES', [
    'login_success' => 'Welcome back, {username}!',
    'login_failed' => 'Invalid username or password.',
    'login_locked' => 'Account locked. Please try again in {minutes} minutes.',
    'logout_success' => 'You have been logged out successfully.',
    'register_success' => 'Registration successful! Please check your email to verify your account.',
    'register_failed' => 'Registration failed. Please try again.',
    'reset_sent' => 'Password reset link has been sent to your email.',
    'reset_failed' => 'Failed to send reset link. Please try again.',
    'password_changed' => 'Password changed successfully.',
    'password_change_failed' => 'Failed to change password. Please check your current password.',
    'email_verified' => 'Email verified successfully! You can now login.',
    'email_verification_failed' => 'Email verification failed. Please try again.',
    '2fa_required' => 'Two-factor authentication is required.',
    '2fa_success' => 'Two-factor authentication verified successfully.',
    '2fa_failed' => 'Invalid verification code. Please try again.',
    'session_expired' => 'Your session has expired. Please login again.',
    'unauthorized' => 'You are not authorized to access this page.'
]);

// ============================================================
// EMAIL TEMPLATES
// ============================================================

define('AUTH_EMAIL_TEMPLATES', [
    'verification' => [
        'subject' => 'Verify Your Email Address',
        'template' => 'email/verification.php',
        'variables' => ['name', 'verification_link']
    ],
    'reset_password' => [
        'subject' => 'Reset Your Password',
        'template' => 'email/reset-password.php',
        'variables' => ['name', 'reset_link']
    ],
    'welcome' => [
        'subject' => 'Welcome to AI Banking GRC Platform',
        'template' => 'email/welcome.php',
        'variables' => ['name', 'login_url']
    ],
    '2fa_alert' => [
        'subject' => 'Two-Factor Authentication Alert',
        'template' => 'email/2fa-alert.php',
        'variables' => ['name', 'ip_address', 'location']
    ]
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get authentication configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value
 * @return mixed
 */
function auth_config(string $key, $default = null)
{
    $config = [
        'session_lifetime' => AUTH_SESSION_LIFETIME,
        'max_login_attempts' => AUTH_MAX_LOGIN_ATTEMPTS,
        'lockout_duration' => AUTH_LOCKOUT_DURATION,
        'remember_me_lifetime' => AUTH_REMEMBER_ME_LIFETIME,
        'password_min_length' => AUTH_PASSWORD_MIN_LENGTH,
        'password_hash_cost' => AUTH_PASSWORD_HASH_COST,
        '2fa_enabled' => AUTH_2FA_ENABLED,
        '2fa_issuer' => AUTH_2FA_ISSUER,
        'email_verification_required' => AUTH_EMAIL_VERIFICATION_REQUIRED,
        'reset_password_expiry' => AUTH_RESET_PASSWORD_EXPIRY
    ];
    
    return $config[$key] ?? $default;
}

/**
 * Get authentication message
 * 
 * @param string $key Message key
 * @param array $replacements Replacement values
 * @return string
 */
function auth_message(string $key, array $replacements = []): string
{
    $messages = AUTH_MESSAGES;
    $message = $messages[$key] ?? $key;
    
    foreach ($replacements as $key => $value) {
        $message = str_replace('{' . $key . '}', $value, $message);
    }
    
    return $message;
}