<?php
session_start();
require_once 'config.php';

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    // Remove token from database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
    }
    
    // Delete cookie
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>