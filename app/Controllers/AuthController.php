<?php
/**
 * AI Banking GRC Platform - Authentication Controller
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This controller handles:
 * - User login and logout
 * - User registration
 * - Password reset
 * - Email verification
 * - Remember me functionality
 * - Two-factor authentication
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Validation;
use App\Services\EmailService;
use App\Services\TwoFactorService;
use Exception;

class AuthController extends BaseController
{
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var EmailService
     */
    private EmailService $emailService;
    
    /**
     * @var TwoFactorService
     */
    private TwoFactorService $twoFactorService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->controllerName = 'Auth';
        $this->userModel = new User();
        $this->emailService = new EmailService();
        $this->twoFactorService = new TwoFactorService();
    }
    
    /**
     * Show login page
     * 
     * @return void
     */
    public function login(): void
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirectToRoute('dashboard');
        }
        
        $this->render('login', [
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
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Get login credentials
            $username = $this->input('username');
            $password = $this->input('password');
            $remember = $this->input('remember') ? true : false;
            
            // Validate input
            $validationRules = [
                'username' => 'required|min:3|max:50',
                'password' => 'required|min:8'
            ];
            
            $this->validate($_POST, $validationRules);
            
            // Attempt login
            $user = Auth::attempt($username, $password, $remember);
            
            if (!$user) {
                $this->setFlashMessage('error', 'Invalid username or password.');
                $this->redirectToRoute('login');
            }
            
            // Check if two-factor authentication is enabled
            if ($user->two_factor_enabled) {
                $_SESSION['2fa_user_id'] = $user->id;
                $_SESSION['2fa_verified'] = false;
                $this->redirectToRoute('auth.2fa');
            }
            
            // Regenerate session
            session_regenerate_id(true);
            
            // Log login activity
            $this->logActivity('login', 'User logged in successfully');
            
            // Redirect to dashboard
            $this->setFlashMessage('success', 'Welcome back, ' . $user->first_name . '!');
            $this->redirectToRoute('dashboard');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('login');
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
            $this->redirectToRoute('login');
        }
        
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirectToRoute('login');
        }
        
        $this->render('2fa', [
            'title' => 'Two-Factor Authentication',
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
                $this->jsonError('Invalid request.');
            }
            
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                $this->jsonError('User not found.');
            }
            
            // Verify 2FA code
            if ($this->twoFactorService->verify($user, $code)) {
                $_SESSION['2fa_verified'] = true;
                Auth::login($user);
                session_regenerate_id(true);
                
                $this->jsonSuccess('2FA verified successfully.', [
                    'redirect' => $this->generateUrl('dashboard')
                ]);
            } else {
                $this->jsonError('Invalid verification code.');
            }
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
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
        
        $this->render('register', [
            'title' => 'Register - ' . APP_NAME
        ]);
    }
    
    /**
     * Register a new user
     * 
     * @return void
     */
    public function store(): void
    {
        try {
            // Validate CSRF token
            $this->validateCSRF($_POST['csrf_token'] ?? '');
            
            // Validate input
            $validationRules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|min:3|max:30|unique:users,username',
                'password' => 'required|min:8|confirmed',
                'mobile' => 'required|regex:/^(\+92|0)[0-9]{10,12}$/'
            ];
            
            $validated = $this->validate($_POST, $validationRules);
            
            // Create user
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'mobile' => $validated['mobile'],
                'password_hash' => password_hash($validated['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]),
                'role_id' => 7, // Default user role
                'status' => USER_STATUS_PENDING,
                'email_verified' => false
            ];
            
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                throw new Exception('Failed to create user account.');
            }
            
            // Send verification email
            $this->emailService->sendVerificationEmail($userId);
            
            $this->setFlashMessage('success', 'Account created successfully! Please check your email to verify your account.');
            $this->redirectToRoute('login');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('register');
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
            $this->logActivity('logout', 'User logged out');
        }
        
        // Clear remember me cookie
        Auth::logout();
        
        // Destroy session
        session_destroy();
        
        $this->setFlashMessage('info', 'You have been logged out successfully.');
        $this->redirectToRoute('login');
    }
    
    /**
     * Show forgot password page
     * 
     * @return void
     */
    public function forgotPassword(): void
    {
        $this->render('password-forgot', [
            'title' => 'Reset Password - ' . APP_NAME
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
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            
            // Save token to database
            $this->userModel->createPasswordResetToken($email, $token);
            
            // Send reset email
            $this->emailService->sendPasswordResetEmail($email, $token);
            
            $this->setFlashMessage('success', 'Password reset link has been sent to your email.');
            $this->jsonSuccess('Reset link sent.');
            
        } catch (Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }
    
    /**
     * Show reset password page
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function resetPassword(array $params): void
    {
        $token = $params['token'] ?? '';
        
        // Validate token
        $user = $this->userModel->validatePasswordResetToken($token);
        
        if (!$user) {
            $this->setFlashMessage('error', 'Invalid or expired password reset token.');
            $this->redirectToRoute('login');
        }
        
        $this->render('password-reset', [
            'title' => 'Reset Password - ' . APP_NAME,
            'token' => $token,
            'email' => $user->email
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
            
            $validationRules = [
                'token' => 'required',
                'password' => 'required|min:8|confirmed'
            ];
            
            $this->validate($_POST, $validationRules);
            
            // Reset password
            $result = $this->userModel->resetPassword($token, $password);
            
            if (!$result) {
                throw new Exception('Failed to reset password. Token may be invalid or expired.');
            }
            
            $this->setFlashMessage('success', 'Password has been reset successfully. Please login with your new password.');
            $this->redirectToRoute('login');
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('login');
        }
    }
    
    /**
     * Verify email
     * 
     * @param array $params Route parameters
     * @return void
     */
    public function verifyEmail(array $params): void
    {
        $token = $params['token'] ?? '';
        
        $user = $this->userModel->verifyEmail($token);
        
        if ($user) {
            $this->setFlashMessage('success', 'Email verified successfully! You can now login.');
        } else {
            $this->setFlashMessage('error', 'Invalid or expired verification token.');
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
                $this->jsonError('Email is already verified.');
            }
            
            $this->emailService->sendVerificationEmail($user->id);
            
            $this->jsonSuccess('Verification email has been resent.');
            
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
        $userId = Auth::id() ?? null;
        $logData = [
            'user_id' => $userId,
            'action' => $action,
            'module' => 'authentication',
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert into activity logs
        // This will be implemented in ActivityLogService
    }
}