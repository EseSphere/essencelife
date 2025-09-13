<?php
// dbconnections.php
if (!headers_sent()) {
    if (!ob_get_level()) ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST'] ?? '', $secure, true);
    }
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;

date_default_timezone_set('Europe/London');

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'essence_life';

$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_errno) {
    error_log('Database connection failed: ' . $conn->connect_error);
    exit('Sorry, we are experiencing technical difficulties. Please try again later.');
}
if (!$conn->set_charset('utf8mb4')) {
    error_log('Error setting charset: ' . $conn->error);
}

$sTime = date('H:i');
$CompanyName = 'Essence Life | For home and community care';
$today = date('Y-m-d');
$tomorrow = (new DateTime('tomorrow'))->format('Y-m-d');
$currentDate = date('F j, Y');
$visitCookieDate = $_COOKIE['VisitDate'] ?? null;
try {
    $encrypted = 'USR-' . strtoupper(bin2hex(random_bytes(4)));
} catch (Exception $e) {
    $encrypted = 'USR-' . strtoupper(bin2hex(openssl_random_pseudo_bytes(4)));
}
$encrypt = uniqid('', true);
$crackEncryptedbinary = $encrypted . '-' . $encrypt;

/**
 * SESSION CHECK
 * Redirect user to ../index if session is not set or user_id is missing
 */
//if (isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == true || !$user_id) {
   // header('Location: ./checkings');
   // exit;
//}
