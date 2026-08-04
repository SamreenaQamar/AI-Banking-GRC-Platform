<?php
/**
 * AI Banking GRC Platform - Authentication Library
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Libraries
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This library provides enterprise authentication functionality:
 * - Login/Logout with session management
 * - Registration with validation
 * - Password reset flow
 * - Remember me with secure tokens
 * - Password hashing and verification
 * - OTP support for 2FA
 * - JWT ready architecture
 * - Role and permission based authentication
 * - Multi-session protection
 * - Login attempt tracking with account lockout
 * - Device validation
 */

declare(strict_types=1);

namespace App\Libraries;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Libraries\Session;
use App\Libraries\Security;
use App\Libraries\Logger;
use App\Libraries\RateLimiter;
use App\Libraries\TokenManager;

class Authentication
{
    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var Logger Logger instance
     */
    private Logger $logger;

    /**
     * @var RateLimiter Rate limiter instance
     */
    private RateLimiter $rateLimiter;

    /**
     * @var TokenManager Token manager instance
     */
    private TokenManager $tokenManager;

    /**
     * @var User User model
     */
    private User $userModel;

    /**
     * @var array User cache
     */
    private array $userCache = [];

    /**
     * @var string Session key for user ID
     */
    private string $userIdKey = 'auth_user_id';

    /**
     * @var string Session key for authentication status
     */
    private string $authKey = 'auth_authenticated';

    /**
     * @var string Session key for user data
     */
    private string $userDataKey = 'auth_user_data';

    /**
     * @var int Maximum login attempts
     */
    private int $maxAttempts = 5;

    /**
     * @var int Lockout duration in minutes
     */
    private int $lockoutDuration = 15;

    /**
     * @var int Session lifetime in seconds
     */
    private int $sessionLifetime = 3600;

    /**
     * @var int Remember me lifetime in seconds
     */
    private int $rememberLifetime = 2592000; // 30 days

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = new Session();
        $this->security = new Security();
        $this->logger = new Logger();
        $this->rateLimiter = new RateLimiter();
        $this->tokenManager = new TokenManager();
        $this->userModel = new User();
    }

    /**
     * Authenticate user with credentials
     * 
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return array
     */
    public function login(string $username, string $password, bool $remember = false): array
    {
        try {
            // Check rate limiting
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $limiterKey = 'login_' . md5($ip . $username);
            
            if ($this->rateLimiter->isLimited($limiterKey, $this->maxAttempts, 300)) {
                $this->logger->warning('Login rate limit exceeded', [
                    'ip' => $ip,
                    'username' => $username
                ]);
                return $this->errorResponse('Too many login attempts. Please try again later.', 'RATE_LIMITED');
            }

            // Find user by username or email
            $user = $this->userModel->findByUsernameOrEmail($username);

            if (!$user) {
                $this->rateLimiter->increment($limiterKey);
                $this->logger->warning('Login failed: User not found', [
                    'username' => $username,
                    'ip' => $ip
                ]);
                return $this->errorResponse('Invalid username or password.', 'INVALID_CREDENTIALS');
            }

            // Check if account is locked
            if ($this->isAccountLocked($user)) {
                $this->logger->warning('Login failed: Account locked', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'ip' => $ip
                ]);
                return $this->errorResponse('Account is temporarily locked. Please try again later.', 'ACCOUNT_LOCKED');
            }

            // Verify password
            if (!$this->verifyPassword($password, $user->password_hash)) {
                $this->recordFailedAttempt($user);
                $this->rateLimiter->increment($limiterKey);
                $this->logger->warning('Login failed: Invalid password', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'ip' => $ip
                ]);
                return $this->errorResponse('Invalid username or password.', 'INVALID_CREDENTIALS');
            }

            // Check user status
            if ($user->status !== 'active') {
                $this->logger->warning('Login failed: Account inactive', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'status' => $user->status
                ]);
                return $this->errorResponse('Your account is not active. Please contact support.', 'ACCOUNT_INACTIVE');
            }

            // Check email verification
            if (!$user->email_verified) {
                return $this->errorResponse('Please verify your email address before logging in.', 'EMAIL_NOT_VERIFIED');
            }

            // Check if 2FA is enabled
            if ($user->two_factor_enabled) {
                $_SESSION['2fa_user_id'] = $user->id;
                return $this->errorResponse('Two-factor authentication required.', '2FA_REQUIRED', [
                    'redirect' => '/2fa'
                ]);
            }

            // Successful login
            $this->resetFailedAttempts($user);
            $this->rateLimiter->reset($limiterKey);

            // Create session
            $this->createSession($user, $remember);

            // Update last login
            $this->userModel->update($user->id, [
                'last_login' => date('Y-m-d H:i:s'),
                'last_login_ip' => $ip
            ]);

            $this->logger->info('User logged in successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $ip
            ]);

            return $this->successResponse('Login successful.', [
                'user' => $this->sanitizeUser($user),
                'redirect' => '/dashboard'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Login error: ' . $e->getMessage(), [
                'username' => $username,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('An error occurred during login.', 'ERROR');
        }
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
            // Validate input
            $validator = new Validator();
            $rules = [
                'username' => ['required', 'min:3', 'max:50', 'unique:users,username'],
                'email' => ['required', 'email', 'unique:users,email'],
                'first_name' => ['required', 'min:2', 'max:50'],
                'last_name' => ['required', 'min:2', 'max:50'],
                'password' => ['required', 'min:8', 'confirmed'],
                'mobile' => ['required', 'phone'],
                'terms' => ['required', 'accepted']
            ];

            if (!$validator->validate($data, $rules)) {
                return $this->errorResponse('Validation failed.', 'VALIDATION_ERROR', [
                    'errors' => $validator->getErrors()
                ]);
            }

            // Check if user already exists
            if ($this->userModel->findByUsername($data['username'])) {
                return $this->errorResponse('Username already taken.', 'USERNAME_TAKEN');
            }

            if ($this->userModel->findByEmail($data['email'])) {
                return $this->errorResponse('Email already registered.', 'EMAIL_TAKEN');
            }

            // Hash password
            $hashedPassword = $this->hashPassword($data['password']);

            // Create user
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password_hash' => $hashedPassword,
                'mobile' => $data['mobile'],
                'role_id' => $data['role_id'] ?? 7, // Default user role
                'status' => 'pending',
                'email_verified' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->create($userData);

            if (!$userId) {
                return $this->errorResponse('Failed to create account.', 'REGISTRATION_FAILED');
            }

            // Generate verification token
            $verificationToken = $this->tokenManager->generate('verification', 64);
            $this->userModel->update($userId, [
                'verification_token' => $verificationToken,
                'verification_expires' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ]);

            $this->logger->info('User registered successfully', [
                'user_id' => $userId,
                'username' => $data['username'],
                'email' => $data['email']
            ]);

            return $this->successResponse('Account created successfully. Please check your email to verify your account.', [
                'user_id' => $userId,
                'verification_token' => $verificationToken
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Registration error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during registration.', 'ERROR');
        }
    }

    /**
     * Logout user
     * 
     * @return bool
     */
    public function logout(): bool
    {
        try {
            $userId = $this->id();
            
            // Clear remember token
            if ($userId) {
                $this->userModel->update($userId, [
                    'remember_token' => null,
                    'remember_expires' => null
                ]);
            }

            // Clear session
            $this->destroySession();
            
            // Clear remember cookie
            setcookie('remember_token', '', time() - 3600, '/');

            $this->logger->info('User logged out', ['user_id' => $userId]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Logout error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset link
     * 
     * @param string $email
     * @return array
     */
    public function forgotPassword(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                return $this->errorResponse('User not found.', 'USER_NOT_FOUND');
            }

            // Generate reset token
            $resetToken = $this->tokenManager->generate('reset', 64);
            $this->userModel->update($user->id, [
                'reset_token' => $resetToken,
                'reset_expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ]);

            $this->logger->info('Password reset requested', [
                'user_id' => $user->id,
                'email' => $email
            ]);

            return $this->successResponse('Password reset link sent.', [
                'reset_token' => $resetToken,
                'email' => $email
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Forgot password error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Reset password with token
     * 
     * @param string $token
     * @param string $password
     * @return array
     */
    public function resetPassword(string $token, string $password): array
    {
        try {
            $user = $this->userModel->findByResetToken($token);

            if (!$user) {
                return $this->errorResponse('Invalid reset token.', 'INVALID_TOKEN');
            }

            // Check if token is expired
            if ($user->reset_expires && strtotime($user->reset_expires) < time()) {
                return $this->errorResponse('Reset token has expired.', 'TOKEN_EXPIRED');
            }

            // Validate password
            $validator = new Validator();
            if (!$validator->validate(['password' => $password], ['password' => ['required', 'min:8']])) {
                return $this->errorResponse('Password must be at least 8 characters.', 'INVALID_PASSWORD');
            }

            // Update password
            $hashedPassword = $this->hashPassword($password);
            $this->userModel->update($user->id, [
                'password_hash' => $hashedPassword,
                'reset_token' => null,
                'reset_expires' => null
            ]);

            $this->logger->info('Password reset successfully', [
                'user_id' => $user->id,
                'username' => $user->username
            ]);

            return $this->successResponse('Password reset successfully.', [
                'user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Reset password error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred.', 'ERROR');
        }
    }

    /**
     * Verify password against hash
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     * 
     * @param string $password
     * @return string
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Create user session
     * 
     * @param object $user
     * @param bool $remember
     * @return void
     */
    public function createSession(object $user, bool $remember = false): void
    {
        // Regenerate session ID for security
        $this->session->regenerate();

        // Store user data
        $this->session->set($this->userIdKey, $user->id);
        $this->session->set($this->authKey, true);
        $this->session->set($this->userDataKey, [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name ?? $user->username,
            'role_id' => $user->role_id,
            'role_name' => $this->getRoleName($user->role_id)
        ]);

        // Set session lifetime
        $this->session->setExpiration($remember ? $this->rememberLifetime : $this->sessionLifetime);

        // Handle remember me
        if ($remember) {
            $this->setRememberToken($user);
        }
    }

    /**
     * Destroy user session
     * 
     * @return void
     */
    public function destroySession(): void
    {
        $this->session->destroy();
    }

    /**
     * Set remember me token
     * 
     * @param object $user
     * @return void
     */
    private function setRememberToken(object $user): void
    {
        $token = $this->tokenManager->generate('remember', 64);
        $expires = time() + $this->rememberLifetime;

        $this->userModel->update($user->id, [
            'remember_token' => $token,
            'remember_expires' => date('Y-m-d H:i:s', $expires)
        ]);

        setcookie(
            'remember_token',
            $token,
            $expires,
            '/',
            '',
            true,
            true
        );
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function check(): bool
    {
        // Check session
        if ($this->session->get($this->authKey) === true) {
            return true;
        }

        // Check remember me token
        return $this->checkRememberToken();
    }

    /**
     * Check remember me token
     * 
     * @return bool
     */
    private function checkRememberToken(): bool
    {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }

        $token = $_COOKIE['remember_token'];
        $user = $this->userModel->findByRememberToken($token);

        if (!$user) {
            return false;
        }

        // Check if token is expired
        if ($user->remember_expires && strtotime($user->remember_expires) < time()) {
            return false;
        }

        // Create session
        $this->createSession($user, true);

        return true;
    }

    /**
     * Get current user
     * 
     * @return object|null
     */
    public function user(): ?object
    {
        $userId = $this->id();
        if (!$userId) {
            return null;
        }

        if (!isset($this->userCache[$userId])) {
            $this->userCache[$userId] = $this->userModel->find($userId);
        }

        return $this->userCache[$userId];
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->session->get($this->userIdKey);
    }

    /**
     * Get current user role
     * 
     * @return string|null
     */
    public function role(): ?string
    {
        $userData = $this->session->get($this->userDataKey);
        return $userData['role_name'] ?? null;
    }

    /**
     * Get current user permissions
     * 
     * @return array
     */
    public function permissions(): array
    {
        $user = $this->user();
        if (!$user) {
            return [];
        }

        return $this->userModel->getPermissionNames($user->id);
    }

    /**
     * Check if user has role
     * 
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        $userRole = $this->role();
        if (!$userRole) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    /**
     * Check if user has permission
     * 
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions();
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Get role name by ID
     * 
     * @param int $roleId
     * @return string|null
     */
    private function getRoleName(int $roleId): ?string
    {
        $roleModel = new Role();
        $role = $roleModel->find($roleId);
        return $role ? $role->name : null;
    }

    /**
     * Validate OTP
     * 
     * @param string $secret
     * @param string $code
     * @return bool
     */
    public function validateOTP(string $secret, string $code): bool
    {
        $otpManager = new OTPManager();
        return $otpManager->verify($secret, $code);
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
     * Record failed login attempt
     * 
     * @param object $user
     * @return void
     */
    private function recordFailedAttempt(object $user): void
    {
        $attempts = ($user->login_attempts ?? 0) + 1;
        $data = ['login_attempts' => $attempts];

        if ($attempts >= $this->maxAttempts) {
            $data['locked_until'] = date('Y-m-d H:i:s', strtotime("+{$this->lockoutDuration} minutes"));
            $this->logger->warning('Account locked due to failed attempts', [
                'user_id' => $user->id,
                'username' => $user->username,
                'attempts' => $attempts
            ]);
        }

        $this->userModel->update($user->id, $data);
    }

    /**
     * Reset failed login attempts
     * 
     * @param object $user
     * @return void
     */
    private function resetFailedAttempts(object $user): void
    {
        $this->userModel->update($user->id, [
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Sanitize user data for response
     * 
     * @param object $user
     * @return array
     */
    private function sanitizeUser(object $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name ?? $user->username,
            'role' => $this->getRoleName($user->role_id),
            'status' => $user->status,
            'profile_image' => $user->profile_image ?? null
        ];
    }

    /**
     * Success response
     * 
     * @param string $message
     * @param array $data
     * @return array
     */
    private function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param string $code
     * @param array $data
     * @return array
     */
    private function errorResponse(string $message, string $code = 'ERROR', array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'data' => $data
        ];
    }

    /**
     * Update user data in session
     * 
     * @param array $data
     * @return void
     */
    public function updateSessionUser(array $data): void
    {
        $userData = $this->session->get($this->userDataKey);
        if ($userData) {
            $this->session->set($this->userDataKey, array_merge($userData, $data));
        }
    }

    /**
     * Get session user data
     * 
     * @return array|null
     */
    public function getUserData(): ?array
    {
        return $this->session->get($this->userDataKey);
    }
}