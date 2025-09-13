<?php
session_start();
include 'dbconnections.php';
header('Content-Type: application/json'); // Return JSON responses

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $playlist_name = $conn->real_escape_string($_POST['playlist_name']);
    if (!empty($playlist_name)) {
        $conn->query("INSERT INTO playlists (user_id, name) VALUES ('$user_id', '$playlist_name')");
        $playlist_id = $conn->insert_id;
        echo json_encode([
            'status' => 'success',
            'message' => "Playlist '$playlist_name' created!",
            'playlist' => [
                'id' => $playlist_id,
                'name' => $playlist_name,
                'audio_count' => 0
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Playlist name cannot be empty']);
    }
    exit;
}

if ($action === 'delete') {
    $playlist_id = intval($_POST['playlist_id']);
    $conn->query("DELETE FROM playlist_audios WHERE playlist_id=$playlist_id");
    $conn->query("DELETE FROM playlists WHERE id=$playlist_id AND user_id='$user_id'");
    echo json_encode(['status' => 'success', 'message' => 'Playlist deleted', 'playlist_id' => $playlist_id]);
    exit;
}

if ($action === 'list') {
    $playlists = [];
    $query = "SELECT p.*, 
                     (SELECT COUNT(*) FROM playlist_audios pa WHERE pa.playlist_id=p.id) AS audio_count
              FROM playlists p
              WHERE p.user_id='$user_id'
              ORDER BY p.id DESC";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $playlists[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'audio_count' => $row['audio_count']
        ];
    }
    echo json_encode(['status' => 'success', 'playlists' => $playlists]);
    exit;
}

if ($action === 'add_audio') {
    $playlist_id = intval($_POST['playlist_id']);
    $audio_id = intval($_POST['audio_id']);
    $exists = $conn->query("SELECT * FROM playlist_audios WHERE playlist_id=$playlist_id AND audio_id=$audio_id")->num_rows;
    if ($exists) {
        echo json_encode(['status' => 'error', 'message' => 'Audio already in playlist']);
    } else {
        $conn->query("INSERT INTO playlist_audios (playlist_id, audio_id) VALUES ($playlist_id, $audio_id)");
        echo json_encode(['status' => 'success']);
    }
    exit;
}

if ($action === 'remove_audio') {
    $playlist_id = intval($_POST['playlist_id']);
    $audio_id = intval($_POST['audio_id']);
    $conn->query("DELETE FROM playlist_audios WHERE playlist_id=$playlist_id AND audio_id=$audio_id");
    echo json_encode(['status' => 'success']);
    exit;
}

// FIXED GET AUDIOS IN PLAYLIST
if ($action === 'get_audios') {
    $playlist_id = intval($_POST['playlist_id']);
    $audios = [];
    $result = $conn->query("
        SELECT c.id, c.content_name AS title, c.content_type AS type, c.image_url 
        FROM contents c
        JOIN playlist_audios pa ON pa.audio_id = c.id
        WHERE pa.playlist_id = $playlist_id
        ORDER BY c.id DESC
    ");
    while ($row = $result->fetch_assoc()) {
        $audios[] = $row;
    }
    echo json_encode(['status' => 'success', 'audios' => $audios]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
