<?php require_once('header-panel.php'); ?>
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

<div style="margin-top: 15%;" class="section text-center">
    <div class="container-fluid">
        <div class="card-3d-wrap mx-auto">
            <div class="card-3d-wrapper">
                <div data-aos="fade-up" data-aos-duration="2000" class="card-front">
                    <div class="center-wrap">
                        <div class="section text-center">
                            <h4 class="mb-4 pb-3 text-white">New Password</h4>

                            <!-- Alert container -->
                            <div id="alertContainer" class="alert-container"></div>

                            <form id="submitForm" autocomplete="off">
                                <div class="form-group mt-2">
                                    <input type="password" required name="logpass" class="form-style" placeholder="Password" id="logpass" autocomplete="off">
                                    <i class="input-icon uil uil-lock-alt"></i>
                                </div>
                                <div class="form-group mt-2">
                                    <input type="password" required name="logcpass" class="form-style" placeholder="Confirm Password" id="logcpass" autocomplete="off">
                                    <i class="input-icon uil uil-lock-alt"></i>
                                </div>
                                <div class="form-group mt-2">
                                    <button type="submit" id="btnSubmitForm" class="action-btn mt-3">
                                        <span id="btnText"><i class="bi bi-sign-in"></i> Update Password</span>
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
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('submitForm');
        const alertContainer = document.getElementById('alertContainer');
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (!token) {
            showAlert('Invalid or missing token.', 'danger');
            form.querySelectorAll('input, button').forEach(el => el.disabled = true);
            return;
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const password = document.getElementById('logpass').value.trim();
            const confirmPassword = document.getElementById('logcpass').value.trim();
            const submitBtn = document.getElementById('btnSubmitForm');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');

            // Reset invalid fields
            document.getElementById('logpass').classList.remove('is-invalid');
            document.getElementById('logcpass').classList.remove('is-invalid');

            // Client-side validation
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (password !== confirmPassword) {
                showAlert('Passwords do not match.', 'danger');
                document.getElementById('logpass').classList.add('is-invalid');
                document.getElementById('logcpass').classList.add('is-invalid');
                return;
            }

            if (!passwordPattern.test(password)) {
                showAlert('Password must be strong (8+ chars, upper/lower, number, special)', 'danger');
                document.getElementById('logpass').classList.add('is-invalid');
                return;
            }

            // Show loader
            btnText.classList.add('d-none');
            btnLoader.classList.remove('d-none');
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('token', token);
            formData.append('password', password);

            fetch('new-password-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.status, data.message);
                    if (data.status === 'success') {
                        form.reset();
                        setTimeout(() => {
                            window.location.href = './';
                        }, 2000);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showAlert('Something went wrong. Please try again.', 'danger');
                })
                .finally(() => {
                    btnText.classList.remove('d-none');
                    btnLoader.classList.add('d-none');
                    submitBtn.disabled = false;
                });
        });

        function showAlert(message, type = 'success') {
            alertContainer.innerHTML = '';
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
            alertContainer.appendChild(alertDiv);
        }
    });
</script>

<?php require_once('footer-panel.php'); ?>