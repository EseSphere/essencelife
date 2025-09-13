<?php
require_once('dbconnections.php'); // Ensure $conn is available

$response = ['status' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['logname']);
    $email = trim($_POST['logemail']);
    $password = $_POST['logpass'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $user_id = 'user_' . time();
    $updated_at = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO users (user_id, name, email, phone, password, updated_at) VALUES (?, ?, ?, '', ?, ?)");
    $stmt->bind_param("sssss", $user_id, $name, $email, $hashedPassword, $updated_at);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Sign up successful!';
    } else {
        $response['status'] = 'danger';
        $response['message'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
