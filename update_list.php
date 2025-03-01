<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $anime_id = (int)$_POST['anime_id'];
    $status = sanitizeInput($_POST['status']);
    
    // Validate status
    $valid_statuses = ['watching', 'completed', 'plan_to_watch', 'dropped'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    // Check if anime already exists in user's list
    $stmt = $conn->prepare("SELECT id FROM anime_list WHERE user_id = ? AND anime_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $anime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing entry
        $stmt = $conn->prepare("UPDATE anime_list SET status = ? WHERE user_id = ? AND anime_id = ?");
        $stmt->bind_param("sii", $status, $_SESSION['user_id'], $anime_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Anime status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update anime status']);
        }
    } else {
        // Get anime details from API
        $response = fetchAnimeData('anime/' . $anime_id);
        
        if (!isset($response['data'])) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch anime details']);
            exit();
        }
        
        $title = $response['data']['title'];
        $image_url = $response['data']['images']['jpg']['image_url'];
        
        // Add new entry
        $stmt = $conn->prepare("INSERT INTO anime_list (user_id, anime_id, title, status, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $_SESSION['user_id'], $anime_id, $title, $status, $image_url);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Anime added to your list']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add anime to list']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>