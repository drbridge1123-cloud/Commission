-- Migration 014: Add intake_date to cases
-- Tracks when case was brought in (separate from settlement month)

ALTER TABLE cases ADD COLUMN intake_date DATE NULL AFTER month;

-- Backfill existing cases: use submitted_at date as intake_date
UPDATE cases SET intake_date = DATE(submitted_at) WHERE intake_date IS NULL;

-- Add indexes for goal queries
CREATE INDEX idx_cases_intake_date ON cases (intake_date);
CREATE INDEX idx_cases_user_intake_status ON cases (user_id, intake_date, status);
