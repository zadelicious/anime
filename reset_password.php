<?php
session_start();
require_once 'config.php';

// Verify token
$token = isset($_GET['token']) ? $_GET['token'] : '';
$validToken = false;

if (!empty($token)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $validToken = true;
    }
}

// Get flash message if exists
$flashMessage = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="script.js"></script>
</head>
<body>
    <div class="container login-box">
        <h1 class="anime-title">New Password</h1>
        
        <?php if ($flashMessage): ?>
            <div class="flash-message flash-<?php echo $flashMessage['type']; ?>">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($validToken): ?>
            <form action="reset_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label><i class="fas fa-lock"></i> New Password</label>
                    <span class="toggle-password"><i class="far fa-eye"></i></span>
                </div>
                
                <div class="input-group">
                    <input type="password" name="confirm_password" required>
                    <label><i class="fas fa-lock"></i> Confirm Password</label>
                    <span class="toggle-password"><i class="far fa-eye"></i></span>
                </div>
                
                <button type="submit">Reset Password <i class="fas fa-key"></i></button>
                
                <div class="form-footer">
                    <p>Remember your password? <a href="index.php">Login</a></p>
                </div>
            </form>
        <?php else: ?>
            <div class="flash-message flash-error">
                Invalid or expired token. Please request a new password reset link.
            </div>
            <div class="form-footer">
                <a href="forgot_password.php" class="btn">Request New Link</a>
                <p>Remember your password? <a href="index.php">Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>