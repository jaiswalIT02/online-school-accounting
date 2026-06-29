-- =====================================================
-- HELPER PROCEDURES FOR COMPATIBILITY
-- (Works on MySQL 5.7+ and 8.x)
-- =====================================================

DROP PROCEDURE IF EXISTS drop_fk_if_exists;
DROP PROCEDURE IF EXISTS drop_col_if_exists;
DROP PROCEDURE IF EXISTS drop_index_if_exists;

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

CREATE PROCEDURE drop_index_if_exists(IN tbl VARCHAR(64), IN idx VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = tbl
          AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` DROP INDEX `', idx, '`');
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
-- SCHOOLS
-- =====================================================

CREATE TABLE IF NOT EXISTS schools (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    registerred_date DATE DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY `schools_name_unique` (`name`),
    UNIQUE KEY `schools_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO schools
(name, slug, registerred_date, status, created_at, updated_at)
VALUES
('KGBVDKJ', 'kgbvdkj', '2021-02-01', 1, NOW(), NOW()),
('ABC School', 'abc-school', '2022-03-15', 1, NOW(), NOW());

-- =====================================================
-- ACCOUNT TYPES
-- =====================================================

CREATE TABLE IF NOT EXISTS account_types (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    description TEXT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    school_id BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY `schools_name_unique` (`name`),
    UNIQUE KEY `schools_slug_unique` (`slug`),
    KEY idx_account_types_school_id (school_id),
    CONSTRAINT fk_account_types_school_id
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO account_types
(name, slug, description, status, school_id, created_at, updated_at)
VALUES
('Type 1', 'type-1', 'Type 1 account with basic features', 1, 1, NOW(), NOW()),
('Type 2', 'type-2', 'Type 2 account with extended features', 1, 1, NOW(), NOW());

-- =====================================================
-- ACCOUNTS
-- =====================================================

CREATE TABLE IF NOT EXISTS `accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `school_id` BIGINT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `slug` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` TINYINT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `accounts_name_unique` (`name`),
    UNIQUE KEY `accounts_slug_unique` (`slug`),
    KEY `idx_accounts_school_id` (`school_id`),
    CONSTRAINT `fk_accounts_school_id`
        FOREIGN KEY (`school_id`)
        REFERENCES `schools` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `accounts`
(`school_id`, `name`, `slug`, `status`, `created_at`, `updated_at`)
VALUES
(1, 'Test Account', 'test-account', 1, NOW(), NOW()),
(1, 'Test 2', 'test-2', 1, NOW(), NOW());

-- =====================================================
-- SESSION YEARS
-- =====================================================

CREATE TABLE IF NOT EXISTS session_years (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    school_id BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_session_years_session_name (session_name),
    UNIQUE KEY uq_session_years_slug (slug),
    KEY idx_session_school (school_id),
    CONSTRAINT fk_session_years_school
        FOREIGN KEY (school_id)
        REFERENCES schools(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO session_years
(session_name, slug, start_date, end_date, status, school_id, created_at, updated_at)
VALUES
('2019-2020', '2019-2020', '2019-04-01', '2020-03-31', 1, 1, NOW(), NOW()),
('2020-2021', '2020-2021', '2020-04-01', '2021-03-31', 1, 1, NOW(), NOW()),
('2021-2022', '2021-2022', '2021-04-01', '2022-03-31', 1, 1, NOW(), NOW()),
('2022-2023', '2022-2023', '2022-04-01', '2023-03-31', 1, 1, NOW(), NOW()),
('2023-2024', '2023-2024', '2023-04-01', '2024-03-31', 1, 1, NOW(), NOW()),
('2024-2025', '2024-2025', '2024-04-01', '2025-03-31', 1, 1, NOW(), NOW()),
('2025-2026', '2025-2026', '2025-04-01', '2026-03-31', 1, 1, NOW(), NOW()),
('2026-2027', '2026-2027', '2026-04-01', '2027-03-31', 1, 1, NOW(), NOW()),
('2027-2028', '2027-2028', '2027-04-01', '2028-03-31', 1, 1, NOW(), NOW()),
('2028-2029', '2028-2029', '2028-04-01', '2029-03-31', 1, 1, NOW(), NOW()),
('2029-2030', '2029-2030', '2029-04-01', '2030-03-31', 1, 1, NOW(), NOW()),
('2030-2031', '2030-2031', '2030-04-01', '2031-03-31', 1, 1, NOW(), NOW());

-- Fix unique indexes on session_years (drop and recreate safely)
CALL drop_index_if_exists('session_years', 'uq_session_years_session_name');
CALL drop_index_if_exists('session_years', 'uq_session_years_slug');

ALTER TABLE session_years
ADD UNIQUE KEY uq_session_years_session_name (session_name),
ADD UNIQUE KEY uq_session_years_slug (slug);

-- =====================================================
-- STEP 1: DROP FOREIGN KEYS (SAFE)
-- =====================================================

-- LEDGERS
CALL drop_fk_if_exists('ledgers', 'fk_ledgers_account_type');
CALL drop_fk_if_exists('ledgers', 'fk_ledgers_school');
CALL drop_fk_if_exists('ledgers', 'fk_ledgers_session_year');

-- LEDGER ENTRIES
CALL drop_fk_if_exists('ledger_entries', 'fk_ledger_entries_session_year');
CALL drop_fk_if_exists('ledger_entries', 'fk_ledger_entries_account_type');
CALL drop_fk_if_exists('ledger_entries', 'fk_ledger_entries_school');

-- ARTICLES
CALL drop_fk_if_exists('articles', 'fk_articles_school');

-- CASHBOOKS
CALL drop_fk_if_exists('cashbooks', 'fk_cashbooks_session_year');
CALL drop_fk_if_exists('cashbooks', 'fk_cashbooks_account_type');
CALL drop_fk_if_exists('cashbooks', 'fk_cashbooks_school');

-- CASHBOOK ENTRIES
CALL drop_fk_if_exists('cashbook_entries', 'fk_cashbook_entries_session_year');
CALL drop_fk_if_exists('cashbook_entries', 'fk_cashbook_entries_account_type');
CALL drop_fk_if_exists('cashbook_entries', 'fk_cashbook_entries_school');

-- STUDENTS
CALL drop_fk_if_exists('students', 'fk_students_session_year');
CALL drop_fk_if_exists('students', 'fk_students_school');

-- STAFF
CALL drop_fk_if_exists('staff', 'fk_staff_session_year');
CALL drop_fk_if_exists('staff', 'fk_staff_school');

-- STOCKS
CALL drop_fk_if_exists('stocks', 'fk_stocks_school');

-- STOCK LEDGERS
CALL drop_fk_if_exists('stock_ledgers', 'fk_stock_ledgers_session_year');
CALL drop_fk_if_exists('stock_ledgers', 'fk_stock_ledgers_school');

-- ITEMS
CALL drop_fk_if_exists('items', 'fk_items_session_year');
CALL drop_fk_if_exists('items', 'fk_items_school');

-- BENEFICIARIES
CALL drop_fk_if_exists('beneficiaries', 'fk_beneficiaries_session_year');
CALL drop_fk_if_exists('beneficiaries', 'fk_beneficiaries_school');

-- USERS
CALL drop_fk_if_exists('users', 'fk_users_school');

-- FUNDS
CALL drop_fk_if_exists('funds', 'fk_funds_session_year');
CALL drop_fk_if_exists('funds', 'fk_funds_account_type');
CALL drop_fk_if_exists('funds', 'fk_funds_school');

-- RECEIPT PAYMENT ACCOUNTS
CALL drop_fk_if_exists('receipt_payment_accounts', 'fk_rpa_session_year');
CALL drop_fk_if_exists('receipt_payment_accounts', 'fk_rpa_account_type');
CALL drop_fk_if_exists('receipt_payment_accounts', 'fk_rpa_account');
CALL drop_fk_if_exists('receipt_payment_accounts', 'fk_rpa_school');

-- =====================================================
-- STEP 2: DROP COLUMNS (SAFE)
-- =====================================================

-- LEDGERS
CALL drop_col_if_exists('ledgers', 'session_year_id');
CALL drop_col_if_exists('ledgers', 'account_type_id');
CALL drop_col_if_exists('ledgers', 'school_id');

-- LEDGER ENTRIES
CALL drop_col_if_exists('ledger_entries', 'session_year_id');
CALL drop_col_if_exists('ledger_entries', 'account_type_id');
CALL drop_col_if_exists('ledger_entries', 'school_id');

-- ARTICLES
CALL drop_col_if_exists('articles', 'school_id');

-- CASHBOOKS
CALL drop_col_if_exists('cashbooks', 'session_year_id');
CALL drop_col_if_exists('cashbooks', 'account_type_id');
CALL drop_col_if_exists('cashbooks', 'school_id');

-- CASHBOOK ENTRIES
CALL drop_col_if_exists('cashbook_entries', 'session_year_id');
CALL drop_col_if_exists('cashbook_entries', 'account_type_id');
CALL drop_col_if_exists('cashbook_entries', 'school_id');

-- STUDENTS
CALL drop_col_if_exists('students', 'session_year_id');
CALL drop_col_if_exists('students', 'school_id');

-- STAFF
CALL drop_col_if_exists('staff', 'session_year_id');
CALL drop_col_if_exists('staff', 'school_id');

-- STOCKS
CALL drop_col_if_exists('stocks', 'school_id');

-- STOCK LEDGERS
CALL drop_col_if_exists('stock_ledgers', 'session_year_id');
CALL drop_col_if_exists('stock_ledgers', 'school_id');

-- ITEMS
CALL drop_col_if_exists('items', 'session_year_id');
CALL drop_col_if_exists('items', 'school_id');

-- BENEFICIARIES
CALL drop_col_if_exists('beneficiaries', 'session_year_id');
CALL drop_col_if_exists('beneficiaries', 'school_id');

-- USERS
CALL drop_col_if_exists('users', 'school_id');

-- FUNDS
CALL drop_col_if_exists('funds', 'session_year_id');
CALL drop_col_if_exists('funds', 'account_type_id');
CALL drop_col_if_exists('funds', 'school_id');

-- RECEIPT PAYMENT ACCOUNTS
CALL drop_col_if_exists('receipt_payment_accounts', 'session_year_id');
CALL drop_col_if_exists('receipt_payment_accounts', 'account_type_id');
CALL drop_col_if_exists('receipt_payment_accounts', 'school_id');
CALL drop_col_if_exists('receipt_payment_accounts', 'account_id');

-- =====================================================
-- STEP 3: ADD COLUMNS BACK
-- =====================================================

-- LEDGERS
ALTER TABLE ledgers
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN account_type_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE ledgers
ADD INDEX idx_ledgers_session_year (session_year_id),
ADD INDEX idx_ledgers_account_type (account_type_id),
ADD INDEX idx_ledgers_school (school_id);

ALTER TABLE ledgers
ADD CONSTRAINT fk_ledgers_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_ledgers_account_type
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_ledgers_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- LEDGER ENTRIES
ALTER TABLE ledger_entries
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN account_type_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE ledger_entries
ADD INDEX idx_ledger_entries_session_year (session_year_id),
ADD INDEX idx_ledger_entries_account_type (account_type_id),
ADD INDEX idx_ledger_entries_school (school_id);

ALTER TABLE ledger_entries
ADD CONSTRAINT fk_ledger_entries_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_ledger_entries_account_type
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_ledger_entries_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- ARTICLES
ALTER TABLE articles
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE articles
ADD INDEX idx_articles_school (school_id);

ALTER TABLE articles
ADD CONSTRAINT fk_articles_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- CASHBOOKS
ALTER TABLE cashbooks
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN account_type_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE cashbooks
ADD INDEX idx_cashbooks_session_year (session_year_id),
ADD INDEX idx_cashbooks_account_type (account_type_id),
ADD INDEX idx_cashbooks_school (school_id);

ALTER TABLE cashbooks
ADD CONSTRAINT fk_cashbooks_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_cashbooks_account_type
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_cashbooks_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- CASHBOOK ENTRIES
ALTER TABLE cashbook_entries
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN account_type_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE cashbook_entries
ADD INDEX idx_cashbook_entries_session_year (session_year_id),
ADD INDEX idx_cashbook_entries_account_type (account_type_id),
ADD INDEX idx_cashbook_entries_school (school_id);

ALTER TABLE cashbook_entries
ADD CONSTRAINT fk_cashbook_entries_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_cashbook_entries_account_type
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_cashbook_entries_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- RECEIPT PAYMENT ACCOUNTS
ALTER TABLE receipt_payment_accounts
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN account_type_id BIGINT UNSIGNED NULL,
ADD COLUMN account_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE receipt_payment_accounts
ADD INDEX idx_rpa_session_year (session_year_id),
ADD INDEX idx_rpa_account_type (account_type_id),
ADD INDEX idx_rpa_account (account_id),
ADD INDEX idx_rpa_school (school_id);

ALTER TABLE receipt_payment_accounts
ADD CONSTRAINT fk_rpa_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_rpa_account_type
    FOREIGN KEY (account_type_id) REFERENCES account_types(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_rpa_account
    FOREIGN KEY (account_id) REFERENCES accounts(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_rpa_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- STUDENTS
ALTER TABLE students
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE students
ADD INDEX idx_students_session_year (session_year_id),
ADD INDEX idx_students_school (school_id);

ALTER TABLE students
ADD CONSTRAINT fk_students_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_students_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- STAFF
ALTER TABLE staff
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE staff
ADD INDEX idx_staff_session_year (session_year_id),
ADD INDEX idx_staff_school (school_id);

ALTER TABLE staff
ADD CONSTRAINT fk_staff_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_staff_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- STOCKS
ALTER TABLE stocks
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE stocks
ADD INDEX idx_stocks_school (school_id);

ALTER TABLE stocks
ADD CONSTRAINT fk_stocks_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- STOCK LEDGERS
ALTER TABLE stock_ledgers
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE stock_ledgers
ADD INDEX idx_stock_ledgers_session_year (session_year_id),
ADD INDEX idx_stock_ledgers_school (school_id);

ALTER TABLE stock_ledgers
ADD CONSTRAINT fk_stock_ledgers_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_stock_ledgers_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- ITEMS
ALTER TABLE items
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE items
ADD INDEX idx_items_session_year (session_year_id),
ADD INDEX idx_items_school (school_id);

ALTER TABLE items
ADD CONSTRAINT fk_items_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_items_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- BENEFICIARIES
ALTER TABLE beneficiaries
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE beneficiaries
ADD INDEX idx_beneficiaries_session_year (session_year_id),
ADD INDEX idx_beneficiaries_school (school_id);

ALTER TABLE beneficiaries
ADD CONSTRAINT fk_beneficiaries_session_year
    FOREIGN KEY (session_year_id) REFERENCES session_years(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_beneficiaries_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- USERS
ALTER TABLE users
ADD COLUMN school_id BIGINT UNSIGNED NULL;

ALTER TABLE users
ADD INDEX idx_users_school (school_id);

ALTER TABLE users
ADD CONSTRAINT fk_users_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- FUNDS
ALTER TABLE funds
ADD COLUMN component_type VARCHAR(255),
ADD COLUMN session_year_id BIGINT UNSIGNED NULL,
ADD COLUMN school_id BIGINT UNSIGNED NULL;

-- =====================================================
-- STEP 4: SET DEFAULT VALUES & UPDATE DATA
-- =====================================================

SELECT id INTO @SESSION_YEAR_ID
FROM session_years
WHERE session_name = '2025-2026'
LIMIT 1;

SET @ACCOUNT_TYPE_ID = 1;
SET @SCHOOL_ID = 1;

-- UPDATE ALL TABLES

UPDATE ledgers
SET session_year_id = @SESSION_YEAR_ID,
    account_type_id = @ACCOUNT_TYPE_ID,
    school_id = @SCHOOL_ID;

UPDATE ledger_entries
SET session_year_id = @SESSION_YEAR_ID,
    account_type_id = @ACCOUNT_TYPE_ID,
    school_id = @SCHOOL_ID;

UPDATE beneficiaries
SET session_year_id = @SESSION_YEAR_ID,
    school_id = @SCHOOL_ID;

UPDATE articles
SET school_id = @SCHOOL_ID;

UPDATE cashbooks
SET session_year_id = @SESSION_YEAR_ID,
    account_type_id = @ACCOUNT_TYPE_ID,
    school_id = @SCHOOL_ID;

UPDATE cashbook_entries
SET session_year_id = @SESSION_YEAR_ID,
    account_type_id = @ACCOUNT_TYPE_ID,
    school_id = @SCHOOL_ID;

UPDATE students
SET session_year_id = @SESSION_YEAR_ID,
    school_id = @SCHOOL_ID;

UPDATE stock_ledgers
SET session_year_id = @SESSION_YEAR_ID,
    school_id = @SCHOOL_ID;

UPDATE items
SET session_year_id = @SESSION_YEAR_ID,
    school_id = @SCHOOL_ID;

UPDATE users
SET school_id = @SCHOOL_ID;

UPDATE receipt_payment_accounts
SET school_id = @SCHOOL_ID;

UPDATE funds
SET component_type = 'salary',
    school_id = @SCHOOL_ID
WHERE TRIM(component_name) IN (
    'Head Teacher/Principal',
    'Full Time Teachers/Lecturer',
    'Warden',
    'Part time teachers',
    'Full time Accountant',
    'Support Staff - Accountant / Assistant Peon Chowkidar',
    'Head Cook',
    'Assistant Cook'
);

-- =====================================================
-- RECEIPT PAYMENT ENTRY TESTS
-- =====================================================

CREATE TABLE IF NOT EXISTS `receipt_payment_entry_tests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` bigint UNSIGNED NOT NULL,
  `type` enum('receipt','payment') NOT NULL,
  `particular_name` varchar(191) DEFAULT NULL,
  `article_id` bigint UNSIGNED DEFAULT NULL,
  `beneficiary_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `remarks` text,
  `date` date DEFAULT NULL COMMENT 'PPA Date in yyyy-mm-dd format',
  `tax_amount` decimal(12,2) DEFAULT NULL,
  `tax_for` enum('tds','pTax') DEFAULT NULL,
  `tax_type` enum('dr','cr') DEFAULT NULL,
  `tax_remark` text,
  `pair_id` bigint UNSIGNED DEFAULT NULL,
  `school_id` bigint UNSIGNED DEFAULT NULL,
  `session_year_id` bigint UNSIGNED DEFAULT NULL,
  `account_type_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receipt_payment_entry_tests_article_id_foreign` (`article_id`),
  KEY `receipt_payment_entry_tests_beneficiary_id_foreign` (`beneficiary_id`),
  KEY `receipt_payment_entry_tests_account_id_type_index` (`account_id`,`type`),
  KEY `receipt_payment_entry_tests_pair_id_index` (`pair_id`),
  KEY `receipt_payment_entry_tests_school_id_index` (`school_id`),
  KEY `receipt_payment_entry_tests_session_year_id_index` (`session_year_id`),
  KEY `receipt_payment_entry_tests_account_type_id_index` (`account_type_id`),
  CONSTRAINT `receipt_payment_entry_tests_account_id_foreign`
    FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `receipt_payment_entry_tests_article_id_foreign`
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `receipt_payment_entry_tests_beneficiary_id_foreign`
    FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiaries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO receipt_payment_entry_tests (
    id, account_id, type, particular_name, article_id, beneficiary_id,
    amount, remarks, date, tax_amount, tax_for, tax_type, tax_remark,
    pair_id, school_id, created_at, updated_at
)
SELECT
    id,
    receipt_payment_account_id AS account_id,
    type,
    particular_name,
    article_id,
    beneficiary_id,
    amount,
    remarks,
    STR_TO_DATE(date, '%d/%m/%Y') AS date,
    tax_amount,
    tax_for,
    tax_type,
    tax_remark,
    pair_id,
    1 AS school_id,
    created_at,
    updated_at
FROM receipt_payment_entries;

-- =====================================================
-- INSERT DEFAULT ADMIN USER
-- =====================================================

INSERT IGNORE INTO `users`
(`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `school_id`)
VALUES
(NULL, 'KGBVDKJ Admin', 'kgbvdkj@gmail.com', NULL, '$2y$12$76DwiCNlp7gqzbfRBMS0/e/aPr3K1kAssB7bdIEWiGu21aB.A3N3.', NULL, '2026-01-27 18:48:19', '2026-01-27 19:27:24', '1');

-- =====================================================
-- CLEANUP HELPER PROCEDURES
-- =====================================================

DROP PROCEDURE IF EXISTS drop_fk_if_exists;
DROP PROCEDURE IF EXISTS drop_col_if_exists;
DROP PROCEDURE IF EXISTS drop_index_if_exists;

COMMIT;