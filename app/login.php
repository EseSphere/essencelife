<?php
// login.php
require_once('dbconnections.php');
require_once('checkings.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Suppress PHP warnings for JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Default JSON response
$response = ['success' => false, 'message' => 'Login failed.'];

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get input
$email = trim($_POST['logemail'] ?? '');
$password = $_POST['logpass'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
    exit;
}

// Fetch user by email
$stmt = $conn->prepare("SELECT id, user_id, name, password FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database query failed.']);
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
    exit;
}

$stmt->bind_result($id, $user_id, $name, $hashedPassword);
$stmt->fetch();
$stmt->close();

// Verify password
if (!password_verify($password, $hashedPassword) && $password !== $hashedPassword) {
    echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    exit;
}

// Successful login: regenerate session
session_regenerate_id(true);
$_SESSION['user_id'] = $user_id;
$_SESSION['name'] = $name;
$_SESSION['logged_in'] = true;

// Combined check for answers and payment
$stmtCheck = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM user_answers WHERE user_id = ?) AS answered_count,
        (SELECT COUNT(*) FROM user_payments WHERE user_id = ? AND status = 'completed') AS payment_count
");
$stmtCheck->bind_param('ss', $user_id, $user_id);
$stmtCheck->execute();
$stmtCheck->bind_result($answered_count, $payment_count);
$stmtCheck->fetch();
$stmtCheck->close();

// Decide redirect
if ($payment_count == 0) {
    $redirectUrl = 'payment.php';
} elseif ($answered_count == 0) {
    $redirectUrl = 'questionnaires.php';
} else {
    $redirectUrl = 'home.php';
}

// Send JSON response
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Login successful! Redirecting...',
    'redirect' => $redirectUrl
]);

$conn->close();
exit;
