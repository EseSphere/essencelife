<?php
require_once('header-panel.php');
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

    .is-invalid {
        border-color: #dc3545;
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
                            <div id="alertContainer" class="alert-container"></div>

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
                                    <button type="submit" id="btnSubmitForm" class="action-btn mt-3">
                                        <span id="btnText"><i class="bi bi-sign-in"></i> Sign Up</span>
                                        <span id="btnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
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

<script>
    document.getElementById('submitForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const submitBtn = document.getElementById('btnSubmitForm');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');
        const alertContainer = document.getElementById('alertContainer');

        // Reset invalid fields
        form.logname.classList.remove('is-invalid');
        form.logemail.classList.remove('is-invalid');
        form.logpass.classList.remove('is-invalid');

        // Client-side validation
        const name = form.logname.value.trim();
        const email = form.logemail.value.trim();
        const password = form.logpass.value;

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

        if (name.length < 2) {
            showAlert('danger', 'Full Name must be at least 2 characters.');
            form.logname.classList.add('is-invalid');
            return;
        }

        if (!emailPattern.test(email)) {
            showAlert('danger', 'Please enter a valid email address.');
            form.logemail.classList.add('is-invalid');
            return;
        }

        if (!passwordPattern.test(password)) {
            showAlert('danger', 'Password must be strong (8+ chars, upper/lower, number, special)');
            form.logpass.classList.add('is-invalid');
            return;
        }

        // Show loader
        btnText.classList.add('d-none');
        btnLoader.classList.remove('d-none');
        submitBtn.disabled = true;

        const formData = new FormData(form);

        fetch('signup-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.status, data.message);
                if (data.status === 'success') form.reset();
            })
            .catch(err => {
                console.error('Error:', err);
                showAlert('danger', 'Something went wrong. Please try again.');
            })
            .finally(() => {
                btnText.classList.remove('d-none');
                btnLoader.classList.add('d-none');
                submitBtn.disabled = false;
            });

        // Helper function to show alerts (user must close manually)
        function showAlert(type, message) {
            alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        }
    });
</script>

<?php require_once('footer-panel.php'); ?>