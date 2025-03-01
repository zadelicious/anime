<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Security validation failed. Please try again.');
        header("Location: register.php");
        exit();
    }
    
    $username = sanitizeInput($_POST["username"]);
    $email = sanitizeInput($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        setFlashMessage('error', 'All fields are required');
        header("Location: register.php");
        exit();
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Please enter a valid email address');
        header("Location: register.php");
        exit();
    }
    
    // Check password length
    if (strlen($password) < 6) {
        setFlashMessage('error', 'Password must be at least 6 characters long');
        header("Location: register.php");
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        setFlashMessage('error', 'Passwords do not match');
        header("Location: register.php");
        exit();
    }
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        setFlashMessage('error', 'Username already exists');
        header("Location: register.php");
        exit();
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        setFlashMessage('error', 'Email already exists');
        header("Location: register.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Registration successful! Please login.');
        header("Location: index.php");
        exit();
    } else {
        setFlashMessage('error', 'Registration failed. Please try again later.');
        header("Location: register.php");
        exit();
    }
}
?>