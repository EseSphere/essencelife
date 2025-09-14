<?php
session_start();
include 'dbconnections.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CHANGE PASSWORD
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }

        if (!password_verify($current, $user['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            exit;
        }

        if ($new !== $confirm) {
            echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
            exit;
        }

        $hashed = password_hash($new, PASSWORD_DEFAULT);
        if ($conn->query("UPDATE users SET password='$hashed' WHERE user_id='$user_id'")) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
        }
        exit;
    }

    // PROFILE UPDATE
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $updateFields = "name='$name', email='$email', phone='$phone'";

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/users/' . uniqid() . '.' . $ext;
        if (!is_dir('uploads/users')) mkdir('uploads/users', 0777, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $filename)) {
            $updateFields .= ", image='" . $conn->real_escape_string($filename) . "'";
        }
    } else {
        // Keep existing image if no new image uploaded
        if (!empty($_POST['existing_image'])) {
            $updateFields .= ", image='" . $conn->real_escape_string($_POST['existing_image']) . "'";
        }
    }

    $sql = "UPDATE users SET $updateFields WHERE user_id='$user_id'";
    if ($conn->query($sql)) {
        $image = isset($filename) ? $filename : $_POST['existing_image'] ?? null;
        echo json_encode(['status' => 'success', 'image' => $image]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
