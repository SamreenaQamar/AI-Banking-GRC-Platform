<?php
/**
 * Authentication Module - Routes
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all route definitions for the authentication module
 */

declare(strict_types=1);

// ============================================================
// PUBLIC ROUTES (Guest Access)
// ============================================================

// Login routes
$router->get('/login', 'AuthController@login', [
    'name' => 'auth.login',
    'middleware' => ['guest']
]);

$router->post('/login', 'AuthController@authenticate', [
    'name' => 'auth.authenticate',
    'middleware' => ['guest']
]);

// Registration routes
$router->get('/register', 'AuthController@register', [
    'name' => 'auth.register',
    'middleware' => ['guest']
]);

$router->post('/register', 'AuthController@store', [
    'name' => 'auth.register.submit',
    'middleware' => ['guest']
]);

// Password reset routes
$router->get('/password/forgot', 'AuthController@forgotPassword', [
    'name' => 'auth.password.forgot',
    'middleware' => ['guest']
]);

$router->post('/password/forgot', 'AuthController@sendResetLink', [
    'name' => 'auth.password.email',
    'middleware' => ['guest']
]);

$router->get('/password/reset/{token}', 'AuthController@resetPassword', [
    'name' => 'auth.password.reset',
    'middleware' => ['guest']
]);

$router->post('/password/reset', 'AuthController@updatePassword', [
    'name' => 'auth.password.update',
    'middleware' => ['guest']
]);

// Email verification
$router->get('/verify/{token}', 'AuthController@verifyEmail', [
    'name' => 'auth.verification.verify'
]);

$router->post('/verify/resend', 'AuthController@resendVerification', [
    'name' => 'auth.verification.resend'
]);

// Two-factor authentication
$router->get('/2fa', 'AuthController@twoFactor', [
    'name' => 'auth.2fa',
    'middleware' => ['auth']
]);

$router->post('/2fa/verify', 'AuthController@verifyTwoFactor', [
    'name' => 'auth.2fa.verify',
    'middleware' => ['auth']
]);

// ============================================================
// PROTECTED ROUTES (Authenticated Access)
// ============================================================

$router->group(['middleware' => ['auth']], function($router) {
    // Logout
    $router->get('/logout', 'AuthController@logout', [
        'name' => 'auth.logout'
    ]);
    
    // Profile management
    $router->get('/profile', 'AuthController@profile', [
        'name' => 'auth.profile'
    ]);
    
    $router->post('/profile/update', 'AuthController@updateProfile', [
        'name' => 'auth.profile.update'
    ]);
    
    $router->post('/profile/password', 'AuthController@changePassword', [
        'name' => 'auth.profile.password'
    ]);
    
    $router->post('/profile/avatar', 'AuthController@updateAvatar', [
        'name' => 'auth.profile.avatar'
    ]);
    
    // Two-factor authentication settings
    $router->post('/profile/2fa/enable', 'AuthController@enableTwoFactor', [
        'name' => 'auth.2fa.enable'
    ]);
    
    $router->post('/profile/2fa/disable', 'AuthController@disableTwoFactor', [
        'name' => 'auth.2fa.disable'
    ]);
    
    $router->post('/profile/2fa/verify', 'AuthController@verifyTwoFactorSetup', [
        'name' => 'auth.2fa.verify-setup'
    ]);
    
    // Sessions management
    $router->get('/sessions', 'AuthController@activeSessions', [
        'name' => 'auth.sessions'
    ]);
    
    $router->post('/sessions/{id}/terminate', 'AuthController@terminateSession', [
        'name' => 'auth.sessions.terminate'
    ]);
    
    $router->post('/sessions/terminate-all', 'AuthController@terminateAllSessions', [
        'name' => 'auth.sessions.terminate-all'
    ]);
});

// ============================================================
// API ROUTES
// ============================================================

$router->group(['prefix' => 'api/v1/auth', 'middleware' => ['api']], function($router) {
    // Authentication
    $router->post('/login', 'ApiAuthController@login', [
        'name' => 'api.auth.login'
    ]);
    
    $router->post('/logout', 'ApiAuthController@logout', [
        'name' => 'api.auth.logout',
        'middleware' => ['auth:api']
    ]);
    
    $router->post('/register', 'ApiAuthController@register', [
        'name' => 'api.auth.register'
    ]);
    
    // Password management
    $router->post('/password/forgot', 'ApiAuthController@forgotPassword', [
        'name' => 'api.auth.password.forgot'
    ]);
    
    $router->post('/password/reset', 'ApiAuthController@resetPassword', [
        'name' => 'api.auth.password.reset'
    ]);
    
    // Email verification
    $router->post('/verify', 'ApiAuthController@verifyEmail', [
        'name' => 'api.auth.verify'
    ]);
    
    $router->post('/verify/resend', 'ApiAuthController@resendVerification', [
        'name' => 'api.auth.verify.resend'
    ]);
    
    // Profile
    $router->get('/profile', 'ApiAuthController@profile', [
        'name' => 'api.auth.profile',
        'middleware' => ['auth:api']
    ]);
    
    $router->put('/profile', 'ApiAuthController@updateProfile', [
        'name' => 'api.auth.profile.update',
        'middleware' => ['auth:api']
    ]);
    
    // Two-factor authentication
    $router->post('/2fa/enable', 'ApiAuthController@enableTwoFactor', [
        'name' => 'api.auth.2fa.enable',
        'middleware' => ['auth:api']
    ]);
    
    $router->post('/2fa/disable', 'ApiAuthController@disableTwoFactor', [
        'name' => 'api.auth.2fa.disable',
        'middleware' => ['auth:api']
    ]);
    
    $router->post('/2fa/verify', 'ApiAuthController@verifyTwoFactor', [
        'name' => 'api.auth.2fa.verify'
    ]);
});

// ============================================================
// RETURN ROUTES
// ============================================================

return $router;