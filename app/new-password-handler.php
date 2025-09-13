<?php
require_once('dbconnections.php');

$response = ['status' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        $response['status'] = 'danger';
        $response['message'] = 'Invalid request.';
    } else {
        // Find user by token and check expiry
        $stmt = $conn->prepare("SELECT user_id, reset_expires FROM users WHERE reset_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $expires);
        $stmt->fetch();

        if ($stmt->num_rows === 0) {
            $response['status'] = 'danger';
            $response['message'] = 'Invalid or expired token.';
        } elseif (strtotime($expires) < time()) {
            $response['status'] = 'danger';
            $response['message'] = 'Token has expired.';
        } else {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?");
            $update->bind_param("ss", $hashedPassword, $user_id);

            if ($update->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Password updated successfully!';
            } else {
                $response['status'] = 'danger';
                $response['message'] = 'Failed to update password. Please try again.';
            }

            $update->close();
        }

        $stmt->close();
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
