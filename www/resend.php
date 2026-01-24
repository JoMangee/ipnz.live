<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resend Verification - IPnz.live</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-festava-live.css" rel="stylesheet">
</head>
<body>
    <main style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #F8CB2E 0%, #EE5007 100%);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-12">
                    <div class="card" style="border-radius: 20px; padding: 40px;">
                        <h2 style="text-align: center; margin-bottom: 30px;">Resend Verification Email</h2>
                        
                        <?php
                        require('datacenter/database.php');
                        require('datacenter/email.php');
                        
                        $emailService = new EmailService($connection);
                        $message = '';
                        $messageType = '';
                        
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
                            $email = trim($_POST['email']);
                            
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $result = $emailService->resendVerification($email);
                                
                                if ($result['success']) {
                                    $message = 'Verification email sent! Please check your inbox.';
                                    $messageType = 'success';
                                } else {
                                    $message = $result['message'];
                                    $messageType = 'danger';
                                }
                            } else {
                                $message = 'Please enter a valid email address.';
                                $messageType = 'danger';
                            }
                        }
                        
                        if ($message) {
                            echo '<div class="alert alert-' . $messageType . '" role="alert">' . htmlspecialchars($message) . '</div>';
                        }
                        ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" 
                                       placeholder="your@email.com" required>
                                <div class="form-text">Enter the email address you used to sign up.</div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Resend Verification Email</button>
                        </form>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <a href="/" class="btn btn-link">Back to Homepage</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    // Version marker: short digest over key files for deployment verification
    $versionMeta = include __DIR__ . '/version_meta.php';
    $vmFiles = $versionMeta['files'] ?? [];
    $vmParts = [];
    foreach ($vmFiles as $vmRel) {
        $vmPath = __DIR__ . '/' . $vmRel;
        if (is_file($vmPath)) {
            $vmParts[] = hash_file('sha256', $vmPath);
        }
    }
    $vmDigest = substr(hash('sha256', implode('', $vmParts)), 0, 12);
    echo "<!-- version=" . ($versionMeta['version'] ?? 'unknown') . " commit=" . ($versionMeta['commit'] ?? 'unknown') . " digest=" . $vmDigest . " -->";
    ?>
</body>
</html>
