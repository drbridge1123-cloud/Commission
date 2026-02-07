USE commission_db;

-- Table for file attachments on traffic cases
CREATE TABLE IF NOT EXISTS traffic_case_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL COMMENT 'Stored filename on disk',
    original_name VARCHAR(255) NOT NULL COMMENT 'Original uploaded filename',
    file_type VARCHAR(100) DEFAULT NULL,
    file_size INT DEFAULT 0 COMMENT 'Size in bytes',
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES traffic_cases(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_case_id (case_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Migration 010_traffic_case_files.sql completed!' as status;
