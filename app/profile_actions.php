<?php
session_start();
include 'dbconnections.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name = trim($_POST['playlist_name'] ?? '');
    if ($name === '') {
        echo json_encode(['status' => 'error', 'message' => 'Playlist name cannot be empty']);
        exit;
    }

    // Prevent duplicate playlist names for the same user
    $check = $conn->prepare("SELECT id FROM playlists WHERE user_id = ? AND name = ?");
    $check->bind_param("ss", $user_id, $name);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Playlist already exists']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
    $stmt->bind_param("ss", $user_id, $name);
    if ($stmt->execute()) {
        // Get the inserted playlist ID
        $playlist_id = $stmt->insert_id;

        // Return the new playlist object with audio_count = 0
        $newPlaylist = [
            'id' => $playlist_id,
            'name' => $name,
            'audio_count' => 0
        ];

        echo json_encode([
            'status' => 'success',
            'message' => 'Playlist created successfully',
            'playlist' => $newPlaylist
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create playlist']);
    }
    exit;
}

if ($action === 'list') {
    $playlists = [];
    $sql = "SELECT p.id, p.name,
            (SELECT COUNT(*) FROM playlist_audios pa WHERE pa.playlist_id = p.id) as audio_count
            FROM playlists p
            WHERE p.user_id = ?
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $playlists[] = $row;
    }
    echo json_encode(['status' => 'success', 'playlists' => $playlists]);
    exit;
}

if ($action === 'delete') {
    $playlist_id = intval($_POST['playlist_id'] ?? 0);
    if ($playlist_id > 0) {
        // Delete playlist audios first
        $stmt = $conn->prepare("DELETE FROM playlist_audios WHERE playlist_id = ?");
        $stmt->bind_param("i", $playlist_id);
        $stmt->execute();

        // Then delete playlist
        $stmt = $conn->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
        $stmt->bind_param("is", $playlist_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete playlist']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid playlist']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
