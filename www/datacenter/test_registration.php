<?php
/**
 * Test Registration Flow
 * 
 * This script tests the full registration flow with referral tracking
 * Run with: php test_registration.php
 */

// Load dependencies
require_once(__DIR__ . '/database.php');
require_once(__DIR__ . '/email.php');

// Create test data
$testName = 'Test User ' . time();
$testEmail = 'test' . time() . '@example.local';
$testPhone = '+1234567890';
$testJoinType = 'standard';
$testRequest = 'Testing referral system';
$testReferrerCode = 'KJF9EX'; // First member's code from migration

echo "=== Registration Test ===\n";
echo "Name: $testName\n";
echo "Email: $testEmail\n";
echo "Referrer Code: $testReferrerCode\n\n";

try {
    // Step 1: Validate referrer code
    echo "Step 1: Validating referrer code...\n";
    $stmt = $connection->prepare("SELECT uuid FROM ipnz_members WHERE referral_code = ? AND deleted_at IS NULL");
    $stmt->bind_param("s", $testReferrerCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "  ✗ Referrer code not found\n";
        exit(1);
    }
    
    $referrerRow = $result->fetch_assoc();
    $referrerUuid = $referrerRow['uuid'];
    echo "  ✓ Referrer found: $referrerUuid\n";
    $stmt->close();
    
    // Step 2: Generate UUID for new member
    echo "\nStep 2: Generating UUID for new member...\n";
    function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    $newMemberUuid = generateUUID();
    echo "  ✓ UUID generated: $newMemberUuid\n";
    
    // Step 3: Generate referral code
    echo "\nStep 3: Generating referral code for new member...\n";
    $emailService = new EmailService($connection);
    $newReferralCode = $emailService->generateReferralCode();
    echo "  ✓ Referral code generated: $newReferralCode\n";
    
    // Step 4: Insert new member
    echo "\nStep 4: Inserting new member...\n";
    $stmt = $connection->prepare("INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, additional_request, avatar_url, has_custom_avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssssssis", $newMemberUuid, $newReferralCode, $testName, $testEmail, $testPhone, $testJoinType, $testRequest, $dummyUrl, $hasCustom);
    
    $dummyUrl = '';
    $hasCustom = 0;
    
    if (!$stmt->execute()) {
        echo "  ✗ Failed to insert member: " . $stmt->error . "\n";
        $stmt->close();
        exit(1);
    }
    
    echo "  ✓ Member inserted successfully\n";
    $stmt->close();
    
    // Step 5: Track referral
    echo "\nStep 5: Tracking referral...\n";
    $trackStmt = $connection->prepare("INSERT INTO ipnz_referrals (referrer_uuid, referral_uuid, referral_code) VALUES (?, ?, ?)");
    $trackStmt->bind_param("sss", $referrerUuid, $newMemberUuid, $testReferrerCode);
    
    if (!$trackStmt->execute()) {
        echo "  ✗ Failed to track referral: " . $trackStmt->error . "\n";
        $trackStmt->close();
        exit(1);
    }
    
    echo "  ✓ Referral tracked: $referrerUuid → $newMemberUuid (Code: $testReferrerCode)\n";
    $trackStmt->close();
    
    // Step 6: Verify the referral was tracked
    echo "\nStep 6: Verifying referral tracking...\n";
    $verifyStmt = $connection->prepare("SELECT referrer_uuid, referral_uuid, referral_code, created_at FROM ipnz_referrals WHERE referral_uuid = ?");
    $verifyStmt->bind_param("s", $newMemberUuid);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($row = $verifyResult->fetch_assoc()) {
        echo "  ✓ Referral record found:\n";
        echo "    Referrer UUID: {$row['referrer_uuid']}\n";
        echo "    New Member UUID: {$row['referral_uuid']}\n";
        echo "    Referral Code: {$row['referral_code']}\n";
        echo "    Created: {$row['created_at']}\n";
    }
    $verifyStmt->close();
    
    echo "\n=== Test Completed Successfully ===\n";
    echo "New Member UUID: $newMemberUuid\n";
    echo "New Member Code: $newReferralCode\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
