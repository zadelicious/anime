<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Fetch trending anime from Jikan API
$response = fetchAnimeData('top/anime?filter=bypopularity&limit=12');

if (isset($response['data'])) {
    $anime = [];
    
    foreach ($response['data'] as $item) {
        $anime[] = [
            'id' => $item['mal_id'],
            'title' => $item['title'],
            'image' => $item['images']['jpg']['image_url'],
            'rating' => $item['score'] ?? 'N/A',
            'episodes' => $item['episodes'] ?? 'Unknown',
            'year' => date('Y', strtotime($item['aired']['from'] ?? 'now'))
        ];
    }
    
    echo json_encode(['success' => true, 'anime' => $anime]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch data']);
}
?>