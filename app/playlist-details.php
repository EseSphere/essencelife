<?php
session_start();
include 'header.php';
include 'dbconnections.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$playlist_id = isset($_GET['playlist_id']) ? intval($_GET['playlist_id']) : 0;

// Get playlist info
$playlist = $conn->query("SELECT * FROM playlists WHERE id='$playlist_id' AND user_id='$user_id'")->fetch_assoc();
if (!$playlist) {
    echo '<div class="container"><p class="text-white">Playlist not found!</p></div>';
    include 'footer.php';
    exit;
}
?>

<link rel="stylesheet" href="./css/playlist_style.css">

<style>
    .audio-card:hover {
        cursor: pointer;
        background-color: rgba(64, 115, 158, 0.2);
        transform: scale(1.02);
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }

    .audio-count {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .audio-card img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 12px;
    }

    #ajaxMessage .alert {
        margin-bottom: 15px;
    }
</style>

<div class="container-fluid">

    <!-- Playlist Header -->
    <div id="card-bg" class="card flex text-white justify-start items-start p-3 text-start shadow-lg border-rounded mb-4">
        <div class="text-start">
            <h4 class="fw-bold"><?= htmlspecialchars($playlist['name']) ?></h4>
            <p class="fs-6">Manage audios in this playlist and add new content.</p>
        </div>
        <div id="ajaxMessage"></div>
    </div>

    <!-- Audios already in playlist -->
    <h5 class="text-white mb-2">Audios in this Playlist</h5>
    <div class="row g-2 mb-4" id="playlistAudiosContainer"></div>

    <hr class="border-light">

    <!-- All available contents to add -->
    <h5 class="text-white mb-2">All Contents</h5>
    <div class="row g-2 mt-3" id="allContentsContainer">
        <?php
        $contents = $conn->query("SELECT * FROM contents WHERE status='active' ORDER BY id DESC");
        if ($contents->num_rows > 0) {
            while ($content = $contents->fetch_assoc()) {
                $image = !empty($content['image_url']) ? $content['image_url'] : 'placeholder.png';
                echo '<div class="col-md-4 col-12">';
                echo '<div class="audio-card shadow-lg p-3 d-flex align-items-center justify-content-between" style="border:1px solid #40739e;">';
                echo '<a href="player.php?id=' . $content['id'] . '" class="text-decoration-none text-white d-flex align-items-center flex-grow-1">';
                echo '<img src="' . htmlspecialchars($image) . '" alt="Audio Image">';
                echo '<div>';
                echo '<h6 class="mb-1">' . htmlspecialchars($content['content_name']) . '</h6>';
                echo '<span class="audio-count">' . htmlspecialchars($content['content_type']) . '</span>';
                echo '</div></a>';
                echo '<button class="btn btn-sm btn-success add-to-playlist-btn" data-audio="' . $content['id'] . '"><i class="bi bi-plus"></i></button>';
                echo '</div></div>';
            }
        } else {
            echo '<div class="col-12"><p class="text-white">No content available.</p></div>';
        }
        ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const playlistId = <?= $playlist_id ?>;
        const playlistContainer = document.getElementById('playlistAudiosContainer');
        const allContentsContainer = document.getElementById('allContentsContainer');
        const messageDiv = document.getElementById('ajaxMessage');

        function showMessage(text, type = 'success') {
            messageDiv.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 3000);
        }

        function loadPlaylistAudios() {
            fetch('playlist_actions.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'get_audios',
                        playlist_id: playlistId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    playlistContainer.innerHTML = '';
                    const playlistAudioIds = [];

                    if (data.status === 'success' && data.audios.length > 0) {
                        data.audios.forEach(audio => {
                            playlistAudioIds.push(audio.id);
                            const image = audio.image_url || 'placeholder.png';
                            const div = document.createElement('div');
                            div.className = 'col-md-4 col-12';
                            div.innerHTML = `
                        <div class="audio-card shadow-lg p-3 d-flex align-items-center justify-content-between" style="border:1px solid #40739e;">
                            <a href="player.php?id=${audio.id}" class="text-decoration-none text-white d-flex align-items-center flex-grow-1">
                                <img src="${image}" alt="Audio Image">
                                <div>
                                    <h6 class="mb-1">${audio.title}</h6>
                                    <span class="audio-count">${audio.type}</span>
                                </div>
                            </a>
                            <button class="btn btn-sm btn-danger remove-from-playlist-btn" data-audio="${audio.id}"><i class="bi bi-trash"></i></button>
                        </div>`;
                            playlistContainer.appendChild(div);
                        });
                    } else {
                        playlistContainer.innerHTML = `<div class="col-12"><p class="text-white">No audios have been added to this playlist yet.</p></div>`;
                    }

                    // Disable Add button for already added audios
                    Array.from(allContentsContainer.querySelectorAll('.audio-card')).forEach(card => {
                        const btn = card.querySelector('.add-to-playlist-btn');
                        const audioId = parseInt(btn.dataset.audio);
                        if (playlistAudioIds.includes(audioId)) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="bi bi-check"></i>';
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-plus"></i>';
                        }
                    });
                });
        }

        loadPlaylistAudios();

        // Add content to playlist
        allContentsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.add-to-playlist-btn')) {
                const btn = e.target.closest('.add-to-playlist-btn');
                const audioId = btn.dataset.audio;
                fetch('playlist_actions.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            action: 'add_audio',
                            playlist_id: playlistId,
                            audio_id: audioId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadPlaylistAudios();
                            showMessage('Audio added to playlist!', 'success');
                        } else {
                            showMessage(data.message, 'danger');
                        }
                    });
            }
        });

        // Remove audio from playlist
        playlistContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-from-playlist-btn')) {
                const btn = e.target.closest('.remove-from-playlist-btn');
                const audioId = btn.dataset.audio;
                fetch('playlist_actions.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            action: 'remove_audio',
                            playlist_id: playlistId,
                            audio_id: audioId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadPlaylistAudios();
                            showMessage('Audio removed from playlist!', 'success');
                        } else {
                            showMessage(data.message, 'danger');
                        }
                    });
            }
        });
    });
</script>

<?php include 'footer.php'; ?>