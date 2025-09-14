<?php
session_start();
include 'header.php';
include 'dbconnections.php'; // mysqli connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
?>

<link rel="stylesheet" href="./css/playlist_style.css">

<style>
    input::placeholder {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    input.form-control {
        background-color: rgba(64, 115, 158, 1.0) !important;
        color: white !important;
        border: 1px solid #40739e !important;
    }

    .playlist-card:hover {
        cursor: pointer;
        background-color: rgba(64, 115, 158, 0.2);
        transform: scale(1.02);
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .playlist-count {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.7);
    }

    /* Inline AJAX message styling */
    #ajaxMessage .alert {
        margin-bottom: 15px;
    }

    /* Make left side fully clickable */
    .playlist-card a.flex-grow-1:hover {
        text-decoration: none;
        color: inherit;
    }
</style>

<div class="container-fluid">
    <!-- Playlist Header -->
    <div data-aos="fade-up" data-aos-anchor-placement="bottom-bottom" data-aos-duration="900" id="card-bg" class="card flex text-white justify-start items-start p-3 text-start shadow-lg border-rounded mb-4">
        <div class="text-center">
            <h4 class="fw-bold">Playlists</h4>
            <p class="fs-6">Create and manage your favorite playlists to organize your audios efficiently.</p>
        </div>

        <!-- AJAX Messages -->
        <div id="ajaxMessage"></div>

        <!-- Create Playlist Form -->
        <form id="createPlaylistForm" class="d-flex gap-2 mb-3">
            <input type="text" id="playlist_name" class="form-control" placeholder="New Playlist" required>
            <button type="submit" class="btn btn-success">Create</button>
        </form>
    </div>

    <!-- Playlist List -->
    <div id="playlistContainer" class="row g-2 mt-3">
        <!-- Playlists will load here via AJAX -->
    </div>

    <div data-aos="fade-right" data-aos-offset="300" data-aos-duration="900" data-aos-easing="ease-in-sine" id="card-bg" class="card flex text-white justify-start items-start p-3 text-start shadow-lg border-rounded mt-5 mb-4">
        <h4 class="fw-bold">Info</h4>
        <ul class="fs-6" style="color: rgba(255,255,255,0.8);">
            <li>Click on a playlist to view or add audios.</li>
            <li>Create new playlists for different moods or categories.</li>
            <li>Delete playlists you no longer need.</li>
            <li>See the number of audios in each playlist at a glance.</li>
        </ul>
        <p>Use the search bar below to quickly find a playlist by name.</p>
    </div>

    <div class="card flex justify-start alert alert-success items-start p-2 text-start mb-4 mt-5 shadow-lg border-rounded">
        <h4 class="font-weight-bold">Essence – Life, Meditate & Relax</h4>
        <hr>
        <p class="lead fs-6 fw-bold">Discover inner peace with guided meditations, calming music, and sleep stories.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('playlistContainer');
        const messageDiv = document.getElementById('ajaxMessage');

        function showMessage(text, type = 'success') {
            messageDiv.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 3000);
        }

        function loadPlaylists() {
            fetch('playlist_actions.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'list'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.status === 'success' && data.playlists.length) {
                        data.playlists.forEach(pl => {
                            const div = document.createElement('div');
                            div.className = 'col-md-4 col-12';
                            div.innerHTML = `
                        <div data-aos="fade-left" data-aos-anchor="#example-anchor" data-aos-offset="500" data-aos-duration="700" class="playlist-card shadow-lg p-3 d-flex justify-content-between align-items-center" style="border:1px solid #40739e;">
                            <a href="playlist-details.php?playlist_id=${pl.id}" class="text-white text-decoration-none flex-grow-1 me-2 d-flex align-items-center">
                                <div>
                                    <h6 class="mb-1">${pl.name}</h6>
                                    <span class="playlist-count">${pl.audio_count} audio(s)</span>
                                </div>
                            </a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${pl.id}"><i class="bi bi-trash"></i></button>
                        </div>`;
                            container.appendChild(div);
                        });
                    } else {
                        container.innerHTML = `<div class="col-12"><p class="text-white">No playlists found. Create one to get started!</p></div>`;
                    }
                });
        }

        loadPlaylists();

        // Create playlist
        document.getElementById('createPlaylistForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('playlist_name').value;
            fetch('playlist_actions.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'create',
                        playlist_name: name
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadPlaylists();
                        document.getElementById('playlist_name').value = '';
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message, 'danger');
                    }
                });
        });

        // Delete playlist
        container.addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const btn = e.target.closest('.delete-btn');
                const playlist_id = btn.dataset.id;
                if (confirm('Are you sure you want to delete this playlist?')) {
                    fetch('playlist_actions.php', {
                            method: 'POST',
                            body: new URLSearchParams({
                                action: 'delete',
                                playlist_id: playlist_id
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                loadPlaylists();
                                showMessage('Playlist deleted successfully!', 'success');
                            } else {
                                showMessage(data.message, 'danger');
                            }
                        });
                }
            }
        });
    });
</script>

<?php include 'footer.php'; ?>