<?php
/**
 * Test Referral Signup Flow
 * This script tests the complete referral system
 */

require 'datacenter/database.php';

// HTML wrapper for better formatting
echo "<pre style='font-family: monospace; white-space: pre-wrap; word-wrap: break-word; background: #f5f5f5; padding: 15px; border-radius: 5px;'>\n";

echo "=== IPnz.live Referral Flow Test ===\n\n";

// Step 1: Get an active member to use as referrer
echo "Step 1: Finding an active member to use as referrer...\n";
$result = $connection->query("SELECT uuid, name, referral_code, email FROM ipnz_members WHERE status='active' LIMIT 1");

if ($result && $result->num_rows > 0) {
    $referrer = $result->fetch_assoc();
    echo "✓ Found referrer: {$referrer['name']} (Code: {$referrer['referral_code']})\n";
    echo "  URL to test: https://auth-dev.ipnz.live/?ref={$referrer['referral_code']}\n\n";
} else {
    echo "✗ No active members found. Creating a test referrer...\n";
    // Create a test referrer
    $testUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    $referralCode = generateReferralCode();
    
    $stmt = $connection->prepare("INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, status, email_verified) VALUES (?, ?, ?, ?, ?, 'active', 1)");
    $stmt->bind_param("sssss", $testUuid, $referralCode, $name, $email, $phone);
    
    $name = "Test Referrer";
    $email = "referrer@test.local";
    $phone = "021234567";
    
    if ($stmt->execute()) {
        echo "✓ Created test referrer\n";
        $referrer = [
            'uuid' => $testUuid,
            'name' => $name,
            'referral_code' => $referralCode,
            'email' => $email
        ];
        echo "  Referral Code: {$referrer['referral_code']}\n";
        echo "  URL to test: https://auth-dev.ipnz.live/?ref={$referrer['referral_code']}\n\n";
    } else {
        echo "✗ Failed to create test referrer: " . $connection->error . "\n";
        exit(1);
    }
    $stmt->close();
}

// Step 2: Check database state before signup
echo "Step 2: Checking current database state...\n";
$countResult = $connection->query("SELECT COUNT(*) as count FROM ipnz_members");
$countRow = $countResult->fetch_assoc();
echo "  Total members: {$countRow['count']}\n";

$refResult = $connection->query("SELECT COUNT(*) as count FROM ipnz_referrals");
$refRow = $refResult->fetch_assoc();
echo "  Total referral relationships: {$refRow['count']}\n\n";

// Step 3: Show the test scenario
echo "Step 3: TEST SCENARIO\n";
echo "=====================\n";
echo "1. Open: https://auth-dev.ipnz.live/?ref={$referrer['referral_code']}\n";
echo "2. You should see a yellow/orange banner saying:\n";
echo "   'Hey! Someone thought this was awesome...'\n";
echo "3. Click 'Join Now' button\n";
echo "4. Fill in the form:\n";
echo "   - Name: Test User [your name]\n";
echo "   - Email: test-" . time() . "@example.com\n";
echo "   - Phone: 021 234 5678\n";
echo "   - Join Type: Standard (or Early Access)\n";
echo "5. Click Submit\n";
echo "6. You should see green success message with:\n";
echo "   - Verification email notification\n";
echo "   - Your new referral code\n\n";

// Step 4: Query helper for verification
echo "Step 4: DATABASE VERIFICATION QUERIES\n";
echo "======================================\n";
echo "After signup, run these to verify:\n\n";

echo "Query 1 - Check new member was created:\n";
echo "  SELECT uuid, name, email, referral_code, status FROM ipnz_members\n";
echo "  ORDER BY created_at DESC LIMIT 1;\n\n";

echo "Query 2 - Check referral relationship:\n";
echo "  SELECT r.referrer_uuid, r.referral_uuid, m.name as referred_member\n";
echo "  FROM ipnz_referrals r\n";
echo "  LEFT JOIN ipnz_members m ON r.referral_uuid = m.uuid\n";
echo "  ORDER BY r.created_at DESC LIMIT 1;\n\n";

echo "Query 3 - Check email was logged:\n";
echo "  SELECT recipient_email, status, created_at FROM ipnz_email_audit_log\n";
echo "  WHERE recipient_email LIKE 'test-%@example.com'\n";
echo "  ORDER BY created_at DESC LIMIT 1;\n\n";

// Step 5: Browser console test
echo "Step 5: BROWSER CONSOLE TEST\n";
echo "=============================\n";
echo "While on the page, paste this in browser DevTools console:\n\n";
echo "// Check 1: Initial page load\n";
echo "console.log('Page URL:', window.location.href);\n";
echo "console.log('Stored referral:', localStorage.getItem('ipnz_incoming_ref'));\n\n";

echo "// Check 2: On join page\n";
echo "const referrerField = document.getElementById('referrer_code');\n";
echo "console.log('Referrer field value:', referrerField ? referrerField.value : 'NOT FOUND');\n";
echo "console.log('localStorage referral:', localStorage.getItem('ipnz_incoming_ref'));\n\n";

echo "// Check 3: After successful signup\n";
echo "console.log('New member UUID:', localStorage.getItem('ipnz_member_uuid'));\n";
echo "console.log('New referral code:', localStorage.getItem('ipnz_ref'));\n\n";

echo "=== Ready to test! ===\n";

echo "</pre>\n"; // Close HTML pre tag

// Helper function
function generateReferralCode() {
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

$connection->close();
?>
