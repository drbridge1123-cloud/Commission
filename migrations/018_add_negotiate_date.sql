USE commission_db;

-- Add negotiate_date column to cases table
-- This is the date negotiation started, manually entered via inline checkbox
ALTER TABLE cases ADD COLUMN negotiate_date DATE NULL AFTER demand_out_date;
