<?php
session_start();
include 'header.php';
include 'dbconnections.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
?>

<link rel="stylesheet" href="./css/playlist_style.css">
<style>
    .profile-card {
        border: 1px solid #40739e;
        border-radius: 10px;
        background-color: rgba(64, 115, 158, 0.1);
        padding: 20px;
        color: white;
        text-align: center;
    }

    .profile-card img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
    }

    input.form-control {
        background-color: rgba(64, 115, 158, 1);
        color: white;
        border: 1px solid #40739e;
    }

    #ajaxMessage .alert {
        margin-bottom: 15px;
    }

    .custom-file-btn {
        display: inline-block;
        padding: 6px 12px;
        cursor: pointer;
        background-color: #40739e;
        color: white;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .custom-file-btn:hover {
        background-color: #365d7d;
    }

    .section-divider {
        height: 1px;
        background-color: #40739e;
        margin: 30px 0;
    }

    .profile-input {
        height: 50px;
    }
</style>

<div class="container-fluid mt-5">
    <div data-aos="fade-up" data-aos-anchor-placement="bottom-bottom" data-aos-duration="900" id="card-bg" class="card text-white flex justify-start items-start text-start shadow-lg border-rounded p-4 mb-4">
        <h4 class="fw-bold">My Profile</h4>
        <p class="fs-6">View or update your profile details here.</p>
    </div>

    <div id="ajaxMessage"></div>

    <div data-aos="fade-up" data-aos-anchor-placement="top-center" data-aos-duration="900" class="profile-card">
        <img id="profileImage" src="<?= htmlspecialchars($user['image'] ?: 'default.png') ?>" alt="Profile Image">

        <!-- Profile Update Form -->
        <form id="updateProfileForm" enctype="multipart/form-data">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($user['image'] ?? '') ?>">

            <div class="mb-3">
                <input type="text" name="name" class="form-control profile-input" placeholder="Name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control profile-input" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <input type="text" name="phone" class="form-control profile-input" placeholder="Phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>

            <!-- Custom File Upload Button -->
            <label class="custom-file-btn">
                Choose Image
                <input type="file" name="image" style="display:none;" id="profileFileInput">
            </label>
            <br>
            <button type="submit" class="btn btn-success">Update Profile</button>
        </form>

        <div class="section-divider"></div>

        <!-- Change Password Form -->
        <form id="changePasswordForm">
            <h5 class="mb-3">Change Password</h5>
            <div class="mb-3">
                <input type="password" name="current_password" class="form-control profile-input" placeholder="Current Password" required>
            </div>
            <div class="mb-3">
                <input type="password" name="new_password" class="form-control profile-input" placeholder="New Password" required>
            </div>
            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control profile-input" placeholder="Confirm New Password" required>
            </div>
            <button type="submit" class="btn btn-warning">Change Password</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateForm = document.getElementById('updateProfileForm');
        const passwordForm = document.getElementById('changePasswordForm');
        const messageDiv = document.getElementById('ajaxMessage');
        const profileImage = document.getElementById('profileImage');
        const profileFileInput = document.getElementById('profileFileInput');

        function showMessage(text, type = 'success') {
            messageDiv.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
            setTimeout(() => messageDiv.innerHTML = '', 4000);
        }

        // Live preview of selected image
        profileFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                // Revert to existing image if no file selected
                profileImage.src = updateForm.querySelector('input[name="existing_image"]').value || 'default.png';
            }
        });

        // AJAX Profile Update
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(updateForm);

            // If no new image selected, append existing image
            if (!profileFileInput.files.length) {
                formData.append('existing_image', updateForm.querySelector('input[name="existing_image"]').value);
            }

            fetch('profile_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.image) profileImage.src = data.image;
                        showMessage('Profile updated successfully!', 'success');
                    } else {
                        showMessage(data.message, 'danger');
                    }
                })
                .catch(() => showMessage('An error occurred.', 'danger'));
        });

        // AJAX Change Password
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(passwordForm);
            formData.append('action', 'change_password');

            fetch('profile_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        passwordForm.reset();
                        showMessage('Password changed successfully!', 'success');
                    } else {
                        showMessage(data.message, 'danger');
                    }
                })
                .catch(() => showMessage('An error occurred.', 'danger'));
        });
    });
</script>

<?php include 'footer.php'; ?>