<?php
session_start();
require_once 'config.php'; // Ensure the correct database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>You must be logged in to view your anime list.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM anime_list WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='anime-card' data-status='" . htmlspecialchars($row['status']) . "'>";
        echo "<img src='" . htmlspecialchars($row['image_url']) . "' class='anime-card-img' alt='" . htmlspecialchars($row['title']) . "'>";
        echo "<div class='anime-card-content'>";
        echo "<h3 class='anime-card-title'>" . htmlspecialchars($row['title']) . "</h3>";
        echo "<p class='anime-card-status'>Status: " . ucfirst(str_replace('_', ' ', $row['status'])) . "</p>";
        if ($row['rating']) {
            echo "<p class='anime-card-status'>Rating: " . $row['rating'] . "/10</p>";
        }
        echo "<div class='anime-card-actions'>";
        echo "<button class='btn btn-sm' onclick='showDetails(" . $row['anime_id'] . ")'>";
        echo "<i class='fas fa-info-circle'></i> Details";
        echo "</button>";
        if ($row['favorite']) {
            echo "<button class='btn btn-sm' style='background: var(--secondary);'>";
            echo "<i class='fas fa-heart'></i>";
            echo "</button>";
        }
        echo "</div></div></div>";
    }
} else {
    echo "<p>No anime in your list.</p>";
}
?>
