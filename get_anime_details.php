<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid anime ID']);
    exit();
}

$anime_id = (int)$_GET['id'];

// Fetch anime details from Jikan API
$response = fetchAnimeData('anime/' . $anime_id);

if (isset($response['data'])) {
    $anime = [
        'id' => $response['data']['mal_id'],
        'title' => $response['data']['title'],
        'image' => $response['data']['images']['jpg']['large_image_url'],
        'synopsis' => $response['data']['synopsis'] ?? 'No synopsis available.',
        'rating' => $response['data']['score'] ?? 'N/A',
        'episodes' => $response['data']['episodes'] ?? 'Unknown',
        'year' => date('Y', strtotime($response['data']['aired']['from'] ?? 'now')),
        'genres' => array_map(function($genre) {
            return $genre['name'];
        }, $response['data']['genres'] ?? [])
    ];
    
    echo json_encode(['success' => true, 'anime' => $anime]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch data']);
}
?>