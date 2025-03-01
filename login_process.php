<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Security validation failed. Please try again.');
        header("Location: index.php");
        exit();
    }
    
    $username = sanitizeInput($_POST["username"]);
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]) ? true : false;

    // Validate input
    if (empty($username) || empty($password)) {
        setFlashMessage('error', 'Username and password are required');
        header("Location: index.php");
        exit();
    }

    // Get user from database
    $stmt = $conn->prepare("SELECT id, username, password, theme_preference FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['theme'] = $user['theme_preference'];
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expire = time() + (30 * 24 * 60 * 60); // 30 days
                
                setcookie('remember_token', $token, $expire, '/', '', true, true);
                
                // Update remember token in database
                $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->bind_param("si", $token, $user['id']);
                $stmt->execute();
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            setFlashMessage('error', 'Invalid password');
        }
    } else {
        setFlashMessage('error', 'User not found');
    }
    
    header("Location: index.php");
    exit();
}
?>