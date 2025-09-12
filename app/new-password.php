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
</style>

<div class="section pt-5 text-center">
    <div class="container-fluid">
        <div class="card-3d-wrap mx-auto">
            <div class="card-3d-wrapper">
                <div class="card-front">
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
                                    <button type="submit" id="btnSubmitForm" class="action-btn mt-3"><i class="bi bi-sign-in"></i> Update Password</button>
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
        let db;

        // Get email from sessionStorage
        const email = sessionStorage.getItem('resetEmail');
        if (!email) {
            showAlert('No email found. Please start password reset again.', 'danger');
            return;
        }

        const request = indexedDB.open('essence_life');

        request.onsuccess = function(event) {
            db = event.target.result;
        };

        request.onerror = function(event) {
            showAlert('Database error: ' + event.target.errorCode, 'danger');
        };

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const password = document.getElementById('logpass').value.trim();
            const confirmPassword = document.getElementById('logcpass').value.trim();

            if (password !== confirmPassword) {
                showAlert('Passwords do not match.', 'danger');
                return;
            }

            if (!db) {
                showAlert('Database not ready. Please try again.', 'danger');
                return;
            }

            const transaction = db.transaction(['users'], 'readwrite');
            const objectStore = transaction.objectStore('users');

            // Use index if exists
            const updatePassword = (user) => {
                user.password = btoa(password); // encode password
                const updateRequest = objectStore.put(user);

                updateRequest.onsuccess = () => {
                    showAlert('Password updated successfully!', 'success');
                    sessionStorage.removeItem('resetEmail');
                    setTimeout(() => {
                        window.location.href = './';
                    }, 2000); // redirect to login after 2 seconds
                };

                updateRequest.onerror = () => {
                    showAlert('Failed to update password.', 'danger');
                };
            };

            if (objectStore.indexNames.contains('email')) {
                const index = objectStore.index('email');
                const getRequest = index.get(email);

                getRequest.onsuccess = function() {
                    const user = getRequest.result;
                    if (user) {
                        updatePassword(user);
                    } else {
                        showAlert('Email not found.', 'danger');
                    }
                };

                getRequest.onerror = function() {
                    showAlert('Error accessing user data.', 'danger');
                };
            } else {
                // fallback: scan all users
                const cursorRequest = objectStore.openCursor();
                let found = false;

                cursorRequest.onsuccess = function(event) {
                    const cursor = event.target.result;
                    if (cursor) {
                        const user = cursor.value;
                        if (user.email === email) {
                            found = true;
                            updatePassword(user);
                            return;
                        }
                        cursor.continue();
                    } else {
                        if (!found) showAlert('Email not found.', 'danger');
                    }
                };

                cursorRequest.onerror = function() {
                    showAlert('Error scanning users.', 'danger');
                };
            }
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