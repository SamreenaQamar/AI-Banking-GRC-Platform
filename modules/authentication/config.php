<?php
/**
 * AI Banking GRC Platform - Authentication Module Configuration
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage Modules\Authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all authentication module configuration:
 * - Session settings
 * - Password policy
 * - Login limits
 * - Security settings
 * - Role mappings
 * - Email settings
 * - Two-factor authentication
 */

declare(strict_types=1);

return [
    // ============================================================
    // SESSION CONFIGURATION
    // ============================================================
    
    /**
     * Session timeout in seconds
     * Default: 3600 (1 hour)
     */
    'session_timeout' => 3600,
    
    /**
     * Session name
     */
    'session_name' => 'grc_session',
    
    /**
     * Regenerate session ID on login
     */
    'regenerate_session_on_login' => true,
    
    /**
     * Regenerate session ID periodically
     */
    'regenerate_session_interval' => 1800, // 30 minutes
    
    // ============================================================
    // PASSWORD POLICY
    // ============================================================
    
    /**
     * Minimum password length
     */
    'min_password_length' => 12,
    
    /**
     * Require at least one uppercase letter
     */
    'require_uppercase' => true,
    
    /**
     * Require at least one lowercase letter
     */
    'require_lowercase' => true,
    
    /**
     * Require at least one number
     */
    'require_number' => true,
    
    /**
     * Require at least one special character
     */
    'require_special' => true,
    
    /**
     * Password history (number of previous passwords to remember)
     */
    'password_history' => 5,
    
    /**
     * Password expiry in days (0 = never)
     */
    'password_expiry_days' => 90,
    
    // ============================================================
    // LOGIN SECURITY
    // ============================================================
    
    /**
     * Maximum failed login attempts before lockout
     */
    'max_login_attempts' => 5,
    
    /**
     * Lockout duration in minutes
     */
    'lockout_minutes' => 15,
    
    /**
     * Rate limiting window in seconds
     */
    'rate_limit_window' => 60,
    
    /**
     * Maximum login attempts per window
     */
    'rate_limit_attempts' => 5,
    
    // ============================================================
    // REMEMBER ME
    // ============================================================
    
    /**
     * Enable remember me functionality
     */
    'remember_me_enabled' => true,
    
    /**
     * Remember me token expiry in days
     */
    'remember_me_days' => 30,
    
    // ============================================================
    // CSRF PROTECTION
    // ============================================================
    
    /**
     * Enable CSRF protection
     */
    'csrf_enabled' => true,
    
    /**
     * CSRF token name
     */
    'csrf_token_name' => 'csrf_token',
    
    /**
     * CSRF token expiry in seconds
     */
    'csrf_token_expiry' => 3600,
    
    // ============================================================
    // TWO-FACTOR AUTHENTICATION
    // ============================================================
    
    /**
     * Enable two-factor authentication
     */
    'two_factor_enabled' => true,
    
    /**
     * Two-factor authentication method
     * Options: 'totp', 'sms', 'email'
     */
    'two_factor_method' => 'totp',
    
    /**
     * TOTP issuer name
     */
    'two_factor_issuer' => 'AI Banking GRC Platform',
    
    /**
     * TOTP algorithm
     */
    'two_factor_algorithm' => 'SHA1',
    
    /**
     * TOTP digits
     */
    'two_factor_digits' => 6,
    
    /**
     * TOTP period in seconds
     */
    'two_factor_period' => 30,
    
    // ============================================================
    // REGISTRATION
    // ============================================================
    
    /**
     * Allow user registration
     */
    'allow_registration' => true,
    
    /**
     * Require email verification on registration
     */
    'require_email_verification' => true,
    
    /**
     * Default role for new users
     */
    'default_role' => 'user',
    
    // ============================================================
    // ROLE REDIRECTS
    // ============================================================
    
    /**
     * Role-based redirect URLs after login
     */
    'role_redirects' => [
        'super_admin' => '/dashboard',
        'admin' => '/dashboard',
        'compliance_officer' => '/compliance',
        'risk_manager' => '/risk',
        'internal_auditor' => '/audit',
        'branch_manager' => '/dashboard',
        'user' => '/dashboard'
    ],
    
    // ============================================================
    // EMAIL SETTINGS
    // ============================================================
    
    /**
     * Send welcome email on registration
     */
    'send_welcome_email' => true,
    
    /**
     * Send password reset email
     */
    'send_password_reset_email' => true,
    
    /**
     * Send email on login from new device
     */
    'send_new_device_alert' => true,
    
    // ============================================================
    // LOGGING
    // ============================================================
    
    /**
     * Log all authentication events
     */
    'log_all_events' => true,
    
    /**
     * Log failed login attempts
     */
    'log_failed_login' => true,
    
    /**
     * Log successful login
     */
    'log_successful_login' => true,
    
    /**
     * Log password changes
     */
    'log_password_change' => true,
    
    // ============================================================
    // SECURITY HEADERS
    // ============================================================
    
    /**
     * Enable security headers
     */
    'security_headers_enabled' => true,
    
    /**
     * HSTS max age in seconds
     */
    'hsts_max_age' => 31536000, // 1 year
    
    /**
     * X-Frame-Options header
     */
    'x_frame_options' => 'DENY',
    
    /**
     * X-Content-Type-Options header
     */
    'x_content_type_options' => 'nosniff',
    
    /**
     * X-XSS-Protection header
     */
    'x_xss_protection' => '1; mode=block',
    
    /**
     * Referrer-Policy header
     */
    'referrer_policy' => 'strict-origin-when-cross-origin'
];