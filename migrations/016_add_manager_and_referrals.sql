USE commission_db;

-- Add is_manager flag to users table
ALTER TABLE users ADD COLUMN is_manager TINYINT(1) NOT NULL DEFAULT 0 AFTER is_attorney;

-- Create referral_entries table
CREATE TABLE IF NOT EXISTS referral_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_number INT NULL COMMENT 'Monthly row number, resets per month',
    signed_date DATE NULL COMMENT 'Date client signed with firm',
    file_number VARCHAR(50) NULL COMMENT 'Maps to cases.case_number for soft link',
    client_name VARCHAR(300) NOT NULL COMMENT 'Client name, may include additional parties',
    date_of_loss DATE NULL COMMENT 'Accident/incident date',
    referred_by VARCHAR(200) NULL COMMENT 'Referral source: Dave/OK Chiro, Google, etc.',
    referred_to_provider VARCHAR(200) NULL COMMENT 'Medical provider referral',
    referred_to_body_shop VARCHAR(200) NULL COMMENT 'Auto body shop referral',
    referral_type VARCHAR(100) NULL COMMENT 'Special type: Pedestrian, etc.',
    case_manager_id INT NULL COMMENT 'Assigned employee user ID',
    remark TEXT NULL COMMENT 'Additional notes',
    entry_month VARCHAR(20) NULL COMMENT 'Month grouping for row_number reset, e.g. Feb. 2026',
    created_by INT NULL COMMENT 'User who created the entry',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
    FOREIGN KEY (case_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ref_signed (signed_date),
    INDEX idx_ref_file (file_number),
    INDEX idx_ref_manager (case_manager_id),
    INDEX idx_ref_month (entry_month),
    INDEX idx_ref_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Manager <-> Team member mapping table
CREATE TABLE IF NOT EXISTS manager_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manager_id INT NOT NULL,
    employee_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_manager_employee (manager_id, employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Set Jimi (id=6) as manager
UPDATE users SET is_manager = 1 WHERE id = 6;

-- Add Soyong (id=4) and Dave (id=3) to Jimi's team
INSERT INTO manager_team (manager_id, employee_id) VALUES (6, 3), (6, 4);

SELECT 'Migration 016 complete: is_manager, referral_entries, manager_team' as status;
