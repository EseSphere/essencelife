
<?php
// login.php
require_once('dbconnections.php');
$response = ['success' => false, 'message' => 'Login failed.'];

function client_wants_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return (strpos($accept, 'application/json') !== false) || strtolower($xrw) === 'xmlhttprequest';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['logemail'] ?? '');
    $password = $_POST['logpass'] ?? '';
    if ($email === '' || $password === '') {
        $response['message'] = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, user_id, name, password FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($id, $user_id, $name, $hashedPassword);
                    $stmt->fetch();
                    $passwordOk = false;
                    if (password_verify($password, $hashedPassword)) {
                        $passwordOk = true;
                    } elseif ($password === $hashedPassword) {
                        $passwordOk = true;
                    }
                    if ($passwordOk) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['name'] = $name;
                        $_SESSION['logged_in'] = true;
                        $response = ['success' => true, 'message' => 'Login successful! Redirecting...'];
                    } else {
                        $response['message'] = 'Invalid password.';
                    }
                } else {
                    $response['message'] = 'Email not found.';
                }
            }
            $stmt->close();
        } else {
            $response['message'] = 'Database query failed: ' . $conn->error;
        }
    }
}

if (client_wants_json()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    $conn->close();
    exit;
} else {
    $_SESSION['flash_message'] = $response['message'];
    if (!empty($response['success'])) {
        header('Location: question.php');
    } else {
        header('Location: index.php');
    }
    $conn->close();
    exit;
}
