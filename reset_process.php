<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Security validation failed. Please try again.');
        header("Location: forgot_password.php");
        exit();
    }
    
    $token = sanitizeInput($_POST["token"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate input
    if (empty($password) || empty($confirm_password)) {
        setFlashMessage('error', 'All fields are required');
        header("Location: reset_password.php?token=" . $token);
        exit();
    }
    
    // Check password length
    if (strlen($password) < 6) {
        setFlashMessage('error', 'Password must be at least 6 characters long');
        header("Location: reset_password.php?token=" . $token);
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        setFlashMessage('error', 'Passwords do not match');
        header("Location: reset_password.php?token=" . $token);
        exit();
    }
    
    // Verify token is valid and not expired
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Password has been reset successfully. You can now login with your new password.');
            header("Location: index.php");
            exit();
        } else {
            setFlashMessage('error', 'Password reset failed. Please try again later.');
            header("Location: reset_password.php?token=" . $token);
            exit();
        }
    } else {
        setFlashMessage('error', 'Invalid or expired token. Please request a new password reset link.');
        header("Location: forgot_password.php");
        exit();
    }
}
?>