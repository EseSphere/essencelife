<?php
// index.php
require_once('header-panel.php');
require_once('dbconnections.php');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Redirect already logged-in users
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
  header("Location: home.php"); // Default post-login page
  exit;
}
?>

<div class="section pt-5 text-center">
  <div class="container-fluid">
    <div class="card-3d-wrap mx-auto">
      <div class="card-3d-wrapper">
        <div class="card-front">
          <div class="center-wrap">
            <div class="section text-center">
              <h4 class="mb-4 pb-3 text-white">Log In</h4>
              <div id="alertContainer" class="alert-container"></div>
              <form id="submitForm" method="POST" autocomplete="off">
                <div class="form-group">
                  <input type="email" required name="logemail" class="form-style" placeholder="Email" id="logemail">
                </div>
                <div class="form-group mt-2">
                  <input type="password" required name="logpass" class="form-style" placeholder="Password" id="logpass">
                </div>
                <div class="form-group mt-2">
                  <button type="submit" id="btnSubmitForm" class="action-btn mt-3">
                    <i class="bi bi-sign-in"></i> Sign In
                  </button>
                </div>
              </form>
              <div class="row">
                <div class="col-6">
                  <p class="mt-4"><a href="./reset-password" class="link">Forgot password?</a></p>
                </div>
                <div class="col-6">
                  <p class="mt-4"><a href="./signup" class="link">Don't have an account?</a></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('submitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = '';

    try {
      const response = await fetch('login.php', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      });

      let result;
      try {
        result = await response.json();
      } catch (err) {
        const text = await response.text();
        alertContainer.innerHTML = `<div class="alert alert-danger">Unexpected server response: ${text}</div>`;
        return;
      }

      // Display alert
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${result.success ? 'success' : 'danger'} alert-dismissible fade show`;
      alertDiv.innerHTML = `${result.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
      alertContainer.appendChild(alertDiv);

      // Redirect on success
      if (result.success && result.redirect) {
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 800);
      }

    } catch (error) {
      alertContainer.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
    }
  });
</script>

<?php require_once('footer-panel.php'); ?>