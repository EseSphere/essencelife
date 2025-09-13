<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('dbconnections.php');

if (!isset($_SESSION['user_id'])) {
    die('User not logged in.');
}

$user_id = $_SESSION['user_id'];

// Combined check using a single query
$stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM user_answers WHERE user_id = ?) AS answered_count,
        (SELECT COUNT(*) FROM user_payments WHERE user_id = ? AND status = 'completed') AS payment_count
");
$stmt->bind_param('ss', $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($answered_count, $payment_count);
$stmt->fetch();
$stmt->close();

if ($answered_count > 0 && $payment_count > 0) {
    // Both conditions met
    echo "User has answered questions and completed payment. Proceeding...";
    // Place your commands here
} else {
    echo "User has not completed all requirements.";
}
