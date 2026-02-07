USE commission_db;

-- Add referral_source column to traffic_requests table
ALTER TABLE traffic_requests ADD COLUMN referral_source VARCHAR(100) DEFAULT NULL AFTER note;

SELECT 'Migration 011_traffic_requests_referral.sql completed!' as status;
