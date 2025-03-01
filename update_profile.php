<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Security validation failed. Please try again.');
        header("Location: dashboard.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $username = sanitizeInput($_POST["username"]);
    $email = sanitizeInput($_POST["email"]);
    $current_password = isset($_POST["current_password"]) ? $_POST["current_password"] : '';
    $new_password = isset($_POST["new_password"]) ? $_POST["new_password"] : '';
    $confirm_password = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : '';
    
    // Get current user data
    $stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Check if username is taken by another user
    if ($username !== $user['username']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            setFlashMessage('error', 'Username already exists');
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Check if email is taken by another user
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            setFlashMessage('error', 'Email already exists');
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Handle password change if requested
    $update_password = false;
    if (!empty($new_password)) {
        // Verify current password
        if (empty($current_password) || !password_verify($current_password, $user['password'])) {
            setFlashMessage('error', 'Current password is incorrect');
            header("Location: dashboard.php");
            exit();
        }
        
        // Check password length
        if (strlen($new_password) < 6) {
            setFlashMessage('error', 'New password must be at least 6 characters long');
            header("Location: dashboard.php");
            exit();
        }
        
        // Check if passwords match
        if ($new_password !== $confirm_password) {
            setFlashMessage('error', 'New passwords do not match');
            header("Location: dashboard.php");
            exit();
        }
        
        $update_password = true;
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    }
    
    // Handle profile image upload
    $update_image = false;
    $new_image_name = $user['profile_pic'];
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file_type = $_FILES['profile_image']['type'];
        $file_size = $_FILES['profile_image']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            setFlashMessage('error', 'Only JPG, PNG and GIF images are allowed');
            header("Location: dashboard.php");
            exit();
        }
        
        if ($file_size > $max_size) {
            setFlashMessage('error', 'Image size should be less than 2MB');
            header("Location: dashboard.php");
            exit();
        }
        
        $upload_dir = 'uploads/';
        $file_name = time() . '_' . $_FILES['profile_image']['name'];
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $update_image = true;
            $new_image_name = $file_name;
        } else {
            setFlashMessage('error', 'Failed to upload image');
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Update user profile in database
    if ($update_password && $update_image) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, profile_pic = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $hashed_password, $new_image_name, $user_id);
    } elseif ($update_password) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
    } elseif ($update_image) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_pic = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $new_image_name, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }
    
    if ($stmt->execute()) {
        // Update session username
        $_SESSION['username'] = $username;
        
        setFlashMessage('success', 'Profile updated successfully');
    } else {
        setFlashMessage('error', 'Failed to update profile');
    }
    
    header("Location: dashboard.php");
    exit();
}
?>