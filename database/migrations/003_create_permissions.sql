-- ============================================================
-- MIGRATION: 003_create_permissions.sql
-- Description: Create permissions table with default permissions
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: permissions
-- Purpose: Granular permissions for fine-grained access control
-- ============================================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Permission slug e.g., user_create',
    display_name VARCHAR(100) NOT NULL COMMENT 'Human readable permission',
    module VARCHAR(50) NOT NULL COMMENT 'Module name e.g., users, compliance',
    description TEXT COMMENT 'Permission description',
    created_by INT UNSIGNED COMMENT 'User who created this record',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    
    INDEX idx_permission_name (name),
    INDEX idx_permission_module (module),
    INDEX idx_permission_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT PERMISSIONS
-- ============================================================

-- User Management
INSERT INTO permissions (name, display_name, module, description, created_at) VALUES
('user_create', 'Create Users', 'users', 'Ability to create new users', NOW()),
('user_view', 'View Users', 'users', 'Ability to view user profiles', NOW()),
('user_update', 'Update Users', 'users', 'Ability to update user information', NOW()),
('user_delete', 'Delete Users', 'users', 'Ability to delete users', NOW()),
('user_role_assign', 'Assign Roles', 'users', 'Ability to assign roles to users', NOW()),
('user_status', 'Manage Status', 'users', 'Ability to update user status', NOW()),

-- Compliance
('compliance_create', 'Create Compliance Tasks', 'compliance', 'Ability to create compliance tasks', NOW()),
('compliance_view', 'View Compliance Tasks', 'compliance', 'Ability to view compliance tasks', NOW()),
('compliance_update', 'Update Compliance Tasks', 'compliance', 'Ability to update compliance tasks', NOW()),
('compliance_delete', 'Delete Compliance Tasks', 'compliance', 'Ability to delete compliance tasks', NOW()),
('compliance_approve', 'Approve Compliance', 'compliance', 'Ability to approve compliance tasks', NOW()),
('compliance_review', 'Review Compliance', 'compliance', 'Ability to review compliance tasks', NOW()),
('compliance_evidence', 'Manage Evidence', 'compliance', 'Ability to upload and verify evidence', NOW()),

-- Risk Management
('risk_create', 'Create Risk Entries', 'risk', 'Ability to create risk entries', NOW()),
('risk_view', 'View Risk Entries', 'risk', 'Ability to view risk entries', NOW()),
('risk_update', 'Update Risk Entries', 'risk', 'Ability to update risk entries', NOW()),
('risk_delete', 'Delete Risk Entries', 'risk', 'Ability to delete risk entries', NOW()),
('risk_assess', 'Assess Risks', 'risk', 'Ability to perform risk assessments', NOW()),
('risk_mitigate', 'Mitigate Risks', 'risk', 'Ability to create mitigation plans', NOW()),
('risk_approve', 'Approve Risks', 'risk', 'Ability to approve risk assessments', NOW()),

-- Audit
('audit_create', 'Create Audit Plans', 'audit', 'Ability to create audit plans', NOW()),
('audit_view', 'View Audit Plans', 'audit', 'Ability to view audit plans', NOW()),
('audit_update', 'Update Audit Plans', 'audit', 'Ability to update audit plans', NOW()),
('audit_delete', 'Delete Audit Plans', 'audit', 'Ability to delete audit plans', NOW()),
('audit_execute', 'Execute Audits', 'audit', 'Ability to execute audit plans', NOW()),
('audit_review', 'Review Audits', 'audit', 'Ability to review audit findings', NOW()),
('audit_close', 'Close Audits', 'audit', 'Ability to close audit plans', NOW()),

-- Policies
('policy_create', 'Create Policies', 'policies', 'Ability to create policies', NOW()),
('policy_view', 'View Policies', 'policies', 'Ability to view policies', NOW()),
('policy_update', 'Update Policies', 'policies', 'Ability to update policies', NOW()),
('policy_delete', 'Delete Policies', 'policies', 'Ability to delete policies', NOW()),
('policy_approve', 'Approve Policies', 'policies', 'Ability to approve policies', NOW()),
('policy_publish', 'Publish Policies', 'policies', 'Ability to publish policies', NOW()),
('policy_archive', 'Archive Policies', 'policies', 'Ability to archive policies', NOW()),
('policy_acknowledge', 'Acknowledge Policies', 'policies', 'Ability to acknowledge policies', NOW()),

-- SBP Circulars
('sbp_create', 'Create SBP Circulars', 'sbp', 'Ability to create SBP circulars', NOW()),
('sbp_view', 'View SBP Circulars', 'sbp', 'Ability to view SBP circulars', NOW()),
('sbp_update', 'Update SBP Circulars', 'sbp', 'Ability to update SBP circulars', NOW()),
('sbp_delete', 'Delete SBP Circulars', 'sbp', 'Ability to delete SBP circulars', NOW()),
('sbp_implement', 'Implement SBP Circulars', 'sbp', 'Ability to mark circulars as implemented', NOW()),

-- Reports
('report_create', 'Create Reports', 'reports', 'Ability to create reports', NOW()),
('report_view', 'View Reports', 'reports', 'Ability to view reports', NOW()),
('report_export', 'Export Reports', 'reports', 'Ability to export reports', NOW()),
('report_schedule', 'Schedule Reports', 'reports', 'Ability to schedule reports', NOW()),

-- AI
('ai_chat', 'AI Chat', 'ai', 'Ability to use AI chat', NOW()),
('ai_policy_generate', 'AI Policy Generate', 'ai', 'Ability to generate policies with AI', NOW()),
('ai_risk_analyze', 'AI Risk Analyze', 'ai', 'Ability to analyze risks with AI', NOW()),
('ai_gap_analyze', 'AI Gap Analyze', 'ai', 'Ability to perform gap analysis with AI', NOW()),

-- Settings
('settings_view', 'View Settings', 'settings', 'Ability to view system settings', NOW()),
('settings_update', 'Update Settings', 'settings', 'Ability to update system settings', NOW()),
('settings_backup', 'Backup Settings', 'settings', 'Ability to backup system settings', NOW()),
('settings_restore', 'Restore Settings', 'settings', 'Ability to restore system settings', NOW());