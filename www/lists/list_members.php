<?php
/**
 * Admin Member List - Requires Authentication
 */

// Admin credentials (username / bcrypt hashed password)
define('ADMIN_USERNAME', 'ipnz-admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$jgAsPEFSyVw/i/87Mi0q1uQnsra5fxd/JgbDRiYMSONFtooHgC.l6');

// Handle login
$authenticated = false;
$loginError = '';

session_start();

// Check if already authenticated
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    $authenticated = true;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_authenticated'] = true;
        $authenticated = true;
    } else {
        $loginError = 'Invalid username or password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// If not authenticated, show login form
if (!$authenticated) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - IPnz.live Member List</title>
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Outfit', sans-serif;
            }
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                max-width: 400px;
                width: 100%;
            }
            .login-container h1 {
                text-align: center;
                margin-bottom: 30px;
                color: #333;
                font-size: 28px;
                font-weight: bold;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #555;
                font-weight: 500;
            }
            .form-group input {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
            }
            .btn-login {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            }
            .error {
                color: #dc3545;
                margin-bottom: 15px;
                padding: 12px;
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                border-radius: 5px;
                text-align: center;
            }
            .login-info {
                text-align: center;
                color: #999;
                font-size: 14px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>IPnz.live</h1>
            <h2 style="text-align: center; color: #999; font-size: 18px; margin-bottom: 30px;">Admin Member List</h2>
            
            <?php if ($loginError): ?>
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn-login">Login</button>
            </form>
            
            <div class="login-info">
                Enter admin credentials to view member list
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If authenticated, show member list
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Member Administration</h2>
    <a href="?logout" class="btn btn-sm btn-outline-danger">Logout</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>UUID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Join Type</th>
                <th>Referral Code</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($results = $sqlSearch->fetch_assoc()) { ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($results['id']); ?></strong></td>
                <td><small class="text-muted"><?php echo htmlspecialchars(substr($results['uuid'], 0, 8)); ?>...</small></td>
                <td><?php echo htmlspecialchars($results['name']); ?></td>
                <td><small><?php echo htmlspecialchars($results['email']); ?></small></td>
                <td><?php echo htmlspecialchars($results['phone'] ?? '-'); ?></td>
                <td><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $results['join_type']))); ?></span></td>
                <td><strong><?php echo htmlspecialchars($results['referral_code']); ?></strong></td>
                <td>
                    <?php 
                    $status = strtolower($results['email_verified'] ?? 'pending');
                    $badgeClass = $status === 'active' ? 'bg-success' : 'bg-warning';
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>">Active</span>
                </td>
                <td><?php echo date('M j, Y', strtotime($results['created_at'])); ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="mt-4 text-muted">
    <p><small>Total members: <strong><?php echo $sqlSearch->num_rows; ?></strong></small></p>
</div>
