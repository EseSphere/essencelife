<?php
include 'dbconnections.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No audio ID provided']);
    exit;
}

$currentId = intval($_GET['id']);
$sql = "SELECT * FROM contents WHERE id = $currentId AND status = 'active'";
$result = mysqli_query($conn, $sql);
$current = mysqli_fetch_assoc($result);

if (!$current) {
    echo json_encode(['error' => 'Audio not found']);
    exit;
}

// Fetch similar audios
$category = mysqli_real_escape_string($conn, $current['content_type']);
$sqlUpNext = "SELECT * FROM contents WHERE content_type = '$category' AND id != $currentId AND status = 'active' LIMIT 10";
$upNextResult = mysqli_query($conn, $sqlUpNext);
$upNext = [];
while ($row = mysqli_fetch_assoc($upNextResult)) {
    $upNext[] = $row;
}

echo json_encode([
    'current' => $current,
    'upNext' => $upNext
]);
