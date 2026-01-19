<?php
require('database.php');
require('email.php');

// Initialize email service
$emailService = new EmailService($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["submit"])) {
        // Sanitize and validate inputs
        $name = trim($_POST['join-form-name']);
        $email = trim($_POST['join-form-email']);
        $phone = trim($_POST['join-form-phone']);
        $addrqst = trim($_POST['join-form-message']);
        $avatarUrl = trim($_POST['avatarUrl']);
        // Map radio value (0/1) to ENUM strings expected by DB
        $joinTypeValue = isset($_POST['join-type']) ? $_POST['join-type'] : '0';
        $joinType = ($joinTypeValue === '1') ? 'standard' : 'early_access';
        
        // Strip "Avatar URL: " prefix if present
        if (strpos($avatarUrl, 'Avatar URL: ') === 0) {
            $avatarUrl = substr($avatarUrl, 12);
        }
        
        // Handle avatar - use default if not provided (soft fail)
        $hasCustomAvatar = 0;
        if (empty($avatarUrl)) {
            // Use default avatar URL
            $avatarUrl = 'https://models.readyplayer.me/64bfa15f0e72c63d7c3934a6.png';
            $hasCustomAvatar = 0;
        } else {
            $hasCustomAvatar = 1;
        }
        
        // Extract referrer UUID from hidden field
        $referrerUuid = null;
        if (!empty($_POST['referrer_uuid'])) {
            $referrerUuid = trim($_POST['referrer_uuid']);
            // Validate referrer exists
            $stmt = $connection->prepare("SELECT uuid FROM ipnz_members WHERE uuid = ? AND deleted_at IS NULL");
            $stmt->bind_param("s", $referrerUuid);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $referrerUuid = null; // Invalid referrer
            }
            $stmt->close();
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Preserve form data for repopulation
            $GLOBALS['form_error'] = 'Invalid email address';
            $GLOBALS['form_data'] = $_POST;
            echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Invalid email address.</div></div>';
            return;
        }

        // If this is an update request (member_uuid present), perform update and return
        $memberUuid = isset($_POST['member_uuid']) ? trim($_POST['member_uuid']) : '';
        if (!empty($memberUuid) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $memberUuid)) {
            $stmt = $connection->prepare("UPDATE ipnz_members SET name = ?, phone = ?, join_type = ?, additional_request = ?, avatar_url = ?, has_custom_avatar = ? WHERE uuid = ? AND email = ? AND deleted_at IS NULL");
            $stmt->bind_param("ssssssss", $name, $phone, $joinType, $addrqst, $avatarUrl, $hasCustomAvatar, $memberUuid, $email);
            if ($stmt->execute()) {
                // Fetch referral_code for response
                $stmt2 = $connection->prepare("SELECT referral_code FROM ipnz_members WHERE uuid = ?");
                $stmt2->bind_param("s", $memberUuid);
                $stmt2->execute();
                $refCode = $stmt2->get_result()->fetch_assoc()['referral_code'];
                $stmt2->close();
                
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Details updated successfully.</div></div>';
                echo '<script>try{localStorage.setItem("ipnz_member_uuid","' . htmlspecialchars($memberUuid) . '");localStorage.setItem("ipnz_ref","' . htmlspecialchars($refCode) . '");localStorage.setItem("ipnz_member_profile",JSON.stringify({name:' . json_encode($name) . ',email:' . json_encode($email) . ',phone:' . json_encode($phone) . ',join_type:' . json_encode($joinType) . ',additional_request:' . json_encode($addrqst) . ',avatar_url:' . json_encode($avatarUrl) . ',has_custom_avatar:' . json_encode($hasCustomAvatar) . '}));}catch(e){}</script>';
            } else {
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Update Failed! Database error occurred.</div></div>';
            }
            $stmt->close();
            return;
        }

        // Check if email already exists (soft check - for notification purposes only)
        $emailExists = false;
        $stmt = $connection->prepare("SELECT uuid, name FROM ipnz_members WHERE email = ? AND deleted_at IS NULL");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $emailExists = true;
            // Note: In production, you would send a security notification email here
            // to the existing account holder(s) informing them another signup attempted
            // with their email address, without revealing any personal information
        }
        $stmt->close();
        
        // Generate UUID and referral code
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        $referralCode = $emailService->generateReferralCode();
        
        // Insert new member using prepared statement (status pending for email verification)
        $stmt = $connection->prepare("INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, additional_request, avatar_url, has_custom_avatar, referrer_uuid, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssssssis", $uuid, $referralCode, $name, $email, $phone, $joinType, $addrqst, $avatarUrl, $hasCustomAvatar, $referrerUuid);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Create verification token and send email
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $token = $emailService->createVerificationToken($uuid, $email, $ipAddress);
            
            if ($token) {
                $emailResult = $emailService->sendVerificationEmail($uuid, $email, $name, $token);
                
                if ($emailResult['success']) {
                    // Success with email sent
                    $message = 'Sign up Success! A verification email has been sent to ' . htmlspecialchars($email) . '. Please check your inbox and verify your email to activate your account.';
                    if ($emailExists) {
                        $message .= ' <br><small>Note: This email address is associated with other accounts.</small>';
                    }
                    $message .= ' <br>Your referral link: https://IPnz.live?ref=' . htmlspecialchars($referralCode);
                    
                    echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">' . $message . '</div></div>';
                    echo '<script>try{localStorage.setItem("ipnz_ref","' . $referralCode . '");localStorage.setItem("ipnz_member_uuid","' . $uuid . '");localStorage.setItem("ipnz_member_profile",JSON.stringify({name:' . json_encode($name) . ',email:' . json_encode($email) . ',phone:' . json_encode($phone) . ',join_type:' . json_encode($joinType) . ',additional_request:' . json_encode($addrqst) . ',avatar_url:' . json_encode($avatarUrl) . ',has_custom_avatar:' . json_encode($hasCustomAvatar) . '}));}catch(e){}</script>';
                } else {
                    // Success but email failed
                    echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ff9800; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! However, we couldn\'t send the verification email. <a href="/resend?email=' . urlencode($email) . '" style="color:white;text-decoration:underline;">Click here to resend</a>. <br>Your referral link: https://IPnz.live?ref=' . htmlspecialchars($referralCode) . '</div></div>';
                    echo '<script>try{localStorage.setItem("ipnz_ref","' . $referralCode . '");localStorage.setItem("ipnz_member_uuid","' . $uuid . '");localStorage.setItem("ipnz_member_profile",JSON.stringify({name:' . json_encode($name) . ',email:' . json_encode($email) . ',phone:' . json_encode($phone) . ',join_type:' . json_encode($joinType) . ',additional_request:' . json_encode($addrqst) . ',avatar_url:' . json_encode($avatarUrl) . ',has_custom_avatar:' . json_encode($hasCustomAvatar) . '}));}catch(e){}</script>';
                }
            } else {
                // Token creation failed
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ff9800; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! However, verification email couldn\'t be sent. Please contact support.</div></div>';
            }
        } else {
            echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Database error occurred.</div></div>';
        }
        $stmt->close();
    }
}
?>