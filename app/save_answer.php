<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'dbconnections.php';

$recentCookie = $_COOKIE['recent_cookie'] ?? null;

$response = ['success' => false, 'message' => 'Failed to save answers.'];

// Use session ID instead of user_id
$sessionId = session_id();
$answers = $_POST['answers'] ?? [];

if (!empty($answers)) {
    foreach ($answers as $questionId => $answerArr) {
        $questionId = (string)$questionId;

        // Delete previous answers for this session and question
        $delStmt = $conn->prepare("DELETE FROM user_answers WHERE session_id=? AND question_id=?");
        $delStmt->bind_param("ss", $sessionId, $questionId);
        $delStmt->execute();
        $delStmt->close();

        // Insert new answers
        $stmt = $conn->prepare("INSERT INTO user_answers (question_id, answer, session_id, cookie_id) VALUES (?, ?, ?, ?)");
        foreach ($answerArr as $ans) {
            $ans = htmlspecialchars($ans, ENT_QUOTES);
            $stmt->bind_param("ssss", $questionId, $ans, $sessionId, $recentCookie);
            $stmt->execute();
        }
        $stmt->close();
    }

    $response['success'] = true;
    $response['message'] = 'All answers saved successfully!';
}

$conn->close();
echo json_encode($response);
