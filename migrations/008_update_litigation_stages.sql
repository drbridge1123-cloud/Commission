-- Update litigation_cases stages
-- New stages: filed, post, after_dep, mediation, post_arb, arb, settle

-- First, update the ENUM column to include new values
ALTER TABLE litigation_cases
MODIFY COLUMN litigation_stage ENUM(
    'in_progress', 'settled', 'waiting_for_check', 'check_arrived', 'disbursed', 'completed',
    'filed', 'post', 'after_dep', 'mediation', 'post_arb', 'arb', 'settle'
) NOT NULL DEFAULT 'filed';

-- Migrate existing data to new stages
UPDATE litigation_cases SET litigation_stage = 'filed' WHERE litigation_stage = 'in_progress';
UPDATE litigation_cases SET litigation_stage = 'settle' WHERE litigation_stage IN ('settled', 'waiting_for_check', 'check_arrived', 'disbursed', 'completed');

-- Now remove old values and keep only new ones
ALTER TABLE litigation_cases
MODIFY COLUMN litigation_stage ENUM(
    'filed', 'post', 'after_dep', 'mediation', 'post_arb', 'arb', 'settle'
) NOT NULL DEFAULT 'filed';

-- Summary: Updates litigation stages from old workflow to new litigation pipeline
