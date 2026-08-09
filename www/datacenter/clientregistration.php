<?php
require('database.php');
require('email.php');

// Cache avatar images locally to avoid external CDN dependency
function cacheAvatarLocally($url, $memberUuid)
{
    if (empty($url) || empty($memberUuid)) {
        return null;
    }

    // Basic URL validation
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    // Prepare destination
    $avatarDir = realpath(__DIR__ . '/../images/avatars');
    if ($avatarDir === false) {
        // Try to create if missing
        $avatarDir = __DIR__ . '/../images/avatars';
        if (!is_dir($avatarDir) && !mkdir($avatarDir, 0755, true)) {
            return null;
        }
    }

    // Fetch remote image (8s timeout, follow redirects, 6MB cap)
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT      => 'IPNZ Avatar Cacher',
    ]);
    $data = curl_exec($ch);
    if ($data === false) {
        curl_close($ch);
        return null;
    }

    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
    $sizeBytes   = strlen($data);
    curl_close($ch);

    // Enforce size limit (6MB)
    if ($sizeBytes <= 0 || $sizeBytes > 6 * 1024 * 1024) {
        return null;
    }

    // Derive extension
    $ext = $allowedMime[$contentType] ?? null;
    if (!$ext) {
        // Fallback to path extension
        $pathExt = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        if (in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp'])) {
            $ext = ($pathExt === 'jpeg') ? 'jpg' : $pathExt;
        }
    }
    if (!$ext) {
        return null; // Unknown type
    }

    $fileName   = $memberUuid . '.' . $ext;
    $targetPath = rtrim($avatarDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

    if (file_put_contents($targetPath, $data) === false) {
        return null;
    }

    // Return public-relative URL
    return '/images/avatars/' . $fileName;
}

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
            
            // Handle avatar
            $hasCustomAvatar = 0;
            if (!empty($avatarUrl)) {
                $hasCustomAvatar = 1;
            }
            
            // Extract referrer code from hidden field and validate
            $referrerCode = null;
            if (!empty($_POST['referrer_code'])) {
                $referrerCode = trim($_POST['referrer_code']);
                // Validate referrer code exists (6-char alphanumeric or legacy m{id} format)
                $stmt = $connection->prepare("SELECT uuid FROM ipnz_members WHERE referral_code = ? AND deleted_at IS NULL");
                $stmt->bind_param("s", $referrerCode);
                $stmt->execute();
                if ($stmt->get_result()->num_rows === 0) {
                    $referrerCode = null; // Invalid referrer code
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

            // If this is an update request (member_id present), perform update and return
            $memberId = isset($_POST['member_id']) ? trim($_POST['member_id']) : '';
            if (!empty($memberId) && is_numeric($memberId)) {
                // Fetch member UUID for caching
                $memberUuidForUpdate = null;
                $uuidStmt = $connection->prepare("SELECT uuid FROM ipnz_members WHERE id = ? AND email = ? AND deleted_at IS NULL");
                $uuidStmt->bind_param("is", $memberId, $email);
                if ($uuidStmt->execute()) {
                    $uuidResult = $uuidStmt->get_result();
                    if ($row = $uuidResult->fetch_assoc()) {
                        $memberUuidForUpdate = $row['uuid'];
                    }
                }
                $uuidStmt->close();

                if ($hasCustomAvatar == 1 && $memberUuidForUpdate) {
                    $cachedAvatar = cacheAvatarLocally($avatarUrl, $memberUuidForUpdate);
                    if ($cachedAvatar) {
                        $avatarUrl = $cachedAvatar;
                    }
                }

                $stmt = $connection->prepare("UPDATE ipnz_members SET name = ?, phone = ?, join_type = ?, additional_request = ?, avatar_url = ?, has_custom_avatar = ? WHERE id = ? AND email = ? AND deleted_at IS NULL");
                $stmt->bind_param("sssssiis", $name, $phone, $joinType, $addrqst, $avatarUrl, $hasCustomAvatar, $memberId, $email);
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
            $stmt = $connection->prepare("SELECT id, uuid, name FROM ipnz_members WHERE email = ? AND deleted_at IS NULL");
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
            
            // Generate UUID and referral code for new member
            function generateUUID() {
                return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
            }
            
            $memberUuid = generateUUID();
            $referralCode = $emailService->generateReferralCode();

            if ($hasCustomAvatar == 1) {
                $cachedAvatar = cacheAvatarLocally($avatarUrl, $memberUuid);
                if ($cachedAvatar) {
                    $avatarUrl = $cachedAvatar;
                }
            }
            
            // Insert new member with UUID and referral code
            $stmt = $connection->prepare("INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, additional_request, avatar_url, has_custom_avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssssssssi", $memberUuid, $referralCode, $name, $email, $phone, $joinType, $addrqst, $avatarUrl, $hasCustomAvatar);
            
            if ($stmt->execute()) {
                $stmt->close();
                
                // Track referral if valid referrer code was provided
                if ($referrerCode) {
                    // Get the referrer's UUID
                    $stmt = $connection->prepare("SELECT uuid FROM ipnz_members WHERE referral_code = ? AND deleted_at IS NULL");
                    $stmt->bind_param("s", $referrerCode);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $referrerUuid = $row['uuid'];
                        // Insert referral tracking record
                        $trackStmt = $connection->prepare("INSERT INTO ipnz_referrals (referrer_uuid, referral_uuid, referral_code) VALUES (?, ?, ?)");
                        $trackStmt->bind_param("sss", $referrerUuid, $memberUuid, $referrerCode);
                        $trackStmt->execute();
                        $trackStmt->close();
                    }
                    $stmt->close();
                }
                
                // Create verification token
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $token = $emailService->createVerificationToken($memberUuid, $email, $ipAddress);
                
                if ($token) {
                    // Send verification email
                    $emailResult = $emailService->sendVerificationEmail($memberUuid, $email, $name, $token);
                    
                    if ($emailResult['success']) {
                        echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #4CAF50; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! A verification email has been sent to ' . htmlspecialchars($email) . '. Your referral link: https://IPnz.live?ref=' . htmlspecialchars($referralCode) . '</div></div>';
                    } else {
                        echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ff9800; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! (Email verification couldn\'t be sent). Your referral link: https://IPnz.live?ref=' . htmlspecialchars($referralCode) . '</div></div>';
                    }
                } else {
                    echo '<div align="center"><div class="alert alert-primary" role="alert" style="background-color: #ff9800; border: 1px solid #aaa; color:white; text-align:center; width:100%; border-radius:0px; margin-top:20px; font-family: "GothamPro", sans-serif;">Sign up Success! Your referral link: https://IPnz.live?ref=' . htmlspecialchars($referralCode) . '</div></div>';
                }
                
                // Store UUID and referral code in localStorage
                echo '<script>try{localStorage.setItem("ipnz_member_uuid","' . htmlspecialchars($memberUuid) . '");localStorage.setItem("ipnz_ref","' . htmlspecialchars($referralCode) . '");localStorage.setItem("ipnz_member_profile",JSON.stringify({name:' . json_encode($name) . ',email:' . json_encode($email) . ',phone:' . json_encode($phone) . ',join_type:' . json_encode($joinType) . ',additional_request:' . json_encode($addrqst) . ',avatar_url:' . json_encode($avatarUrl) . ',has_custom_avatar:' . json_encode($hasCustomAvatar) . '}));}catch(e){}</script>';
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
