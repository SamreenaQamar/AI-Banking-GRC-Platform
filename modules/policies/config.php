<?php
/**
 * Policies Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/policies
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the policies module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('POLICIES_MODULE_NAME', 'Policy Management');
define('POLICIES_MODULE_VERSION', '1.0.0');
define('POLICIES_MODULE_AUTHOR', 'GRC Platform Team');
define('POLICIES_MODULE_DESCRIPTION', 'Enterprise policy management for banking compliance');

// ============================================================
// POLICY CATEGORIES
// ============================================================

define('POLICY_CATEGORIES', [
    'governance' => [
        'label' => 'Corporate Governance',
        'description' => 'Policies related to corporate governance',
        'icon' => 'fa-building',
        'color' => '#0B3D91'
    ],
    'risk_management' => [
        'label' => 'Risk Management',
        'description' => 'Risk management framework and policies',
        'icon' => 'fa-shield-alt',
        'color' => '#EF4444'
    ],
    'compliance' => [
        'label' => 'Compliance',
        'description' => 'Regulatory compliance policies',
        'icon' => 'fa-gavel',
        'color' => '#2563EB'
    ],
    'information_security' => [
        'label' => 'Information Security',
        'description' => 'Information security policies and standards',
        'icon' => 'fa-lock',
        'color' => '#8B5CF6'
    ],
    'data_privacy' => [
        'label' => 'Data Privacy',
        'description' => 'Data protection and privacy policies',
        'icon' => 'fa-user-secret',
        'color' => '#EC4899'
    ],
    'human_resources' => [
        'label' => 'Human Resources',
        'description' => 'HR policies and procedures',
        'icon' => 'fa-users',
        'color' => '#3B82F6'
    ],
    'finance' => [
        'label' => 'Finance & Accounting',
        'description' => 'Financial policies and controls',
        'icon' => 'fa-coins',
        'color' => '#22C55E'
    ],
    'operations' => [
        'label' => 'Operations',
        'description' => 'Operational policies and procedures',
        'icon' => 'fa-cogs',
        'color' => '#F59E0B'
    ],
    'it' => [
        'label' => 'Information Technology',
        'description' => 'IT policies and standards',
        'icon' => 'fa-server',
        'color' => '#3B82F6'
    ],
    'business_continuity' => [
        'label' => 'Business Continuity',
        'description' => 'BCP and disaster recovery policies',
        'icon' => 'fa-recycle',
        'color' => '#10B981'
    ],
    'aml' => [
        'label' => 'Anti-Money Laundering',
        'description' => 'AML and KYC policies',
        'icon' => 'fa-money-bill-wave',
        'color' => '#EF4444'
    ],
    'fraud' => [
        'label' => 'Fraud Prevention',
        'description' => 'Fraud prevention and detection policies',
        'icon' => 'fa-fingerprint',
        'color' => '#DC2626'
    ]
]);

// ============================================================
// POLICY STATUS
// ============================================================

define('POLICY_STATUS', [
    'draft' => [
        'label' => 'Draft',
        'color' => '#94A3B8',
        'icon' => 'fa-file',
        'editable' => true
    ],
    'review' => [
        'label' => 'Under Review',
        'color' => '#F59E0B',
        'icon' => 'fa-eye',
        'editable' => false
    ],
    'approved' => [
        'label' => 'Approved',
        'color' => '#3B82F6',
        'icon' => 'fa-check-circle',
        'editable' => false
    ],
    'active' => [
        'label' => 'Active',
        'color' => '#22C55E',
        'icon' => 'fa-check-double',
        'editable' => false
    ],
    'archived' => [
        'label' => 'Archived',
        'color' => '#64748B',
        'icon' => 'fa-archive',
        'editable' => false
    ],
    'expired' => [
        'label' => 'Expired',
        'color' => '#EF4444',
        'icon' => 'fa-clock',
        'editable' => false
    ]
]);

// ============================================================
// POLICY SETTINGS
// ============================================================

define('POLICY_SETTINGS', [
    'version_control_enabled' => true,
    'auto_version_increment' => true,
    'require_approval' => true,
    'require_acknowledgement' => true,
    'max_file_size' => 10, // MB
    'allowed_file_types' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
    'retention_days' => 2555, // 7 years
    'review_interval_days' => 365, // 1 year
    'auto_archive_days' => 730, // 2 years
    'max_versions' => 10
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('POLICY_NOTIFICATIONS', [
    'created' => 'New policy created: {title}',
    'updated' => 'Policy updated: {title}',
    'approved' => 'Policy approved: {title}',
    'published' => 'Policy published: {title}',
    'review_due' => 'Policy review due: {title}',
    'expired' => 'Policy expired: {title}',
    'acknowledgement_required' => 'Policy acknowledgement required: {title}'
]);

// ============================================================
// PERMISSION SETTINGS
// ============================================================

define('POLICY_PERMISSIONS', [
    'policy_create' => 'Create new policies',
    'policy_view' => 'View policies',
    'policy_update' => 'Update policies',
    'policy_delete' => 'Delete policies',
    'policy_approve' => 'Approve policies',
    'policy_publish' => 'Publish policies',
    'policy_archive' => 'Archive policies',
    'policy_acknowledge' => 'Acknowledge policies'
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get policy category label
 * 
 * @param string $category
 * @return string
 */
function get_policy_category_label(string $category): string
{
    $categories = POLICY_CATEGORIES;
    return $categories[$category]['label'] ?? $category;
}

/**
 * Get policy status label
 * 
 * @param string $status
 * @return string
 */
function get_policy_status_label(string $status): string
{
    $statuses = POLICY_STATUS;
    return $statuses[$status]['label'] ?? $status;
}

/**
 * Get policy status color
 * 
 * @param string $status
 * @return string
 */
function get_policy_status_color(string $status): string
{
    $statuses = POLICY_STATUS;
    return $statuses[$status]['color'] ?? '#64748B';
}

/**
 * Get policy setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function policy_setting(string $key, $default = null)
{
    $settings = POLICY_SETTINGS;
    return $settings[$key] ?? $default;
}