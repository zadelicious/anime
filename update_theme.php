<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['user_id'])) {
    $theme = sanitizeInput($_POST['theme']);
    
    if ($theme === 'dark' || $theme === 'light') {
        $stmt = $conn->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
        $stmt->bind_param("si", $theme, $_SESSION['user_id']);
        $stmt->execute();
        
        $_SESSION['theme'] = $theme;
        
        echo json_encode(['success' => true]);
        exit();
    }
}

echo json_encode(['success' => false]);
?>