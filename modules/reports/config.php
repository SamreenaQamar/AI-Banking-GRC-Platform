<?php
/**
 * Reports Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/reports
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the reports module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('REPORTS_MODULE_NAME', 'Reports');
define('REPORTS_MODULE_VERSION', '1.0.0');
define('REPORTS_MODULE_AUTHOR', 'GRC Platform Team');
define('REPORTS_MODULE_DESCRIPTION', 'Enterprise reporting and analytics');

// ============================================================
// REPORT TYPES
// ============================================================

define('REPORT_TYPES', [
    'executive' => [
        'label' => 'Executive Report',
        'description' => 'High-level executive summary',
        'icon' => 'fa-chart-line',
        'color' => '#0B3D91',
        'permission' => 'report_executive'
    ],
    'compliance' => [
        'label' => 'Compliance Report',
        'description' => 'Compliance status and metrics',
        'icon' => 'fa-check-circle',
        'color' => '#22C55E',
        'permission' => 'report_compliance'
    ],
    'risk' => [
        'label' => 'Risk Report',
        'description' => 'Risk assessment and analysis',
        'icon' => 'fa-shield-alt',
        'color' => '#EF4444',
        'permission' => 'report_risk'
    ],
    'audit' => [
        'label' => 'Audit Report',
        'description' => 'Audit findings and recommendations',
        'icon' => 'fa-clipboard-list',
        'color' => '#3B82F6',
        'permission' => 'report_audit'
    ],
    'policy' => [
        'label' => 'Policy Report',
        'description' => 'Policy compliance and status',
        'icon' => 'fa-file-contract',
        'color' => '#8B5CF6',
        'permission' => 'report_policy'
    ],
    'sbp' => [
        'label' => 'SBP Circular Report',
        'description' => 'SBP circular compliance',
        'icon' => 'fa-newspaper',
        'color' => '#F59E0B',
        'permission' => 'report_sbp'
    ],
    'custom' => [
        'label' => 'Custom Report',
        'description' => 'Custom report builder',
        'icon' => 'fa-cog',
        'color' => '#64748B',
        'permission' => 'report_custom'
    ]
]);

// ============================================================
// REPORT FORMATS
// ============================================================

define('REPORT_FORMATS', [
    'pdf' => [
        'label' => 'PDF',
        'icon' => 'fa-file-pdf',
        'color' => '#DC2626',
        'mime' => 'application/pdf',
        'extension' => 'pdf'
    ],
    'excel' => [
        'label' => 'Excel',
        'icon' => 'fa-file-excel',
        'color' => '#22C55E',
        'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'extension' => 'xlsx'
    ],
    'csv' => [
        'label' => 'CSV',
        'icon' => 'fa-file-csv',
        'color' => '#3B82F6',
        'mime' => 'text/csv',
        'extension' => 'csv'
    ],
    'json' => [
        'label' => 'JSON',
        'icon' => 'fa-file-code',
        'color' => '#F59E0B',
        'mime' => 'application/json',
        'extension' => 'json'
    ],
    'html' => [
        'label' => 'HTML',
        'icon' => 'fa-file-code',
        'color' => '#8B5CF6',
        'mime' => 'text/html',
        'extension' => 'html'
    ]
]);

// ============================================================
// REPORT SETTINGS
// ============================================================

define('REPORT_SETTINGS', [
    'default_format' => 'pdf',
    'default_period' => 'month',
    'max_records' => 10000,
    'export_timeout' => 300,
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'max_downloads_per_user' => 100,
    'retention_days' => 90,
    'scheduled_reports_enabled' => true,
    'email_reports_enabled' => true
]);

// ============================================================
// REPORT PERIODS
// ============================================================

define('REPORT_PERIODS', [
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'week' => 'This Week',
    'month' => 'This Month',
    'quarter' => 'This Quarter',
    'year' => 'This Year',
    'custom' => 'Custom Range'
]);

// ============================================================
// REPORT SCHEDULES
// ============================================================

define('REPORT_SCHEDULES', [
    'daily' => 'Daily',
    'weekly' => 'Weekly',
    'biweekly' => 'Bi-Weekly',
    'monthly' => 'Monthly',
    'quarterly' => 'Quarterly',
    'yearly' => 'Yearly'
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('REPORT_NOTIFICATIONS', [
    'report_generated' => 'Report generated: {name}',
    'report_ready' => 'Report ready for download: {name}',
    'report_failed' => 'Report generation failed: {name}',
    'schedule_active' => 'Report schedule active: {name}',
    'schedule_completed' => 'Scheduled report generated: {name}'
]);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get report type label
 * 
 * @param string $type
 * @return string
 */
function get_report_type_label(string $type): string
{
    $types = REPORT_TYPES;
    return $types[$type]['label'] ?? $type;
}

/**
 * Get report format configuration
 * 
 * @param string $format
 * @return array|null
 */
function get_report_format(string $format): ?array
{
    $formats = REPORT_FORMATS;
    return $formats[$format] ?? null;
}

/**
 * Get report setting
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function report_setting(string $key, $default = null)
{
    $settings = REPORT_SETTINGS;
    return $settings[$key] ?? $default;
}