-- Litigation Cases Table
CREATE TABLE IF NOT EXISTS litigation_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_number VARCHAR(100) NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    injury_type VARCHAR(100),
    opposing_insurance VARCHAR(255),
    litigation_stage ENUM('in_progress', 'settled', 'waiting_for_check', 'check_arrived', 'disbursed', 'completed') DEFAULT 'in_progress',
    next_deadline DATE,
    days_open INT DEFAULT 0,
    settlement_amount DECIMAL(12, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settled_date DATETIME,
    check_arrived_date DATETIME,
    disbursed_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_stage (user_id, litigation_stage),
    INDEX idx_case_number (case_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Litigation Case Updates/History Table
CREATE TABLE IF NOT EXISTS litigation_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    user_id INT NOT NULL,
    old_stage VARCHAR(50),
    new_stage VARCHAR(50),
    update_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES litigation_cases(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_case_id (case_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
