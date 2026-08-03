<?php
/**
 * AI Banking GRC Platform - Authentication Module Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage Modules\Authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file defines all authentication-related routes:
 * - Login/Logout
 * - Registration
 * - Password reset
 * - Email verification
 * - Profile management
 * - Two-factor authentication
 */

declare(strict_types=1);

use Modules\Authentication\Controller;

return [
    // ============================================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================================
    
    // Login routes
    'GET /login' => [
        'controller' => Controller::class,
        'method' => 'login',
        'name' => 'login'
    ],
    'POST /login' => [
        'controller' => Controller::class,
        'method' => 'authenticate',
        'name' => 'login.submit'
    ],
    'GET /logout' => [
        'controller' => Controller::class,
        'method' => 'logout',
        'name' => 'logout'
    ],
    
    // Registration routes
    'GET /register' => [
        'controller' => Controller::class,
        'method' => 'register',
        'name' => 'register'
    ],
    'POST /register' => [
        'controller' => Controller::class,
        'method' => 'storeRegistration',
        'name' => 'register.submit'
    ],
    
    // Password reset routes
    'GET /forgot-password' => [
        'controller' => Controller::class,
        'method' => 'forgotPassword',
        'name' => 'password.forgot'
    ],
    'POST /forgot-password' => [
        'controller' => Controller::class,
        'method' => 'sendResetLink',
        'name' => 'password.email'
    ],
    'GET /reset-password/{token}' => [
        'controller' => Controller::class,
        'method' => 'resetPassword',
        'name' => 'password.reset'
    ],
    'POST /reset-password' => [
        'controller' => Controller::class,
        'method' => 'updatePassword',
        'name' => 'password.update'
    ],
    
    // Email verification routes
    'GET /verify-email/{token}' => [
        'controller' => Controller::class,
        'method' => 'verifyEmail',
        'name' => 'verification.verify'
    ],
    'POST /verify-email/resend' => [
        'controller' => Controller::class,
        'method' => 'resendVerification',
        'name' => 'verification.resend'
    ],
    
    // ============================================================
    // PROTECTED ROUTES (Authentication Required)
    // ============================================================
    
    // Change password
    'GET /change-password' => [
        'controller' => Controller::class,
        'method' => 'changePassword',
        'name' => 'password.change',
        'middleware' => ['Auth']
    ],
    'POST /change-password' => [
        'controller' => Controller::class,
        'method' => 'processChangePassword',
        'name' => 'password.change.submit',
        'middleware' => ['Auth']
    ],
    
    // Two-factor authentication
    'GET /2fa' => [
        'controller' => Controller::class,
        'method' => 'twoFactor',
        'name' => 'auth.2fa'
    ],
    'POST /2fa/verify' => [
        'controller' => Controller::class,
        'method' => 'verifyTwoFactor',
        'name' => 'auth.2fa.verify'
    ],
    
    // Session status (AJAX)
    'GET /session/status' => [
        'controller' => Controller::class,
        'method' => 'sessionStatus',
        'name' => 'session.status'
    ],
    
    // ============================================================
    // API ROUTES
    // ============================================================
    
    // Authentication API
    'POST /api/auth/login' => [
        'controller' => Controller::class,
        'method' => 'authenticate',
        'name' => 'api.auth.login'
    ],
    'POST /api/auth/logout' => [
        'controller' => Controller::class,
        'method' => 'logout',
        'name' => 'api.auth.logout',
        'middleware' => ['Auth']
    ],
    'GET /api/auth/status' => [
        'controller' => Controller::class,
        'method' => 'sessionStatus',
        'name' => 'api.auth.status'
    ],
    'POST /api/auth/2fa/verify' => [
        'controller' => Controller::class,
        'method' => 'verifyTwoFactor',
        'name' => 'api.auth.2fa.verify'
    ]
];