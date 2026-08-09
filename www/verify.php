<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - IPnz.live</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/ipnz-live.css" rel="stylesheet">
</head>
<body>
    <main style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #F8CB2E 0%, #EE5007 100%);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-12">
                    <div class="card" style="border-radius: 20px; padding: 40px; text-align: center;">
                        <?php
                        require('datacenter/database.php');
                        require('datacenter/email.php');
                        
                        $emailService = new EmailService($connection);
                        
                        if (isset($_GET['token'])) {
                            $token = $_GET['token'];
                            $result = $emailService->verifyToken($token);
                            
                            if ($result['success']) {
                                echo '<div style="color: #4CAF50; font-size: 3rem; margin-bottom: 20px;">✓</div>';
                                echo '<h2 style="color: #4CAF50;">Email Verified!</h2>';
                                echo '<p>' . htmlspecialchars($result['message']) . '</p>';
                                echo '<p><a href="/" class="btn btn-primary btn-lg mt-3">Go to Homepage</a></p>';
                            } else {
                                echo '<div style="color: #ea0309; font-size: 3rem; margin-bottom: 20px;">✗</div>';
                                echo '<h2 style="color: #ea0309;">Verification Failed</h2>';
                                echo '<p>' . htmlspecialchars($result['message']) . '</p>';
                                if (strpos($result['message'], 'expired') !== false) {
                                    echo '<p><a href="/resend" class="btn btn-warning btn-lg mt-3">Resend Verification Email</a></p>';
                                }
                                echo '<p><a href="/" class="btn btn-secondary mt-2">Go to Homepage</a></p>';
                            }
                        } else {
                            echo '<h2>Invalid Verification Link</h2>';
                            echo '<p>No token provided.</p>';
                            echo '<p><a href="/" class="btn btn-secondary mt-3">Go to Homepage</a></p>';
                        }
                        ?>
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
