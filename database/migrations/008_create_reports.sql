-- ============================================================
-- MIGRATION: 008_create_reports.sql
-- Description: Create report management tables
-- Author: GRC Platform Team
-- Date: 2026-08-03
-- ============================================================

-- ============================================================
-- TABLE: reports
-- Purpose: Store report definitions and configurations
-- ============================================================
CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    report_type VARCHAR(50) NOT NULL COMMENT 'executive, compliance, risk, audit, policy, sbp, custom',
    
    -- Configuration
    parameters JSON COMMENT 'Report parameters',
    format VARCHAR(20) DEFAULT 'pdf',
    template VARCHAR(100),
    
    -- Scheduling
    is_scheduled BOOLEAN DEFAULT FALSE,
    schedule_config JSON COMMENT 'Cron schedule configuration',
    next_run TIMESTAMP NULL,
    last_run TIMESTAMP NULL,
    
    -- Sharing
    is_public BOOLEAN DEFAULT FALSE,
    share_with JSON COMMENT 'Array of user IDs or roles',
    
    -- System
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_report_type (report_type),
    INDEX idx_report_scheduled (is_scheduled),
    INDEX idx_report_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: generated_reports
-- Purpose: Store generated report instances
-- ============================================================
CREATE TABLE IF NOT EXISTS generated_reports (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    report_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(20) NOT NULL,
    file_size INT UNSIGNED,
    parameters_used JSON,
    generated_by INT UNSIGNED NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    download_count INT DEFAULT 0,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    
    INDEX idx_generated_report (report_id),
    INDEX idx_generated_date (generated_at),
    INDEX idx_generated_user (generated_by),
    INDEX idx_generated_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;