<?php
/**
 * Users Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/users
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the users module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('USERS_MODULE_NAME', 'User Management');
define('USERS_MODULE_VERSION', '1.0.0');
define('USERS_MODULE_AUTHOR', 'GRC Platform Team');
define('USERS_MODULE_DESCRIPTION', 'User management and access control');

// ============================================================
// USER ROLES
// ============================================================

define('USER_ROLES', [
    'super_admin' => [
        'label' => 'Super Administrator',
        'level' => 10,
        'color' => '#DC2626',
        'icon' => 'fa-crown',
        'description' => 'Full system access with all privileges'
    ],
    'admin' => [
        'label' => 'Administrator',
        'level' => 8,
        'color' => '#EF4444',
        'icon' => 'fa-user-tie',
        'description' => 'Administrative access with user management'
    ],
    'compliance_officer' => [
        'label' => 'Compliance Officer',
        'level' => 6,
        'color' => '#2563EB',
        'icon' => 'fa-check-circle',
        'description' => 'Manage compliance tasks and regulations'
    ],
    'risk_manager' => [
        'label' => 'Risk Manager',
        'level' => 6,
        'color' => '#F59E0B',
        'icon' => 'fa-shield-alt',
        'description' => 'Manage risk assessments and register'
    ],
    'internal_auditor' => [
        'label' => 'Internal Auditor',
        'level' => 5,
        'color' => '#8B5CF6',
        'icon' => 'fa-clipboard-list',
        'description' => 'Conduct internal audits'
    ],
    'department_head' => [
        'label' => 'Department Head',
        'level' => 4,
        'color' => '#3B82F6',
        'icon' => 'fa-building',
        'description' => 'Manage department compliance'
    ],
    'user' => [
        'label' => 'Standard User',
        'level' => 2,
        'color' => '#64748B',
        'icon' => 'fa-user',
        'description' => 'Regular user access'
    ]
]);

// ============================================================
// USER STATUS
// ============================================================

define('USER_STATUS', [
    'active' => [
        'label' => 'Active',
        'color' => '#22C55E',
        'icon' => 'fa-check-circle'
    ],
    'inactive' => [
        'label' => 'Inactive',
        'color' => '#94A3B8',
        'icon' => 'fa-times-circle'
    ],
    'suspended' => [
        'label' => 'Suspended',
        'color' => '#EF4444',
        'icon' => 'fa-ban'
    ],
    'pending' => [
        'label' => 'Pending',
        'color' => '#F59E0B',
        'icon' => 'fa-clock'
    ]
]);

// ============================================================
// USER SETTINGS
// ============================================================

define('USER_SETTINGS', [
    'registration_enabled' => true,
    'email_verification_required' => true,
    'default_role' => 'user',
    'default_status' => 'pending',
    'profile_image_max_size' => 2, // MB
    'profile_image_allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'password_history' => 5,
    'session_timeout' => 3600,
    'max_login_attempts' => 5,
    'lockout_duration' => 15 // minutes
]);

// ============================================================
// PERMISSION DEFINITIONS
// ============================================================

define('USER_PERMISSIONS', [
    // User Management
    'user_create' => 'Create new users',
    'user_view' => 'View user profiles',
    'user_update' => 'Update user information',
    'user_delete' => 'Delete users',
    'user_role_assign' => 'Assign roles to users',
    
    // Profile
    'profile_view' => 'View own profile',
    'profile_update' => 'Update own profile',
    'profile_password' => 'Change own password',
    
    // Roles & Permissions
    'role_view' => 'View roles',
    'role_create' => 'Create roles',
    'role_update' => 'Update roles',
    'role_delete' => 'Delete roles',
    
    // Activity
    'activity_view' => 'View user activity logs'
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('USER_NOTIFICATIONS', [
    'user_created' => 'New user account created: {username}',
    'user_updated' => 'User account updated: {username}',
    'user_deleted' => 'User account deleted: {username}',
    'user_suspended' => 'User account suspended: {username}',
    'user_activated' => 'User account activated: {username}',
    'password_changed' => 'Password changed for user: {username}',
    'role_assigned' => 'Role assigned to user: {username}'
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get user role label
 * 
 * @param string $role
 * @return string
 */
function get_user_role_label(string $role): string
{
    $roles = USER_ROLES;
    return $roles[$role]['label'] ?? $role;
}

/**
 * Get user role level
 * 
 * @param string $role
 * @return int
 */
function get_user_role_level(string $role): int
{
    $roles = USER_ROLES;
    return $roles[$role]['level'] ?? 0;
}

/**
 * Get user status label
 * 
 * @param string $status
 * @return string
 */
function get_user_status_label(string $status): string
{
    $statuses = USER_STATUS;
    return $statuses[$status]['label'] ?? $status;
}

/**
 * Get user setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function user_setting(string $key, $default = null)
{
    $settings = USER_SETTINGS;
    return $settings[$key] ?? $default;
}