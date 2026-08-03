<?php
/**
 * Audit Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/audit
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the audit module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('AUDIT_MODULE_NAME', 'Audit Management');
define('AUDIT_MODULE_VERSION', '1.0.0');
define('AUDIT_MODULE_AUTHOR', 'GRC Platform Team');
define('AUDIT_MODULE_DESCRIPTION', 'Enterprise audit management for banking operations');

// ============================================================
// AUDIT TYPES
// ============================================================

define('AUDIT_TYPES', [
    'internal' => [
        'label' => 'Internal Audit',
        'description' => 'Internal audit of processes and controls',
        'icon' => 'fa-building',
        'color' => '#2563EB'
    ],
    'external' => [
        'label' => 'External Audit',
        'description' => 'External audit by third-party firms',
        'icon' => 'fa-user-tie',
        'color' => '#8B5CF6'
    ],
    'compliance' => [
        'label' => 'Compliance Audit',
        'description' => 'Audit for regulatory compliance',
        'icon' => 'fa-gavel',
        'color' => '#22C55E'
    ],
    'it' => [
        'label' => 'IT Audit',
        'description' => 'Information technology systems audit',
        'icon' => 'fa-server',
        'color' => '#3B82F6'
    ],
    'operational' => [
        'label' => 'Operational Audit',
        'description' => 'Operational processes and procedures audit',
        'icon' => 'fa-cogs',
        'color' => '#F59E0B'
    ],
    'financial' => [
        'label' => 'Financial Audit',
        'description' => 'Financial statements and controls audit',
        'icon' => 'fa-coins',
        'color' => '#22C55E'
    ],
    'forensic' => [
        'label' => 'Forensic Audit',
        'description' => 'Forensic investigation and fraud audit',
        'icon' => 'fa-search',
        'color' => '#EF4444'
    ]
]);

// ============================================================
// AUDIT STATUS
// ============================================================

define('AUDIT_STATUS', [
    'planned' => [
        'label' => 'Planned',
        'color' => '#3B82F6',
        'icon' => 'fa-calendar-plus'
    ],
    'scheduled' => [
        'label' => 'Scheduled',
        'color' => '#8B5CF6',
        'icon' => 'fa-calendar-check'
    ],
    'in_progress' => [
        'label' => 'In Progress',
        'color' => '#F59E0B',
        'icon' => 'fa-spinner'
    ],
    'review' => [
        'label' => 'Under Review',
        'color' => '#EC4899',
        'icon' => 'fa-eye'
    ],
    'completed' => [
        'label' => 'Completed',
        'color' => '#22C55E',
        'icon' => 'fa-check-circle'
    ],
    'closed' => [
        'label' => 'Closed',
        'color' => '#64748B',
        'icon' => 'fa-check-double'
    ],
    'cancelled' => [
        'label' => 'Cancelled',
        'color' => '#EF4444',
        'icon' => 'fa-times-circle'
    ]
]);

// ============================================================
// AUDIT PRIORITY
// ============================================================

define('AUDIT_PRIORITY', [
    'critical' => [
        'label' => 'Critical',
        'color' => '#DC2626',
        'icon' => 'fa-exclamation-circle',
        'level' => 1
    ],
    'high' => [
        'label' => 'High',
        'color' => '#EF4444',
        'icon' => 'fa-exclamation-triangle',
        'level' => 2
    ],
    'medium' => [
        'label' => 'Medium',
        'color' => '#F59E0B',
        'icon' => 'fa-shield',
        'level' => 3
    ],
    'low' => [
        'label' => 'Low',
        'color' => '#22C55E',
        'icon' => 'fa-info-circle',
        'level' => 4
    ]
]);

// ============================================================
// AUDIT FINDING SEVERITY
// ============================================================

define('AUDIT_FINDING_SEVERITY', [
    'critical' => [
        'label' => 'Critical',
        'color' => '#DC2626',
        'icon' => 'fa-exclamation-circle',
        'max_days' => 7,
        'escalate' => true
    ],
    'high' => [
        'label' => 'High',
        'color' => '#EF4444',
        'icon' => 'fa-exclamation-triangle',
        'max_days' => 14,
        'escalate' => true
    ],
    'medium' => [
        'label' => 'Medium',
        'color' => '#F59E0B',
        'icon' => 'fa-shield',
        'max_days' => 30,
        'escalate' => false
    ],
    'low' => [
        'label' => 'Low',
        'color' => '#22C55E',
        'icon' => 'fa-info-circle',
        'max_days' => 60,
        'escalate' => false
    ]
]);

// ============================================================
// AUDIT FINDING STATUS
// ============================================================

define('AUDIT_FINDING_STATUS', [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'resolved' => 'Resolved',
    'verified' => 'Verified',
    'closed' => 'Closed',
    'accepted_risk' => 'Accepted Risk'
]);

// ============================================================
// AUDIT SETTINGS
// ============================================================

define('AUDIT_SETTINGS', [
    'evidence_upload_enabled' => true,
    'max_evidence_size' => 20, // MB
    'allowed_evidence_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4', 'zip'],
    'evidence_retention_days' => 1095, // 3 years
    'finding_resolution_days' => 30,
    'audit_planning_days' => 30,
    'auto_reminder_days' => 7,
    'max_auditors_per_audit' => 10
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('AUDIT_NOTIFICATIONS', [
    'audit_created' => 'New audit created: {title}',
    'audit_assigned' => 'You have been assigned to audit: {title}',
    'audit_started' => 'Audit started: {title}',
    'audit_completed' => 'Audit completed: {title}',
    'finding_created' => 'New finding in audit: {title}',
    'finding_assigned' => 'You have been assigned to finding: {title}',
    'finding_resolved' => 'Finding resolved in audit: {title}',
    'due_soon' => 'Audit "{title}" due in {days} days'
]);

// ============================================================
// AUDIT FREQUENCY
// ============================================================

define('AUDIT_FREQUENCY', [
    'annual' => 'Annual',
    'semi_annual' => 'Semi-Annual',
    'quarterly' => 'Quarterly',
    'monthly' => 'Monthly',
    'adhoc' => 'Ad-hoc'
]);

// ============================================================
// EVIDENCE TYPES
// ============================================================

define('EVIDENCE_TYPES', [
    'document' => 'Document',
    'screenshot' => 'Screenshot',
    'video' => 'Video Recording',
    'audio' => 'Audio Recording',
    'certificate' => 'Certificate',
    'report' => 'Report',
    'log' => 'Log File',
    'test_result' => 'Test Result',
    'interview' => 'Interview Notes',
    'other' => 'Other'
]);

// ============================================================
// AI AUDIT SETTINGS
// ============================================================

define('AI_AUDIT_SETTINGS', [
    'enabled' => true,
    'recommendation_enabled' => true,
    'finding_analysis_enabled' => true,
    'summary_generation_enabled' => true,
    'compliance_verification_enabled' => true,
    'max_recommendations' => 5,
    'confidence_threshold' => 70
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get audit type label
 * 
 * @param string $type
 * @return string
 */
function get_audit_type_label(string $type): string
{
    $types = AUDIT_TYPES;
    return $types[$type]['label'] ?? $type;
}

/**
 * Get audit status label
 * 
 * @param string $status
 * @return string
 */
function get_audit_status_label(string $status): string
{
    $statuses = AUDIT_STATUS;
    return $statuses[$status]['label'] ?? $status;
}

/**
 * Get audit priority label
 * 
 * @param string $priority
 * @return string
 */
function get_audit_priority_label(string $priority): string
{
    $priorities = AUDIT_PRIORITY;
    return $priorities[$priority]['label'] ?? $priority;
}

/**
 * Get finding severity label
 * 
 * @param string $severity
 * @return string
 */
function get_finding_severity_label(string $severity): string
{
    $severities = AUDIT_FINDING_SEVERITY;
    return $severities[$severity]['label'] ?? $severity;
}

/**
 * Get audit setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function audit_setting(string $key, $default = null)
{
    $settings = AUDIT_SETTINGS;
    return $settings[$key] ?? $default;
}