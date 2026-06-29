-- =====================================================
-- HELPER PROCEDURES FOR COMPATIBILITY (MySQL 5.7+)
-- =====================================================

DROP PROCEDURE IF EXISTS drop_fk_if_exists;
DROP PROCEDURE IF EXISTS drop_col_if_exists;
DROP PROCEDURE IF EXISTS add_col_if_not_exists;
DROP PROCEDURE IF EXISTS rename_col_if_needed;

DELIMITER $$

CREATE PROCEDURE drop_fk_if_exists(IN tbl VARCHAR(64), IN fk VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = tbl
          AND CONSTRAINT_NAME = fk
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` DROP FOREIGN KEY `', fk, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

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

CREATE PROCEDURE rename_col_if_needed(
    IN tbl VARCHAR(64),
    IN old_col VARCHAR(64),
    IN new_col VARCHAR(64),
    IN col_def TEXT
)
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME  = tbl
          AND COLUMN_NAME = old_col
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME  = tbl
          AND COLUMN_NAME = new_col
    ) THEN
        SET @sql = CONCAT(
            'ALTER TABLE `', tbl, '` CHANGE COLUMN `', old_col, '` `', new_col, '` ', col_def
        );
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
SELECT * FROM receipt_payment_entries;

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
    session_year_id = 7,
    account_type_id = 1;

-- =====================================================
-- STEP 4: RENAME COLUMN (SAFE)
-- Only renames if receipt_payment_account_id exists
-- and account_id does not already exist.
-- =====================================================

CALL rename_col_if_needed(
    'receipt_payment_entries',
    'receipt_payment_account_id',
    'account_id',
    'BIGINT UNSIGNED NOT NULL'
);

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
-- STEP 10: SWAP FOREIGN KEY ON account_id (SAFE)
-- =====================================================

CALL drop_fk_if_exists(
    'receipt_payment_entries',
    'receipt_payment_entries_receipt_payment_account_id_foreign'
);

UPDATE receipt_payment_entries
SET account_id = 1;

ALTER TABLE receipt_payment_entries
ADD CONSTRAINT receipt_payment_entries_account_id_foreign
    FOREIGN KEY (account_id)
    REFERENCES accounts(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- =====================================================
-- CLEANUP HELPER PROCEDURES
-- =====================================================

DROP PROCEDURE IF EXISTS drop_fk_if_exists;
DROP PROCEDURE IF EXISTS drop_col_if_exists;
DROP PROCEDURE IF EXISTS add_col_if_not_exists;
DROP PROCEDURE IF EXISTS rename_col_if_needed;

COMMIT;

-- =====================================================
-- VERIFICATION (runs outside transaction)
-- =====================================================

SELECT COUNT(*) AS backup_count  FROM receipt_payment_entries_old;
SELECT COUNT(*) AS current_count FROM receipt_payment_entries;
SELECT MIN(id) AS first_id, MAX(id) AS last_id FROM receipt_payment_entries;