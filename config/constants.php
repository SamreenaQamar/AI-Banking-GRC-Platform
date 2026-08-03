<?php
/**
 * AI Banking GRC Platform - Enterprise Application Constants
 * 
 * @package    AI-Banking-GRC-Platform
 * @version    1.0.0
 * @author     GRC Platform Team
 * @copyright  2026 AI Banking GRC Platform
 * @license    Proprietary
 * 
 * This file contains all application constants organized by category:
 * - Application metadata
 * - Role & permission definitions (RBAC)
 * - Status codes and enums
 * - HTTP status codes
 * - Configuration constants
 * - Default values
 * - File and path definitions
 * - Banking specific constants
 * - Regular expression patterns
 * - Security constants
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
define('ROLE_BRANCH_MANAGER', 'branch_manager');
define('ROLE_USER', 'user');

define('ROLES', [
    ROLE_SUPER_ADMIN => 'Super Administrator',
    ROLE_ADMIN => 'Administrator',
    ROLE_COMPLIANCE_OFFICER => 'Compliance Officer',
    ROLE_RISK_MANAGER => 'Risk Manager',
    ROLE_INTERNAL_AUDITOR => 'Internal Auditor',
    ROLE_DEPARTMENT_HEAD => 'Department Head',
    ROLE_BRANCH_MANAGER => 'Branch Manager',
    ROLE_USER => 'Standard User',
]);

define('ROLE_HIERARCHY', [
    ROLE_SUPER_ADMIN => 10,
    ROLE_ADMIN => 8,
    ROLE_COMPLIANCE_OFFICER => 6,
    ROLE_RISK_MANAGER => 6,
    ROLE_INTERNAL_AUDITOR => 5,
    ROLE_DEPARTMENT_HEAD => 4,
    ROLE_BRANCH_MANAGER => 3,
    ROLE_USER => 2,
]);

// ============================================================
// PERMISSIONS (Granular Access Control)
// ============================================================

// User Management Permissions
define('PERM_USER_CREATE', 'user_create');
define('PERM_USER_VIEW', 'user_view');
define('PERM_USER_UPDATE', 'user_update');
define('PERM_USER_DELETE', 'user_delete');
define('PERM_USER_ROLE_ASSIGN', 'user_role_assign');
define('PERM_USER_STATUS', 'user_status');

// Compliance Permissions
define('PERM_COMPLIANCE_CREATE', 'compliance_create');
define('PERM_COMPLIANCE_VIEW', 'compliance_view');
define('PERM_COMPLIANCE_UPDATE', 'compliance_update');
define('PERM_COMPLIANCE_DELETE', 'compliance_delete');
define('PERM_COMPLIANCE_APPROVE', 'compliance_approve');
define('PERM_COMPLIANCE_REVIEW', 'compliance_review');
define('PERM_COMPLIANCE_EVIDENCE', 'compliance_evidence');

// Risk Management Permissions
define('PERM_RISK_CREATE', 'risk_create');
define('PERM_RISK_VIEW', 'risk_view');
define('PERM_RISK_UPDATE', 'risk_update');
define('PERM_RISK_DELETE', 'risk_delete');
define('PERM_RISK_ASSESS', 'risk_assess');
define('PERM_RISK_MITIGATE', 'risk_mitigate');
define('PERM_RISK_APPROVE', 'risk_approve');

// Audit Permissions
define('PERM_AUDIT_CREATE', 'audit_create');
define('PERM_AUDIT_VIEW', 'audit_view');
define('PERM_AUDIT_UPDATE', 'audit_update');
define('PERM_AUDIT_DELETE', 'audit_delete');
define('PERM_AUDIT_EXECUTE', 'audit_execute');
define('PERM_AUDIT_REVIEW', 'audit_review');
define('PERM_AUDIT_CLOSE', 'audit_close');

// Policy Permissions
define('PERM_POLICY_CREATE', 'policy_create');
define('PERM_POLICY_VIEW', 'policy_view');
define('PERM_POLICY_UPDATE', 'policy_update');
define('PERM_POLICY_DELETE', 'policy_delete');
define('PERM_POLICY_APPROVE', 'policy_approve');
define('PERM_POLICY_PUBLISH', 'policy_publish');
define('PERM_POLICY_ARCHIVE', 'policy_archive');
define('PERM_POLICY_ACKNOWLEDGE', 'policy_acknowledge');

// SBP Circular Permissions
define('PERM_SBP_CREATE', 'sbp_create');
define('PERM_SBP_VIEW', 'sbp_view');
define('PERM_SBP_UPDATE', 'sbp_update');
define('PERM_SBP_DELETE', 'sbp_delete');
define('PERM_SBP_IMPLEMENT', 'sbp_implement');

// Report Permissions
define('PERM_REPORT_CREATE', 'report_create');
define('PERM_REPORT_VIEW', 'report_view');
define('PERM_REPORT_EXPORT', 'report_export');
define('PERM_REPORT_SCHEDULE', 'report_schedule');

// AI Permissions
define('PERM_AI_CHAT', 'ai_chat');
define('PERM_AI_POLICY_GENERATE', 'ai_policy_generate');
define('PERM_AI_RISK_ANALYZE', 'ai_risk_analyze');
define('PERM_AI_GAP_ANALYZE', 'ai_gap_analyze');

// Settings Permissions
define('PERM_SETTINGS_VIEW', 'settings_view');
define('PERM_SETTINGS_UPDATE', 'settings_update');
define('PERM_SETTINGS_BACKUP', 'settings_backup');
define('PERM_SETTINGS_RESTORE', 'settings_restore');

// ============================================================
// USER STATUS
// ============================================================

define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_PENDING', 'pending');
define('USER_STATUS_LOCKED', 'locked');

define('USER_STATUSES', [
    USER_STATUS_ACTIVE => 'Active',
    USER_STATUS_INACTIVE => 'Inactive',
    USER_STATUS_SUSPENDED => 'Suspended',
    USER_STATUS_PENDING => 'Pending Verification',
    USER_STATUS_LOCKED => 'Locked',
]);

// ============================================================
// USER TYPES
// ============================================================

define('USER_TYPE_SYSTEM', 'system');
define('USER_TYPE_BANK', 'bank');
define('USER_TYPE_EXTERNAL', 'external');
define('USER_TYPE_AUDITOR', 'auditor');

define('USER_TYPES', [
    USER_TYPE_SYSTEM => 'System User',
    USER_TYPE_BANK => 'Bank User',
    USER_TYPE_EXTERNAL => 'External User',
    USER_TYPE_AUDITOR => 'Auditor',
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
define('COMPLIANCE_STATUS_APPROVED', 'approved');

define('COMPLIANCE_STATUSES', [
    COMPLIANCE_STATUS_PENDING => 'Pending',
    COMPLIANCE_STATUS_IN_PROGRESS => 'In Progress',
    COMPLIANCE_STATUS_COMPLETED => 'Completed',
    COMPLIANCE_STATUS_OVERDUE => 'Overdue',
    COMPLIANCE_STATUS_CANCELLED => 'Cancelled',
    COMPLIANCE_STATUS_REVIEW => 'Under Review',
    COMPLIANCE_STATUS_REJECTED => 'Rejected',
    COMPLIANCE_STATUS_APPROVED => 'Approved',
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
define('RISK_LEVEL_VERY_LOW', 'very_low');

define('RISK_LEVELS', [
    RISK_LEVEL_CRITICAL => 'Critical',
    RISK_LEVEL_HIGH => 'High',
    RISK_LEVEL_MEDIUM => 'Medium',
    RISK_LEVEL_LOW => 'Low',
    RISK_LEVEL_VERY_LOW => 'Very Low',
]);

define('RISK_SCORE_CRITICAL_MIN', 80);
define('RISK_SCORE_HIGH_MIN', 60);
define('RISK_SCORE_MEDIUM_MIN', 40);
define('RISK_SCORE_LOW_MIN', 20);
define('RISK_SCORE_VERY_LOW_MIN', 0);

// ============================================================
// RISK STATUS
// ============================================================

define('RISK_STATUS_IDENTIFIED', 'identified');
define('RISK_STATUS_ASSESSED', 'assessed');
define('RISK_STATUS_MITIGATING', 'mitigating');
define('RISK_STATUS_MITIGATED', 'mitigated');
define('RISK_STATUS_MONITORED', 'monitored');
define('RISK_STATUS_REVIEW', 'review');
define('RISK_STATUS_CLOSED', 'closed');
define('RISK_STATUS_REJECTED', 'rejected');

define('RISK_STATUSES', [
    RISK_STATUS_IDENTIFIED => 'Identified',
    RISK_STATUS_ASSESSED => 'Assessed',
    RISK_STATUS_MITIGATING => 'Mitigating',
    RISK_STATUS_MITIGATED => 'Mitigated',
    RISK_STATUS_MONITORED => 'Monitored',
    RISK_STATUS_REVIEW => 'Under Review',
    RISK_STATUS_CLOSED => 'Closed',
    RISK_STATUS_REJECTED => 'Rejected',
]);

// ============================================================
// AUDIT STATUS
// ============================================================

define('AUDIT_STATUS_PLANNED', 'planned');
define('AUDIT_STATUS_SCHEDULED', 'scheduled');
define('AUDIT_STATUS_IN_PROGRESS', 'in_progress');
define('AUDIT_STATUS_REVIEW', 'review');
define('AUDIT_STATUS_COMPLETED', 'completed');
define('AUDIT_STATUS_CLOSED', 'closed');
define('AUDIT_STATUS_CANCELLED', 'cancelled');

define('AUDIT_STATUSES', [
    AUDIT_STATUS_PLANNED => 'Planned',
    AUDIT_STATUS_SCHEDULED => 'Scheduled',
    AUDIT_STATUS_IN_PROGRESS => 'In Progress',
    AUDIT_STATUS_REVIEW => 'Under Review',
    AUDIT_STATUS_COMPLETED => 'Completed',
    AUDIT_STATUS_CLOSED => 'Closed',
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

define('FINDING_SEVERITY_COLORS', [
    FINDING_SEVERITY_CRITICAL => '#DC2626',
    FINDING_SEVERITY_HIGH => '#EF4444',
    FINDING_SEVERITY_MEDIUM => '#F59E0B',
    FINDING_SEVERITY_LOW => '#22C55E',
]);

// ============================================================
// AUDIT TYPES
// ============================================================

define('AUDIT_TYPE_INTERNAL', 'internal');
define('AUDIT_TYPE_EXTERNAL', 'external');
define('AUDIT_TYPE_REGULATORY', 'regulatory');
define('AUDIT_TYPE_FORENSIC', 'forensic');
define('AUDIT_TYPE_COMPLIANCE', 'compliance');
define('AUDIT_TYPE_IT', 'it');
define('AUDIT_TYPE_OPERATIONAL', 'operational');
define('AUDIT_TYPE_FINANCIAL', 'financial');

define('AUDIT_TYPES', [
    AUDIT_TYPE_INTERNAL => 'Internal Audit',
    AUDIT_TYPE_EXTERNAL => 'External Audit',
    AUDIT_TYPE_REGULATORY => 'Regulatory Audit',
    AUDIT_TYPE_FORENSIC => 'Forensic Audit',
    AUDIT_TYPE_COMPLIANCE => 'Compliance Audit',
    AUDIT_TYPE_IT => 'IT Audit',
    AUDIT_TYPE_OPERATIONAL => 'Operational Audit',
    AUDIT_TYPE_FINANCIAL => 'Financial Audit',
]);

// ============================================================
// SBP CIRCULAR CATEGORIES
// ============================================================

define('SBP_CATEGORY_PRUDENTIAL', 'prudential');
define('SBP_CATEGORY_OPERATIONAL', 'operational');
define('SBP_CATEGORY_COMPLIANCE', 'compliance');
define('SBP_CATEGORY_RISK', 'risk');
define('SBP_CATEGORY_GOVERNANCE', 'governance');
define('SBP_CATEGORY_REPORTING', 'reporting');
define('SBP_CATEGORY_AML', 'aml');
define('SBP_CATEGORY_CONSUMER', 'consumer');

define('SBP_CATEGORIES', [
    SBP_CATEGORY_PRUDENTIAL => 'Prudential Regulations',
    SBP_CATEGORY_OPERATIONAL => 'Operational Guidelines',
    SBP_CATEGORY_COMPLIANCE => 'Compliance Requirements',
    SBP_CATEGORY_RISK => 'Risk Management',
    SBP_CATEGORY_GOVERNANCE => 'Corporate Governance',
    SBP_CATEGORY_REPORTING => 'Reporting Requirements',
    SBP_CATEGORY_AML => 'AML/CFT',
    SBP_CATEGORY_CONSUMER => 'Consumer Protection',
]);

// ============================================================
// SBP CIRCULAR STATUS
// ============================================================

define('SBP_STATUS_ACTIVE', 'active');
define('SBP_STATUS_PENDING', 'pending');
define('SBP_STATUS_IMPLEMENTED', 'implemented');
define('SBP_STATUS_SUPERSEDED', 'superseded');
define('SBP_STATUS_WITHDRAWN', 'withdrawn');
define('SBP_STATUS_IN_PROGRESS', 'in_progress');

define('SBP_STATUSES', [
    SBP_STATUS_ACTIVE => 'Active',
    SBP_STATUS_PENDING => 'Pending Implementation',
    SBP_STATUS_IMPLEMENTED => 'Implemented',
    SBP_STATUS_SUPERSEDED => 'Superseded',
    SBP_STATUS_WITHDRAWN => 'Withdrawn',
    SBP_STATUS_IN_PROGRESS => 'In Progress',
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
define('NOTIFICATION_TYPE_TASK', 'task');
define('NOTIFICATION_TYPE_REMINDER', 'reminder');
define('NOTIFICATION_TYPE_ALERT', 'alert');

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
    NOTIFICATION_TYPE_TASK => 'Task Assignment',
    NOTIFICATION_TYPE_REMINDER => 'Reminder',
    NOTIFICATION_TYPE_ALERT => 'Alert',
]);

// ============================================================
// NOTIFICATION PRIORITY
// ============================================================

define('NOTIFICATION_PRIORITY_LOW', 'low');
define('NOTIFICATION_PRIORITY_MEDIUM', 'medium');
define('NOTIFICATION_PRIORITY_HIGH', 'high');
define('NOTIFICATION_PRIORITY_CRITICAL', 'critical');

define('NOTIFICATION_PRIORITIES', [
    NOTIFICATION_PRIORITY_LOW => 'Low',
    NOTIFICATION_PRIORITY_MEDIUM => 'Medium',
    NOTIFICATION_PRIORITY_HIGH => 'High',
    NOTIFICATION_PRIORITY_CRITICAL => 'Critical',
]);

// ============================================================
// HTTP STATUS CODES
// ============================================================

// 2xx Success
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_ACCEPTED', 202);
define('HTTP_NO_CONTENT', 204);
define('HTTP_RESET_CONTENT', 205);

// 3xx Redirection
define('HTTP_MOVED_PERMANENTLY', 301);
define('HTTP_FOUND', 302);
define('HTTP_SEE_OTHER', 303);
define('HTTP_NOT_MODIFIED', 304);
define('HTTP_TEMPORARY_REDIRECT', 307);
define('HTTP_PERMANENT_REDIRECT', 308);

// 4xx Client Errors
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_NOT_ACCEPTABLE', 406);
define('HTTP_REQUEST_TIMEOUT', 408);
define('HTTP_CONFLICT', 409);
define('HTTP_GONE', 410);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_TOO_MANY_REQUESTS', 429);

// 5xx Server Errors
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_NOT_IMPLEMENTED', 501);
define('HTTP_BAD_GATEWAY', 502);
define('HTTP_SERVICE_UNAVAILABLE', 503);
define('HTTP_GATEWAY_TIMEOUT', 504);

// ============================================================
// SUPPORTED LANGUAGES
// ============================================================

define('LANGUAGE_ENGLISH', 'en');
define('LANGUAGE_URDU', 'ur');
define('LANGUAGE_EN_PK', 'en_PK');

define('SUPPORTED_LANGUAGES', [
    LANGUAGE_ENGLISH => 'English',
    LANGUAGE_URDU => 'Urdu',
    LANGUAGE_EN_PK => 'English (Pakistan)',
]);

define('DEFAULT_LANGUAGE_CODE', LANGUAGE_ENGLISH);

// ============================================================
// DATE & TIME FORMATS (ISO Standards)
// ============================================================

define('DATE_FORMAT', 'Y-m-d');
define('DATE_FORMAT_DISPLAY', 'd M Y');
define('DATE_FORMAT_FULL', 'l, d F Y');
define('DATE_FORMAT_ISO', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('TIME_FORMAT_12H', 'h:i A');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DATETIME_FORMAT_DISPLAY', 'd M Y h:i A');
define('DATETIME_FORMAT_ISO', 'Y-m-d\TH:i:sP');
define('DATE_PICKER_FORMAT', 'yyyy-mm-dd');
define('TIME_PICKER_FORMAT', 'HH:mm');

// ============================================================
// FILE UPLOAD LIMITS
// ============================================================

define('MAX_FILE_SIZE', 10485760); // 10MB
define('MAX_FILE_SIZE_DISPLAY', '10MB');
define('MAX_IMAGE_SIZE', 5242880); // 5MB
define('MAX_DOCUMENT_SIZE', 10485760); // 10MB
define('MAX_AVATAR_SIZE', 2097152); // 2MB

// ============================================================
// FILE EXTENSIONS
// ============================================================

define('EXTENSIONS_IMAGES', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico']);
define('EXTENSIONS_DOCUMENTS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp']);
define('EXTENSIONS_ARCHIVES', ['zip', 'rar', '7z', 'tar', 'gz', 'bz2']);
define('EXTENSIONS_SPREADSHEETS', ['xls', 'xlsx', 'csv', 'ods']);
define('EXTENSIONS_PRESENTATIONS', ['ppt', 'pptx', 'odp']);
define('EXTENSIONS_ALLOWED', array_merge(
    EXTENSIONS_IMAGES,
    EXTENSIONS_DOCUMENTS,
    EXTENSIONS_ARCHIVES
));

// ============================================================
// SESSION KEYS
// ============================================================

define('SESSION_KEY_USER_ID', 'user_id');
define('SESSION_KEY_USERNAME', 'username');
define('SESSION_KEY_USER_ROLE', 'user_role');
define('SESSION_KEY_ROLE_LEVEL', 'role_level');
define('SESSION_KEY_USER_EMAIL', 'user_email');
define('SESSION_KEY_USER_NAME', 'user_name');
define('SESSION_KEY_USER_PERMISSIONS', 'user_permissions');
define('SESSION_KEY_AUTHENTICATED', 'authenticated');
define('SESSION_KEY_CSRF_TOKEN', 'csrf_token');
define('SESSION_KEY_LAST_ACTIVITY', 'last_activity');
define('SESSION_KEY_IP_ADDRESS', 'ip_address');
define('SESSION_KEY_USER_AGENT', 'user_agent');
define('SESSION_KEY_2FA_VERIFIED', '2fa_verified');
define('SESSION_KEY_2FA_USER_ID', '2fa_user_id');
define('SESSION_KEY_INTENDED_URL', 'intended_url');
define('SESSION_KEY_LAST_REGENERATED', 'last_regenerated');

// ============================================================
// CACHE KEYS
// ============================================================

define('CACHE_KEY_USER_PREFIX', 'user_');
define('CACHE_KEY_ROLE_PREFIX', 'role_');
define('CACHE_KEY_PERMISSION_PREFIX', 'permission_');
define('CACHE_KEY_SETTING_PREFIX', 'setting_');
define('CACHE_KEY_COMPLIANCE_PREFIX', 'compliance_');
define('CACHE_KEY_RISK_PREFIX', 'risk_');
define('CACHE_KEY_AUDIT_PREFIX', 'audit_');
define('CACHE_KEY_POLICY_PREFIX', 'policy_');
define('CACHE_KEY_REPORT_PREFIX', 'report_');
define('CACHE_KEY_DASHBOARD_PREFIX', 'dashboard_');
define('CACHE_KEY_AI_PREFIX', 'ai_');

// ============================================================
// DEFAULT VALUES
// ============================================================

define('DEFAULT_AVATAR', 'default-avatar.png');
define('DEFAULT_AVATAR_PATH', '/assets/images/default-avatar.png');
define('DEFAULT_LOGO', 'logo.png');
define('DEFAULT_LOGO_PATH', '/assets/images/logo.png');
define('DEFAULT_FAVICON', 'favicon.ico');
define('DEFAULT_FAVICON_PATH', '/favicon.ico');
define('DEFAULT_PAGE_TITLE', 'AI Banking GRC Platform');

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
define('DASHBOARD_ACTIVITY_LIMIT', 10);

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
define('AI_USE_CASE_RISK', 'risk');
define('AI_USE_CASE_POLICY', 'policy');
define('AI_USE_CASE_AUDIT', 'audit');
define('AI_USE_CASE_GENERAL', 'general');

define('AI_USE_CASES', [
    AI_USE_CASE_SUMMARIZE => 'Content Summarization',
    AI_USE_CASE_ANALYZE => 'Data Analysis',
    AI_USE_CASE_RECOMMEND => 'Recommendations',
    AI_USE_CASE_REPORT => 'Report Generation',
    AI_USE_CASE_COMPLIANCE => 'Compliance Assistance',
    AI_USE_CASE_RISK => 'Risk Analysis',
    AI_USE_CASE_POLICY => 'Policy Generation',
    AI_USE_CASE_AUDIT => 'Audit Assistance',
    AI_USE_CASE_GENERAL => 'General Assistance',
]);

// ============================================================
// LOG LEVELS
// ============================================================

define('LOG_LEVEL_DEBUG', 'debug');
define('LOG_LEVEL_INFO', 'info');
define('LOG_LEVEL_NOTICE', 'notice');
define('LOG_LEVEL_WARNING', 'warning');
define('LOG_LEVEL_ERROR', 'error');
define('LOG_LEVEL_CRITICAL', 'critical');
define('LOG_LEVEL_ALERT', 'alert');
define('LOG_LEVEL_EMERGENCY', 'emergency');

define('LOG_LEVELS', [
    LOG_LEVEL_DEBUG => 100,
    LOG_LEVEL_INFO => 200,
    LOG_LEVEL_NOTICE => 250,
    LOG_LEVEL_WARNING => 300,
    LOG_LEVEL_ERROR => 400,
    LOG_LEVEL_CRITICAL => 500,
    LOG_LEVEL_ALERT => 550,
    LOG_LEVEL_EMERGENCY => 600,
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
define('REGEX_CNIC', '/^[0-9]{5}-[0-9]{7}-[0-9]$/');
define('REGEX_IBAN', '/^PK[0-9]{2}[A-Z]{4}[0-9A-Z]{16}$/');
define('REGEX_SWIFT', '/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/');

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
    'CNY' => 'Chinese Yuan',
    'JPY' => 'Japanese Yen',
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
define('ENV_TESTING', 'testing');

define('ENVIRONMENTS', [
    ENV_DEVELOPMENT => 'Development',
    ENV_STAGING => 'Staging',
    ENV_PRODUCTION => 'Production',
    ENV_TESTING => 'Testing',
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
define('RATE_LIMIT_REGISTER', 3);
define('RATE_LIMIT_WINDOW', 60);
define('RATE_LIMIT_BLOCK_DURATION', 300);

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
define