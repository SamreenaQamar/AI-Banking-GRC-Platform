<?php
/**
 * AI Banking GRC Platform - Authentication Module Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage Modules\Authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles all authentication-related operations:
 * - User login and logout
 * - User registration
 * - Password reset and recovery
 * - Email verification
 * - Session management
 * - Two-factor authentication
 * - Role-based redirection
 * - CSRF protection
 * - Input validation
 * - Secure error handling
 */

declare(strict_types=1);

namespace Modules\Authentication;

use App\Controllers\BaseController;
use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use App\Services\EmailService;
use Modules\Authentication\Service as AuthService;
use Exception;

class Controller extends BaseController
{
    /**
     * @var AuthService
     */
    private AuthService $authService;
    
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var EmailService
     */
    private EmailService $emailService;
    
    /**
     * @var array Module configuration
     */
    private array $config;
    
    /**
     * Constructor - Initialize authentication module
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Auth';
        $this->authService = new AuthService();
        $this->userModel = new User();
        $this->emailService = new EmailService();
        
        // Load module configuration
        $this->loadConfig();
    }
    
    /**
     * Load module configuration
     * 
     * @return void
     */
    private function loadConfig(): void
    {
        $configPath = __DIR__ . '/config.php';
        if (file_exists($configPath)) {
            $this->config = require $configPath;
            
            // Define module constants for easy access
            foreach ($this->config as $key => $value) {
                if (is_scalar($value)) {
                    define('AUTH_' . strtoupper($key), $value);
                }
            }
        }
    }
    
    /**
     * Display login page
     * 
     * @return void
     */
    public function login(): void
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirectToRoute('dashboard');
        }
        
        $this->render('auth/login', [
            'title' => 'Login - ' . APP_NAME,
            'has_error' => false,
            'module' => 'authentication'
        ]);
    }
    
    /**
     * Authenticate user credentials
     * 
     * @return void
     */
    public function authenticate(): void
    {
        try {
            // Validate CSRF token
            if (CSRF::enabled() && !CSRF::validate($this->input('csrf_token'))) {
                throw new Exception('Invalid security token. Please refresh the page and try again.');
            }
            
            // Get credentials
            $username = $this->input('username');
            $password = $this->input('password');
            $remember = (bool)$this->input('remember', false);
            
            // Validate input
            $validationRules = [
                'username' => 'required|min:3|max:50',
                'password' => 'required|min:' . (AUTH_MIN_PASSWORD_LENGTH ?? 8)
            ];
            
            $this->validate($_POST, $validationRules);
            
            // Check rate limiting
            if (!$this->authService->checkRateLimit($username)) {
                throw new Exception('Too many login attempts. Please try again later.');
            }
            
            // Attempt login
            $result = $this->authService->login($username, $password, $remember);
            
            if (!$result['success']) {
                // Log failed attempt
                $this->authService->logFailedAttempt($username);
                throw new Exception($result['message'] ?? 'Invalid credentials.');
            }
            
            // Check if 2FA is required
            if ($result['requires_2fa'] ?? false) {
                $_SESSION['2fa_user_id'] = $result['user_id'];
                $_SESSION['2fa_verified'] = false;
                $this->jsonSuccess('Two-factor authentication required.', [
                    'redirect' => $this->generateUrl('auth.2fa')
                ]);
            }
            
            // Login successful - regenerate session ID
            session_regenerate_id(true);
            
            // Log successful login
            $this->authService->logSuccessfulLogin($result['user_id']);
            
            // Get redirect URL based on role
            $redirectUrl = $this->getRedirectUrl($result['role']);
            
            $this->jsonSuccess('Login successful. Welcome back!', [
                'redirect' => $redirectUrl
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Display two-factor authentication page
     * 
     * @return void
     */
    public function twoFactor(): void
    {
        $userId = $_SESSION['2fa_user_id'] ?? null;
        
        if (!$userId) {
            $this->redirectToRoute('login');
        }
        
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirectToRoute('login');
        }
        
        $this->render('auth/2fa', [
            'title' => 'Two-Factor Authentication - ' . APP_NAME,
            'user' => $user,
            'module' => 'authentication'
        ]);
    }
    
    /**
     * Verify two-factor authentication code
     * 
     * @return void
     */
    public function verifyTwoFactor(): void
    {
        try {
            $userId = $_SESSION['2fa_user_id'] ?? null;
            $code = $this->input('code');
            
            if (!$userId || !$code) {
                throw new Exception('Invalid request. Please try again.');
            }
            
            if (strlen($code) !== 6 || !ctype_digit($code)) {
                throw new Exception('Please enter a valid 6-digit verification code.');
            }
            
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Verify 2FA code
            if ($this->authService->verifyTwoFactor($user, $code)) {
                $_SESSION['2fa_verified'] = true;
                $this->authService->loginUser($user);
                
                // Log successful 2FA verification
                $this->authService->logActivity($userId, '2fa_verified', 'Two-factor authentication verified');
                
                $redirectUrl = $this->getRedirectUrl($user->role_name ?? 'user');
                
                $this->jsonSuccess('Two-factor authentication verified successfully.', [
                    'redirect' => $redirectUrl
                ]);
            } else {
                throw new Exception('Invalid verification code. Please try again.');
            }
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout(): void
    {
        // Log logout activity
        if (Auth::check()) {
            $this->authService->logActivity(Auth::id(), 'logout', 'User logged out');
        }
        
        $this->authService->logout();
        
        $this->setFlashMessage('info', 'You have been logged out successfully.');
        $this->redirectToRoute('login');
    }
    
    /**
     * Display forgot password page
     * 
     * @return void
     */
    public function forgotPassword(): void
    {
        $this->render('auth/forgot-password', [
            'title' => 'Reset Password - ' . APP_NAME,
            'module' => 'authentication'
        ]);
    }
    
    /**
     * Send password reset link
     * 
     * @return void
     */
    public function sendResetLink(): void
    {
        try {
            $email = $this->input('email');
            
            $validationRules = [
                'email' => 'required|email|exists:users,email'
            ];
            
            $this->validate($_POST, $validationRules);
            
            // Check rate limiting for password reset
            if (!$this->authService->checkResetRateLimit($email)) {
                throw new Exception('Too many reset attempts. Please try again later.');
            }
            
            $result = $this->authService->sendPasswordResetLink($email);
            
            if (!$result['success']) {
                throw new Exception($result['message'] ?? 'Failed to send reset link.');
            }
            
            $this->jsonSuccess('Password reset link has been sent to your email address.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Display reset password page
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function resetPassword(array $params): void
    {
        $token = $params['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlashMessage('error', 'Invalid password reset token.');
            $this->redirectToRoute('login');
        }
        
        // Validate token
        $user = $this->authService->validateResetToken($token);
        
        if (!$user) {
            $this->setFlashMessage('error', 'Invalid or expired password reset token. Please request a new one.');
            $this->redirectToRoute('login');
        }
        
        $this->render('auth/reset-password', [
            'title' => 'Reset Password - ' . APP_NAME,
            'token' => $token,
            'email' => $user->email,
            'module' => 'authentication'
        ]);
    }
    
    /**
     * Process password reset
     * 
     * @return void
     */
    public function updatePassword(): void
    {
        try {
            // Validate CSRF token
            if (CSRF::enabled() && !CSRF::validate($this->input('csrf_token'))) {
                throw new Exception('Invalid security token. Please refresh the page and try again.');
            }
            
            $token = $this->input('token');
            $password = $this->input('password');
            $passwordConfirmation = $this->input('password_confirmation');
            
            $validationRules = [
                'token' => 'required',
                'password' => 'required|min:' . (AUTH_MIN_PASSWORD_LENGTH ?? 8) . '|confirmed'
            ];
            
            $this->validate($_POST, $validationRules);
            
            $result = $this->authService->resetPassword($token, $password);
            
            if (!$result['success']) {
                throw new Exception($result['message'] ?? 'Failed to reset password.');
            }
            
            $this->jsonSuccess('Password has been reset successfully. Please login with your new password.', [
                'redirect' => $this->generateUrl('login')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Display change password page
     * 
     * @return void
     */
    public function changePassword(): void
    {
        $this->requireAuth();
        
        $this->render('auth/change-password', [
            'title' => 'Change Password - ' . APP_NAME,
            'module' => 'authentication'
        ]);
    }
    
    /**
     * Process password change
     * 
     * @return void
     */
    public function processChangePassword(): void
    {
        try {
            $this->requireAuth();
            
            // Validate CSRF token
            if (CSRF::enabled() && !CSRF::validate($this->input('csrf_token'))) {
                throw new Exception('Invalid security token. Please refresh the page and try again.');
            }
            
            $currentPassword = $this->input('current_password');
            $newPassword = $this->input('new_password');
            $newPasswordConfirmation = $this->input('new_password_confirmation');
            
            $validationRules = [
                'current_password' => 'required',
                'new_password' => 'required|min:' . (AUTH_MIN_PASSWORD_LENGTH ?? 8) . '|confirmed'
            ];
            
            $this->validate($_POST, $validationRules);
            
            $result = $this->authService->changePassword(
                Auth::id(),
                $currentPassword,
                $newPassword
            );
            
            if (!$result['success']) {
                throw new Exception($result['message'] ?? 'Failed to change password.');
            }
            
            // Log password change
            $this->authService->logActivity(Auth::id(), 'password_changed', 'User changed password');
            
            $this->jsonSuccess('Password changed successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Get redirect URL based on user role
     * 
     * @param string $role
     * @return string
     */
    private function getRedirectUrl(string $role): string
    {
        $roleRedirects = [
            'super_admin' => '/dashboard',
            'admin' => '/dashboard',
            'compliance_officer' => '/compliance',
            'risk_manager' => '/risk',
            'internal_auditor' => '/audit',
            'branch_manager' => '/dashboard'
        ];
        
        $redirect = $roleRedirects[$role] ?? '/dashboard';
        
        return $this->generateUrl(ltrim($redirect, '/'));
    }
    
    /**
     * Display registration page
     * 
     * @return void
     */
    public function register(): void
    {
        if (Auth::check()) {
            $this->redirectToRoute('dashboard');
        }
        
        if (!AUTH_ALLOW_REGISTRATION ?? true) {
            $this->setFlashMessage('error', 'Registration is currently disabled.');
            $this->redirectToRoute('login');
        }
        
        $this->render('auth/register', [
            'title' => 'Register - ' . APP_NAME,
            'module' => 'authentication'
        ]);
    }
    
    /**
     * Process user registration
     * 
     * @return void
     */
    public function storeRegistration(): void
    {
        try {
            if (!AUTH_ALLOW_REGISTRATION ?? true) {
                throw new Exception('Registration is currently disabled.');
            }
            
            // Validate CSRF token
            if (CSRF::enabled() && !CSRF::validate($this->input('csrf_token'))) {
                throw new Exception('Invalid security token. Please refresh the page and try again.');
            }
            
            // Validate input
            $validationRules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|min:3|max:30|unique:users,username',
                'password' => 'required|min:' . (AUTH_MIN_PASSWORD_LENGTH ?? 8) . '|confirmed',
                'mobile' => 'required|regex:/^(\+92|0)[0-9]{10,12}$/'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Register user
            $result = $this->authService->register($validated);
            
            if (!$result['success']) {
                throw new Exception($result['message'] ?? 'Registration failed.');
            }
            
            $this->jsonSuccess('Registration successful. Please check your email to verify your account.', [
                'redirect' => $this->generateUrl('login')
            ]);
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Verify email address
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function verifyEmail(array $params): void
    {
        $token = $params['token'] ?? '';
        
        if (empty($token)) {
            $this->setFlashMessage('error', 'Invalid verification token.');
            $this->redirectToRoute('login');
        }
        
        $result = $this->authService->verifyEmail($token);
        
        if ($result['success']) {
            $this->setFlashMessage('success', 'Email verified successfully! You can now login.');
        } else {
            $this->setFlashMessage('error', $result['message'] ?? 'Failed to verify email.');
        }
        
        $this->redirectToRoute('login');
    }
    
    /**
     * Resend verification email
     * 
     * @return void
     */
    public function resendVerification(): void
    {
        try {
            $email = $this->input('email');
            
            $validationRules = [
                'email' => 'required|email|exists:users,email'
            ];
            
            $this->validate($_POST, $validationRules);
            
            $user = $this->userModel->findByEmail($email);
            
            if ($user->email_verified) {
                throw new Exception('Email is already verified.');
            }
            
            $this->authService->sendVerificationEmail($user->id);
            
            $this->jsonSuccess('Verification email has been resent. Please check your inbox.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Check session status (for AJAX)
     * 
     * @return void
     */
    public function sessionStatus(): void
    {
        $this->json([
            'authenticated' => Auth::check(),
            'user' => Auth::check() ? [
                'id' => Auth::id(),
                'name' => Auth::user()->full_name ?? Auth::user()->username,
                'role' => Auth::user()->role_name ?? 'user'
            ] : null,
            'csrf_token' => CSRF::getToken()
        ]);
    }
}