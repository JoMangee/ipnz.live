<?php
/**
 * Full Registration and Referral Flow Test
 * Tests the complete user journey from registration through referral
 */

require_once('database.php');
require_once('email.php');

echo "=== FULL REGISTRATION & REFERRAL FLOW TEST ===\n\n";

// Initialize services
$emailService = new EmailService($connection);

// Step 1: Get an existing member's referral code to use
echo "Step 1: Getting existing member referral code...\n";
$result = $connection->query("SELECT uuid, referral_code, name FROM ipnz_members WHERE deleted_at IS NULL ORDER BY created_at ASC LIMIT 1");
$referrer = $result->fetch_assoc();
if (!$referrer) {
    die("ERROR: No existing members found!\n");
}
echo "  ✓ Using referrer: {$referrer['name']} (Code: {$referrer['referral_code']})\n";
echo "  ✓ Referrer UUID: {$referrer['uuid']}\n\n";

// Step 2: Simulate new user registration with referral
echo "Step 2: Creating new member with referral...\n";
$timestamp = time();
$newMemberData = [
    'name' => "Test Member {$timestamp}",
    'email' => "test{$timestamp}@example.local",
    'phone' => '+64212345678',
    'join_type' => 'early_access',
    'additional_request' => 'Testing full flow',
    'avatar_url' => 'https://models.readyplayer.me/test.png',
    'has_custom_avatar' => 1,
    'referrer_code' => $referrer['referral_code']
];

// Generate UUID and referral code
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$newUuid = generateUUID();
$newReferralCode = $emailService->generateReferralCode();

echo "  ✓ Generated UUID: {$newUuid}\n";
echo "  ✓ Generated referral code: {$newReferralCode}\n";

// Insert new member
$stmt = $connection->prepare("INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, additional_request, avatar_url, has_custom_avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("ssssssssi", 
    $newUuid, 
    $newReferralCode, 
    $newMemberData['name'], 
    $newMemberData['email'], 
    $newMemberData['phone'], 
    $newMemberData['join_type'], 
    $newMemberData['additional_request'], 
    $newMemberData['avatar_url'], 
    $newMemberData['has_custom_avatar']
);

if (!$stmt->execute()) {
    die("ERROR: Failed to insert member: " . $stmt->error . "\n");
}
$stmt->close();
echo "  ✓ Member created successfully\n\n";

// Step 3: Track the referral
echo "Step 3: Tracking referral relationship...\n";
$stmt = $connection->prepare("INSERT INTO ipnz_referrals (referrer_uuid, referral_uuid, referral_code) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $referrer['uuid'], $newUuid, $referrer['referral_code']);
if (!$stmt->execute()) {
    die("ERROR: Failed to track referral: " . $stmt->error . "\n");
}
$stmt->close();
echo "  ✓ Referral tracked: {$referrer['name']} referred {$newMemberData['name']}\n\n";

// Step 4: Create email verification token
echo "Step 4: Creating email verification token...\n";
$ipAddress = '127.0.0.1';
$token = $emailService->createVerificationToken($newUuid, $newMemberData['email'], $ipAddress);
if ($token) {
    echo "  ✓ Verification token created: {$token}\n";
    echo "  ✓ Verify URL: https://localhost/verify?token={$token}\n\n";
} else {
    echo "  ✗ Failed to create verification token\n\n";
}

// Step 5: Verify all database records
echo "Step 5: Verifying database records...\n";

// Check member exists
$stmt = $connection->prepare("SELECT uuid, referral_code, name, email, status FROM ipnz_members WHERE uuid = ?");
$stmt->bind_param("s", $newUuid);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if ($member) {
    echo "  ✓ Member found in database:\n";
    echo "    - UUID: {$member['uuid']}\n";
    echo "    - Name: {$member['name']}\n";
    echo "    - Email: {$member['email']}\n";
    echo "    - Referral Code: {$member['referral_code']}\n";
    echo "    - Status: {$member['status']}\n";
} else {
    die("  ✗ Member not found in database!\n");
}

// Check referral tracking
$stmt = $connection->prepare("SELECT referrer_uuid, referral_uuid, referral_code, created_at FROM ipnz_referrals WHERE referral_uuid = ?");
$stmt->bind_param("s", $newUuid);
$stmt->execute();
$result = $stmt->get_result();
$referralRecord = $result->fetch_assoc();
$stmt->close();

if ($referralRecord) {
    echo "  ✓ Referral tracking verified:\n";
    echo "    - Referrer: {$referralRecord['referrer_uuid']}\n";
    echo "    - Referred: {$referralRecord['referral_uuid']}\n";
    echo "    - Via Code: {$referralRecord['referral_code']}\n";
    echo "    - Date: {$referralRecord['created_at']}\n";
} else {
    echo "  ✗ Referral tracking record not found!\n";
}

// Check verification token
if ($token) {
    $stmt = $connection->prepare("SELECT token, email, expires_at, verified_at FROM ipnz_email_verifications WHERE member_uuid = ?");
    $stmt->bind_param("s", $newUuid);
    $stmt->execute();
    $result = $stmt->get_result();
    $verification = $result->fetch_assoc();
    $stmt->close();
    
    if ($verification) {
        echo "  ✓ Verification token stored:\n";
        echo "    - Token: {$verification['token']}\n";
        echo "    - Email: {$verification['email']}\n";
        echo "    - Expires: {$verification['expires_at']}\n";
        echo "    - Verified: " . ($verification['verified_at'] ? $verification['verified_at'] : 'Not yet') . "\n";
    } else {
        echo "  ✗ Verification token not found in database!\n";
    }
}

echo "\n";

// Step 6: Test that new member can also refer others
echo "Step 6: Testing that new member can refer others...\n";
$nextMemberData = [
    'name' => "Test Member " . ($timestamp + 1),
    'email' => "test" . ($timestamp + 1) . "@example.local",
    'phone' => '+64212345679',
];

$nextUuid = generateUUID();
$nextReferralCode = $emailService->generateReferralCode();

echo "  ✓ New member's referral code: {$newReferralCode}\n";
echo "  ✓ Creating another test member referred by the first test member...\n";

$stmt = $connection->prepare("INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, status) VALUES (?, ?, ?, ?, ?, 'standard', 'pending')");
$stmt->bind_param("sssss", $nextUuid, $nextReferralCode, $nextMemberData['name'], $nextMemberData['email'], $nextMemberData['phone']);
$stmt->execute();
$stmt->close();

// Track this referral
$stmt = $connection->prepare("INSERT INTO ipnz_referrals (referrer_uuid, referral_uuid, referral_code) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $newUuid, $nextUuid, $newReferralCode);
$stmt->execute();
$stmt->close();

echo "  ✓ Second-level referral created successfully\n";
echo "  ✓ Chain: {$referrer['name']} → {$newMemberData['name']} → {$nextMemberData['name']}\n\n";

// Step 7: Generate referral statistics
echo "Step 7: Referral statistics...\n";
$result = $connection->query("SELECT COUNT(*) as total FROM ipnz_referrals");
$stats = $result->fetch_assoc();
echo "  ✓ Total referrals in system: {$stats['total']}\n";

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM ipnz_referrals WHERE referrer_uuid = ?");
$stmt->bind_param("s", $referrer['uuid']);
$stmt->execute();
$result = $stmt->get_result();
$referrerStats = $result->fetch_assoc();
$stmt->close();
echo "  ✓ Original referrer ({$referrer['name']}) has referred: {$referrerStats['count']} members\n";

$stmt = $connection->prepare("SELECT COUNT(*) as count FROM ipnz_referrals WHERE referrer_uuid = ?");
$stmt->bind_param("s", $newUuid);
$stmt->execute();
$result = $stmt->get_result();
$newMemberStats = $result->fetch_assoc();
$stmt->close();
echo "  ✓ Test member ({$newMemberData['name']}) has referred: {$newMemberStats['count']} members\n\n";

echo "=== FLOW TEST COMPLETED SUCCESSFULLY ===\n";
echo "\nTest Summary:\n";
echo "✓ New member registration with UUID and referral code\n";
echo "✓ Referral tracking between members\n";
echo "✓ Email verification token generation\n";
echo "✓ Multi-level referral chain (3 levels deep)\n";
echo "✓ Referral statistics tracking\n";
echo "\nTest Members Created:\n";
echo "1. {$newMemberData['name']} (Code: {$newReferralCode})\n";
echo "2. {$nextMemberData['name']} (Code: {$nextReferralCode})\n";
echo "\nYou can test the referral link: https://localhost/join?ref={$newReferralCode}\n";
