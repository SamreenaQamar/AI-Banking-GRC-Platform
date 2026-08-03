<?php
/**
 * Notifications Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/notifications
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the notifications module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('NOTIFICATIONS_MODULE_NAME', 'Notifications');
define('NOTIFICATIONS_MODULE_VERSION', '1.0.0');
define('NOTIFICATIONS_MODULE_AUTHOR', 'GRC Platform Team');
define('NOTIFICATIONS_MODULE_DESCRIPTION', 'Enterprise notification and alert management');

// ============================================================
// NOTIFICATION TYPES
// ============================================================

define('NOTIFICATION_TYPES', [
    'compliance' => [
        'label' => 'Compliance Alert',
        'icon' => 'fa-check-circle',
        'color' => '#2563EB',
        'priority' => 3
    ],
    'risk' => [
        'label' => 'Risk Alert',
        'icon' => 'fa-exclamation-triangle',
        'color' => '#EF4444',
        'priority' => 1
    ],
    'audit' => [
        'label' => 'Audit Notification',
        'icon' => 'fa-clipboard-list',
        'color' => '#8B5CF6',
        'priority' => 2
    ],
    'policy' => [
        'label' => 'Policy Update',
        'icon' => 'fa-file-contract',
        'color' => '#3B82F6',
        'priority' => 3
    ],
    'system' => [
        'label' => 'System Notification',
        'icon' => 'fa-server',
        'color' => '#64748B',
        'priority' => 4
    ],
    'task' => [
        'label' => 'Task Assignment',
        'icon' => 'fa-tasks',
        'color' => '#F59E0B',
        'priority' => 2
    ],
    'reminder' => [
        'label' => 'Reminder',
        'icon' => 'fa-clock',
        'color' => '#22C55E',
        'priority' => 3
    ],
    'alert' => [
        'label' => 'Alert',
        'icon' => 'fa-bell',
        'color' => '#EF4444',
        'priority' => 1
    ]
]);

// ============================================================
// NOTIFICATION CHANNELS
// ============================================================

define('NOTIFICATION_CHANNELS', [
    'in_app' => [
        'label' => 'In-App',
        'enabled' => true,
        'icon' => 'fa-bell'
    ],
    'email' => [
        'label' => 'Email',
        'enabled' => true,
        'icon' => 'fa-envelope'
    ],
    'sms' => [
        'label' => 'SMS',
        'enabled' => false,
        'icon' => 'fa-sms'
    ],
    'push' => [
        'label' => 'Push Notification',
        'enabled' => false,
        'icon' => 'fa-mobile-alt'
    ]
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('NOTIFICATION_SETTINGS', [
    'in_app_enabled' => true,
    'email_enabled' => true,
    'sms_enabled' => false,
    'push_enabled' => false,
    'retention_days' => 90,
    'max_notifications' => 1000,
    'batch_size' => 50,
    'email_queue_enabled' => true,
    'email_queue_batch' => 100,
    'reminder_check_interval' => 3600,
    'auto_mark_read' => false,
    'read_timeout' => 7 // days
]);

// ============================================================
// NOTIFICATION PRIORITIES
// ============================================================

define('NOTIFICATION_PRIORITIES', [
    'critical' => [
        'label' => 'Critical',
        'level' => 1,
        'color' => '#DC2626',
        'channels' => ['in_app', 'email', 'sms']
    ],
    'high' => [
        'label' => 'High',
        'level' => 2,
        'color' => '#EF4444',
        'channels' => ['in_app', 'email']
    ],
    'medium' => [
        'label' => 'Medium',
        'level' => 3,
        'color' => '#F59E0B',
        'channels' => ['in_app', 'email']
    ],
    'low' => [
        'label' => 'Low',
        'level' => 4,
        'color' => '#22C55E',
        'channels' => ['in_app']
    ]
]);

// ============================================================
// REMINDER SETTINGS
// ============================================================

define('REMINDER_SETTINGS', [
    'default_frequency' => 'daily',
    'frequencies' => [
        'once' => 'Once',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly'
    ],
    'advance_notice' => [
        '1_day' => '1 Day Before',
        '2_days' => '2 Days Before',
        '3_days' => '3 Days Before',
        '1_week' => '1 Week Before'
    ]
]);

// ============================================================
// NOTIFICATION TEMPLATES
// ============================================================

define('NOTIFICATION_TEMPLATES', [
    'compliance_task_overdue' => [
        'subject' => 'Compliance Task Overdue',
        'body' => 'Your compliance task "{task_title}" is overdue by {days} days.'
    ],
    'risk_identified' => [
        'subject' => 'New Risk Identified',
        'body' => 'A new risk "{risk_title}" has been identified with {risk_level} severity.'
    ],
    'audit_finding' => [
        'subject' => 'Audit Finding Assigned',
        'body' => 'You have been assigned to audit finding "{finding_title}" with {severity} severity.'
    ],
    'policy_updated' => [
        'subject' => 'Policy Updated',
        'body' => 'Policy "{policy_title}" has been updated to version {version}.'
    ],
    'reminder' => [
        'subject' => 'Reminder: {title}',
        'body' => 'This is a reminder for: {description}'
    ]
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get notification type label
 * 
 * @param string $type
 * @return string
 */
function get_notification_type_label(string $type): string
{
    $types = NOTIFICATION_TYPES;
    return $types[$type]['label'] ?? $type;
}

/**
 * Get notification type color
 * 
 * @param string $type
 * @return string
 */
function get_notification_type_color(string $type): string
{
    $types = NOTIFICATION_TYPES;
    return $types[$type]['color'] ?? '#64748B';
}

/**
 * Get notification priority label
 * 
 * @param string $priority
 * @return string
 */
function get_notification_priority_label(string $priority): string
{
    $priorities = NOTIFICATION_PRIORITIES;
    return $priorities[$priority]['label'] ?? $priority;
}

/**
 * Get notification setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function notification_setting(string $key, $default = null)
{
    $settings = NOTIFICATION_SETTINGS;
    return $settings[$key] ?? $default;
}