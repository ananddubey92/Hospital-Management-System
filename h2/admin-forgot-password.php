<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';
$step = 1;

if ($_POST) {
    if (isset($_POST['email'])) {
        $email = sanitize($_POST['email']);
        
        if (empty($email)) {
            $error = 'Please enter your email address';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } else {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $reset_token = bin2hex(random_bytes(32));
                $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Add reset columns if they don't exist
                try {
                    $pdo->exec("ALTER TABLE admin ADD COLUMN reset_token VARCHAR(64)");
                    $pdo->exec("ALTER TABLE admin ADD COLUMN reset_expires DATETIME");
                } catch (PDOException $e) {
                    // Columns already exist
                }
                
                $stmt = $pdo->prepare("UPDATE admin SET reset_token = ?, reset_expires = ? WHERE email = ?");
                $stmt->execute([$reset_token, $reset_expires, $email]);
                
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_token'] = $reset_token;
                $step = 2;
                $success = 'Security verification required. Please enter the verification code: ' . substr($reset_token, 0, 8);
            } else {
                $error = 'No admin account found with this email address';
            }
        }
    } elseif (isset($_POST['verification_code']) && isset($_POST['new_password'])) {
        $verification_code = sanitize($_POST['verification_code']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($verification_code) || empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all fields';
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match';
            $step = 2;
        } elseif (!isset($_SESSION['reset_token']) || $verification_code !== substr($_SESSION['reset_token'], 0, 8)) {
            $error = 'Invalid verification code';
            $step = 2;
        } else {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? AND reset_token = ? AND reset_expires > NOW()");
            $stmt->execute([$_SESSION['reset_email'], $_SESSION['reset_token']]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
                $stmt->execute([$hashed_password, $_SESSION['reset_email']]);
                
                unset($_SESSION['reset_email'], $_SESSION['reset_token']);
                $step = 3;
                $success = 'Password reset successfully! You can now login with your new password.';
            } else {
                $error = 'Invalid or expired verification code';
                $step = 2;
            }
        }
    }
}

if (isset($_SESSION['reset_email']) && !isset($_POST['email'])) {
    $step = 2;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="auth-card glass">
                        <h2><i class="fas fa-key"></i> Admin Password Reset</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($step === 1): ?>
                            <p class="text-center text-white mb-4">
                                Enter your admin email address to reset your password.
                            </p>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Admin Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-paper-plane"></i> Send Reset Code
                                </button>
                            </form>
                        
                        <?php elseif ($step === 2): ?>
                            <p class="text-center text-white mb-4">
                                Enter the verification code and your new password.
                            </p>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="verification_code" class="form-label">Verification Code</label>
                                    <input type="text" class="form-control" id="verification_code" name="verification_code" required>
                                    <small class="text-white-50">Enter the 8-character code shown above</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100 mb-3">
                                    <i class="fas fa-check"></i> Reset Password
                                </button>
                            </form>
                        
                        <?php elseif ($step === 3): ?>
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                                <h3 class="text-white mb-3">Password Reset Complete</h3>
                                <p class="text-white mb-4">Your password has been successfully reset.</p>
                                <a href="login.php?type=admin" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login Now
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="login.php?type=admin" class="text-info">
                                <i class="fas fa-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>