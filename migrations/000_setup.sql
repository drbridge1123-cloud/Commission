-- Commission Calculator Database Setup
-- Run this in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS commission_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE commission_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    uses_presuit_offer TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cases table
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
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Messages table (for admin to employee communication)
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
    FOREIGN KEY (to_user_id) REFERENCES users(id)
);

-- Insert users (password is hashed version of simple passwords - change in production!)
-- Default passwords: admin123 for Daniel, employee123 for others
INSERT INTO users (username, password, display_name, role, commission_rate, uses_presuit_offer) VALUES
('daniel', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbLgxQmqQNqLtMXMuVyRNHOMwGKGa', 'Daniel (Admin)', 'admin', 10.00, 1),
('charb', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Charb', 'employee', 10.00, 1),
('chong', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Chong', 'employee', 7.50, 1),
('soyong', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Soyong', 'employee', 15.00, 0),
('dave', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Dave', 'employee', 15.00, 0),
('ella', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Ella', 'employee', 15.00, 0),
('jimi', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Jimi', 'employee', 15.00, 0);

-- Note: Default passwords
-- Daniel (admin): admin123
-- All employees: employee123
-- CHANGE THESE IN PRODUCTION!
