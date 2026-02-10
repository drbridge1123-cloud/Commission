USE commission_db;

-- Add demand_out_date column to cases table
-- This is the date the demand was sent out, manually entered by user
ALTER TABLE cases ADD COLUMN demand_out_date DATE NULL AFTER demand_deadline;
