-- Migration: Add indexes and audit logging
-- Run this after initial setup.sql
-- Compatible with MySQL 5.7+

USE commission_db;

-- ============================================
-- Add indexes for performance (skip if exists)
-- ============================================

-- Cases table indexes
DROP PROCEDURE IF EXISTS add_indexes;
DELIMITER //
CREATE PROCEDURE add_indexes()
BEGIN
    -- Cases indexes
    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_user_id') THEN
        CREATE INDEX idx_cases_user_id ON cases(user_id);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_status') THEN
        CREATE INDEX idx_cases_status ON cases(status);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_month') THEN
        CREATE INDEX idx_cases_month ON cases(month);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_submitted_at') THEN
        CREATE INDEX idx_cases_submitted_at ON cases(submitted_at);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_reviewed_at') THEN
        CREATE INDEX idx_cases_reviewed_at ON cases(reviewed_at);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_user_status') THEN
        CREATE INDEX idx_cases_user_status ON cases(user_id, status);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'cases' AND index_name = 'idx_cases_deleted_at') THEN
        CREATE INDEX idx_cases_deleted_at ON cases(deleted_at);
    END IF;

    -- Messages indexes
    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'messages' AND index_name = 'idx_messages_to_user') THEN
        CREATE INDEX idx_messages_to_user ON messages(to_user_id);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'messages' AND index_name = 'idx_messages_from_user') THEN
        CREATE INDEX idx_messages_from_user ON messages(from_user_id);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'messages' AND index_name = 'idx_messages_is_read') THEN
        CREATE INDEX idx_messages_is_read ON messages(is_read);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'messages' AND index_name = 'idx_messages_created_at') THEN
        CREATE INDEX idx_messages_created_at ON messages(created_at);
    END IF;

    -- Users indexes
    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'idx_users_role') THEN
        CREATE INDEX idx_users_role ON users(role);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'idx_users_is_active') THEN
        CREATE INDEX idx_users_is_active ON users(is_active);
    END IF;
END //
DELIMITER ;

CALL add_indexes();
DROP PROCEDURE IF EXISTS add_indexes;

-- ============================================
-- Create audit_logs table
-- ============================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NULL,
    record_id INT NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_user_id (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_table_name (table_name),
    INDEX idx_audit_created_at (created_at),
    INDEX idx_audit_record_id (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Add soft delete column to cases (if not exists)
-- ============================================

DROP PROCEDURE IF EXISTS add_deleted_at_column;
DELIMITER //
CREATE PROCEDURE add_deleted_at_column()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'cases' AND column_name = 'deleted_at') THEN
        ALTER TABLE cases ADD COLUMN deleted_at TIMESTAMP NULL;
    END IF;
END //
DELIMITER ;

CALL add_deleted_at_column();
DROP PROCEDURE IF EXISTS add_deleted_at_column;

-- ============================================
-- Note: rate_limits table removed - using session-based rate limiting instead
-- ============================================

SELECT 'Migration completed successfully!' as status;
