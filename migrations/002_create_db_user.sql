-- ============================================
-- Commission DB - Create Dedicated User
-- Run this script as MySQL root user
-- ============================================

-- IMPORTANT: Change the password before running!
-- Replace 'YourStrongPassword123!' with a secure password

-- Step 1: Create the dedicated user
CREATE USER IF NOT EXISTS 'commission_user'@'localhost'
    IDENTIFIED BY 'YourStrongPassword123!';

-- Step 2: Grant only necessary permissions on commission_db
-- SELECT, INSERT, UPDATE, DELETE are sufficient for the application
GRANT SELECT, INSERT, UPDATE, DELETE
    ON commission_db.*
    TO 'commission_user'@'localhost';

-- Step 3: Apply the changes
FLUSH PRIVILEGES;

-- ============================================
-- Verification Queries (Optional)
-- ============================================

-- Check if user was created
-- SELECT User, Host FROM mysql.user WHERE User = 'commission_user';

-- Check user privileges
-- SHOW GRANTS FOR 'commission_user'@'localhost';

-- ============================================
-- After running this script:
-- ============================================
-- 1. Update your .env file:
--    DB_USER=commission_user
--    DB_PASS=YourStrongPassword123!
--
-- 2. Test the connection by logging in to your app
--
-- 3. If you need to remove the user later:
--    DROP USER 'commission_user'@'localhost';
-- ============================================

-- ============================================
-- Password Security Tips:
-- ============================================
-- Use a password generator to create a strong password:
-- - At least 16 characters
-- - Mix of uppercase, lowercase, numbers, and symbols
-- - Example tools:
--   - openssl rand -base64 24
--   - pwgen -s 24 1
-- ============================================
