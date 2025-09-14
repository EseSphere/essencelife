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

    // Generate a unique cookie ID
    $cookie_id = bin2hex(random_bytes(32));

    // Set permanent cookie (10 years lifetime)
    setcookie("device_cookie", $cookie_id, time() + (10 * 365 * 24 * 60 * 60), "/", "", false, true);

    $stmt = $conn->prepare("INSERT INTO users (user_id, name, email, phone, password, cookie_id, updated_at) 
                            VALUES (?, ?, ?, '', ?, ?, ?)");
    $stmt->bind_param("ssssss", $user_id, $name, $email, $hashedPassword, $cookie_id, $updated_at);

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
