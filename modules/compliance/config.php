<?php
/**
 * Compliance Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/compliance
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the compliance module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('COMPLIANCE_MODULE_NAME', 'Compliance');
define('COMPLIANCE_MODULE_VERSION', '1.0.0');
define('COMPLIANCE_MODULE_AUTHOR', 'GRC Platform Team');
define('COMPLIANCE_MODULE_DESCRIPTION', 'Enterprise compliance management for banking regulations');

// ============================================================
// COMPLIANCE FRAMEWORKS
// ============================================================

define('COMPLIANCE_FRAMEWORKS', [
    'sbp_prudential' => [
        'name' => 'SBP Prudential Regulations',
        'version' => '2024',
        'authority' => 'State Bank of Pakistan',
        'description' => 'Prudential regulations for banking operations',
        'categories' => ['capital', 'risk', 'governance', 'disclosure']
    ],
    'sbp_circulars' => [
        'name' => 'SBP Circulars',
        'version' => '2024',
        'authority' => 'State Bank of Pakistan',
        'description' => 'Regulatory circulars from State Bank of Pakistan',
        'categories' => ['compliance', 'risk', 'operations', 'reporting']
    ],
    'iso27001' => [
        'name' => 'ISO 27001:2022',
        'version' => '2022',
        'authority' => 'ISO',
        'description' => 'Information Security Management System',
        'categories' => ['security', 'governance', 'risk', 'compliance']
    ],
    'nist_csf' => [
        'name' => 'NIST Cybersecurity Framework',
        'version' => '1.1',
        'authority' => 'NIST',
        'description' => 'Cybersecurity framework for critical infrastructure',
        'categories' => ['identify', 'protect', 'detect', 'respond', 'recover']
    ],
    'basel_iii' => [
        'name' => 'Basel III',
        'version' => '2024',
        'authority' => 'Basel Committee',
        'description' => 'Global regulatory standards on bank capital adequacy',
        'categories' => ['capital', 'liquidity', 'leverage', 'risk']
    ],
    'aml_cft' => [
        'name' => 'AML/CFT Regulations',
        'version' => '2024',
        'authority' => 'SBP / FATF',
        'description' => 'Anti-Money Laundering and Counter-Financing of Terrorism',
        'categories' => ['kyc', 'reporting', 'screening', 'monitoring']
    ]
]);

// ============================================================
// COMPLIANCE LEVELS
// ============================================================

define('COMPLIANCE_LEVELS', [
    'compliant' => [
        'label' => 'Compliant',
        'score_min' => 80,
        'color' => '#22C55E',
        'icon' => 'fa-check-circle',
        'description' => 'Meets all compliance requirements'
    ],
    'partial' => [
        'label' => 'Partially Compliant',
        'score_min' => 60,
        'color' => '#F59E0B',
        'icon' => 'fa-exclamation-triangle',
        'description' => 'Partially meets requirements, gaps identified'
    ],
    'non_compliant' => [
        'label' => 'Non-Compliant',
        'score_min' => 0,
        'color' => '#EF4444',
        'icon' => 'fa-times-circle',
        'description' => 'Does not meet requirements'
    ]
]);

// ============================================================
// COMPLIANCE THRESHOLDS
// ============================================================

define('COMPLIANCE_THRESHOLDS', [
    'critical' => [
        'score' => 80,
        'status' => 'compliant',
        'action' => 'maintain'
    ],
    'warning' => [
        'score' => 60,
        'status' => 'partial',
        'action' => 'review'
    ],
    'danger' => [
        'score' => 40,
        'status' => 'non_compliant',
        'action' => 'immediate_action'
    ]
]);

// ============================================================
// COMPLIANCE CATEGORIES
// ============================================================

define('COMPLIANCE_CATEGORIES', [
    'regulatory' => 'Regulatory Compliance',
    'operational' => 'Operational Compliance',
    'information_security' => 'Information Security',
    'data_privacy' => 'Data Privacy',
    'risk_management' => 'Risk Management',
    'governance' => 'Governance',
    'internal_controls' => 'Internal Controls',
    'external_audit' => 'External Audit'
]);

// ============================================================
// SBP CIRCULAR CATEGORIES
// ============================================================

define('SBP_CIRCULAR_CATEGORIES', [
    'prudential' => 'Prudential Regulations',
    'operational' => 'Operational Guidelines',
    'compliance' => 'Compliance Requirements',
    'risk' => 'Risk Management',
    'governance' => 'Corporate Governance',
    'reporting' => 'Reporting Requirements',
    'aml' => 'AML/CFT',
    'consumer' => 'Consumer Protection'
]);

// ============================================================
// COMPLIANCE STATUS
// ============================================================

define('COMPLIANCE_STATUS', [
    'pending' => 'Pending Review',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'overdue' => 'Overdue',
    'rejected' => 'Rejected',
    'approved' => 'Approved',
    'review' => 'Under Review'
]);

// ============================================================
// EVIDENCE TYPES
// ============================================================

define('EVIDENCE_TYPES', [
    'document' => 'Document',
    'screenshot' => 'Screenshot',
    'recording' => 'Recording',
    'certificate' => 'Certificate',
    'report' => 'Report',
    'policy' => 'Policy',
    'procedure' => 'Procedure',
    'other' => 'Other'
]);

// ============================================================
// COMPLIANCE SETTINGS
// ============================================================

define('COMPLIANCE_SETTINGS', [
    'auto_reminder_days' => 7,
    'overdue_threshold_days' => 5,
    'review_interval_days' => 90,
    'evidence_retention_days' => 365,
    'max_evidence_size' => 10, // MB
    'allowed_evidence_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt']
]);

// ============================================================
// ALERT CONFIGURATION
// ============================================================

define('COMPLIANCE_ALERTS', [
    'overdue' => [
        'enabled' => true,
        'channels' => ['email', 'notification'],
        'threshold' => 5 // days
    ],
    'critical_gap' => [
        'enabled' => true,
        'channels' => ['email', 'notification', 'sms'],
        'threshold' => 1 // high priority
    ],
    'upcoming_deadline' => [
        'enabled' => true,
        'channels' => ['email', 'notification'],
        'threshold' => 7 // days
    ],
    'status_change' => [
        'enabled' => true,
        'channels' => ['notification'],
        'threshold' => 0
    ]
]);

// ============================================================
// COMPLIANCE SCORING
// ============================================================

define('COMPLIANCE_SCORING', [
    'weight_factors' => [
        'regulatory' => 0.35,
        'operational' => 0.25,
        'information_security' => 0.20,
        'risk_management' => 0.10,
        'governance' => 0.10
    ],
    'calculation_method' => 'weighted_average',
    'minimum_score' => 0,
    'maximum_score' => 100
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('COMPLIANCE_NOTIFICATIONS', [
    'task_assigned' => 'You have been assigned a compliance task: {title}',
    'task_reminder' => 'Reminder: Compliance task "{title}" is due in {days} days',
    'task_overdue' => 'Compliance task "{title}" is overdue',
    'task_completed' => 'Compliance task "{title}" has been completed',
    'circular_uploaded' => 'New SBP Circular uploaded: {title}',
    'gap_identified' => 'Compliance gap identified: {description}',
    'recommendation' => 'New compliance recommendation: {title}'
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get compliance framework configuration
 * 
 * @param string $framework Framework key
 * @return array|null
 */
function compliance_framework_config(string $framework): ?array
{
    $frameworks = COMPLIANCE_FRAMEWORKS;
    return $frameworks[$framework] ?? null;
}

/**
 * Get compliance level by score
 * 
 * @param float $score
 * @return string
 */
function get_compliance_level(float $score): string
{
    $levels = COMPLIANCE_LEVELS;
    
    foreach ($levels as $key => $level) {
        if ($score >= $level['score_min']) {
            return $key;
        }
    }
    
    return 'non_compliant';
}

/**
 * Get compliance level label
 * 
 * @param string $level
 * @return string
 */
function get_compliance_level_label(string $level): string
{
    $levels = COMPLIANCE_LEVELS;
    return $levels[$level]['label'] ?? $level;
}

/**
 * Get compliance status label
 * 
 * @param string $status
 * @return string
 */
function get_compliance_status_label(string $status): string
{
    $statuses = COMPLIANCE_STATUS;
    return $statuses[$status] ?? $status;
}

/**
 * Get compliance setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function compliance_setting(string $key, $default = null)
{
    $settings = COMPLIANCE_SETTINGS;
    return $settings[$key] ?? $default;
}