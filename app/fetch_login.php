<?php
ob_start();
session_start();
require_once('dbconnections.php');

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => 'Login failed.'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $email = trim($_POST['logemail'] ?? '');
    $password = $_POST['logpass'] ?? '';

    if ($email === '' || $password === '') {
        throw new Exception('Please enter both email and password.');
    }

    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection failed.');
    }

    // Fetch user
    $stmt = $conn->prepare("SELECT user_id, name, password FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) throw new Exception('Database query failed.');

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception('Email not found.');
    }

    $stmt->bind_result($user_id, $name, $hashedPassword);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($password, $hashedPassword)) {
        throw new Exception('Invalid password.');
    }

    // Successful login
    session_regenerate_id(true);

    // Persistent session for 10 years
    $cookieLifetime = 10 * 365 * 24 * 60 * 60;
    setcookie(session_name(), session_id(), time() + $cookieLifetime, "/");

    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['logged_in'] = true;

    // Check payment status
    $stmtPay = $conn->prepare("SELECT COUNT(*) FROM user_payments WHERE user_id = ? AND status = 'Paid'");
    $stmtPay->bind_param('s', $user_id);
    $stmtPay->execute();
    $stmtPay->bind_result($payment_count);
    $stmtPay->fetch();
    $stmtPay->close();

    $redirectUrl = $payment_count > 0 ? 'home.php' : 'payment.php';

    $response = [
        'success' => true,
        'message' => 'Login successful! Redirecting...',
        'redirect' => $redirectUrl
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
ob_end_flush();
$conn->close();
exit;
