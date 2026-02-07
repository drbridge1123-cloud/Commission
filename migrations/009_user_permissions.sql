USE commission_db;

-- Add permissions JSON column to users table
ALTER TABLE users
  ADD COLUMN permissions JSON DEFAULT NULL AFTER uses_presuit_offer;

-- Set default permissions for all existing users
-- Admins get all permissions enabled by default
UPDATE users SET permissions = '{"can_request_traffic": true}' WHERE role = 'admin';

-- Chong (user_id=2) keeps existing traffic access
UPDATE users SET permissions = '{"can_request_traffic": true}' WHERE id = 2;

-- Other employees start with traffic disabled
UPDATE users SET permissions = '{"can_request_traffic": false}' WHERE role = 'employee' AND id != 2 AND permissions IS NULL;

SELECT 'Migration 009_user_permissions.sql completed!' as status;
