-- Commission Calculator Full Schema
-- Complete database setup (combines all migrations)
-- Run this for fresh installation

CREATE DATABASE IF NOT EXISTS commission_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE commission_db;

-- ============================================
-- Users table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    uses_presuit_offer TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Cases table
-- ============================================
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_type VARCHAR(50) NOT NULL,
    case_number VARCHAR(50) NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    resolution_type VARCHAR(100),
    fee_rate DECIMAL(5,2) NOT NULL DEFAULT 33.33,
    month VARCHAR(20) NOT NULL,
    settled DECIMAL(15,2) NOT NULL DEFAULT 0,
    presuit_offer DECIMAL(15,2) NOT NULL DEFAULT 0,
    difference DECIMAL(15,2) NOT NULL DEFAULT 0,
    legal_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
    discounted_legal_fee DECIMAL(15,2) NOT NULL DEFAULT 0,
    commission DECIMAL(15,2) NOT NULL DEFAULT 0,
    note TEXT,
    check_received TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending', 'paid', 'rejected') NOT NULL DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_cases_user_id (user_id),
    INDEX idx_cases_status (status),
    INDEX idx_cases_month (month),
    INDEX idx_cases_submitted_at (submitted_at),
    INDEX idx_cases_reviewed_at (reviewed_at),
    INDEX idx_cases_user_status (user_id, status),
    INDEX idx_cases_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Messages table
-- ============================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id),
    INDEX idx_messages_to_user (to_user_id),
    INDEX idx_messages_from_user (from_user_id),
    INDEX idx_messages_is_read (is_read),
    INDEX idx_messages_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Audit logs table
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
-- Litigation cases table (optional)
-- ============================================
CREATE TABLE IF NOT EXISTS litigation_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_number VARCHAR(50) NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    case_type VARCHAR(50) DEFAULT 'Litigation',
    litigation_stage ENUM('in_progress', 'settled', 'closed') DEFAULT 'in_progress',
    next_deadline DATE NULL,
    deadline_description VARCHAR(255) NULL,
    settlement_amount DECIMAL(15,2) NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_lit_user_id (user_id),
    INDEX idx_lit_stage (litigation_stage),
    INDEX idx_lit_deadline (next_deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Default admin user
-- Password: admin123 (change in production!)
-- ============================================
INSERT INTO users (username, password, display_name, role, commission_rate, uses_presuit_offer) VALUES
('daniel', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbLgxQmqQNqLtMXMuVyRNHOMwGKGa', 'Daniel (Admin)', 'admin', 10.00, 1)
ON DUPLICATE KEY UPDATE id=id;

SELECT 'Full schema installed successfully!' as status;
