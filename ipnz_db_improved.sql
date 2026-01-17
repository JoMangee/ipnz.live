-- Improved IPnz.live Database Schema
-- Date: January 18, 2026

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ipnz_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ipnz_members`
-- Separated from contact messages for better data organization
--

CREATE TABLE `ipnz_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Member display name',
  `email` varchar(255) NOT NULL COMMENT 'Member email address (can be shared)',
  `phone` varchar(30) DEFAULT NULL COMMENT 'Contact phone number',
  `join_type` ENUM('early_access', 'standard') NOT NULL DEFAULT 'early_access' COMMENT 'Membership type',
  `additional_request` text DEFAULT NULL COMMENT 'Additional member notes or requests',
  `avatar_url` varchar(500) DEFAULT NULL COMMENT 'Ready Player Me avatar URL',
  `status` ENUM('active', 'pending', 'inactive', 'deleted') NOT NULL DEFAULT 'pending' COMMENT 'Member account status',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Email verification status',
  `privacy_consent` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'GDPR/Privacy policy consent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Registration timestamp',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update timestamp',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft delete timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Internet Party members registry';

-- --------------------------------------------------------

--
-- Table structure for table `ipnz_contacts`
-- Separate table for contact form submissions
--

CREATE TABLE `ipnz_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Contact person name',
  `email` varchar(255) NOT NULL COMMENT 'Contact email address',
  `company` varchar(150) DEFAULT NULL COMMENT 'Company or affiliation',
  `message` text NOT NULL COMMENT 'Contact message content',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Submitter IP address',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Browser user agent',
  `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new' COMMENT 'Contact message status',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Submission timestamp',
  `replied_at` timestamp NULL DEFAULT NULL COMMENT 'Reply timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contact form submissions';

-- --------------------------------------------------------

--
-- Table structure for table `ipnz_member_activity`
-- Track member activities and login history
--

CREATE TABLE `ipnz_member_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `activity_type` ENUM('login', 'profile_update', 'avatar_change', 'status_change') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Member activity log';

-- --------------------------------------------------------

--
-- Indexes for table `ipnz_members`
--
ALTER TABLE `ipnz_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_join_type` (`join_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indexes for table `ipnz_contacts`
--
ALTER TABLE `ipnz_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `ipnz_member_activity`
--
ALTER TABLE `ipnz_member_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_member_activity` (`member_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `ipnz_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `ipnz_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `ipnz_member_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `ipnz_member_activity`
--
ALTER TABLE `ipnz_member_activity`
  ADD CONSTRAINT `fk_member_activity` FOREIGN KEY (`member_id`) REFERENCES `ipnz_members` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- View: Active members for public display
--
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
    m.created_at AS `Joined`,
    DATEDIFF(CURRENT_DATE, m.created_at) AS `Days Active`
FROM ipnz_members m
WHERE m.status = 'active' 
  AND m.deleted_at IS NULL
ORDER BY m.created_at DESC;

-- --------------------------------------------------------

--
-- View: Pending members requiring approval
--
CREATE OR REPLACE VIEW `view_pending_members` AS 
SELECT 
    m.id,
    m.name,
    m.email,
    m.phone,
    m.join_type,
    m.avatar_url,
    m.email_verified,
    m.privacy_consent,
    m.created_at,
    DATEDIFF(CURRENT_DATE, m.created_at) AS `days_pending`
FROM ipnz_members m
WHERE m.status = 'pending' 
  AND m.deleted_at IS NULL
ORDER BY m.created_at ASC;

-- --------------------------------------------------------

--
-- View: Unread contact messages
--
CREATE OR REPLACE VIEW `view_new_contacts` AS 
SELECT 
    c.id,
    c.name,
    c.email,
    c.company,
    LEFT(c.message, 100) AS `message_preview`,
    c.created_at,
    DATEDIFF(CURRENT_DATE, c.created_at) AS `days_old`
FROM ipnz_contacts c
WHERE c.status = 'new'
ORDER BY c.created_at DESC;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
