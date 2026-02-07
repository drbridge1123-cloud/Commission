-- Import Chong's January 2026 Demand Cases
-- Run this in phpMyAdmin or MySQL CLI

-- Check if cases already exist before inserting
INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '202022' as case_number,
        'Tony Lee' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        22250.00 as settled,
        0 as presuit_offer,
        22250.00 as difference,
        7416.67 as legal_fee,
        7416.67 as discounted_legal_fee,
        370.83 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-06' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '202022' AND client_name = 'Tony Lee' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '202022' as case_number,
        'Annie Lee' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        24250.00 as settled,
        0 as presuit_offer,
        24250.00 as difference,
        8083.33 as legal_fee,
        8083.33 as discounted_legal_fee,
        404.17 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-06' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '202022' AND client_name = 'Annie Lee' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '201951' as case_number,
        'Kwang Myung Han' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        60800.00 as settled,
        0 as presuit_offer,
        60800.00 as difference,
        20266.67 as legal_fee,
        20266.67 as discounted_legal_fee,
        1013.33 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'UM - Will probably need discount on legal fee' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-06' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '201951' AND client_name = 'Kwang Myung Han' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '201562' as case_number,
        'Kil Sung Kim' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        200000.00 as settled,
        0 as presuit_offer,
        200000.00 as difference,
        66666.67 as legal_fee,
        66666.67 as discounted_legal_fee,
        3333.33 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-12' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '201562' AND client_name = 'Kil Sung Kim' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '201915' as case_number,
        'Jeongwon Yoon' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        11976.39 as settled,
        0 as presuit_offer,
        11976.39 as difference,
        3992.13 as legal_fee,
        3992.13 as discounted_legal_fee,
        199.61 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'UIM only' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-16' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '201915' AND client_name = 'Jeongwon Yoon' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '201744' as case_number,
        'Trinh Tran' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        85348.25 as settled,
        0 as presuit_offer,
        85348.25 as difference,
        28449.42 as legal_fee,
        28449.42 as discounted_legal_fee,
        1422.47 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI&UIM' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-20' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '201744' AND client_name = 'Trinh Tran' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '201639' as case_number,
        'Kristy Tran' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        50000.00 as settled,
        0 as presuit_offer,
        50000.00 as difference,
        16666.67 as legal_fee,
        16666.67 as discounted_legal_fee,
        833.33 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI - waiting for final UIM settlement, so might be better to move this to next month' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-26' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '201639' AND client_name = 'Kristy Tran' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '202062' as case_number,
        'Susana Cartagena' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        35713.00 as settled,
        0 as presuit_offer,
        35713.00 as difference,
        11904.33 as legal_fee,
        11904.33 as discounted_legal_fee,
        595.22 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-29' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '202062' AND client_name = 'Susana Cartagena' AND user_id = 2
);

INSERT INTO cases (
    user_id, case_type, case_number, client_name, resolution_type,
    fee_rate, month, settled, presuit_offer, difference,
    legal_fee, discounted_legal_fee, commission, commission_type,
    phase, note, check_received, status, submitted_at
)
SELECT * FROM (
    SELECT
        2 as user_id,
        'Auto Accident' as case_type,
        '201892' as case_number,
        'Seung Haam' as client_name,
        'Demand Settle' as resolution_type,
        33.33 as fee_rate,
        'Jan. 2026' as month,
        2450.00 as settled,
        0 as presuit_offer,
        2450.00 as difference,
        816.67 as legal_fee,
        816.67 as discounted_legal_fee,
        40.83 as commission,
        'demand_5pct' as commission_type,
        'demand' as phase,
        'BI' as note,
        0 as check_received,
        'pending' as status,
        '2026-01-30' as submitted_at
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM cases
    WHERE case_number = '201892' AND client_name = 'Seung Haam' AND user_id = 2
);

-- Summary: 9 Demand cases imported for January 2026
-- Total Commission: $8,213.12
