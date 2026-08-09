-- Add new columns to existing ipnz_members table (safe version)
-- Only adds columns if they don't exist

-- Add UUID column if it doesn't exist
ALTER TABLE `ipnz_members` 
ADD COLUMN IF NOT EXISTS `uuid` CHAR(36) NULL FIRST;

-- Add referral code column if it doesn't exist
ALTER TABLE `ipnz_members` 
ADD COLUMN IF NOT EXISTS `referral_code` VARCHAR(10) NULL AFTER `uuid`;

-- Add email verification flag if it doesn't exist
ALTER TABLE `ipnz_members` 
ADD COLUMN IF NOT EXISTS `email_verified` TINYINT(1) NOT NULL DEFAULT 0;

-- Add unique indexes (will fail silently if they exist)
ALTER TABLE `ipnz_members` ADD UNIQUE INDEX IF NOT EXISTS `uuid_unique` (`uuid`);
ALTER TABLE `ipnz_members` ADD UNIQUE INDEX IF NOT EXISTS `referral_code_unique` (`referral_code`);
