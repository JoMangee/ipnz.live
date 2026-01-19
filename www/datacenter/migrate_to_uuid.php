<?php
/**
 * Database Migration Script: Add UUID and Email Verification Support
 * This script safely migrates existing members to the new schema
 * Preserves all existing data and generates UUIDs for existing members
 */

require('database.php');

function generateUUID() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generateReferralCode() {
    $charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $charset[mt_rand(0, strlen($charset) - 1)];
    }
    return $code;
}

echo "Starting database migration...\n\n";

try {
    // Step 1: Add uuid column if it doesn't exist
    echo "Step 1: Adding uuid column...\n";
    $checkUUID = $connection->query("SHOW COLUMNS FROM ipnz_members LIKE 'uuid'");
    if ($checkUUID->num_rows === 0) {
        $connection->query("ALTER TABLE ipnz_members ADD COLUMN uuid CHAR(36) UNIQUE AFTER id");
        echo "  ✓ uuid column added\n";
    } else {
        echo "  ✓ uuid column already exists\n";
    }

    // Step 2: Add referral_code column if it doesn't exist
    echo "Step 2: Adding referral_code column...\n";
    $checkRefCode = $connection->query("SHOW COLUMNS FROM ipnz_members LIKE 'referral_code'");
    if ($checkRefCode->num_rows === 0) {
        $connection->query("ALTER TABLE ipnz_members ADD COLUMN referral_code VARCHAR(10) UNIQUE AFTER uuid");
        echo "  ✓ referral_code column added\n";
    } else {
        echo "  ✓ referral_code column already exists\n";
    }

    // Step 3: Create email verification table if it doesn't exist
    echo "Step 3: Creating email verification table...\n";
    $checkTable = $connection->query("SHOW TABLES LIKE 'ipnz_email_verifications'");
    if ($checkTable->num_rows === 0) {
        $connection->query("
            CREATE TABLE ipnz_email_verifications (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                member_uuid CHAR(36),
                email VARCHAR(255) NOT NULL,
                token CHAR(64) UNIQUE NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                verified_at TIMESTAMP NULL,
                attempts INT DEFAULT 0,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_member_uuid (member_uuid),
                KEY idx_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "  ✓ ipnz_email_verifications table created\n";
    } else {
        echo "  ✓ ipnz_email_verifications table already exists\n";
    }

    // Step 4: Create email audit log table if it doesn't exist
    echo "Step 4: Creating email audit log table...\n";
    $checkAudit = $connection->query("SHOW TABLES LIKE 'ipnz_email_audit_log'");
    if ($checkAudit->num_rows === 0) {
        $connection->query("
            CREATE TABLE ipnz_email_audit_log (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                member_uuid CHAR(36),
                recipient_email VARCHAR(255) NOT NULL,
                email_type ENUM('verification', 'password_reset', 'notification') DEFAULT 'verification',
                subject VARCHAR(255),
                status ENUM('pending', 'sent', 'failed', 'bounced', 'rejected') DEFAULT 'pending',
                error_message TEXT,
                sent_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_member_uuid (member_uuid),
                KEY idx_status (status),
                KEY idx_email (recipient_email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "  ✓ ipnz_email_audit_log table created\n";
    } else {
        echo "  ✓ ipnz_email_audit_log table already exists\n";
    }

    // Step 5: Migrate existing members - generate UUIDs and referral codes
    echo "Step 5: Migrating existing members...\n";
    $members = $connection->query("SELECT id FROM ipnz_members WHERE uuid IS NULL AND deleted_at IS NULL");
    $migratedCount = 0;
    
    while ($row = $members->fetch_assoc()) {
        $memberId = $row['id'];
        $uuid = generateUUID();
        $referralCode = generateReferralCode();
        
        $stmt = $connection->prepare("UPDATE ipnz_members SET uuid = ?, referral_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $uuid, $referralCode, $memberId);
        if ($stmt->execute()) {
            $migratedCount++;
            echo "  ✓ Member $memberId: uuid=$uuid, code=$referralCode\n";
        } else {
            echo "  ✗ Failed to migrate member $memberId: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
    echo "  Total migrated: $migratedCount members\n";

    // Step 6: Update views if they exist
    echo "Step 6: Updating views...\n";
    $checkView = $connection->query("SHOW TABLES LIKE 'view_active_members'");
    if ($checkView->num_rows > 0) {
        $connection->query("DROP VIEW IF EXISTS view_active_members");
        $connection->query("
            CREATE VIEW view_active_members AS
            SELECT 
                id, uuid, email, name, phone, join_type,
                referral_code, email_verified, created_at
            FROM ipnz_members
            WHERE status = 'active' AND deleted_at IS NULL
        ");
        echo "  ✓ view_active_members recreated\n";
    }

    echo "\n✅ Migration completed successfully!\n";
    echo "\nVerification:\n";
    
    // Show migrated members
    $result = $connection->query("SELECT id, uuid, referral_code, email FROM ipnz_members WHERE uuid IS NOT NULL AND deleted_at IS NULL");
    echo "Members with UUIDs:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']}, UUID: {$row['uuid']}, Code: {$row['referral_code']}, Email: {$row['email']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}

$connection->close();
?>
