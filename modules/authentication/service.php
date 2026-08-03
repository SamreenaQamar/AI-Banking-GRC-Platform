<?php
/**
 * Authentication Module - Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles authentication business logic:
 * - User authentication
 * - Password management
 * - Token generation and validation
 * - Two-factor authentication
 * - Session management
 */

declare(strict_types=1);

namespace Modules\Authentication\Services;

use App\Models\User;
use App\Helpers\Password;
use App\Helpers\Security;
use App\Services\EmailService;
use Exception;
use PDO;

class AuthService
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
        $this->userModel = new User();
        $this->emailService = new EmailService();
        $this->twoFactorService = new TwoFactorService();
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username
     * @param string $password
     * @return array
     */
    public function authenticate(string $username, string $password): array
    {
        try {
            // Find user by username or email
            $user = $this->userModel->findByUsernameOrEmail($username);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials.',
                    'locked' => false
                ];
            }
            
            // Check if account is locked
            if ($this->isAccountLocked($user)) {
                return [
                    'success' => false,
                    'message' => 'Account is locked. Please try again later.',
                    'locked' => true
                ];
            }
            
            // Verify password
            if (!Password::verify($password, $user->password_hash)) {
                $this->incrementLoginAttempts($user);
                return [
                    'success' => false,
                    'message' => 'Invalid credentials.',
                    'locked' => false
                ];
            }
            
            // Reset login attempts
            $this->resetLoginAttempts($user);
            
            // Update last login
            $this->userModel->update($user->id, [
                'last_login' => date('Y-m-d H:i:s'),
                'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return [
                'success' => true,
                'user' => $user,
                'locked' => false
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'locked' => false
            ];
        }
    }
    
    /**
     * Register new user
     * 
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        try {
            // Check if username or email exists
            if ($this->userModel->findByUsername($data['username'])) {
                return [
                    'success' => false,
                    'message' => 'Username already taken.'
                ];
            }
            
            if ($this->userModel->findByEmail($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email already registered.'
                ];
            }
            
            // Hash password
            $hashedPassword = Password::hash($data['password']);
            
            // Create user
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password_hash' => $hashedPassword,
                'mobile' => $data['mobile'] ?? null,
                'status' => AUTH_EMAIL_VERIFICATION_REQUIRED ? 'pending' : 'active',
                'email_verified' => !AUTH_EMAIL_VERIFICATION_REQUIRED,
                'role_id' => 7, // Default user role
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Registration failed. Please try again.'
                ];
            }
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration successful.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send password reset link
     * 
     * @param string $email
     * @return array
     */
    public function sendResetLink(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Generate reset token
            $token = Security::generateToken(64);
            
            // Save token
            $this->userModel->createPasswordResetToken($user->id, $token);
            
            // Send email
            $this->emailService->sendPasswordResetEmail($user->email, $token);
            
            return [
                'success' => true,
                'message' => 'Reset link sent.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate reset token
     * 
     * @param string $token
     * @return array
     */
    public function validateResetToken(string $token): array
    {
        try {
            $result = $this->userModel->validateResetToken($token);
            
            if (!$result) {
                return [
                    'valid' => false,
                    'message' => 'Invalid or expired token.'
                ];
            }
            
            return [
                'valid' => true,
                'email' => $result['email'],
                'user_id' => $result['user_id']
            ];
            
        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Reset password
     * 
     * @param string $token
     * @param string $password
     * @return array
     */
    public function resetPassword(string $token, string $password): array
    {
        try {
            $result = $this->validateResetToken($token);
            
            if (!$result['valid']) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired token.'
                ];
            }
            
            // Update password
            $hashedPassword = Password::hash($password);
            $this->userModel->update($result['user_id'], [
                'password_hash' => $hashedPassword
            ]);
            
            // Delete used token
            $this->userModel->deleteResetToken($token);
            
            return [
                'success' => true,
                'message' => 'Password reset successfully.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify email
     * 
     * @param string $token
     * @return array
     */
    public function verifyEmail(string $token): array
    {
        try {
            $userId = $this->userModel->verifyEmailToken($token);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired verification token.'
                ];
            }
            
            $this->userModel->update($userId, [
                'email_verified' => true,
                'status' => 'active'
            ]);
            
            return [
                'success' => true,
                'message' => 'Email verified successfully.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Resend verification email
     * 
     * @param string $email
     * @return array
     */
    public function resendVerification(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            if ($user->email_verified) {
                return [
                    'success' => false,
                    'message' => 'Email already verified.'
                ];
            }
            
            $this->emailService->sendVerificationEmail($user->id);
            
            return [
                'success' => true,
                'message' => 'Verification email resent.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Change password
     * 
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            if (!Password::verify($currentPassword, $user->password_hash)) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ];
            }
            
            $hashedPassword = Password::hash($newPassword);
            $this->userModel->update($userId, [
                'password_hash' => $hashedPassword
            ]);
            
            return [
                'success' => true,
                'message' => 'Password changed successfully.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Clear remember token
     * 
     * @return void
     */
    public function clearRememberToken(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $this->userModel->clearRememberToken($token);
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    /**
     * Check if account is locked
     * 
     * @param object $user
     * @return bool
     */
    private function isAccountLocked(object $user): bool
    {
        if (!$user->locked_until) {
            return false;
        }
        
        return strtotime($user->locked_until) > time();
    }
    
    /**
     * Increment login attempts
     * 
     * @param object $user
     * @return void
     */
    private function incrementLoginAttempts(object $user): void
    {
        $attempts = ($user->login_attempts ?? 0) + 1;
        $data = ['login_attempts' => $attempts];
        
        if ($attempts >= AUTH_MAX_LOGIN_ATTEMPTS) {
            $data['locked_until'] = date('Y-m-d H:i:s', strtotime('+' . AUTH_LOCKOUT_DURATION . ' minutes'));
        }
        
        $this->userModel->update($user->id, $data);
    }
    
    /**
     * Reset login attempts
     * 
     * @param object $user
     * @return void
     */
    private function resetLoginAttempts(object $user): void
    {
        $this->userModel->update($user->id, [
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }
}