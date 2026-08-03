-- ============================================================
-- MIGRATION: 004_create_compliance.sql
-- Description: Create compliance tables (circulars, tasks, gap analysis, recommendations)
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: circulars (SBP Circulars)
-- Purpose: Track State Bank of Pakistan circulars
-- ============================================================
CREATE TABLE IF NOT EXISTS circulars (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    circular_number VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    -- Classification
    category ENUM('prudential', 'operational', 'compliance', 'risk', 'governance', 'reporting', 'aml', 'consumer') DEFAULT 'compliance',
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    
    -- Dates
    issuance_date DATE NOT NULL,
    effective_date DATE NOT NULL,
    compliance_deadline DATE NOT NULL,
    review_date DATE,
    
    -- Document
    document_path VARCHAR(500),
    document_type VARCHAR(50),
    
    -- AI Analysis
    ai_summary TEXT,
    ai_analysis JSON,
    ai_analyzed_at TIMESTAMP NULL,
    
    -- Status
    status ENUM('active', 'pending', 'in_progress', 'implemented', 'superseded', 'withdrawn') DEFAULT 'pending',
    
    -- Implementation
    implemented_by INT UNSIGNED,
    implementation_date DATE,
    implementation_notes TEXT,
    
    -- Supersedes
    supersedes_circular_id INT UNSIGNED,
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_circular_number (circular_number),
    INDEX idx_circular_status (status),
    INDEX idx_circular_category (category),
    INDEX idx_circular_effective (effective_date),
    INDEX idx_circular_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: compliance_tasks
-- Purpose: Track compliance obligations and tasks
-- ============================================================
CREATE TABLE IF NOT EXISTS compliance_tasks (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reference_number VARCHAR(50) UNIQUE,
    category_id INT UNSIGNED NOT NULL,
    framework_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    
    -- Task Details
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'overdue', 'cancelled', 'review', 'rejected', 'approved') DEFAULT 'pending',
    
    -- Dates
    due_date DATE NOT NULL,
    completed_date DATE NULL,
    reminder_date DATE NULL,
    review_date DATE NULL,
    
    -- Metrics
    compliance_score DECIMAL(5,2) DEFAULT 0,
    evidence_required BOOLEAN DEFAULT TRUE,
    auto_review BOOLEAN DEFAULT FALSE,
    
    -- Assignment
    assigned_to INT UNSIGNED,
    assigned_by INT UNSIGNED,
    reviewed_by INT UNSIGNED,
    
    -- System
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_task_reference (reference_number),
    INDEX idx_task_status (status),
    INDEX idx_task_due_date (due_date),
    INDEX idx_task_assigned (assigned_to),
    INDEX idx_task_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: gap_analysis
-- Purpose: Store compliance gap analysis results
-- ============================================================
CREATE TABLE IF NOT EXISTS gap_analysis (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    framework_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    gap_title VARCHAR(255) NOT NULL,
    gap_description TEXT NOT NULL,
    severity ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    recommendation TEXT,
    action_plan TEXT,
    assigned_to INT UNSIGNED,
    due_date DATE,
    resolved_at TIMESTAMP NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_gap_framework (framework_id),
    INDEX idx_gap_status (status),
    INDEX idx_gap_severity (severity),
    INDEX idx_gap_assigned (assigned_to),
    INDEX idx_gap_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: recommendations
-- Purpose: Store compliance recommendations
-- ============================================================
CREATE TABLE IF NOT EXISTS recommendations (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    priority ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('pending', 'accepted', 'rejected', 'implemented') DEFAULT 'pending',
    source_type VARCHAR(50) COMMENT 'gap_analysis, audit, ai',
    source_id INT UNSIGNED,
    implementation_plan TEXT,
    expected_benefits TEXT,
    assigned_to INT UNSIGNED,
    deadline DATE,
    implemented_at TIMESTAMP NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_rec_status (status),
    INDEX idx_rec_priority (priority),
    INDEX idx_rec_assigned (assigned_to),
    INDEX idx_rec_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;