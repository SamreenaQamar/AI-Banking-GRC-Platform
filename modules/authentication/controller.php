<?php
/**
 * Authentication Module - Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles all authentication actions:
 * - Login, logout, registration
 * - Password reset and email verification
 * - Two-factor authentication
 * - Profile management
 * - Session management
 */

declare(strict_types=1);

namespace Modules\Authentication;

use App\Controllers\BaseController;
use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use App\Services\EmailService;
use Modules\Authentication\Services\AuthService;
use Modules\Authentication\Services\TwoFactorService;
use Exception;

class AuthController extends BaseController
{
    /**
     * @var AuthService
     */
    private AuthService $authService;
    
    /**
     * @var TwoFactorService
     */
    private TwoFactorService $twoFactorService;
    
    /**
     * @var EmailService
     */
    private EmailService $emailService;
    
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Auth';
        $this->authService = new AuthService();
        $this->twoFactorService = new TwoFactorService();
        $this->emailService = new EmailService();
        $this->userModel = new User();
    }
    
    /**
     * Show login page
     * 
     * @return void
     */
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirectToRoute('dashboard');
        }
        
        $this->render('auth/login', [
            'title' => 'Login - ' . APP_NAME,
            'has_error' => false
        ]);
    }
    
    /**
     * Authenticate user
     * 
     * @return void
     */
    public function authenticate(): void
    {
        try {
            // Validate CSRF token
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            // Get credentials
            $username = $this->input('username');
            $password = $this->input('password');
            $remember = $this->input('remember') ? true : false;
            
            // Validate input
            $rules = [
                'username' => 'required|min:3|max:50',
                'password' => 'required|min:8'
            ];
            $this->validate($_POST, $rules);
            
            // Attempt login
            $result = $this->authService->authenticate($username, $password);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            // Check if user is locked
            if ($result['locked']) {
                throw new Exception('Your account is locked. Please try again later.');
            }
            
            // Check if email is verified
            if (AUTH_EMAIL_VERIFICATION_REQUIRED && !$result['user']->email_verified) {
                $this->setFlashMessage('warning', 'Please verify your email before logging in.');
                $this->redirectToRoute('auth.login');
                return;
            }
            
            // Check if 2FA is required
            if ($result['user']->two_factor_enabled) {
                $_SESSION['2fa_user_id'] = $result['user']->id;
                $_SESSION['2fa_verified'] = false;
                $this->redirectToRoute('auth.2fa');
                return;
            }
            
            // Login user
            Auth::login($result['user']);
            
            // Regenerate session
            session_regenerate_id(true);
            
            // Log activity
            $this->logActivity('login', 'User logged in successfully');
            
            // Set flash message
            $this->setFlashMessage('success', 'Welcome back, ' . $result['user']->first_name . '!');
            
            // Redirect
            $redirect = $_SESSION['intended_url'] ?? $this->generateUrl('dashboard');
            unset($_SESSION['intended_url']);
            $this->redirect($redirect);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('auth.login');
        }
    }
    
    /**
     * Show two-factor authentication page
     * 
     * @return void
     */
    public function twoFactor(): void
    {
        $userId = $_SESSION['2fa_user_id'] ?? null;
        
        if (!$userId) {
            $this->redirectToRoute('auth.login');
        }
        
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirectToRoute('auth.login');
        }
        
        $this->render('auth/2fa', [
            'title' => 'Two-Factor Authentication - ' . APP_NAME,
            'user' => $user
        ]);
    }
    
    /**
     * Verify two-factor authentication
     * 
     * @return void
     */
    public function verifyTwoFactor(): void
    {
        try {
            $userId = $_SESSION['2fa_user_id'] ?? null;
            $code = $this->input('code');
            
            if (!$userId || !$code) {
                throw new Exception('Invalid request.');
            }
            
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                throw new Exception('User not found.');
            }
            
            // Verify 2FA code
            if (!$this->twoFactorService->verify($user, $code)) {
                throw new Exception('Invalid verification code.');
            }
            
            $_SESSION['2fa_verified'] = true;
            Auth::login($user);
            session_regenerate_id(true);
            
            $this->setFlashMessage('success', 'Two-factor authentication verified successfully.');
            
            $redirect = $_SESSION['intended_url'] ?? $this->generateUrl('dashboard');
            unset($_SESSION['intended_url']);
            $this->redirect($redirect);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('auth.2fa');
        }
    }
    
    /**
     * Show registration page
     * 
     * @return void
     */
    public function register(): void
    {
        if (Auth::check()) {
            $this->redirectToRoute('dashboard');
        }
        
        $this->render('auth/register', [
            'title' => 'Register - ' . APP_NAME
        ]);
    }
    
    /**
     * Register new user
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            // Validate CSRF
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            // Validate input
            $rules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|min:3|max:30|unique:users,username',
                'password' => 'required|min:8|confirmed',
                'mobile' => 'required|regex:/^(\+92|0)[0-9]{10,12}$/'
            ];
            
            $validated = $this->validate($_POST, $rules);
            
            // Create user
            $result = $this->authService->register($validated);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            // Send verification email
            if (AUTH_EMAIL_VERIFICATION_REQUIRED) {
                $this->emailService->sendVerificationEmail($result['user_id']);
            }
            
            $this->setFlashMessage('success', auth_message('register_success'));
            $this->redirectToRoute('auth.login');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('auth.register');
        }
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout(): void
    {
        // Log activity
        if (Auth::check()) {
            $this->logActivity('logout', 'User logged out');
        }
        
        // Clear remember me
        $this->authService->clearRememberToken();
        
        // Logout
        Auth::logout();
        
        // Destroy session
        session_destroy();
        
        $this->setFlashMessage('info', auth_message('logout_success'));
        $this->redirectToRoute('auth.login');
    }
    
    /**
     * Show forgot password page
     * 
     * @return void
     */
    public function forgotPassword(): void
    {
        $this->render('auth/forgot-password', [
            'title' => 'Forgot Password - ' . APP_NAME
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
            
            $rules = [
                'email' => 'required|email|exists:users,email'
            ];
            $this->validate($_POST, $rules);
            
            $result = $this->authService->sendResetLink($email);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            $this->setFlashMessage('success', auth_message('reset_sent'));
            $this->jsonSuccess('Reset link sent.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Show reset password page
     * 
     * @param array $params
     * @return void
     */
    public function resetPassword(array $params): void
    {
        $token = $params['token'] ?? '';
        
        // Validate token
        $result = $this->authService->validateResetToken($token);
        
        if (!$result['valid']) {
            $this->setFlashMessage('error', 'Invalid or expired password reset token.');
            $this->redirectToRoute('auth.login');
        }
        
        $this->render('auth/reset-password', [
            'title' => 'Reset Password - ' . APP_NAME,
            'token' => $token,
            'email' => $result['email']
        ]);
    }
    
    /**
     * Update password after reset
     * 
     * @return void
     */
    public function updatePassword(): void
    {
        try {
            $token = $this->input('token');
            $password = $this->input('password');
            $passwordConfirmation = $this->input('password_confirmation');
            
            $rules = [
                'token' => 'required',
                'password' => 'required|min:8|confirmed'
            ];
            $this->validate($_POST, $rules);
            
            $result = $this->authService->resetPassword($token, $password);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            $this->setFlashMessage('success', 'Password has been reset successfully.');
            $this->redirectToRoute('auth.login');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('auth.login');
        }
    }
    
    /**
     * Verify email
     * 
     * @param array $params
     * @return void
     */
    public function verifyEmail(array $params): void
    {
        $token = $params['token'] ?? '';
        
        $result = $this->authService->verifyEmail($token);
        
        if ($result['success']) {
            $this->setFlashMessage('success', auth_message('email_verified'));
        } else {
            $this->setFlashMessage('error', auth_message('email_verification_failed'));
        }
        
        $this->redirectToRoute('auth.login');
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
            
            $rules = [
                'email' => 'required|email|exists:users,email'
            ];
            $this->validate($_POST, $rules);
            
            $result = $this->authService->resendVerification($email);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            $this->jsonSuccess('Verification email has been resent.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Change password (authenticated)
     * 
     * @return void
     */
    public function changePassword(): void
    {
        try {
            $this->requireAuth();
            CSRF::validate($_POST['csrf_token'] ?? '');
            
            $rules = [
                'current_password' => 'required|min:8',
                'new_password' => 'required|min:8|confirmed'
            ];
            $this->validate($_POST, $rules);
            
            $result = $this->authService->changePassword(
                Auth::id(),
                $this->input('current_password'),
                $this->input('new_password')
            );
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            $this->setFlashMessage('success', auth_message('password_changed'));
            $this->jsonSuccess('Password changed successfully.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @return void
     */
    private function logActivity(string $action, string $description): void
    {
        // This will be implemented in ActivityLogService
    }
}