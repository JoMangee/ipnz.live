<?php
/**
 * Email Service
 * Handles email sending with audit logging and verification tokens
 */

require_once('database.php');

class EmailService {
    private $connection;
    private $fromEmail = 'noreply@ipnz.live';
    private $fromName = 'IPnz.live';
    
    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }
    
    /**
     * Generate a short alphanumeric referral code
     * Format: 6 characters, uppercase letters + numbers (avoiding ambiguous chars)
     * Supports 2.1 billion unique codes (36^6)
     */
    public function generateReferralCode() {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed 0, O, 1, I for clarity
        $maxAttempts = 10;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            
            // Check uniqueness
            $stmt = $this->connection->prepare("SELECT uuid FROM ipnz_members WHERE referral_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $stmt->close();
                return $code;
            }
            $stmt->close();
        }
        
        // Fallback: use timestamp + random
        return strtoupper(substr(md5(microtime(true) . random_bytes(8)), 0, 6));
    }
    
    /**
     * Generate verification token
     */
    public function generateVerificationToken() {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }
    
    /**
     * Create verification token record
     */
    public function createVerificationToken($memberUuid, $email, $ipAddress = null) {
        $token = $this->generateVerificationToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $this->connection->prepare(
            "INSERT INTO ipnz_email_verifications (member_uuid, email, token, expires_at, ip_address) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $memberUuid, $email, $token, $expiresAt, $ipAddress);
        
        if ($stmt->execute()) {
            $stmt->close();
            return $token;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Send verification email
     * Returns: ['success' => bool, 'message' => string, 'log_id' => int]
     */
    public function sendVerificationEmail($memberUuid, $email, $name, $token) {
        $subject = 'Verify your IPnz.live account';
        $verifyUrl = 'https://ipnz.live/verify?token=' . $token;
        
        // Log the attempt first
        $logId = $this->logEmailAttempt($memberUuid, $email, 'verification', $subject);
        
        // Build email body
        $body = $this->buildVerificationEmailBody($name, $verifyUrl);
        
        // Attempt to send
        $sent = $this->sendEmail($email, $subject, $body);
        
        if ($sent) {
            $this->updateEmailLogStatus($logId, 'sent');
            return [
                'success' => true,
                'message' => 'Verification email sent successfully',
                'log_id' => $logId
            ];
        } else {
            $this->updateEmailLogStatus($logId, 'failed', 'Email sending failed');
            return [
                'success' => false,
                'message' => 'Failed to send verification email',
                'log_id' => $logId
            ];
        }
    }
    
    /**
     * Build verification email HTML
     */
    private function buildVerificationEmailBody($name, $verifyUrl) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #F8CB2E 0%, #EE5007 100%); padding: 30px; text-align: center; color: white; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 8px; margin-top: 20px; }
        .button { display: inline-block; padding: 12px 30px; background: #EE5007; color: white; text-decoration: none; border-radius: 50px; font-weight: bold; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to IPnz.live!</h1>
        </div>
        <div class="content">
            <p>Hi $name,</p>
            <p>Thanks for joining the Internet Party movement! We're excited to have you.</p>
            <p>Please verify your email address by clicking the button below:</p>
            <p style="text-align: center;">
                <a href="$verifyUrl" class="button">Verify My Email</a>
            </p>
            <p>Or copy this link into your browser:</p>
            <p style="word-break: break-all; background: white; padding: 10px; border-radius: 4px;">$verifyUrl</p>
            <p><strong>This link expires in 24 hours.</strong></p>
            <p>If you didn't sign up for IPnz.live, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Internet Party of New Zealand | <a href="https://ipnz.live">IPnz.live</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Actual email sending (uses PHP mail() - should be replaced with proper SMTP)
     */
    private function sendEmail($to, $subject, $htmlBody) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: hello-ops@ipnz.live\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // For local dev, just log and return true
        if (getenv('APP_ENV') === 'local' || strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
            error_log("EMAIL (DEV MODE): To: $to, Subject: $subject");
            error_log("Body preview: " . substr(strip_tags($htmlBody), 0, 200));
            // In local, we "succeed" but don't actually send
            return true;
        }
        
        // Production: use mail() (should be replaced with SMTP library like PHPMailer)
        return mail($to, $subject, $htmlBody, $headers);
    }
    
    /**
     * Log email send attempt
     */
    private function logEmailAttempt($memberUuid, $recipientEmail, $emailType, $subject) {
        $stmt = $this->connection->prepare(
            "INSERT INTO ipnz_email_audit_log (member_uuid, recipient_email, email_type, subject, status) 
             VALUES (?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param("ssss", $memberUuid, $recipientEmail, $emailType, $subject);
        $stmt->execute();
        $logId = $this->connection->insert_id;
        $stmt->close();
        return $logId;
    }
    
    /**
     * Update email log status
     */
    private function updateEmailLogStatus($logId, $status, $errorMessage = null) {
        $sentAt = ($status === 'sent') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $this->connection->prepare(
            "UPDATE ipnz_email_audit_log 
             SET status = ?, error_message = ?, sent_at = ? 
             WHERE id = ?"
        );
        $stmt->bind_param("sssi", $status, $errorMessage, $sentAt, $logId);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Verify token and activate member
     */
    public function verifyToken($token) {
        // Find token
        $stmt = $this->connection->prepare(
            "SELECT member_uuid, email, expires_at, verified_at 
             FROM ipnz_email_verifications 
             WHERE token = ?"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Invalid verification link'];
        }
        
        $row = $result->fetch_assoc();
        $stmt->close();
        
        // Check if already verified
        if ($row['verified_at']) {
            return ['success' => false, 'message' => 'Email already verified'];
        }
        
        // Check expiration
        if (strtotime($row['expires_at']) < time()) {
            return ['success' => false, 'message' => 'Verification link expired'];
        }
        
        // Mark token as verified
        $stmt = $this->connection->prepare(
            "UPDATE ipnz_email_verifications SET verified_at = NOW() WHERE token = ?"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();
        
        // Update member status
        $stmt = $this->connection->prepare(
            "UPDATE ipnz_members 
             SET email_verified = 1, status = 'active', updated_at = NOW() 
             WHERE uuid = ?"
        );
        $stmt->bind_param("s", $row['member_uuid']);
        $stmt->execute();
        $stmt->close();
        
        return [
            'success' => true,
            'message' => 'Email verified successfully! Your account is now active.',
            'member_uuid' => $row['member_uuid']
        ];
    }
    
    /**
     * Resend verification email
     */
    public function resendVerification($email) {
        // Find pending member with this email
        $stmt = $this->connection->prepare(
            "SELECT uuid, name FROM ipnz_members 
             WHERE email = ? AND email_verified = 0 AND deleted_at IS NULL 
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'No pending verification found for this email'];
        }
        
        $member = $result->fetch_assoc();
        $stmt->close();
        
        // Create new token
        $token = $this->createVerificationToken($member['uuid'], $email, $_SERVER['REMOTE_ADDR'] ?? null);
        
        // Send email
        return $this->sendVerificationEmail($member['uuid'], $email, $member['name'], $token);
    }
}
?>
