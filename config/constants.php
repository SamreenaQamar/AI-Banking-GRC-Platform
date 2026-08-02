<?php
/**
 * AI Banking GRC Platform - Application Constants
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all application constants organized by category:
 * - Application metadata
 * - Role & permission definitions
 * - Status codes and enums
 * - HTTP status codes
 * - Configuration constants
 * - Default values
 * - File and path definitions
 */

declare(strict_types=1);

// ============================================================
// APPLICATION CONSTANTS
// ============================================================

define('APP_CONSTANTS_VERSION', '1.0.0');
define('APP_CONSTANTS_BUILD', '2026.08.02.001');

// ============================================================
// USER ROLES (RBAC)
// ============================================================

define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_COMPLIANCE_OFFICER', 'compliance_officer');
define('ROLE_RISK_MANAGER', 'risk_manager');
define('ROLE_INTERNAL_AUDITOR', 'internal_auditor');
define('ROLE_DEPARTMENT_HEAD', 'department_head');
define('ROLE_USER', 'user');

define('ROLES', [
    ROLE_SUPER_ADMIN => 'Super Administrator',
    ROLE_ADMIN => 'Administrator',
    ROLE_COMPLIANCE_OFFICER => 'Compliance Officer',
    ROLE_RISK_MANAGER => 'Risk Manager',
    ROLE_INTERNAL_AUDITOR => 'Internal Auditor',
    ROLE_DEPARTMENT_HEAD => 'Department Head',
    ROLE_USER => 'Standard User',
]);

define('ROLE_HIERARCHY', [
    ROLE_SUPER_ADMIN => 10,
    ROLE_ADMIN => 8,
    ROLE_COMPLIANCE_OFFICER => 6,
    ROLE_RISK_MANAGER => 6,
    ROLE_INTERNAL_AUDITOR => 5,
    ROLE_DEPARTMENT_HEAD => 4,
    ROLE_USER => 2,
]);

// ============================================================
// PERMISSIONS
// ============================================================

// User Management Permissions
define('PERM_USER_CREATE', 'user_create');
define('PERM_USER_VIEW', 'user_view');
define('PERM_USER_UPDATE', 'user_update');
define('PERM_USER_DELETE', 'user_delete');
define('PERM_USER_ROLE_ASSIGN', 'user_role_assign');

// Compliance Permissions
define('PERM_COMPLIANCE_CREATE', 'compliance_create');
define('PERM_COMPLIANCE_VIEW', 'compliance_view');
define('PERM_COMPLIANCE_UPDATE', 'compliance_update');
define('PERM_COMPLIANCE_DELETE', 'compliance_delete');
define('PERM_COMPLIANCE_APPROVE', 'compliance_approve');

// Risk Management Permissions
define('PERM_RISK_CREATE', 'risk_create');
define('PERM_RISK_VIEW', 'risk_view');
define('PERM_RISK_UPDATE', 'risk_update');
define('PERM_RISK_DELETE', 'risk_delete');
define('PERM_RISK_ASSESS', 'risk_assess');

// Audit Permissions
define('PERM_AUDIT_CREATE', 'audit_create');
define('PERM_AUDIT_VIEW', 'audit_view');
define('PERM_AUDIT_UPDATE', 'audit_update');
define('PERM_AUDIT_DELETE', 'audit_delete');
define('PERM_AUDIT_EXECUTE', 'audit_execute');

// Policy Permissions
define('PERM_POLICY_CREATE', 'policy_create');
define('PERM_POLICY_VIEW', 'policy_view');
define('PERM_POLICY_UPDATE', 'policy_update');
define('PERM_POLICY_DELETE', 'policy_delete');
define('PERM_POLICY_APPROVE', 'policy_approve');

// SBP Circular Permissions
define('PERM_SBP_CREATE', 'sbp_create');
define('PERM_SBP_VIEW', 'sbp_view');
define('PERM_SBP_UPDATE', 'sbp_update');
define('PERM_SBP_IMPLEMENT', 'sbp_implement');

// Report Permissions
define('PERM_REPORT_CREATE', 'report_create');
define('PERM_REPORT_VIEW', 'report_view');
define('PERM_REPORT_EXPORT', 'report_export');

// ============================================================
// USER STATUS
// ============================================================

define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_PENDING', 'pending');

define('USER_STATUSES', [
    USER_STATUS_ACTIVE => 'Active',
    USER_STATUS_INACTIVE => 'Inactive',
    USER_STATUS_SUSPENDED => 'Suspended',
    USER_STATUS_PENDING => 'Pending Verification',
]);

// ============================================================
// USER TYPES
// ============================================================

define('USER_TYPE_SYSTEM', 'system');
define('USER_TYPE_BANK', 'bank');
define('USER_TYPE_EXTERNAL', 'external');

define('USER_TYPES', [
    USER_TYPE_SYSTEM => 'System User',
    USER_TYPE_BANK => 'Bank User',
    USER_TYPE_EXTERNAL => 'External User',
]);

// ============================================================
// COMPLIANCE STATUS
// ============================================================

define('COMPLIANCE_STATUS_PENDING', 'pending');
define('COMPLIANCE_STATUS_IN_PROGRESS', 'in_progress');
define('COMPLIANCE_STATUS_COMPLETED', 'completed');
define('COMPLIANCE_STATUS_OVERDUE', 'overdue');
define('COMPLIANCE_STATUS_CANCELLED', 'cancelled');
define('COMPLIANCE_STATUS_REVIEW', 'under_review');
define('COMPLIANCE_STATUS_REJECTED', 'rejected');

define('COMPLIANCE_STATUSES', [
    COMPLIANCE_STATUS_PENDING => 'Pending',
    COMPLIANCE_STATUS_IN_PROGRESS => 'In Progress',
    COMPLIANCE_STATUS_COMPLETED => 'Completed',
    COMPLIANCE_STATUS_OVERDUE => 'Overdue',
    COMPLIANCE_STATUS_CANCELLED => 'Cancelled',
    COMPLIANCE_STATUS_REVIEW => 'Under Review',
    COMPLIANCE_STATUS_REJECTED => 'Rejected',
]);

define('COMPLIANCE_PRIORITY_CRITICAL', 'critical');
define('COMPLIANCE_PRIORITY_HIGH', 'high');
define('COMPLIANCE_PRIORITY_MEDIUM', 'medium');
define('COMPLIANCE_PRIORITY_LOW', 'low');

define('COMPLIANCE_PRIORITIES', [
    COMPLIANCE_PRIORITY_CRITICAL => 'Critical',
    COMPLIANCE_PRIORITY_HIGH => 'High',
    COMPLIANCE_PRIORITY_MEDIUM => 'Medium',
    COMPLIANCE_PRIORITY_LOW => 'Low',
]);

// ============================================================
// RISK LEVELS
// ============================================================

define('RISK_LEVEL_CRITICAL', 'critical');
define('RISK_LEVEL_HIGH', 'high');
define('RISK_LEVEL_MEDIUM', 'medium');
define('RISK_LEVEL_LOW', 'low');

define('RISK_LEVELS', [
    RISK_LEVEL_CRITICAL => 'Critical',
    RISK_LEVEL_HIGH => 'High',
    RISK_LEVEL_MEDIUM => 'Medium',
    RISK_LEVEL_LOW => 'Low',
]);

define('RISK_SCORE_CRITICAL_MIN', 80);
define('RISK_SCORE_HIGH_MIN', 60);
define('RISK_SCORE_MEDIUM_MIN', 40);
define('RISK_SCORE_LOW_MIN', 0);

// ============================================================
// RISK STATUS
// ============================================================

define('RISK_STATUS_IDENTIFIED', 'identified');
define('RISK_STATUS_ASSESSED', 'assessed');
define('RISK_STATUS_MITIGATED', 'mitigated');
define('RISK_STATUS_MONITORED', 'monitored');
define('RISK_STATUS_CLOSED', 'closed');

define('RISK_STATUSES', [
    RISK_STATUS_IDENTIFIED => 'Identified',
    RISK_STATUS_ASSESSED => 'Assessed',
    RISK_STATUS_MITIGATED => 'Mitigated',
    RISK_STATUS_MONITORED => 'Monitored',
    RISK_STATUS_CLOSED => 'Closed',
]);

// ============================================================
// AUDIT STATUS
// ============================================================

define('AUDIT_STATUS_PLANNED', 'planned');
define('AUDIT_STATUS_IN_PROGRESS', 'in_progress');
define('AUDIT_STATUS_COMPLETED', 'completed');
define('AUDIT_STATUS_CANCELLED', 'cancelled');

define('AUDIT_STATUSES', [
    AUDIT_STATUS_PLANNED => 'Planned',
    AUDIT_STATUS_IN_PROGRESS => 'In Progress',
    AUDIT_STATUS_COMPLETED => 'Completed',
    AUDIT_STATUS_CANCELLED => 'Cancelled',
]);

// ============================================================
// AUDIT FINDING SEVERITY
// ============================================================

define('FINDING_SEVERITY_CRITICAL', 'critical');
define('FINDING_SEVERITY_HIGH', 'high');
define('FINDING_SEVERITY_MEDIUM', 'medium');
define('FINDING_SEVERITY_LOW', 'low');

define('FINDING_SEVERITIES', [
    FINDING_SEVERITY_CRITICAL => 'Critical',
    FINDING_SEVERITY_HIGH => 'High',
    FINDING_SEVERITY_MEDIUM => 'Medium',
    FINDING_SEVERITY_LOW => 'Low',
]);

// ============================================================
// AUDIT TYPES
// ============================================================

define('AUDIT_TYPE_INTERNAL', 'internal');
define('AUDIT_TYPE_EXTERNAL', 'external');
define('AUDIT_TYPE_REGULATORY', 'regulatory');
define('AUDIT_TYPE_FORENSIC', 'forensic');

define('AUDIT_TYPES', [
    AUDIT_TYPE_INTERNAL => 'Internal Audit',
    AUDIT_TYPE_EXTERNAL => 'External Audit',
    AUDIT_TYPE_REGULATORY => 'Regulatory Audit',
    AUDIT_TYPE_FORENSIC => 'Forensic Audit',
]);

// ============================================================
// SBP CIRCULAR CATEGORIES
// ============================================================

define('SBP_CATEGORY_PRUDENTIAL', 'prudential');
define('SBP_CATEGORY_OPERATIONAL', 'operational');
define('SBP_CATEGORY_COMPLIANCE', 'compliance');
define('SBP_CATEGORY_RISK', 'risk');
define('SBP_CATEGORY_GOVERNANCE', 'governance');

define('SBP_CATEGORIES', [
    SBP_CATEGORY_PRUDENTIAL => 'Prudential Regulations',
    SBP_CATEGORY_OPERATIONAL => 'Operational Guidelines',
    SBP_CATEGORY_COMPLIANCE => 'Compliance Requirements',
    SBP_CATEGORY_RISK => 'Risk Management',
    SBP_CATEGORY_GOVERNANCE => 'Corporate Governance',
]);

// ============================================================
// SBP CIRCULAR STATUS
// ============================================================

define('SBP_STATUS_ACTIVE', 'active');
define('SBP_STATUS_PENDING', 'pending');
define('SBP_STATUS_IMPLEMENTED', 'implemented');
define('SBP_STATUS_SUPERSEDED', 'superseded');
define('SBP_STATUS_WITHDRAWN', 'withdrawn');

define('SBP_STATUSES', [
    SBP_STATUS_ACTIVE => 'Active',
    SBP_STATUS_PENDING => 'Pending Implementation',
    SBP_STATUS_IMPLEMENTED => 'Implemented',
    SBP_STATUS_SUPERSEDED => 'Superseded',
    SBP_STATUS_WITHDRAWN => 'Withdrawn',
]);

// ============================================================
// POLICY STATUS
// ============================================================

define('POLICY_STATUS_DRAFT', 'draft');
define('POLICY_STATUS_REVIEW', 'review');
define('POLICY_STATUS_APPROVED', 'approved');
define('POLICY_STATUS_ACTIVE', 'active');
define('POLICY_STATUS_ARCHIVED', 'archived');
define('POLICY_STATUS_EXPIRED', 'expired');

define('POLICY_STATUSES', [
    POLICY_STATUS_DRAFT => 'Draft',
    POLICY_STATUS_REVIEW => 'Under Review',
    POLICY_STATUS_APPROVED => 'Approved',
    POLICY_STATUS_ACTIVE => 'Active',
    POLICY_STATUS_ARCHIVED => 'Archived',
    POLICY_STATUS_EXPIRED => 'Expired',
]);

// ============================================================
// NOTIFICATION TYPES
// ============================================================

define('NOTIFICATION_TYPE_INFO', 'info');
define('NOTIFICATION_TYPE_WARNING', 'warning');
define('NOTIFICATION_TYPE_SUCCESS', 'success');
define('NOTIFICATION_TYPE_ERROR', 'error');
define('NOTIFICATION_TYPE_COMPLIANCE', 'compliance');
define('NOTIFICATION_TYPE_RISK', 'risk');
define('NOTIFICATION_TYPE_AUDIT', 'audit');
define('NOTIFICATION_TYPE_POLICY', 'policy');
define('NOTIFICATION_TYPE_SBP', 'sbp');
define('NOTIFICATION_TYPE_SYSTEM', 'system');

define('NOTIFICATION_TYPES', [
    NOTIFICATION_TYPE_INFO => 'Information',
    NOTIFICATION_TYPE_WARNING => 'Warning',
    NOTIFICATION_TYPE_SUCCESS => 'Success',
    NOTIFICATION_TYPE_ERROR => 'Error',
    NOTIFICATION_TYPE_COMPLIANCE => 'Compliance Alert',
    NOTIFICATION_TYPE_RISK => 'Risk Alert',
    NOTIFICATION_TYPE_AUDIT => 'Audit Notification',
    NOTIFICATION_TYPE_POLICY => 'Policy Update',
    NOTIFICATION_TYPE_SBP => 'SBP Circular',
    NOTIFICATION_TYPE_SYSTEM => 'System Notification',
]);

// ============================================================
// NOTIFICATION PRIORITY
// ============================================================

define('NOTIFICATION_PRIORITY_LOW', 'low');
define('NOTIFICATION_PRIORITY_MEDIUM', 'medium');
define('NOTIFICATION_PRIORITY_HIGH', 'high');

define('NOTIFICATION_PRIORITIES', [
    NOTIFICATION_PRIORITY_LOW => 'Low',
    NOTIFICATION_PRIORITY_MEDIUM => 'Medium',
    NOTIFICATION_PRIORITY_HIGH => 'High',
]);

// ============================================================
// HTTP STATUS CODES
// ============================================================

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_ACCEPTED', 202);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_SERVICE_UNAVAILABLE', 503);

// ============================================================
// SUPPORTED LANGUAGES
// ============================================================

define('LANGUAGE_ENGLISH', 'en');
define('LANGUAGE_URDU', 'ur');

define('SUPPORTED_LANGUAGES', [
    LANGUAGE_ENGLISH => 'English',
    LANGUAGE_URDU => 'Urdu',
]);

define('DEFAULT_LANGUAGE_CODE', LANGUAGE_ENGLISH);

// ============================================================
// DATE & TIME FORMATS
// ============================================================

define('DATE_FORMAT', 'Y-m-d');
define('DATE_FORMAT_DISPLAY', 'd M Y');
define('DATE_FORMAT_FULL', 'l, d F Y');
define('TIME_FORMAT', 'H:i:s');
define('TIME_FORMAT_12H', 'h:i A');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DATETIME_FORMAT_DISPLAY', 'd M Y h:i A');

// ============================================================
// FILE UPLOAD LIMITS
// ============================================================

define('MAX_FILE_SIZE', 10485760); // 10MB
define('MAX_FILE_SIZE_DISPLAY', '10MB');
define('MAX_IMAGE_SIZE', 5242880); // 5MB
define('MAX_DOCUMENT_SIZE', 10485760); // 10MB

// ============================================================
// FILE EXTENSIONS
// ============================================================

define('EXTENSIONS_IMAGES', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']);
define('EXTENSIONS_DOCUMENTS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt']);
define('EXTENSIONS_ARCHIVES', ['zip', 'rar', '7z', 'tar', 'gz']);
define('EXTENSIONS_ALLOWED', array_merge(EXTENSIONS_IMAGES, EXTENSIONS_DOCUMENTS, EXTENSIONS_ARCHIVES));

// ============================================================
// SESSION KEYS
// ============================================================

define('SESSION_KEY_USER_ID', 'user_id');
define('SESSION_KEY_USERNAME', 'username');
define('SESSION_KEY_USER_ROLE', 'user_role');
define('SESSION_KEY_USER_EMAIL', 'user_email');
define('SESSION_KEY_USER_NAME', 'user_name');
define('SESSION_KEY_AUTHENTICATED', 'authenticated');
define('SESSION_KEY_CSRF_TOKEN', 'csrf_token');
define('SESSION_KEY_LAST_ACTIVITY', 'last_activity');
define('SESSION_KEY_IP_ADDRESS', 'ip_address');
define('SESSION_KEY_USER_AGENT', 'user_agent');

// ============================================================
// CACHE KEYS
// ============================================================

define('CACHE_KEY_USER_PREFIX', 'user_');
define('CACHE_KEY_ROLE_PREFIX', 'role_');
define('CACHE_KEY_PERMISSION_PREFIX', 'permission_');
define('CACHE_KEY_SETTING_PREFIX', 'setting_');
define('CACHE_KEY_COMPLIANCE_PREFIX', 'compliance_');
define('CACHE_KEY_RISK_PREFIX', 'risk_');

// ============================================================
// DEFAULT VALUES
// ============================================================

define('DEFAULT_AVATAR', 'default-avatar.png');
define('DEFAULT_AVATAR_PATH', '/assets/images/default-avatar.png');
define('DEFAULT_LOGO', 'logo.png');
define('DEFAULT_LOGO_PATH', '/assets/images/logo.png');
define('DEFAULT_FAVICON', 'favicon.ico');
define('DEFAULT_FAVICON_PATH', '/favicon.ico');

define('DEFAULT_PER_PAGE', 15);
define('DEFAULT_SORT_BY', 'created_at');
define('DEFAULT_SORT_ORDER', 'DESC');

// ============================================================
// DASHBOARD CONSTANTS
// ============================================================

define('DASHBOARD_WIDGETS_PER_ROW', 4);
define('DASHBOARD_REFRESH_INTERVAL', 300000); // 5 minutes in milliseconds
define('DASHBOARD_MAX_RECENT_ITEMS', 10);
define('DASHBOARD_NOTIFICATIONS_LIMIT', 5);

// ============================================================
// AI CONSTANTS
// ============================================================

define('AI_MAX_TOKENS', 4096);
define('AI_TEMPERATURE', 0.7);
define('AI_TOP_P', 0.9);
define('AI_FREQUENCY_PENALTY', 0.0);
define('AI_PRESENCE_PENALTY', 0.0);

define('AI_USE_CASE_SUMMARIZE', 'summarize');
define('AI_USE_CASE_ANALYZE', 'analyze');
define('AI_USE_CASE_RECOMMEND', 'recommend');
define('AI_USE_CASE_REPORT', 'report');
define('AI_USE_CASE_COMPLIANCE', 'compliance');

define('AI_USE_CASES', [
    AI_USE_CASE_SUMMARIZE => 'Content Summarization',
    AI_USE_CASE_ANALYZE => 'Data Analysis',
    AI_USE_CASE_RECOMMEND => 'Recommendations',
    AI_USE_CASE_REPORT => 'Report Generation',
    AI_USE_CASE_COMPLIANCE => 'Compliance Assistance',
]);

// ============================================================
// LOG LEVELS
// ============================================================

define('LOG_LEVEL_DEBUG', 'debug');
define('LOG_LEVEL_INFO', 'info');
define('LOG_LEVEL_WARNING', 'warning');
define('LOG_LEVEL_ERROR', 'error');
define('LOG_LEVEL_CRITICAL', 'critical');

define('LOG_LEVELS', [
    LOG_LEVEL_DEBUG => 100,
    LOG_LEVEL_INFO => 200,
    LOG_LEVEL_WARNING => 300,
    LOG_LEVEL_ERROR => 400,
    LOG_LEVEL_CRITICAL => 500,
]);

// ============================================================
// REGULAR EXPRESSION PATTERNS
// ============================================================

define('REGEX_EMAIL', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
define('REGEX_PHONE', '/^(\+92|0)[0-9]{10,12}$/');
define('REGEX_USERNAME', '/^[a-zA-Z0-9_]{3,30}$/');
define('REGEX_PASSWORD', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/');
define('REGEX_POLICY_NUMBER', '/^POL-[0-9]{4}-[0-9]{4}$/');
define('REGEX_RISK_CODE', '/^RISK-[0-9]{4}-[0-9]{4}$/');
define('REGEX_AUDIT_CODE', '/^AUDIT-[0-9]{4}-[0-9]{4}$/');
define('REGEX_SBP_CIRCULAR', '/^SBP-[A-Z]{2,3}-[0-9]{4}-[0-9]{2,4}$/');

// ============================================================
// ENCRYPTION CONSTANTS
// ============================================================

define('ENCRYPTION_METHOD', 'AES-256-CBC');
define('ENCRYPTION_KEY_LENGTH', 32);
define('ENCRYPTION_IV_LENGTH', 16);

// ============================================================
// VALIDATION CONSTANTS
// ============================================================

define('VALIDATION_STRING_MIN', 2);
define('VALIDATION_STRING_MAX', 255);
define('VALIDATION_TEXT_MIN', 10);
define('VALIDATION_TEXT_MAX', 65535);

define('VALIDATION_USERNAME_MIN', 3);
define('VALIDATION_USERNAME_MAX', 30);
define('VALIDATION_PASSWORD_MIN', 8);
define('VALIDATION_PASSWORD_MAX', 100);

// ============================================================
// BANKING SPECIFIC CONSTANTS
// ============================================================

define('BANKING_SBP_CODE_LENGTH', 4);
define('BANKING_SWIFT_CODE_LENGTH', 8);
define('BANKING_IBAN_LENGTH', 24);
define('BANKING_ACCOUNT_LENGTH', 16);

define('BANKING_CURRENCIES', [
    'PKR' => 'Pakistani Rupee',
    'USD' => 'US Dollar',
    'EUR' => 'Euro',
    'GBP' => 'British Pound',
    'AED' => 'UAE Dirham',
    'SAR' => 'Saudi Riyal',
]);

define('BANKING_DEFAULT_CURRENCY', 'PKR');

// ============================================================
// MODULE NAMES
// ============================================================

define('MODULE_AUTHENTICATION', 'authentication');
define('MODULE_DASHBOARD', 'dashboard');
define('MODULE_USERS', 'users');
define('MODULE_COMPLIANCE', 'compliance');
define('MODULE_RISK', 'risk');
define('MODULE_AUDIT', 'audit');
define('MODULE_POLICIES', 'policies');
define('MODULE_SBP', 'sbp-circulars');
define('MODULE_AI', 'ai-copilot');
define('MODULE_REPORTS', 'reports');
define('MODULE_NOTIFICATIONS', 'notifications');
define('MODULE_SETTINGS', 'settings');

define('MODULES', [
    MODULE_AUTHENTICATION => 'Authentication',
    MODULE_DASHBOARD => 'Dashboard',
    MODULE_USERS => 'User Management',
    MODULE_COMPLIANCE => 'Compliance Management',
    MODULE_RISK => 'Risk Management',
    MODULE_AUDIT => 'Audit Management',
    MODULE_POLICIES => 'Policy Management',
    MODULE_SBP => 'SBP Circulars',
    MODULE_AI => 'AI Copilot',
    MODULE_REPORTS => 'Reports',
    MODULE_NOTIFICATIONS => 'Notifications',
    MODULE_SETTINGS => 'System Settings',
]);

// ============================================================
// ENVIRONMENT CONSTANTS
// ============================================================

define('ENV_DEVELOPMENT', 'development');
define('ENV_STAGING', 'staging');
define('ENV_PRODUCTION', 'production');

define('ENVIRONMENTS', [
    ENV_DEVELOPMENT => 'Development',
    ENV_STAGING => 'Staging',
    ENV_PRODUCTION => 'Production',
]);

// ============================================================
// HEADER SECURITY CONSTANTS
// ============================================================

define('HEADER_XFRAME_OPTIONS', 'DENY');
define('HEADER_XSS_PROTECTION', '1; mode=block');
define('HEADER_CONTENT_TYPE', 'nosniff');
define('HEADER_REFERRER_POLICY', 'strict-origin-when-cross-origin');
define('HEADER_PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()');

// ============================================================
// COOKIE CONSTANTS
// ============================================================

define('COOKIE_REMEMBER_ME_NAME', 'remember_token');
define('COOKIE_REMEMBER_ME_LIFETIME', 2592000); // 30 days
define('COOKIE_SESSION_NAME', 'grc_session');
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
define('COOKIE_SECURE', true);
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Strict');

// ============================================================
// API RATE LIMITING
// ============================================================

define('RATE_LIMIT_GENERAL', 100);
define('RATE_LIMIT_AUTH', 10);
define('RATE_LIMIT_API', 60);
define('RATE_LIMIT_LOGIN', 5);
define('RATE_LIMIT_WINDOW', 60); // seconds
define('RATE_LIMIT_BLOCK_DURATION', 300); // 5 minutes

// ============================================================
// PAGINATION CONSTANTS
// ============================================================

define('PAGINATION_DEFAULT_LIMIT', 15);
define('PAGINATION_MAX_LIMIT', 100);
define('PAGINATION_LINKS_TO_SHOW', 5);
define('PAGINATION_LIMIT_OPTIONS', [10, 15, 25, 50, 100]);

// ============================================================
// EXPORT CONSTANTS
// ============================================================

define('EXPORT_FORMAT_PDF', 'pdf');
define('EXPORT_FORMAT_EXCEL', 'xlsx');
define('EXPORT_FORMAT_CSV', 'csv');
define('EXPORT_FORMAT_JSON', 'json');

define('EXPORT_FORMATS', [
    EXPORT_FORMAT_PDF => 'PDF Document',
    EXPORT_FORMAT_EXCEL => 'Excel Spreadsheet',
    EXPORT_FORMAT_CSV => 'CSV File',
    EXPORT_FORMAT_JSON => 'JSON Data',
]);

// ============================================================
// HELPER FUNCTIONS FOR CONSTANTS
// ============================================================

/**
 * Get all roles with labels
 * 
 * @return array
 */
function getRoles(): array
{
    return ROLES;
}

/**
 * Get role label by role key
 * 
 * @param string $role
 * @return string
 */
function getRoleLabel(string $role): string
{
    return ROLES[$role] ?? ucfirst(str_replace('_', ' ', $role));
}

/**
 * Get all statuses for a specific type
 * 
 * @param string $type status type (user, compliance, risk, etc.)
 * @return array
 */
function getStatuses(string $type): array
{
    $statusMap = [
        'user' => USER_STATUSES,
        'compliance' => COMPLIANCE_STATUSES,
        'risk' => RISK_STATUSES,
        'audit' => AUDIT_STATUSES,
        'policy' => POLICY_STATUSES,
        'sbp' => SBP_STATUSES,
    ];
    
    return $statusMap[$type] ?? [];
}

/**
 * Get status label by type and key
 * 
 * @param string $type
 * @param string $status
 * @return string
 */
function getStatusLabel(string $type, string $status): string
{
    $statuses = getStatuses($type);
    return $statuses[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Get priority label
 * 
 * @param string $priority
 * @return string
 */
function getPriorityLabel(string $priority): string
{
    $priorities = COMPLIANCE_PRIORITIES;
    return $priorities[$priority] ?? ucfirst($priority);
}

/**
 * Get notification type label
 * 
 * @param string $type
 * @return string
 */
function getNotificationTypeLabel(string $type): string
{
    return NOTIFICATION_TYPES[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

// ============================================================
// END OF CONSTANTS
// ============================================================