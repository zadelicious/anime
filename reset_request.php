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
    
    $email = sanitizeInput($_POST["email"]);

    // Validate input
    if (empty($email)) {
        setFlashMessage('error', 'Email is required');
        header("Location: forgot_password.php");
        exit();
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->bind_param("ssi", $token, $expires, $user['id']);
        $stmt->execute();
        
        // In a real application, you would send an email here
        // For this demo, we'll just redirect to the reset page with the token
        
        setFlashMessage('success', 'Password reset link has been sent to your email.');
        
        // Normally you'd send an email, but for demo purposes we'll redirect
        header("Location: reset_password.php?token=" . $token);
        exit();
    } else {
        // Don't reveal if email exists for security
        setFlashMessage('success', 'If your email exists in our system, you will receive a reset link.');
        header("Location: forgot_password.php");
        exit();
    }
}
?>