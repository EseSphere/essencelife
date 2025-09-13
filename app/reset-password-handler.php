<?php
require_once('dbconnections.php'); // Ensure $conn is available

$response = ['status' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['logemail']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Generate secure reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // token valid 1 hour

        // Store token and expiry in database (create columns if needed: reset_token, reset_expires)
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE user_id = ?");
        $updateStmt->bind_param("sss", $token, $expiry, $user_id);
        $updateStmt->execute();
        $updateStmt->close();

        // Create reset link
        $resetLink = "https://essencelife.com/new-password.php?token=$token";

        // Send email
        $subject = "Reset your Essence Life password";
        $message = "
            <html>
            <head>
              <title>Reset your password</title>
            </head>
            <body>
              <p>Hi,</p>
              <p>We received a request to reset your password for your Essence Life account.</p>
              <p>Click the link below to reset your password:</p>
              <p><a href='$resetLink'>$resetLink</a></p>
              <p>This link will expire in 1 hour.</p>
              <p>If you did not request a password reset, you can ignore this email.</p>
              <p>Essence Life Team</p>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Essence Life <no-reply@essencelife.com>" . "\r\n";

        if (mail($email, $subject, $message, $headers)) {
            $response['status'] = 'success';
            $response['message'] = 'Reset link has been sent to your email.';
        } else {
            $response['status'] = 'danger';
            $response['message'] = 'Failed to send email. Please try again.';
        }
    } else {
        $response['status'] = 'danger';
        $response['message'] = 'Email not found.';
    }

    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
