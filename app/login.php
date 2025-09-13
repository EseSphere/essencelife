<?php
ob_start(); // Start output buffering
session_start();
require_once('dbconnections.php');

// Disable PHP errors output
ini_set('display_errors', 0);
error_reporting(0);

// Force JSON response
header('Content-Type: application/json; charset=utf-8');

// Default response
$response = ['success' => false, 'message' => 'Login failed.'];

try {
    // Only accept POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Get input
    $email = trim($_POST['logemail'] ?? '');
    $password = $_POST['logpass'] ?? '';

    if ($email === '' || $password === '') {
        throw new Exception('Please enter both email and password.');
    }

    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection failed.');
    }

    // Fetch user
    $stmt = $conn->prepare("SELECT id, user_id, name, password FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) throw new Exception('Database query failed.');

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception('Email not found.');
    }

    $stmt->bind_result($id, $user_id, $name, $hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verify password
    if (!password_verify($password, $hashedPassword)) {
        throw new Exception('Invalid password.');
    }

    // Successful login
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['logged_in'] = true;

    // Check user_answers
    $stmtAns = $conn->prepare("SELECT COUNT(*) FROM user_answers WHERE user_id = ?");
    $stmtAns->bind_param('s', $user_id);
    $stmtAns->execute();
    $stmtAns->bind_result($answered_count);
    $stmtAns->fetch();
    $stmtAns->close();

    if ($answered_count == 0) {
        // No answers → go to questionnaire
        $redirectUrl = 'questionnaire.php';
    } else {
        // Answers exist → check payments
        $stmtPay = $conn->prepare("SELECT COUNT(*) FROM user_payments WHERE user_id = ? AND status = 'Paid'");
        $stmtPay->bind_param('s', $user_id);
        $stmtPay->execute();
        $stmtPay->bind_result($payment_count);
        $stmtPay->fetch();
        $stmtPay->close();

        if ($payment_count == 0) {
            $redirectUrl = 'payment.php';
        } else {
            $redirectUrl = 'home.php';
        }
    }

    $response = [
        'success' => true,
        'message' => 'Login successful! Redirecting...',
        'redirect' => $redirectUrl
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON
echo json_encode($response);
ob_end_flush(); // Ensure no other output
$conn->close();
exit;
