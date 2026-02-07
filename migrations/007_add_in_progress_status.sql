-- Add 'in_progress' status to cases table
-- Run this in phpMyAdmin or MySQL CLI

ALTER TABLE cases
MODIFY COLUMN status ENUM('pending', 'in_progress', 'paid', 'rejected') NOT NULL DEFAULT 'pending';

-- Summary: Adds 'in_progress' status option to cases
