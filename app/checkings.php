<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('dbconnections.php');

// Check if device cookie exists
if (isset($_COOKIE['device_cookie'])) {
    $cookie_id = $_COOKIE['device_cookie'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE cookie_id = ? LIMIT 1");
    $stmt->bind_param("s", $cookie_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Cookie exists in DB
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // User is logged in → redirect to home
            header("Location: home.php");
            exit();
        } else {
            // User not logged in → redirect to login
            header("Location: login.php");
            exit();
        }
    } else {
        // Cookie does not exist in DB → redirect to questionnaire
        header("Location: questionnaire.php");
        exit();
    }

    $stmt->close();
} else {
    // No cookie found → redirect to questionnaire
    header("Location: questionnaire.php");
    exit();
}
