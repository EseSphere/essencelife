<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'dbconnections.php';

$response = ['success' => false, 'message' => 'Failed to save answers.'];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$answers = $_POST['answers'] ?? [];
$sessionId = session_id();

if (!empty($answers)) {
    foreach ($answers as $questionId => $answerArr) {
        $questionId = (string)$questionId;
        $delStmt = $conn->prepare("DELETE FROM user_answers WHERE user_id=? AND question_id=?");
        $delStmt->bind_param("ss", $userId, $questionId);
        $delStmt->execute();
        $delStmt->close();

        $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, answer, session_id) VALUES (?, ?, ?, ?)");
        foreach ($answerArr as $ans) {
            $ans = htmlspecialchars($ans, ENT_QUOTES);
            $stmt->bind_param("ssss", $userId, $questionId, $ans, $sessionId);
            $stmt->execute();
        }
        $stmt->close();
    }

    $response['success'] = true;
    $response['message'] = 'Your answers have been saved successfully!';
}

$conn->close();
echo json_encode($response);
