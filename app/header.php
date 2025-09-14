<?php
include 'dbconnections.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
  $user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
  if ($user && !empty($user['image'])) {
    $profileImage = $user['image'];
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Essence – Life, Meditate & Relax</title>
  <meta name="description" content="Essence helps you find peace and relaxation with guided meditations, sleep stories, calming music, and mindfulness exercises.">
  <meta name="keywords" content="Essence app, meditation, mindfulness, sleep stories, calming music, relaxation, stress relief, wellness">
  <meta name="author" content="Essence Team">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="Essence – Life, Meditate & Relax">
  <meta property="og:description" content="Discover inner calm with Essence. Guided meditations, soothing music, and sleep stories to improve focus and relaxation.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.essenceapp.com">
  <meta property="og:image" content="./images/logo/favicon.png">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Essence – Life, Meditate & Relax">
  <meta name="twitter:description" content="Relax, sleep better, and focus with Essence. Guided meditations, calming music, and sleep stories.">
  <meta name="twitter:image" content="./images/logo/favicon.png">
  <link rel="icon" href="./images/logo/favicon.png" type="image/x-icon">
  <link rel="shortcut icon" href="./images/logo/favicon.png" type="image/x-icon">
  <link rel="apple-touch-icon" href="./images/logo/favicon.png">
  <link rel=" stylesheet" href="./css/style2.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>

<body>
  <!-- Navbar -->
  <nav style="background-color: #001F54;" class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <div class="row w-100 align-items-center">
        <div class="col-md-2 col-2">
          <a class="navbar-brand" href="./home">
            <img src="./images/logo/favicon.png" alt="Essence Life Logo" style="height: 40px;">
          </a>
        </div>
        <div class="col-md-6 col-1"></div>
        <div class="col-md-3 col-8">
          <input type="text" id="searchInput" class="form-control form-control-lg" placeholder="🔍 Search...">
        </div>
        <div class="col-md-1 col-1 text-end">
          <!-- Profile Dropdown -->
          <div class="dropdown">
            <a href="#" class="d-block" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile" class="rounded-circle" style="width:40px; height:40px; object-fit:cover; cursor:pointer;" onerror="this.onerror=null; this.src='./uploads/users/default.png';">
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Wrapper -->
  <div class="wrapper" id="hero">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <!-- Main content wrapper for AJAX -->
    <div id="mainContent" class="mt-5 text-white">
      <div class="container-fluid my-5">