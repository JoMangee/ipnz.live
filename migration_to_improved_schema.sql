-- Migration Script: Upgrade from old schema to new improved schema
-- Run this to migrate existing data without losing it

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Step 1: Rename old table
RENAME TABLE `ipnz_members` TO `ipnz_members_old`;

-- Step 2: Create new improved tables
CREATE TABLE `ipnz_members` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `join_type` ENUM('early_access', 'standard') NOT NULL DEFAULT 'early_access',
  `additional_request` text DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `status` ENUM('active', 'pending', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `privacy_consent` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_join_type` (`join_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ipnz_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied_at` timestamp NULL DEFAULT NULL,
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Migrate member data (status IS NULL = actual members)
INSERT INTO `ipnz_members` 
  (`id`, `name`, `email`, `phone`, `join_type`, `additional_request`, `avatar_url`, `status`, `created_at`)
SELECT 
  `id`,
  `name`,
  `email`,
  `phone`,
  CASE `join_type` 
    WHEN 0 THEN 'early_access'
    WHEN 1 THEN 'standard'
    ELSE 'early_access'
  END,
  `additional_request`,
  CASE 
    WHEN `avatar_url` LIKE 'Avatar URL:%' THEN TRIM(SUBSTRING(`avatar_url`, 12))
    ELSE `avatar_url`
  END,
  'active',
  current_timestamp()
FROM `ipnz_members_old`
WHERE `status` IS NULL OR `status` = 0;

-- Step 4: Migrate contact form submissions (status = 1 = contacts)
INSERT INTO `ipnz_contacts` 
  (`name`, `email`, `company`, `message`, `status`, `created_at`)
SELECT 
  `name`,
  `email`,
  COALESCE(`phone`, ''),
  COALESCE(`additional_request`, 'No message provided'),
  'new',
  current_timestamp()
FROM `ipnz_members_old`
WHERE `status` = 1;

-- Step 5: Update AUTO_INCREMENT to continue from old max ID
SET @max_id = (SELECT IFNULL(MAX(id), 0) FROM ipnz_members_old);
SET @sql = CONCAT('ALTER TABLE ipnz_members AUTO_INCREMENT = ', @max_id + 1);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 6: Drop old view and create new ones
DROP VIEW IF EXISTS `view_members`;

CREATE OR REPLACE VIEW `view_active_members` AS 
SELECT 
    ROW_NUMBER() OVER (ORDER BY m.created_at DESC) AS `No`,
    m.id,
    m.name AS `Name`,
    m.email AS `Email`,
    m.phone AS `Phone`,
    CASE m.join_type 
        WHEN 'early_access' THEN 'Early Access' 
        WHEN 'standard' THEN 'Standard'
    END AS `Join Type`,
    m.avatar_url AS `Avatar`,
    m.created_at AS `Joined`
FROM ipnz_members m
WHERE m.status = 'active' 
  AND m.deleted_at IS NULL
ORDER BY m.created_at DESC;

-- Step 7: Keep old table for safety (remove this line after confirming migration)
-- DROP TABLE `ipnz_members_old`;

COMMIT;

-- Note: After confirming migration is successful, run:
-- DROP TABLE `ipnz_members_old`;
