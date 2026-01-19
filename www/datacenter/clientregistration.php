<?php
require('database.php');
require('email.php');

// Initialize email service
$emailService = new EmailService($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["submit"])) {
        try {
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
            
            // Extract referrer ID from hidden field and validate
            $referrerId = null;
            if (!empty($_POST['referrer_id'])) {
                $referrerId = trim($_POST['referrer_id']);
                if (is_numeric($referrerId)) {
                    // Validate referrer exists
                    $stmt = $connection->prepare("SELECT id FROM ipnz_members WHERE id = ? AND deleted_at IS NULL");
                    $stmt->bind_param("s", $referrerId);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows === 0) {
                        $referrerId = null; // Invalid referrer
                    }
                    $stmt->close();
                }
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Preserve form data for repopulation
                $GLOBALS['form_error'] = 'Invalid email address';
                $GLOBALS['form_data'] = $_POST;
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Invalid email address.</div></div>';
                return;
            }

            // If this is an update request (member_id present), perform update and return
            $memberId = isset($_POST['member_id']) ? trim($_POST['member_id']) : '';
            if (!empty($memberId) && is_numeric($memberId)) {
                $stmt = $connection->prepare("UPDATE ipnz_members SET name = ?, phone = ?, join_type = ?, additional_request = ?, avatar_url = ?, has_custom_avatar = ? WHERE id = ? AND email = ? AND deleted_at IS NULL");
                $stmt->bind_param("ssssssss", $name, $phone, $joinType, $addrqst, $avatarUrl, $hasCustomAvatar, $memberId, $email);
                if ($stmt->execute()) {
                    echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Details updated successfully.</div></div>';
                    echo '<script>try{localStorage.setItem("ipnz_member_id","' . htmlspecialchars($memberId) . '");localStorage.setItem("ipnz_member_profile",JSON.stringify({name:' . json_encode($name) . ',email:' . json_encode($email) . ',phone:' . json_encode($phone) . ',join_type:' . json_encode($joinType) . ',additional_request:' . json_encode($addrqst) . ',avatar_url:' . json_encode($avatarUrl) . ',has_custom_avatar:' . json_encode($hasCustomAvatar) . '}));}catch(e){}</script>';
                } else {
                    echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Update Failed! Database error occurred.</div></div>';
                }
                $stmt->close();
                return;
            }

            // Check if email already exists (soft check - for notification purposes only)
            $emailExists = false;
            $stmt = $connection->prepare("SELECT id, name FROM ipnz_members WHERE email = ? AND deleted_at IS NULL");
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
            
            // For now, use the existing 'id' column since database hasn't been migrated to UUID yet
            // Insert new member using prepared statement
            $stmt = $connection->prepare("INSERT INTO ipnz_members (name, email, phone, join_type, additional_request, avatar_url, has_custom_avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $phone, $joinType, $addrqst, $avatarUrl, $hasCustomAvatar);
            
            if ($stmt->execute()) {
                $memberId = $connection->insert_id;
                $stmt->close();
                
                // Generate a simple referral code for now (m{id})
                $referralCode = 'm' . $memberId;
                
                // Store member ID in localStorage for now
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! Welcome to IPnz.live. Your referral link: https://IPnz.live?ref=' . htmlspecialchars($referralCode) . '</div></div>';
                echo '<script>try{localStorage.setItem("ipnz_ref","' . $referralCode . '");localStorage.setItem("ipnz_member_id","' . $memberId . '");localStorage.setItem("ipnz_member_profile",JSON.stringify({name:' . json_encode($name) . ',email:' . json_encode($email) . ',phone:' . json_encode($phone) . ',join_type:' . json_encode($joinType) . ',additional_request:' . json_encode($addrqst) . ',avatar_url:' . json_encode($avatarUrl) . ',has_custom_avatar:' . json_encode($hasCustomAvatar) . '}));}catch(e){}</script>';
            } else {
                throw new Exception('Database insert failed: ' . $connection->error);
            }
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
    }
}
?>
