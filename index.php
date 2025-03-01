<?php 
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
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
    <title>Anime Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script defer src="script.js"></script>
</head>
<body>
    <div class="container login-box">
        <h1 class="anime-title">Anime Login</h1>
        
        <?php if ($flashMessage): ?>
            <div class="flash-message flash-<?php echo $flashMessage['type']; ?>">
                <?php echo $flashMessage['message']; ?>
            </div>
        <?php endif; ?>
        
        <form action="login_process.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="input-group">
                <input type="text" name="username" required>
                <label><i class="fas fa-user"></i> Username</label>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" required>
                <label><i class="fas fa-lock"></i> Password</label>
                <span class="toggle-password"><i class="far fa-eye"></i></span>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit">Login <i class="fas fa-arrow-right"></i></button>
            
            <div class="form-footer">
                <a href="forgot_password.php">Forgot Password?</a>
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </form>
    </div>
</body>
</html>