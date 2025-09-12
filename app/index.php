<?php
require_once('header-panel.php');

// Start the session at the top
session_start();

$alertMessage = '';
$alertClass = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['logemail']);
  $password = $_POST['logpass'];

  // Fetch user by email
  $stmt = $conn->prepare("SELECT id, user_id, name, password FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $user_id, $name, $hashedPassword);
    $stmt->fetch();

    // Verify password
    if (password_verify($password, $hashedPassword)) {
      // Login successful, store user info in session
      $_SESSION['user_id'] = $user_id;
      $_SESSION['name'] = $name;
      $_SESSION['logged_in'] = true;

      // Redirect to protected page
      header("Location: question.php");
      exit;
    } else {
      $alertMessage = "Email or password is incorrect.";
      $alertClass = "danger";
    }
  } else {
    $alertMessage = "Email or password is incorrect.";
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
              <h4 class="mb-4 pb-3 text-white">Log In</h4>

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
                  <input type="email" required name="logemail" class="form-style" placeholder="Email" id="logemail" autocomplete="off">
                  <i class="input-icon uil uil-at"></i>
                </div>
                <div class="form-group mt-2">
                  <input type="password" required name="logpass" class="form-style" placeholder="Password" id="logpass" autocomplete="off">
                  <i class="input-icon uil uil-lock-alt"></i>
                </div>
                <div class="form-group mt-2">
                  <button type="submit" id="btnSubmitForm" class="action-btn mt-3"><i class="bi bi-sign-in"></i> Sign In</button>
                </div>
                <div class="row">
                  <div class="col-6">
                    <p class="mb-0 mt-4 text-center"><a href="./reset-password" class="link">Forgot password?</a></p>
                  </div>
                  <div class="col-6">
                    <p class="mb-0 mt-4 text-center"><a href="./signup" class="link">Don't have account?</a></p>
                  </div>
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