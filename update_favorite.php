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
    
    // Check if anime exists in user's list
    $stmt = $conn->prepare("SELECT id, favorite FROM anime_list WHERE user_id = ? AND anime_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $anime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $anime = $result->fetch_assoc();
        $new_favorite = $anime['favorite'] ? 0 : 1;
        
        // Update favorite status
        $stmt = $conn->prepare("UPDATE anime_list SET favorite = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_favorite, $anime['id']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => $new_favorite ? 'Added to favorites' : 'Removed from favorites',
                'is_favorite' => (bool)$new_favorite
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update favorite status']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Anime not found in your list. Add it to your list first.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>