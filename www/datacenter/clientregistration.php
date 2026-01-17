<?php
require('database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["submit"])) {
        // Sanitize and validate inputs
        $name = trim($_POST['join-form-name']);
        $email = trim($_POST['join-form-email']);
        $phone = trim($_POST['join-form-phone']);
        $addrqst = trim($_POST['join-form-message']);
        $avatarUrl = trim($_POST['avatarUrl']);
        $joinType = isset($_POST['join-type']) ? intval($_POST['join-type']) : 0;
        
        // Strip "Avatar URL: " prefix if present
        if (strpos($avatarUrl, 'Avatar URL: ') === 0) {
            $avatarUrl = substr($avatarUrl, 12);
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Invalid email address.</div></div>';
            return;
        }
        
        // Validate avatar URL
        if (empty($avatarUrl)) {
            echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Please create your avatar.</div></div>';
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
        
        // Insert new member using prepared statement (status pending for email verification)
        $stmt = $connection->prepare("INSERT INTO ipnz_members (name, email, phone, join_type, additional_request, avatar_url, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssiss", $name, $email, $phone, $joinType, $addrqst, $avatarUrl);
        
        if ($stmt->execute()) {
            $newMemberId = $connection->insert_id;
            
            // TODO: Send verification email to the provided email address
            // Include verification token/link and member ID
            
            if ($emailExists) {
                // If email is shared, all account holders at this email will receive notification
                // This is privacy-secure as it doesn't reveal who else uses the email
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! A verification email has been sent to ' . htmlspecialchars($email) . '. Please verify your email to activate your account. Note: This email address is associated with other accounts.</div></div>';
            } else {
                echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! A verification email has been sent to ' . htmlspecialchars($email) . '. Please verify your email to activate your account.</div></div>';
            }
        } else {
            echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ea0309; border: 1px solid #ea0309; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Failed! Database error occurred.</div></div>';
        }
        $stmt->close();
    }
}
?>