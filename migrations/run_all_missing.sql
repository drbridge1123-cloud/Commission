-- ============================================
-- Run ALL missing migrations at once
-- Safe to run multiple times (uses IF NOT EXISTS)
-- ============================================

USE commission_db;

-- ============================================
-- 004: Cases table extensions (Chong system)
-- ============================================
-- Add columns if they don't exist (MySQL will error if they already exist, so we use a procedure)
DELIMITER //
CREATE PROCEDURE add_columns_if_not_exists()
BEGIN
    -- cases extensions
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='phase') THEN
        ALTER TABLE cases ADD COLUMN phase ENUM('demand','litigation','settled') DEFAULT NULL AFTER status;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='assigned_date') THEN
        ALTER TABLE cases ADD COLUMN assigned_date DATE DEFAULT NULL AFTER check_received;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='demand_deadline') THEN
        ALTER TABLE cases ADD COLUMN demand_deadline DATE DEFAULT NULL AFTER assigned_date;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='demand_settled_date') THEN
        ALTER TABLE cases ADD COLUMN demand_settled_date DATE DEFAULT NULL AFTER demand_deadline;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='litigation_start_date') THEN
        ALTER TABLE cases ADD COLUMN litigation_start_date DATE DEFAULT NULL AFTER demand_settled_date;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='litigation_settled_date') THEN
        ALTER TABLE cases ADD COLUMN litigation_settled_date DATE DEFAULT NULL AFTER litigation_start_date;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='commission_type') THEN
        ALTER TABLE cases ADD COLUMN commission_type VARCHAR(50) DEFAULT NULL AFTER commission;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='demand_duration_days') THEN
        ALTER TABLE cases ADD COLUMN demand_duration_days INT DEFAULT NULL AFTER commission_type;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='litigation_duration_days') THEN
        ALTER TABLE cases ADD COLUMN litigation_duration_days INT DEFAULT NULL AFTER demand_duration_days;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='cases' AND COLUMN_NAME='total_duration_days') THEN
        ALTER TABLE cases ADD COLUMN total_duration_days INT DEFAULT NULL AFTER litigation_duration_days;
    END IF;

    -- 009: permissions column on users
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='commission_db' AND TABLE_NAME='users' AND COLUMN_NAME='permissions') THEN
        ALTER TABLE users ADD COLUMN permissions JSON DEFAULT NULL AFTER uses_presuit_offer;
    END IF;
END //
DELIMITER ;

CALL add_columns_if_not_exists();
DROP PROCEDURE IF EXISTS add_columns_if_not_exists;

-- Set default permissions
UPDATE users SET permissions = '{"can_request_traffic": true}' WHERE role = 'admin' AND permissions IS NULL;
UPDATE users SET permissions = '{"can_request_traffic": false}' WHERE role = 'employee' AND permissions IS NULL;

-- ============================================
-- 006: Deadline Extension Requests
-- ============================================
CREATE TABLE IF NOT EXISTS deadline_extension_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    user_id INT NOT NULL,
    current_deadline DATE NOT NULL,
    requested_deadline DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_note TEXT,
    reviewed_by INT,
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_dr_case_id (case_id),
    INDEX idx_dr_user_id (user_id),
    INDEX idx_dr_status (status),
    INDEX idx_dr_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 007: Add in_progress status to cases
-- ============================================
ALTER TABLE cases
MODIFY COLUMN status ENUM('pending', 'in_progress', 'paid', 'rejected') NOT NULL DEFAULT 'pending';

-- ============================================
-- 004: Performance Snapshots
-- ============================================
CREATE TABLE IF NOT EXISTS performance_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    snapshot_month VARCHAR(7) NOT NULL,
    cases_settled INT DEFAULT 0,
    demand_settled INT DEFAULT 0,
    litigation_settled INT DEFAULT 0,
    total_commission DECIMAL(15,2) DEFAULT 0,
    new_cases_received INT DEFAULT 0,
    avg_demand_days DECIMAL(5,1) DEFAULT 0,
    avg_litigation_days DECIMAL(5,1) DEFAULT 0,
    avg_total_days DECIMAL(5,1) DEFAULT 0,
    demand_resolution_rate DECIMAL(5,2) DEFAULT 0,
    deadline_compliance_rate DECIMAL(5,2) DEFAULT 0,
    avg_days_before_deadline DECIMAL(5,1) DEFAULT 0,
    overdue_cases_count INT DEFAULT 0,
    active_cases_count INT DEFAULT 0,
    max_concurrent_cases INT DEFAULT 0,
    capacity_score DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_employee_month (employee_id, snapshot_month),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ps_employee_id (employee_id),
    INDEX idx_ps_snapshot_month (snapshot_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Traffic Cases (no migration existed)
-- ============================================
CREATE TABLE IF NOT EXISTS traffic_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    client_phone VARCHAR(50) DEFAULT NULL,
    client_email VARCHAR(200) DEFAULT NULL,
    court VARCHAR(100) DEFAULT NULL,
    court_date DATE DEFAULT NULL,
    charge VARCHAR(200) DEFAULT NULL,
    case_number VARCHAR(50) DEFAULT NULL,
    prosecutor_offer TEXT DEFAULT NULL,
    disposition VARCHAR(20) DEFAULT 'pending',
    commission DECIMAL(10,2) DEFAULT 0,
    discovery TINYINT(1) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    note TEXT DEFAULT NULL,
    referral_source VARCHAR(100) DEFAULT NULL,
    paid TINYINT(1) DEFAULT 0,
    noa_sent_date DATE DEFAULT NULL,
    citation_issued_date DATE DEFAULT NULL,
    request_id INT DEFAULT NULL,
    requested_by INT DEFAULT NULL,
    resolved_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tc_user_id (user_id),
    INDEX idx_tc_status (status),
    INDEX idx_tc_court_date (court_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Traffic Requests (no migration existed)
-- ============================================
CREATE TABLE IF NOT EXISTS traffic_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requested_by INT NOT NULL,
    assigned_to INT NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    client_phone VARCHAR(50) DEFAULT NULL,
    client_email VARCHAR(200) DEFAULT NULL,
    court VARCHAR(100) DEFAULT NULL,
    court_date DATE DEFAULT NULL,
    charge VARCHAR(200) DEFAULT NULL,
    case_number VARCHAR(50) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    referral_source VARCHAR(100) DEFAULT NULL,
    citation_issued_date DATE DEFAULT NULL,
    status ENUM('pending', 'accepted', 'denied') DEFAULT 'pending',
    deny_reason TEXT DEFAULT NULL,
    responded_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    INDEX idx_trq_requested_by (requested_by),
    INDEX idx_trq_assigned_to (assigned_to),
    INDEX idx_trq_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Traffic Case Files (migration 010)
-- ============================================
CREATE TABLE IF NOT EXISTS traffic_case_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) DEFAULT NULL,
    file_size INT DEFAULT 0,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES traffic_cases(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_tcf_case_id (case_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Notifications (no migration existed)
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    data JSON DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user_id (user_id),
    INDEX idx_notif_is_read (is_read),
    INDEX idx_notif_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Employee Goals (migration 012)
-- ============================================
CREATE TABLE IF NOT EXISTS employee_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    year INT NOT NULL,
    target_cases INT NOT NULL DEFAULT 50,
    target_legal_fee DECIMAL(15,2) NOT NULL DEFAULT 500000.00,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    UNIQUE KEY uk_user_year (user_id, year),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Done!
-- ============================================
SELECT 'All missing migrations applied successfully!' as status;
