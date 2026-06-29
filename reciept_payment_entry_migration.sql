START TRANSACTION;

-- =====================================================
-- STEP 1: CREATE BACKUP TABLE
-- =====================================================

DROP TABLE IF EXISTS receipt_payment_entry_test;

CREATE TABLE receipt_payment_entry_test AS
SELECT *
FROM receipt_payment_entries;

-- =====================================================
-- STEP 2: RENAME ORIGINAL TABLE FOR SAFETY
-- =====================================================

RENAME TABLE receipt_payment_entries TO receipt_payment_entries_old;

-- =====================================================
-- STEP 3: CREATE NEW TABLE STRUCTURE
-- =====================================================

CREATE TABLE receipt_payment_entries (

    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    account_id BIGINT UNSIGNED NOT NULL,

    type ENUM('receipt','payment') COLLATE utf8mb4_unicode_ci NOT NULL,

    particular_name VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,

    article_id BIGINT UNSIGNED DEFAULT NULL,

    beneficiary_id BIGINT UNSIGNED DEFAULT NULL,

    amount DECIMAL(12,2) NOT NULL DEFAULT '0.00',

    remarks TEXT COLLATE utf8mb4_unicode_ci,

    date DATE DEFAULT NULL COMMENT 'PPA Date in yyyy-mm-dd format',

    tax_amount DECIMAL(12,2) DEFAULT NULL,

    tax_for ENUM('tds','pTax') COLLATE utf8mb4_unicode_ci DEFAULT NULL,

    tax_type ENUM('dr','cr') COLLATE utf8mb4_unicode_ci DEFAULT NULL,

    tax_remark TEXT COLLATE utf8mb4_unicode_ci,

    pair_id BIGINT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,

    updated_at TIMESTAMP NULL DEFAULT NULL,

    school_id BIGINT DEFAULT NULL,

    session_year_id BIGINT DEFAULT NULL,

    account_type_id BIGINT DEFAULT NULL,

    PRIMARY KEY (id)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 4: COPY DATA
-- PRESERVE ORIGINAL IDs
-- CONVERT dd/mm/yyyy -> yyyy-mm-dd
-- SKIP type='testing'
-- =====================================================

INSERT INTO receipt_payment_entries (

    id,
    account_id,
    type,
    particular_name,
    article_id,
    beneficiary_id,
    amount,
    remarks,
    date,
    tax_amount,
    tax_for,
    tax_type,
    tax_remark,
    pair_id,
    created_at,
    updated_at,
    school_id,
    session_year_id,
    account_type_id

)
SELECT

    id,
    receipt_payment_account_id,
    type,
    particular_name,
    article_id,
    beneficiary_id,
    amount,
    remarks,

    CASE
        WHEN date IS NULL OR TRIM(date) = '' THEN NULL
        ELSE STR_TO_DATE(date,'%d/%m/%Y')
    END,

    tax_amount,
    tax_for,
    tax_type,
    tax_remark,
    pair_id,
    created_at,
    updated_at,

    NULL,
    NULL,
    NULL

FROM receipt_payment_entries_old
WHERE type IN ('receipt','payment');

-- =====================================================
-- STEP 5: SET AUTO_INCREMENT
-- TO CONTINUE AFTER HIGHEST EXISTING ID
-- =====================================================

SET @next_id = (
    SELECT COALESCE(MAX(id),0) + 1
    FROM receipt_payment_entries
);

SET @sql = CONCAT(
    'ALTER TABLE receipt_payment_entries AUTO_INCREMENT = ',
    @next_id
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


UPDATE receipt_payment_entries
SET
    school_id = 1,
    session_year_id = 7,
    account_type_id = 1
WHERE school_id IS NULL
   OR session_year_id IS NULL
   OR account_type_id IS NULL;

UPDATE funds
SET
    school_id = 1,
    session_year_id = 7
WHERE school_id IS NULL
   OR session_year_id IS NULL;

UPDATE `funds` SET `component_type`='non-salary' WHERE component_type IS NULL;

UPDATE receipt_payment_accounts SET school_id = 1, session_year_id = 7, account_type_id = 1, account_id = 1 WHERE school_id IS NULL OR session_year_id IS NULL OR account_type_id IS NULL;

COMMIT;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

SELECT COUNT(*) AS backup_count
FROM receipt_payment_entry_test;

SELECT COUNT(*) AS old_valid_count
FROM receipt_payment_entries_old
WHERE type IN ('receipt','payment');

SELECT COUNT(*) AS new_count
FROM receipt_payment_entries;

SELECT MIN(id) AS first_id,
       MAX(id) AS last_id
FROM receipt_payment_entries;