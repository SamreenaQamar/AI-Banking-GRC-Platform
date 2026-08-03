-- ============================================================
-- MIGRATION: 006_create_audits.sql
-- Description: Create audit management tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: audits
-- Purpose: Define audit plans and schedules
-- ============================================================
CREATE TABLE IF NOT EXISTS audits (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    
    -- Audit Type
    audit_type ENUM('internal', 'external', 'regulatory', 'forensic', 'compliance', 'it', 'operational', 'financial') DEFAULT 'internal',
    audit_frequency ENUM('annual', 'semi_annual', 'quarterly', 'monthly', 'adhoc') DEFAULT 'annual',
    
    -- Scope
    scope_description TEXT NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    
    -- Schedule
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    planned_duration_days INT GENERATED ALWAYS AS (DATEDIFF(end_date, start_date)) STORED,
    
    -- Team
    lead_auditor_id INT UNSIGNED NOT NULL,
    audit_team TEXT,
    
    -- Status
    status ENUM('planned', 'scheduled', 'in_progress', 'review', 'completed', 'closed', 'cancelled') DEFAULT 'planned',
    
    -- Budget
    estimated_budget DECIMAL(15,2),
    actual_cost DECIMAL(15,2),
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_audit_reference (reference_number),
    INDEX idx_audit_status (status),
    INDEX idx_audit_department (department_id),
    INDEX idx_audit_dates (start_date, end_date),
    INDEX idx_audit_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_findings
-- Purpose: Record audit findings and observations
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_findings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    audit_id INT UNSIGNED NOT NULL,
    finding_code VARCHAR(50) UNIQUE NOT NULL,
    
    -- Details
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Severity
    severity ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    impact_description TEXT,
    
    -- Root Cause
    root_cause TEXT,
    evidence_path VARCHAR(500),
    
    -- Recommendation
    recommendation TEXT NOT NULL,
    management_response TEXT,
    
    -- Status
    status ENUM('open', 'in_progress', 'resolved', 'verified', 'closed', 'accepted_risk') DEFAULT 'open',
    
    -- Assignment
    assigned_to INT UNSIGNED,
    assigned_by INT UNSIGNED,
    resolved_by INT UNSIGNED,
    verified_by INT UNSIGNED,
    
    -- Dates
    finding_date DATE NOT NULL,
    resolution_date DATE,
    verification_date DATE,
    review_date DATE,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (audit_id) REFERENCES audits(id) ON DELETE CASCADE,
    
    INDEX idx_finding_code (finding_code),
    INDEX idx_finding_status (status),
    INDEX idx_finding_severity (severity),
    INDEX idx_finding_audit (audit_id),
    INDEX idx_finding_assigned (assigned_to),
    INDEX idx_finding_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_evidence
-- Purpose: Store evidence for audit findings
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_evidence (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    audit_id INT UNSIGNED NOT NULL,
    finding_id INT UNSIGNED NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT UNSIGNED,
    description TEXT,
    type ENUM('document', 'screenshot', 'video', 'audio', 'certificate', 'report', 'log', 'test_result', 'interview', 'other') DEFAULT 'document',
    uploaded_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (audit_id) REFERENCES audits(id) ON DELETE CASCADE,
    FOREIGN KEY (finding_id) REFERENCES audit_findings(id) ON DELETE SET NULL,
    
    INDEX idx_evidence_audit (audit_id),
    INDEX idx_evidence_finding (finding_id),
    INDEX idx_evidence_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;