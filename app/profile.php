<?php
session_start();
include 'header.php';
include 'dbconnections.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
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
</style>

<div class="container-fluid">
    <div id="card-bg" class="card flex justify-start items-start text-start shadow-lg border-rounded p-4 mb-4">
        <h4 class="fw-bold">My Profile</h4>
        <p class="fs-6">View or update your profile details here.</p>
    </div>

    <div id="ajaxMessage"></div>

    <div class="profile-card text-center">
        <img id="profileImage" src="<?= htmlspecialchars($user['image'] ?: 'default_profile.png') ?>" alt="Profile Image">
        <form id="updateProfileForm" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            <div class="mb-3">
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Update Profile</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('updateProfileForm');
        const messageDiv = document.getElementById('ajaxMessage');
        const profileImage = document.getElementById('profileImage');

        function showMessage(text, type = 'success') {
            messageDiv.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
            setTimeout(() => messageDiv.innerHTML = '', 3000);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch('profile_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.image) profileImage.src = data.image; // Update profile image
                        showMessage('Profile updated successfully!', 'success');
                    } else {
                        showMessage(data.message, 'danger');
                    }
                })
                .catch(err => showMessage('An error occurred.', 'danger'));
        });
    });
</script>