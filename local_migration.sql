-- =====================================================
-- HELPER PROCEDURES FOR COMPATIBILITY (MySQL 5.7+)
-- =====================================================

DROP PROCEDURE IF EXISTS drop_col_if_exists;
DROP PROCEDURE IF EXISTS add_col_if_not_exists;

DELIMITER $$

CREATE PROCEDURE drop_col_if_exists(IN tbl VARCHAR(64), IN col VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = tbl
          AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` DROP COLUMN `', col, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

CREATE PROCEDURE add_col_if_not_exists(IN tbl VARCHAR(64), IN col VARCHAR(64), IN col_def TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = tbl
          AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', col_def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- START TRANSACTION
-- =====================================================

START TRANSACTION;

-- =====================================================
-- STEP 1: BACKUP TABLE
-- =====================================================

DROP TABLE IF EXISTS receipt_payment_entries_old;

CREATE TABLE receipt_payment_entries_old AS
SELECT *
FROM receipt_payment_entries;

-- =====================================================
-- STEP 2: ADD NEW COLUMNS (SAFE)
-- =====================================================

CALL add_col_if_not_exists('receipt_payment_entries', 'school_id',       'BIGINT NULL');
CALL add_col_if_not_exists('receipt_payment_entries', 'session_year_id', 'BIGINT NULL');
CALL add_col_if_not_exists('receipt_payment_entries', 'account_type_id', 'BIGINT NULL');

-- =====================================================
-- STEP 3: POPULATE NEW COLUMNS
-- =====================================================

UPDATE receipt_payment_entries
SET
    school_id       = 1,
    session_year_id = 187,
    account_type_id = 1;

-- =====================================================
-- STEP 4: RENAME COLUMN
-- Runs only if receipt_payment_account_id exists
-- and account_id does not already exist.
-- =====================================================

DROP PROCEDURE IF EXISTS rename_col_if_needed;

DELIMITER $$
CREATE PROCEDURE rename_col_if_needed()
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'receipt_payment_entries'
          AND COLUMN_NAME  = 'receipt_payment_account_id'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'receipt_payment_entries'
          AND COLUMN_NAME  = 'account_id'
    ) THEN
        ALTER TABLE receipt_payment_entries
        CHANGE COLUMN receipt_payment_account_id account_id BIGINT UNSIGNED NOT NULL;
    END IF;
END$$
DELIMITER ;

CALL rename_col_if_needed();
DROP PROCEDURE IF EXISTS rename_col_if_needed;

-- =====================================================
-- STEP 5: CONVERT DATE VALUES
-- =====================================================

CALL add_col_if_not_exists('receipt_payment_entries', 'date_new', 'DATE NULL');

UPDATE receipt_payment_entries
SET date_new =
    CASE
        WHEN date IS NULL OR TRIM(date) = '' THEN NULL
        ELSE STR_TO_DATE(date, '%d/%m/%Y')
    END;

CALL drop_col_if_exists('receipt_payment_entries', 'date');

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

SET @next_id = (
    SELECT COALESCE(MAX(id), 0) + 1
    FROM receipt_payment_entries
);

SET @sql = CONCAT(
    'ALTER TABLE receipt_payment_entries AUTO_INCREMENT = ',
    @next_id
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- STEP 9: FILL ANY REMAINING NULL VALUES
-- =====================================================

UPDATE receipt_payment_entries
SET
    school_id       = 1,
    session_year_id = 7,
    account_type_id = 1
WHERE school_id       IS NULL
   OR session_year_id IS NULL
   OR account_type_id IS NULL;

UPDATE funds
SET
    school_id       = 1,
    session_year_id = 7
WHERE school_id       IS NULL
   OR session_year_id IS NULL;

UPDATE funds
SET component_type = 'non-salary'
WHERE component_type IS NULL;

UPDATE receipt_payment_accounts
SET
    school_id       = 1,
    session_year_id = 7,
    account_type_id = 1,
    account_id      = 1
WHERE school_id       IS NULL
   OR session_year_id IS NULL
   OR account_type_id IS NULL;

-- =====================================================
-- CLEANUP HELPER PROCEDURES
-- =====================================================

DROP PROCEDURE IF EXISTS drop_col_if_exists;
DROP PROCEDURE IF EXISTS add_col_if_not_exists;

COMMIT;

-- =====================================================
-- VERIFICATION (runs outside transaction)
-- =====================================================

SELECT COUNT(*) AS backup_count  FROM receipt_payment_entries_old;
SELECT COUNT(*) AS current_count FROM receipt_payment_entries;
SELECT MIN(id) AS first_id, MAX(id) AS last_id FROM receipt_payment_entries;