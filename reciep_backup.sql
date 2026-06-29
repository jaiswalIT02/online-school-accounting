START TRANSACTION;

-- =====================================================
-- STEP 1: BACKUP TABLE
-- =====================================================

DROP TABLE IF EXISTS receipt_payment_entries_old;

CREATE TABLE receipt_payment_entries_old AS
SELECT *
FROM receipt_payment_entries;

-- =====================================================
-- STEP 2: ADD NEW COLUMNS
-- =====================================================

ALTER TABLE receipt_payment_entries
ADD COLUMN IF NOT EXISTS school_id BIGINT NULL,
ADD COLUMN IF NOT EXISTS session_year_id BIGINT NULL,
ADD COLUMN IF NOT EXISTS account_type_id BIGINT NULL;

-- =====================================================
-- STEP 3: POPULATE NEW COLUMNS
-- =====================================================

UPDATE receipt_payment_entries
SET
    school_id = 1,
    session_year_id = 7,
    account_type_id = 1;

-- =====================================================
-- STEP 4: RENAME COLUMN
-- =====================================================
-- Uncomment ONLY if receipt_payment_account_id exists
-- and account_id does not already exist.

ALTER TABLE receipt_payment_entries
CHANGE COLUMN receipt_payment_account_id account_id BIGINT UNSIGNED NOT NULL;

-- =====================================================
-- STEP 5: CONVERT DATE VALUES
-- =====================================================

ALTER TABLE receipt_payment_entries
ADD COLUMN date_new DATE NULL;

UPDATE receipt_payment_entries
SET date_new =
    CASE
        WHEN date IS NULL OR TRIM(date) = '' THEN NULL
        ELSE STR_TO_DATE(date,'%d/%m/%Y')
    END;

ALTER TABLE receipt_payment_entries
DROP COLUMN date;

ALTER TABLE receipt_payment_entries
CHANGE COLUMN date_new date DATE NULL
COMMENT 'PPA Date in yyyy-mm-dd format';

-- =====================================================
-- STEP 6: REMOVE TESTING RECORDS
-- =====================================================

DELETE FROM receipt_payment_entries
WHERE type = 'testing';

-- =====================================================
-- STEP 7: UPDATE ENUM
-- =====================================================

ALTER TABLE receipt_payment_entries
MODIFY COLUMN type ENUM('receipt','payment')
COLLATE utf8mb4_unicode_ci NOT NULL;

-- =====================================================
-- STEP 8: ENSURE AUTO_INCREMENT
-- =====================================================

SET @next_id =
(
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




ALTER TABLE receipt_payment_entries
DROP FOREIGN KEY receipt_payment_entries_receipt_payment_account_id_foreign;

UPDATE receipt_payment_entries
SET account_id = 1;

ALTER TABLE receipt_payment_entries
ADD CONSTRAINT receipt_payment_entries_account_id_foreign
FOREIGN KEY (account_id)
REFERENCES accounts(id)
ON DELETE CASCADE
ON UPDATE CASCADE;

COMMIT;

-- =====================================================
-- VERIFICATION
-- =====================================================

SELECT COUNT(*) AS backup_count
FROM receipt_payment_entries_old;

SELECT COUNT(*) AS current_count
FROM receipt_payment_entries;

SELECT MIN(id) AS first_id,
       MAX(id) AS last_id
FROM receipt_payment_entries;