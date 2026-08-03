<?php
/**
 * AI Banking GRC Platform - Authentication Service
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage Modules\Authentication
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This service handles all authentication business logic:
 * - User verification and authentication
 * - Password hashing and verification
 * - Session management
 * - Login history tracking
 * - Failed login counter
 * - Account lockout
 * - Password reset
 * - Email verification
 * - OTP support (future)
 * - Two-factor authentication
 */

declare(strict_types=1);

namespace Modules\Authentication;

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\EmailService;
use App\Services\TwoFactorService;
use App\Helpers\Password;
use App\Helpers\Session;
use Exception;
use PDO;

class Service
{
    /**
     * @var User
     */
    private User $userModel;
    
    /**
     * @var ActivityLog
     */
    private ActivityLog $logModel;
    
    /**
     * @var EmailService
     */
    private EmailService $emailService;
    
    /**
     * @var TwoFactorService
     */
    private TwoFactorService $twoFactorService;
    
    /**
     * @var array Module configuration
     */
    private array $config;
    
    /**
     * @var PDO Database connection
     */
    private PDO $db;
    
    /**
     * Constructor - Initialize authentication service
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->logModel = new ActivityLog();
        $this->emailService = new EmailService();
        $this->twoFactorService = new TwoFactorService();
        $this->db = $this->userModel->db ?? new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS
        );
        
        // Load configuration
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
        }
    }
    
    /**
     * Authenticate user by credentials
     * 
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return array
     */
    public function login(string $username, string $password, bool $remember = false): array
    {
        try {
            // Find user by username or email
            $user = $this->userModel->findByUsernameOrEmail($username);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials.'
                ];
            }
            
            // Check if account is locked
            if ($this->isAccountLocked($user->id)) {
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked due to too many failed attempts. Please try again later.'
                ];
            }
            
            // Check account status
            if ($user->status !== 'active') {
                $statusMessages = [
                    'inactive' => 'Your account is inactive. Please contact support.',
                    'suspended' => 'Your account has been suspended. Please contact support.',
                    'pending' => 'Your account is pending verification. Please check your email.'
                ];
                return [
                    'success' => false,
                    'message' => $statusMessages[$user->status] ?? 'Account access is restricted.'
                ];
            }
            
            // Verify password
            if (!Password::verify($password, $user->password_hash)) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials.'
                ];
            }
            
            // Check if password needs rehash
            if (Password::needsRehash($user->password_hash)) {
                $this->userModel->update($user->id, [
                    'password_hash' => Password::hash($password)
                ]);
            }
            
            // Check if 2FA is required
            if ($user->two_factor_enabled) {
                return [
                    'success' => true,
                    'requires_2fa' => true,
                    'user_id' => $user->id
                ];
            }
            
            // Login user
            $this->loginUser($user, $remember);
            
            return [
                'success' => true,
                'user_id' => $user->id,
                'role' => $user->role_name ?? 'user'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred during login: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Login user (set session)
     * 
     * @param object $user
     * @param bool $remember
     * @return void
     */
    public function loginUser(object $user, bool $remember = false): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['user_role'] = $user->role_name ?? 'user';
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->full_name ?? $user->username;
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Set remember me cookie if enabled
        if ($remember && defined('AUTH_REMEMBER_ME_ENABLED') && AUTH_REMEMBER_ME_ENABLED) {
            $this->setRememberToken($user->id);
        }
        
        // Update last login
        $this->userModel->update($user->id, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'login_attempts' => 0
        ]);
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function logout(): void
    {
        // Clear remember me token
        if (isset($_SESSION['user_id'])) {
            $this->clearRememberToken($_SESSION['user_id']);
        }
        
        // Clear session
        $_SESSION = [];
        
        // Clear session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Register a new user
     * 
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        try {
            // Hash password
            $hashedPassword = Password::hash($data['password']);
            
            // Prepare user data
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'],
                'password_hash' => $hashedPassword,
                'role_id' => $this->getDefaultRoleId(),
                'status' => 'pending',
                'email_verified' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Create user
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Failed to create user account.'
                ];
            }
            
            // Send verification email
            $this->sendVerificationEmail($userId);
            
            // Log registration
            $this->logActivity($userId, 'register', 'User registered');
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration successful.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get default role ID for new users
     * 
     * @return int
     */
    private function getDefaultRoleId(): int
    {
        $sql = "SELECT id FROM roles WHERE name = 'user' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ? (int)$result->id : 7;
    }
    
    /**
     * Send verification email
     * 
     * @param int $userId
     * @return bool
     */
    public function sendVerificationEmail(int $userId): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Generate verification token
        $token = bin2hex(random_bytes(32));
        
        // Save token
        $sql = "INSERT INTO email_verifications (user_id, token, expires_at, created_at) 
                VALUES (:user_id, :token, :expires_at, :created_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Send email
        return $this->emailService->sendVerificationEmail($user->email, $token);
    }
    
    /**
     * Verify email address
     * 
     * @param string $token
     * @return array
     */
    public function verifyEmail(string $token): array
    {
        try {
            // Find verification record
            $sql = "SELECT * FROM email_verifications 
                    WHERE token = :token AND used = 0 AND expires_at > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['token' => $token]);
            $verification = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$verification) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired verification token.'
                ];
            }
            
            // Mark as used
            $sql = "UPDATE email_verifications SET used = 1, used_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $verification->id]);
            
            // Update user
            $this->userModel->update($verification->user_id, [
                'email_verified' => true,
                'status' => 'active'
            ]);
            
            return [
                'success' => true,
                'user_id' => $verification->user_id,
                'message' => 'Email verified successfully.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send password reset link
     * 
     * @param string $email
     * @return array
     */
    public function sendPasswordResetLink(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email address not found.'
                ];
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            
            // Save token
            $sql = "INSERT INTO password_resets (email, token, expires_at, created_at) 
                    VALUES (:email, :token, :expires_at, :created_at)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $email,
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours')),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send email
            $this->emailService->sendPasswordResetEmail($email, $token);
            
            return [
                'success' => true,
                'message' => 'Password reset link sent.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send reset link: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate password reset token
     * 
     * @param string $token
     * @return object|null
     */
    public function validateResetToken(string $token): ?object
    {
        $sql = "SELECT * FROM password_resets 
                WHERE token = :token AND used = 0 AND expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$result) {
            return null;
        }
        
        return $this->userModel->findByEmail($result->email);
    }
    
    /**
     * Reset password
     * 
     * @param string $token
     * @param string $newPassword
     * @return array
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            // Find token
            $sql = "SELECT * FROM password_resets 
                    WHERE token = :token AND used = 0 AND expires_at > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$reset) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired reset token.'
                ];
            }
            
            // Get user
            $user = $this->userModel->findByEmail($reset->email);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Hash new password
            $hashedPassword = Password::hash($newPassword);
            
            // Update password
            $this->userModel->update($user->id, [
                'password_hash' => $hashedPassword
            ]);
            
            // Mark token as used
            $sql = "UPDATE password_resets SET used = 1, used_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $reset->id]);
            
            // Log password reset
            $this->logActivity($user->id, 'password_reset', 'Password reset completed');
            
            return [
                'success' => true,
                'message' => 'Password reset successfully.'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Password reset failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Change user password
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
            
            // Verify current password
            if (!Password::verify($currentPassword, $user->password_hash)) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ];
            }
            
            // Hash new password
            $hashedPassword = Password::hash($newPassword);
            
            // Update password
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
                'message' => 'Password change failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if account is locked
     * 
     * @param int $userId
     * @return bool
     */
    public function isAccountLocked(int $userId): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $maxAttempts = AUTH_MAX_LOGIN_ATTEMPTS ?? 5;
        $lockoutMinutes = AUTH_LOCKOUT_MINUTES ?? 15;
        
        if ($user->login_attempts >= $maxAttempts) {
            $lockedUntil = strtotime($user->locked_until ?? '1970-01-01 00:00:00');
            if ($lockedUntil > time()) {
                return true;
            } else {
                // Reset attempts if lockout expired
                $this->userModel->update($userId, [
                    'login_attempts' => 0,
                    'locked_until' => null
                ]);
            }
        }
        
        return false;
    }
    
    /**
     * Log failed login attempt
     * 
     * @param string $username
     * @return void
     */
    public function logFailedAttempt(string $username): void
    {
        $user = $this->userModel->findByUsernameOrEmail($username);
        
        if (!$user) {
            return;
        }
        
        $maxAttempts = AUTH_MAX_LOGIN_ATTEMPTS ?? 5;
        $lockoutMinutes = AUTH_LOCKOUT_MINUTES ?? 15;
        
        // Increment attempts
        $newAttempts = ($user->login_attempts ?? 0) + 1;
        
        $updateData = [
            'login_attempts' => $newAttempts
        ];
        
        // Lock account if max attempts reached
        if ($newAttempts >= $maxAttempts) {
            $updateData['locked_until'] = date('Y-m-d H:i:s', strtotime("+{$lockoutMinutes} minutes"));
        }
        
        $this->userModel->update($user->id, $updateData);
        
        // Log failed attempt
        $this->logActivity($user->id, 'login_failed', "Failed login attempt from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    /**
     * Log successful login
     * 
     * @param int $userId
     * @return void
     */
    public function logSuccessfulLogin(int $userId): void
    {
        $this->logActivity($userId, 'login_success', "Successful login from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    }
    
    /**
     * Set remember me token
     * 
     * @param int $userId
     * @return void
     */
    private function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (AUTH_REMEMBER_ME_DAYS ?? 30) * 86400;
        
        // Save token in database
        $sql = "UPDATE users SET remember_token = :token WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'token' => $token,
            'id' => $userId
        ]);
        
        // Set cookie
        setcookie(
            'remember_token',
            $token,
            $expires,
            '/',
            '',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            true
        );
    }
    
    /**
     * Clear remember me token
     * 
     * @param int $userId
     * @return void
     */
    private function clearRememberToken(int $userId): void
    {
        $sql = "UPDATE users SET remember_token = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    /**
     * Verify two-factor authentication code
     * 
     * @param object $user
     * @param string $code
     * @return bool
     */
    public function verifyTwoFactor(object $user, string $code): bool
    {
        return $this->twoFactorService->verify($user, $code);
    }
    
    /**
     * Log activity
     * 
     * @param int|null $userId
     * @param string $action
     * @param string $description
     * @return void
     */
    public function logActivity(?int $userId, string $action, string $description): void
    {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'module' => 'authentication',
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->logModel->create($data);
    }
    
    /**
     * Check rate limit for login attempts
     * 
     * @param string $username
     * @return bool
     */
    public function checkRateLimit(string $username): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $window = AUTH_RATE_LIMIT_WINDOW ?? 60;
        $maxAttempts = AUTH_RATE_LIMIT_ATTEMPTS ?? 5;
        
        $sql = "SELECT COUNT(*) FROM activity_logs 
                WHERE action = 'login_failed' 
                AND (user_id = (SELECT id FROM users WHERE username = :username OR email = :username) 
                OR ip_address = :ip)
                AND created_at > DATE_SUB(NOW(), INTERVAL :window SECOND)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $username,
            'ip' => $ip,
            'window' => $window
        ]);
        
        $count = (int)$stmt->fetchColumn();
        
        return $count < $maxAttempts;
    }
    
    /**
     * Check rate limit for password reset
     * 
     * @param string $email
     * @return bool
     */
    public function checkResetRateLimit(string $email): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $window = 3600; // 1 hour
        $maxAttempts = 3;
        
        $sql = "SELECT COUNT(*) FROM password_resets 
                WHERE email = :email AND created_at > DATE_SUB(NOW(), INTERVAL :window SECOND)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'window' => $window
        ]);
        
        $count = (int)$stmt->fetchColumn();
        
        return $count < $maxAttempts;
    }
}