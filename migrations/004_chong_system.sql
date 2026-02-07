-- ============================================
-- Chong Commission & Performance System
-- Migration 004
-- Date: 2026-02-03
-- ============================================

USE commission_db;

-- ============================================
-- 1. cases 테이블 확장 (기존 데이터에 영향 없음)
-- ============================================
ALTER TABLE cases
  ADD COLUMN phase ENUM('demand', 'litigation', 'settled') DEFAULT NULL AFTER status,
  ADD COLUMN assigned_date DATE DEFAULT NULL AFTER check_received,
  ADD COLUMN demand_deadline DATE DEFAULT NULL AFTER assigned_date,
  ADD COLUMN demand_settled_date DATE DEFAULT NULL AFTER demand_deadline,
  ADD COLUMN litigation_start_date DATE DEFAULT NULL AFTER demand_settled_date,
  ADD COLUMN litigation_settled_date DATE DEFAULT NULL AFTER litigation_start_date,
  ADD COLUMN commission_type VARCHAR(50) DEFAULT NULL AFTER commission,
  ADD COLUMN demand_duration_days INT DEFAULT NULL AFTER commission_type,
  ADD COLUMN litigation_duration_days INT DEFAULT NULL AFTER demand_duration_days,
  ADD COLUMN total_duration_days INT DEFAULT NULL AFTER litigation_duration_days;

-- phase 인덱스
ALTER TABLE cases ADD INDEX idx_user_phase (user_id, phase);
ALTER TABLE cases ADD INDEX idx_phase_status (phase, status);
ALTER TABLE cases ADD INDEX idx_assigned_date (assigned_date);
ALTER TABLE cases ADD INDEX idx_demand_deadline (demand_deadline);

-- ============================================
-- 2. performance_snapshots 테이블 (Owner Analytics)
-- ============================================
CREATE TABLE IF NOT EXISTS performance_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    snapshot_month VARCHAR(7) NOT NULL,        -- '2026-01' 형식

    -- 생산성 (Productivity)
    cases_settled INT DEFAULT 0,
    demand_settled INT DEFAULT 0,
    litigation_settled INT DEFAULT 0,
    total_commission DECIMAL(15,2) DEFAULT 0,
    new_cases_received INT DEFAULT 0,

    -- 효율성 (Efficiency)
    avg_demand_days DECIMAL(5,1) DEFAULT 0,
    avg_litigation_days DECIMAL(5,1) DEFAULT 0,
    avg_total_days DECIMAL(5,1) DEFAULT 0,
    demand_resolution_rate DECIMAL(5,2) DEFAULT 0,  -- % 소송 없이 해결

    -- 시간 관리 (Time Management)
    deadline_compliance_rate DECIMAL(5,2) DEFAULT 0, -- % 기한 내 완료
    avg_days_before_deadline DECIMAL(5,1) DEFAULT 0,
    overdue_cases_count INT DEFAULT 0,

    -- Capacity
    active_cases_count INT DEFAULT 0,
    max_concurrent_cases INT DEFAULT 0,
    capacity_score DECIMAL(5,2) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_employee_month (employee_id, snapshot_month),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_employee_id (employee_id),
    INDEX idx_snapshot_month (snapshot_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. 기존 Chong 케이스를 settled 상태로 마이그레이션
-- (user_id = 2가 Chong)
-- ============================================
UPDATE cases
SET phase = 'settled',
    commission_type = 'legacy',
    assigned_date = DATE(submitted_at)
WHERE user_id = 2
  AND phase IS NULL
  AND status IN ('pending', 'paid');

-- ============================================
-- 완료 메시지
-- ============================================
SELECT 'Migration 004_chong_system.sql completed successfully!' as status;
SELECT COUNT(*) as 'Chong cases migrated' FROM cases WHERE user_id = 2 AND commission_type = 'legacy';
