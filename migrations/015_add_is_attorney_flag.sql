USE commission_db;

-- Add is_attorney flag to users table
ALTER TABLE users ADD COLUMN is_attorney TINYINT(1) NOT NULL DEFAULT 0 AFTER role;

-- Set Chong (user_id=2) as attorney
UPDATE users SET is_attorney = 1 WHERE id = 2;

SELECT 'Migration 015_add_is_attorney_flag completed!' as status;
