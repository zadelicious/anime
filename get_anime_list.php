<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM anime_list WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$anime_list = [];
while ($row = $result->fetch_assoc()) {
    $anime_list[] = $row;
}

echo json_encode(['success' => true, 'anime_list' => $anime_list]);
?>