<?php
/**
 * Settings Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/settings
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the settings module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('SETTINGS_MODULE_NAME', 'System Settings');
define('SETTINGS_MODULE_VERSION', '1.0.0');
define('SETTINGS_MODULE_AUTHOR', 'GRC Platform Team');
define('SETTINGS_MODULE_DESCRIPTION', 'System configuration and settings management');

// ============================================================
// SETTINGS SECTIONS
// ============================================================

define('SETTINGS_SECTIONS', [
    'general' => [
        'label' => 'General Settings',
        'icon' => 'fa-cogs',
        'description' => 'Basic system settings',
        'permission' => 'settings_general'
    ],
    'company' => [
        'label' => 'Company Settings',
        'icon' => 'fa-building',
        'description' => 'Company profile and branding',
        'permission' => 'settings_company'
    ],
    'security' => [
        'label' => 'Security Settings',
        'icon' => 'fa-shield-alt',
        'description' => 'Security and authentication',
        'permission' => 'settings_security'
    ],
    'email' => [
        'label' => 'Email Settings',
        'icon' => 'fa-envelope',
        'description' => 'SMTP and email configuration',
        'permission' => 'settings_email'
    ],
    'api' => [
        'label' => 'API Settings',
        'icon' => 'fa-code',
        'description' => 'API keys and integrations',
        'permission' => 'settings_api'
    ],
    'backup' => [
        'label' => 'Backup Settings',
        'icon' => 'fa-database',
        'description' => 'Backup and restore configuration',
        'permission' => 'settings_backup'
    ],
    'ai' => [
        'label' => 'AI Settings',
        'icon' => 'fa-robot',
        'description' => 'AI service configuration',
        'permission' => 'settings_ai'
    ],
    'notifications' => [
        'label' => 'Notification Settings',
        'icon' => 'fa-bell',
        'description' => 'Notification preferences',
        'permission' => 'settings_notifications'
    ]
]);

// ============================================================
// SETTINGS DEFAULTS
// ============================================================

define('SETTINGS_DEFAULTS', [
    'general' => [
        'app_name' => 'AI Banking GRC Platform',
        'app_version' => '1.0.0',
        'app_timezone' => 'Asia/Karachi',
        'app_locale' => 'en',
        'maintenance_mode' => false,
        'maintenance_message' => 'System under maintenance. Please check back later.'
    ],
    'company' => [
        'company_name' => 'AI Banking GRC Solutions',
        'company_short_name' => 'GRCS',
        'company_registration' => '',
        'company_tax_id' => '',
        'company_address' => '',
        'company_city' => 'Karachi',
        'company_country' => 'Pakistan',
        'company_phone' => '',
        'company_email' => 'info@grc-platform.com',
        'company_website' => 'https://grc-platform.com',
        'company_logo' => ''
    ],
    'security' => [
        'session_lifetime' => 3600,
        'max_login_attempts' => 5,
        'lockout_duration' => 15,
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_lowercase' => true,
        'password_require_numbers' => true,
        'password_require_special' => true,
        'two_factor_enabled' => true,
        'remember_me_enabled' => true,
        'remember_me_lifetime' => 2592000
    ],
    'email' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls',
        'from_address' => 'noreply@grc-platform.com',
        'from_name' => 'AI Banking GRC Platform'
    ],
    'api' => [
        'api_enabled' => true,
        'rate_limit' => 100,
        'rate_limit_window' => 60,
        'api_version' => 'v1',
        'api_prefix' => '/api'
    ],
    'backup' => [
        'backup_enabled' => true,
        'backup_frequency' => 'daily',
        'backup_time' => '02:00',
        'backup_retention' => 30,
        'backup_location' => 'storage/backups'
    ],
    'ai' => [
        'ai_enabled' => true,
        'ai_provider' => 'openai',
        'ai_model' => 'gpt-4',
        'ai_api_key' => '',
        'ai_max_tokens' => 4096,
        'ai_temperature' => 0.7
    ],
    'notifications' => [
        'in_app_enabled' => true,
        'email_enabled' => true,
        'sms_enabled' => false,
        'push_enabled' => false,
        'retention_days' => 90
    ]
]);

// ============================================================
// PERMISSION SETTINGS
// ============================================================

define('SETTINGS_PERMISSIONS', [
    'settings_general' => 'Manage general settings',
    'settings_company' => 'Manage company settings',
    'settings_security' => 'Manage security settings',
    'settings_email' => 'Manage email settings',
    'settings_api' => 'Manage API settings',
    'settings_backup' => 'Manage backup settings',
    'settings_ai' => 'Manage AI settings',
    'settings_notifications' => 'Manage notification settings'
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get settings section label
 * 
 * @param string $section
 * @return string
 */
function get_settings_section_label(string $section): string
{
    $sections = SETTINGS_SECTIONS;
    return $sections[$section]['label'] ?? $section;
}

/**
 * Get settings default
 * 
 * @param string $section
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_settings_default(string $section, string $key, $default = null)
{
    $defaults = SETTINGS_DEFAULTS;
    return $defaults[$section][$key] ?? $default;
}

/**
 * Get all settings defaults for section
 * 
 * @param string $section
 * @return array
 */
function get_section_defaults(string $section): array
{
    $defaults = SETTINGS_DEFAULTS;
    return $defaults[$section] ?? [];
}