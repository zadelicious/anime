<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Get flash message if exists
$flashMessage = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="script.js"></script>
</head>
<body>
    <div class="container login-box">
        <h1 class="anime-title">Reset Password</h1>
        
        <?php if ($flashMessage): ?>
            <div class="flash-message flash-<?php echo $flashMessage['type']; ?>">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>
        
        <form action="reset_request.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="input-group">
                <input type="email" name="email" required>
                <label><i class="fas fa-envelope"></i> Email</label>
            </div>
            
            <button type="submit">Request Reset <i class="fas fa-paper-plane"></i></button>
            
            <div class="form-footer">
                <p>Remember your password? <a href="index.php">Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>