-- UIM (Underinsured Motorist) Phase
-- Adds 'uim' to phase ENUM and UIM-related columns

-- Phase ENUM에 'uim' 추가
ALTER TABLE cases MODIFY COLUMN phase ENUM('demand', 'litigation', 'uim', 'settled') DEFAULT 'demand';

-- Policy Limit 플래그
ALTER TABLE cases ADD COLUMN is_policy_limit TINYINT(1) NOT NULL DEFAULT 0 AFTER is_marketing;

-- UIM phase 날짜/단계
ALTER TABLE cases ADD COLUMN uim_start_date DATE NULL AFTER litigation_duration_days;
ALTER TABLE cases ADD COLUMN uim_demand_out_date DATE NULL AFTER uim_start_date;
ALTER TABLE cases ADD COLUMN uim_negotiate_date DATE NULL AFTER uim_demand_out_date;
ALTER TABLE cases ADD COLUMN uim_settled_date DATE NULL AFTER uim_negotiate_date;
ALTER TABLE cases ADD COLUMN uim_duration_days INT NULL AFTER uim_settled_date;

-- UIM settlement 데이터 (첫 settlement는 기존 컬럼 사용)
ALTER TABLE cases ADD COLUMN uim_settled DECIMAL(15,2) NULL AFTER uim_duration_days;
ALTER TABLE cases ADD COLUMN uim_legal_fee DECIMAL(15,2) NULL AFTER uim_settled;
ALTER TABLE cases ADD COLUMN uim_discounted_legal_fee DECIMAL(15,2) NULL AFTER uim_legal_fee;
ALTER TABLE cases ADD COLUMN uim_commission DECIMAL(15,2) NULL AFTER uim_discounted_legal_fee;
