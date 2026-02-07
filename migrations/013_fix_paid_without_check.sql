-- Fix inconsistent data: cases marked as 'paid' without check_received
-- These were approved before the check_received validation was added to approve.php
-- Soft delete only the cases that violate the business rule

UPDATE cases
SET deleted_at = NOW()
WHERE status = 'paid'
  AND check_received = 0
  AND deleted_at IS NULL;
