<?php
require_once('header-panel.php');

// Handle form submission
$alertMessage = '';
$alertClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['logname']);
    $email = trim($_POST['logemail']);
    $password = $_POST['logpass'];

    // Simple password hashing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $user_id = 'user_' . time();
    $updated_at = date('Y-m-d');

    // Prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO users (user_id, name, email, phone, password, updated_at) VALUES (?, ?, ?, '', ?, ?)");
    $stmt->bind_param("sssss", $user_id, $name, $email, $hashedPassword, $updated_at);

    if ($stmt->execute()) {
        $alertMessage = "Sign up successful!";
        $alertClass = "success";
    } else {
        $alertMessage = "Error: " . $stmt->error;
        $alertClass = "danger";
    }

    $stmt->close();
}

$conn->close();
?>

<style>
    #submitQuestionnaire,
    #btnSubmitForm {
        background: linear-gradient(135deg, #0d6efd, #198754);
        color: #fff;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
    }

    #submitQuestionnaire:hover,
    #btnSubmitForm:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.4);
    }

    .alert-container {
        margin-bottom: 1rem;
    }
</style>

<div class="section pt-5 text-center">
    <div class="container-fluid">
        <div class="card-3d-wrap mx-auto">
            <div class="card-3d-wrapper">
                <div class="card-front">
                    <div class="center-wrap">
                        <div class="section text-center">
                            <h4 class="mb-4 pb-3 text-white">Sign Up</h4>

                            <!-- Alert container -->
                            <div id="alertContainer" class="alert-container">
                                <?php if ($alertMessage): ?>
                                    <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
                                        <?= $alertMessage ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form id="submitForm" method="POST" autocomplete="off">
                                <div class="form-group">
                                    <input type="text" required name="logname" class="form-style" placeholder="Full Name" id="logname" autocomplete="off">
                                    <i class="input-icon uil uil-user"></i>
                                </div>
                                <div class="form-group mt-2">
                                    <input type="email" required name="logemail" class="form-style" placeholder="Email" id="logemail" autocomplete="off">
                                    <i class="input-icon uil uil-at"></i>
                                </div>
                                <div class="form-group mt-2">
                                    <input type="password" required name="logpass" class="form-style" placeholder="Password" id="logpass" autocomplete="off">
                                    <i class="input-icon uil uil-lock-alt"></i>
                                </div>
                                <div class="form-group mt-2">
                                    <button type="submit" id="btnSubmitForm" class="action-btn mt-3"><i class="bi bi-sign-in"></i> Sign Up</button>
                                </div>
                                <div class="form-group mt-2 w-100 flex justify-start items-start text-start">
                                    <p class="mb-0 mt-4 text-left"><a href="./" class="link">Have account? Login</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer-panel.php'); ?>