<?php
/**
 * AI Banking GRC Platform - Authentication Helper
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage app/Helpers
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This helper provides authentication-related functionality:
 * - Login/Logout management
 * - User session handling
 * - Role and permission checking
 * - User data retrieval
 * - Remember me functionality
 */

declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use App\Models\Role;
use App\Helpers\SessionHelper;
use App\Helpers\SecurityHelper;

class AuthHelper
{
    /**
     * @var User|null Current authenticated user
     */
    private static ?User $currentUser = null;

    /**
     * @var array User roles cache
     */
    private static array $roleCache = [];

    /**
     * @var array User permissions cache
     */
    private static array $permissionCache = [];

    /**
     * Attempt to login a user
     * 
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return bool
     */
    public static function login(string $username, string $password, bool $remember = false): bool
    {
        try {
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);

            if (!$user) {
                return false;
            }

            // Check if account is active
            if ($user->status !== 'active') {
                return false;
            }

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user in session
            SessionHelper::set('user_id', $user->id);
            SessionHelper::set('username', $user->username);
            SessionHelper::set('user_role', $user->role_id);
            SessionHelper::set('authenticated', true);
            SessionHelper::set('user_name', $user->full_name ?? $user->username);
            SessionHelper::set('user_email', $user->email);

            // Load user permissions
            $permissions = $userModel->getPermissions($user->id);
            $permissionNames = array_map(function($perm) {
                return $perm->name;
            }, $permissions);
            SessionHelper::set('user_permissions', $permissionNames);

            // Cache current user
            self::$currentUser = $user;

            // Handle remember me
            if ($remember) {
                self::rememberUser($user->id);
            }

            return true;

        } catch (\Exception $e) {
            error_log("Login failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logout current user
     * 
     * @return void
     */
    public static function logout(): void
    {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Clear session
        SessionHelper::destroy();

        // Clear cached user
        self::$currentUser = null;
        self::$roleCache = [];
        self::$permissionCache = [];

        // Regenerate session ID
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public static function check(): bool
    {
        if (SessionHelper::has('authenticated') && SessionHelper::get('authenticated') === true) {
            return true;
        }

        // Check remember me token
        if (isset($_COOKIE['remember_token'])) {
            return self::rememberLogin();
        }

        return false;
    }

    /**
     * Get current authenticated user
     * 
     * @return User|null
     */
    public static function user(): ?User
    {
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        $userId = SessionHelper::get('user_id');
        if (!$userId) {
            return null;
        }

        $userModel = new User();
        self::$currentUser = $userModel->find((int)$userId);

        return self::$currentUser;
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public static function id(): ?int
    {
        return SessionHelper::get('user_id');
    }

    /**
     * Check if user has a specific role
     * 
     * @param string|array $roles
     * @return bool
     */
    public static function hasRole($roles): bool
    {
        if (!self::check()) {
            return false;
        }

        $user = self::user();
        if (!$user) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($user->role_id, $roles);
        }

        return $user->role_id === $roles;
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        $permissions = SessionHelper::get('user_permissions', []);
        
        // Super admin has all permissions
        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }

    /**
     * Check if user is an admin
     * 
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return self::hasRole(['admin', 'super_admin']);
    }

    /**
     * Check if user is an auditor
     * 
     * @return bool
     */
    public static function isAuditor(): bool
    {
        return self::hasRole('internal_auditor');
    }

    /**
     * Check if user is a risk manager
     * 
     * @return bool
     */
    public static function isRiskManager(): bool
    {
        return self::hasRole('risk_manager');
    }

    /**
     * Check if user is a compliance officer
     * 
     * @return bool
     */
    public static function isComplianceOfficer(): bool
    {
        return self::hasRole('compliance_officer');
    }

    /**
     * Handle remember me login
     * 
     * @return bool
     */
    private static function rememberLogin(): bool
    {
        $token = $_COOKIE['remember_token'] ?? '';
        if (empty($token)) {
            return false;
        }

        $userModel = new User();
        $user = $userModel->findByRememberToken($token);

        if (!$user) {
            return false;
        }

        // Login the user
        SessionHelper::set('user_id', $user->id);
        SessionHelper::set('username', $user->username);
        SessionHelper::set('authenticated', true);
        SessionHelper::set('user_name', $user->full_name ?? $user->username);
        SessionHelper::set('user_email', $user->email);

        self::$currentUser = $user;

        return true;
    }

    /**
     * Remember user with cookie
     * 
     * @param int $userId
     * @return void
     */
    private static function rememberUser(int $userId): void
    {
        $token = SecurityHelper::generateRandomString(64);
        $expiry = time() + 2592000; // 30 days

        $userModel = new User();
        $userModel->update($userId, ['remember_token' => $token]);

        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }

    /**
     * Destroy user session
     * 
     * @return void
     */
    public static function destroySession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }

        self::$currentUser = null;
        self::$roleCache = [];
        self::$permissionCache = [];
    }

    /**
     * Get user role name
     * 
     * @return string|null
     */
    public static function getRoleName(): ?string
    {
        $user = self::user();
        if (!$user) {
            return null;
        }

        $roleModel = new Role();
        $role = $roleModel->find($user->role_id);

        return $role ? $role->display_name : null;
    }

    /**
     * Get user permissions as array
     * 
     * @return array
     */
    public static function getPermissions(): array
    {
        return SessionHelper::get('user_permissions', []);
    }
}