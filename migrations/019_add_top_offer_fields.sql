USE commission_db;

-- Add top offer fields to cases table
-- These track when a top offer is received on a demand case
ALTER TABLE cases
  ADD COLUMN top_offer_amount DECIMAL(15,2) NULL AFTER negotiate_date,
  ADD COLUMN top_offer_date DATE NULL AFTER top_offer_amount,
  ADD COLUMN top_offer_assignee_id INT NULL AFTER top_offer_date,
  ADD COLUMN top_offer_note TEXT NULL AFTER top_offer_assignee_id;
