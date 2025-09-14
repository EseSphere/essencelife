<?php
include 'dbconnections.php'; // your DB connection
$id = intval($_GET['id']); // current song id

// Get current song
$song = $conn->query("SELECT * FROM audios WHERE id='$id'")->fetch_assoc();
if (!$song) {
    echo json_encode(['error' => 'Song not found']);
    exit;
}

// Fetch related audios (same category, exclude current song)
$related = [];
$res = $conn->query("SELECT * FROM audios WHERE category='{$song['category']}' AND id != '$id' LIMIT 10");
while ($row = $res->fetch_assoc()) {
    $related[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'url' => $row['audio_url'],
        'image' => $row['image'] ?: 'default.png',
        'category' => $row['category'],
        'description' => $row['description'] ?? ''
    ];
}

echo json_encode([
    'song' => [
        'id' => $song['id'],
        'title' => $song['title'],
        'audio' => $song['audio_url'],
        'image' => $song['image'] ?: 'default.png',
        'category' => $song['category'],
        'description' => $song['description'] ?? ''
    ],
    'related' => $related
]);
