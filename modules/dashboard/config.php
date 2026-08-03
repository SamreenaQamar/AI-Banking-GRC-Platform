<?php
/**
 * Dashboard Module - Configuration File
 * 
 * @package    AI-Banking-GRC-Platform
 * @subpackage modules/dashboard
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all configuration for the dashboard module
 */

declare(strict_types=1);

// ============================================================
// MODULE METADATA
// ============================================================

define('DASHBOARD_MODULE_NAME', 'Dashboard');
define('DASHBOARD_MODULE_VERSION', '1.0.0');
define('DASHBOARD_MODULE_AUTHOR', 'GRC Platform Team');
define('DASHBOARD_MODULE_DESCRIPTION', 'Enterprise dashboard for GRC monitoring and analytics');

// ============================================================
// DASHBOARD SETTINGS
// ============================================================

// Refresh settings
define('DASHBOARD_REFRESH_INTERVAL', 300); // seconds (5 minutes)
define('DASHBOARD_AUTO_REFRESH_ENABLED', true);
define('DASHBOARD_REFRESH_WARNING_THRESHOLD', 30); // seconds before refresh

// Widget settings
define('DASHBOARD_WIDGETS_PER_ROW', 4);
define('DASHBOARD_DEFAULT_WIDGETS', [
    'compliance_score',
    'risk_score',
    'open_risks',
    'audit_findings',
    'sbp_circulars',
    'pending_tasks',
    'recent_activities',
    'ai_insights'
]);

// Chart settings
define('DASHBOARD_CHART_COLORS', [
    'primary' => '#2563EB',
    'secondary' => '#0B3D91',
    'success' => '#22C55E',
    'warning' => '#F59E0B',
    'danger' => '#EF4444',
    'info' => '#3B82F6',
    'purple' => '#8B5CF6',
    'pink' => '#EC4899'
]);

define('DASHBOARD_CHART_ANIMATION_DURATION', 800);
define('DASHBOARD_CHART_DEFAULT_PERIOD', 'month');

// Statistics settings
define('DASHBOARD_STATISTICS_PERIODS', [
    'today' => 'Today',
    'week' => 'This Week',
    'month' => 'This Month',
    'quarter' => 'This Quarter',
    'year' => 'This Year'
]);

define('DASHBOARD_DEFAULT_STATISTICS_PERIOD', 'month');

// Activity settings
define('DASHBOARD_RECENT_ACTIVITIES_LIMIT', 10);
define('DASHBOARD_NOTIFICATIONS_LIMIT', 5);
define('DASHBOARD_ACTIVITY_LOG_RETENTION', 30); // days

// ============================================================
// ROLE-BASED DASHBOARD SETTINGS
// ============================================================

define('DASHBOARD_ROLE_WIDGETS', [
    'super_admin' => [
        'compliance_score',
        'risk_score',
        'open_risks',
        'audit_findings',
        'sbp_circulars',
        'pending_tasks',
        'recent_activities',
        'ai_insights',
        'user_activity',
        'system_health'
    ],
    'admin' => [
        'compliance_score',
        'risk_score',
        'open_risks',
        'audit_findings',
        'sbp_circulars',
        'pending_tasks',
        'recent_activities',
        'user_activity'
    ],
    'compliance_officer' => [
        'compliance_score',
        'pending_tasks',
        'sbp_circulars',
        'recent_activities',
        'ai_insights'
    ],
    'risk_manager' => [
        'risk_score',
        'open_risks',
        'risk_heatmap',
        'recent_activities',
        'ai_insights'
    ],
    'internal_auditor' => [
        'audit_findings',
        'audit_progress',
        'recent_activities'
    ],
    'department_head' => [
        'compliance_score',
        'pending_tasks',
        'recent_activities',
        'team_performance'
    ],
    'user' => [
        'pending_tasks',
        'recent_activities',
        'notifications'
    ]
]);

// ============================================================
// WIDGET CONFIGURATION
// ============================================================

define('DASHBOARD_WIDGETS_CONFIG', [
    'compliance_score' => [
        'title' => 'Compliance Score',
        'icon' => 'fa-check-circle',
        'color' => '#2563EB',
        'bg_color' => 'rgba(37, 99, 235, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'compliance_view'
    ],
    'risk_score' => [
        'title' => 'Risk Score',
        'icon' => 'fa-shield-alt',
        'color' => '#EF4444',
        'bg_color' => 'rgba(239, 68, 68, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'risk_view'
    ],
    'open_risks' => [
        'title' => 'Open Risks',
        'icon' => 'fa-exclamation-triangle',
        'color' => '#F59E0B',
        'bg_color' => 'rgba(245, 158, 11, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'risk_view'
    ],
    'audit_findings' => [
        'title' => 'Audit Findings',
        'icon' => 'fa-clipboard-list',
        'color' => '#10B981',
        'bg_color' => 'rgba(16, 185, 129, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'audit_view'
    ],
    'sbp_circulars' => [
        'title' => 'SBP Circulars',
        'icon' => 'fa-newspaper',
        'color' => '#2563EB',
        'bg_color' => 'rgba(37, 99, 235, 0.1)',
        'refresh_interval' => 600,
        'permission' => 'sbp_view'
    ],
    'pending_tasks' => [
        'title' => 'Pending Tasks',
        'icon' => 'fa-tasks',
        'color' => '#F59E0B',
        'bg_color' => 'rgba(245, 158, 11, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'compliance_view'
    ],
    'recent_activities' => [
        'title' => 'Recent Activities',
        'icon' => 'fa-clock',
        'color' => '#3B82F6',
        'bg_color' => 'rgba(59, 130, 246, 0.1)',
        'refresh_interval' => 60,
        'permission' => null
    ],
    'ai_insights' => [
        'title' => 'AI Insights',
        'icon' => 'fa-robot',
        'color' => '#8B5CF6',
        'bg_color' => 'rgba(139, 92, 246, 0.1)',
        'refresh_interval' => 600,
        'permission' => 'ai_view'
    ],
    'user_activity' => [
        'title' => 'User Activity',
        'icon' => 'fa-users',
        'color' => '#8B5CF6',
        'bg_color' => 'rgba(139, 92, 246, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'user_view'
    ],
    'system_health' => [
        'title' => 'System Health',
        'icon' => 'fa-heartbeat',
        'color' => '#22C55E',
        'bg_color' => 'rgba(34, 197, 94, 0.1)',
        'refresh_interval' => 60,
        'permission' => 'admin'
    ],
    'risk_heatmap' => [
        'title' => 'Risk Heatmap',
        'icon' => 'fa-fire',
        'color' => '#EF4444',
        'bg_color' => 'rgba(239, 68, 68, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'risk_view'
    ],
    'audit_progress' => [
        'title' => 'Audit Progress',
        'icon' => 'fa-chart-line',
        'color' => '#3B82F6',
        'bg_color' => 'rgba(59, 130, 246, 0.1)',
        'refresh_interval' => 300,
        'permission' => 'audit_view'
    ]
]);

// ============================================================
// CHART CONFIGURATION
// ============================================================

define('DASHBOARD_CHARTS', [
    'compliance_trend' => [
        'title' => 'Compliance Trend',
        'type' => 'line',
        'description' => 'Compliance score trend over time',
        'permission' => 'compliance_view'
    ],
    'risk_distribution' => [
        'title' => 'Risk Distribution',
        'type' => 'doughnut',
        'description' => 'Risk distribution by severity',
        'permission' => 'risk_view'
    ],
    'audit_status' => [
        'title' => 'Audit Status',
        'type' => 'bar',
        'description' => 'Audit status distribution',
        'permission' => 'audit_view'
    ],
    'sbp_compliance' => [
        'title' => 'SBP Compliance',
        'type' => 'bar',
        'description' => 'SBP circular compliance status',
        'permission' => 'sbp_view'
    ]
]);

// ============================================================
// NOTIFICATION SETTINGS
// ============================================================

define('DASHBOARD_NOTIFICATION_TYPES', [
    'compliance' => 'Compliance Alert',
    'risk' => 'Risk Alert',
    'audit' => 'Audit Notification',
    'system' => 'System Notification',
    'policy' => 'Policy Update',
    'sbp' => 'SBP Circular'
]);

define('DASHBOARD_NOTIFICATION_PRIORITIES', [
    'high' => 'High',
    'medium' => 'Medium',
    'low' => 'Low'
]);

// ============================================================
// THEME SETTINGS
// ============================================================

define('DASHBOARD_THEME', [
    'sidebar_collapsed' => false,
    'dark_mode' => false,
    'sidebar_color' => '#0F172A',
    'topbar_color' => '#0B3D91',
    'card_shadow' => '0 1px 3px rgba(0,0,0,0.06)',
    'border_radius' => '12px'
]);

// ============================================================
// SECURITY SETTINGS
// ============================================================

define('DASHBOARD_SESSION_VALIDATION', true);
define('DASHBOARD_IP_VALIDATION', true);
define('DASHBOARD_USER_AGENT_VALIDATION', true);
define('DASHBOARD_ACTIVITY_LOGGING', true);

// ============================================================
// PERFORMANCE SETTINGS
// ============================================================

define('DASHBOARD_CACHE_ENABLED', true);
define('DASHBOARD_CACHE_DURATION', 300); // seconds
define('DASHBOARD_QUERY_LIMIT', 1000);
define('DASHBOARD_BATCH_SIZE', 100);

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get dashboard widget configuration
 * 
 * @param string $widget Widget name
 * @return array|null
 */
function dashboard_widget_config(string $widget): ?array
{
    $config = DASHBOARD_WIDGETS_CONFIG;
    return $config[$widget] ?? null;
}

/**
 * Get dashboard role widgets
 * 
 * @param string $role User role
 * @return array
 */
function dashboard_role_widgets(string $role): array
{
    $roleWidgets = DASHBOARD_ROLE_WIDGETS;
    return $roleWidgets[$role] ?? DASHBOARD_ROLE_WIDGETS['user'];
}

/**
 * Check if widget is enabled for role
 * 
 * @param string $widget Widget name
 * @param string $role User role
 * @return bool
 */
function dashboard_widget_enabled(string $widget, string $role): bool
{
    $roleWidgets = dashboard_role_widgets($role);
    return in_array($widget, $roleWidgets);
}

/**
 * Get dashboard chart configuration
 * 
 * @param string $chart Chart name
 * @return array|null
 */
function dashboard_chart_config(string $chart): ?array
{
    $charts = DASHBOARD_CHARTS;
    return $charts[$chart] ?? null;
}

/**
 * Get dashboard configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value
 * @return mixed
 */
function dashboard_config(string $key, $default = null)
{
    $config = [
        'refresh_interval' => DASHBOARD_REFRESH_INTERVAL,
        'auto_refresh' => DASHBOARD_AUTO_REFRESH_ENABLED,
        'widgets_per_row' => DASHBOARD_WIDGETS_PER_ROW,
        'default_widgets' => DASHBOARD_DEFAULT_WIDGETS,
        'chart_colors' => DASHBOARD_CHART_COLORS,
        'statistics_periods' => DASHBOARD_STATISTICS_PERIODS,
        'default_period' => DASHBOARD_DEFAULT_STATISTICS_PERIOD,
        'activities_limit' => DASHBOARD_RECENT_ACTIVITIES_LIMIT,
        'notifications_limit' => DASHBOARD_NOTIFICATIONS_LIMIT
    ];
    
    return $config[$key] ?? $default;
}