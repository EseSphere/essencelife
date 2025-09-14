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

                // Playable wrapper
                echo '<div class="d-flex align-items-center flex-grow-1 play-audio-wrapper"
                    data-id="' . $content['id'] . '"
                    data-title="' . htmlspecialchars($content['content_name']) . '"
                    data-audio="' . htmlspecialchars($content['content_url']) . '"
                    data-image="' . htmlspecialchars($image) . '"
                    data-category="' . htmlspecialchars($content['content_type']) . '"
                    data-description="' . htmlspecialchars($content['description'] ?? '') . '"
                    data-related=\'' . json_encode([]) . '\'>
                <img src="' . htmlspecialchars($image) . '" alt="Audio Image">
                <div>
                    <h6 class="mb-1">' . htmlspecialchars($content['content_name']) . '</h6>
                    <span class="audio-count">' . htmlspecialchars($content['content_type']) . '</span>
                </div>
            </div>';

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

        const playerContainer = $('#audioPlayerContainer'); // Footer player
        const miniPlayer = $('#miniPlayer'); // Mini player
        const audioEl = $('#audioPlayer')[0];

        // Mini player elements
        const miniTitleEl = $('#miniCurrentSongTitle');
        const miniImgEl = $('#miniCurrentSongImage');
        const miniPlayPauseBtn = $('#miniPlayPauseBtn');

        // Footer player elements
        const playerTitleEl = $('#currentSongTitle');
        const playerImgEl = $('#currentSongImage');
        const playerCategoryEl = $('#currentSongCategory');
        const playerDescriptionEl = $('#currentSongDescription');
        const playPauseBtn = $('#playPauseBtn');

        function showMessage(text, type = 'success') {
            messageDiv.innerHTML = `<div class="alert alert-${type}">${text}</div>`;
            setTimeout(() => messageDiv.innerHTML = '', 3000);
        }

        // Load audios in playlist
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
                            <div class="d-flex align-items-center flex-grow-1 play-audio-wrapper"
                                 data-id="${audio.id}"
                                 data-title="${audio.title}"
                                 data-audio="${audio.content_url}"
                                 data-image="${image}"
                                 data-category="${audio.type}"
                                 data-description="">
                                <img src="${image}" alt="Audio Image">
                                <div>
                                    <h6 class="mb-1">${audio.title}</h6>
                                    <span class="audio-count">${audio.type}</span>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-danger remove-from-playlist-btn" data-audio="${audio.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>`;
                            playlistContainer.appendChild(div);
                        });
                    } else {
                        playlistContainer.innerHTML = `<div class="col-12"><p class="text-white">No audios in this playlist yet.</p></div>`;
                    }

                    // Disable add buttons for already added audios
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

                    attachPlayAudioEvents();
                });
        }

        loadPlaylistAudios();

        // Add audio
        allContentsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.add-to-playlist-btn')) {
                const btn = e.target.closest('.add-to-playlist-btn');
                fetch('playlist_actions.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            action: 'add_audio',
                            playlist_id: playlistId,
                            audio_id: btn.dataset.audio
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadPlaylistAudios();
                            showMessage('Audio added!', 'success');
                        } else showMessage(data.message, 'danger');
                    });
            }
        });

        // Remove audio
        playlistContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-from-playlist-btn')) {
                const btn = e.target.closest('.remove-from-playlist-btn');
                fetch('playlist_actions.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            action: 'remove_audio',
                            playlist_id: playlistId,
                            audio_id: btn.dataset.audio
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadPlaylistAudios();
                            showMessage('Audio removed!', 'success');
                        } else showMessage(data.message, 'danger');
                    });
            }
        });

        // Play song in both mini + footer player
        function playSong(song) {
            localStorage.setItem('currentSong', JSON.stringify(song));

            // Show both players
            miniPlayer.show();
            playerContainer.show();

            // Update footer player
            playerTitleEl.text(song.title);
            playerImgEl.attr('src', song.image || 'default.png');
            playerCategoryEl.text(song.category || '');
            playerDescriptionEl.text(song.description || 'No description available.');

            // Update mini player
            miniTitleEl.text(song.title);
            miniImgEl.attr('src', song.image || 'default.png');

            // Set audio src and play
            if (audioEl.src !== song.audio) {
                audioEl.src = song.audio;
                audioEl.currentTime = song.time || 0;
            }
            audioEl.play();

            // Update play/pause icons
            playPauseBtn.find('i').removeClass('bi-play-fill').addClass('bi-pause-fill');
            miniPlayPauseBtn.find('i').removeClass('bi-play-fill').addClass('bi-pause-fill');
        }

        // Attach play events to audio cards
        function attachPlayAudioEvents() {
            document.querySelectorAll('.play-audio-wrapper').forEach(wrapper => {
                wrapper.addEventListener('click', function(e) {
                    e.preventDefault();
                    const data = e.currentTarget.dataset;
                    playSong({
                        id: data.id,
                        title: data.title,
                        audio: data.audio,
                        image: data.image,
                        category: data.category,
                        description: data.description,
                        time: 0
                    });
                });
            });
        }

        // Restore last played song
        const savedSong = JSON.parse(localStorage.getItem('currentSong') || '{}');
        if (savedSong.audio) {
            playSong(savedSong);
        }

        // Update playback time
        $('#audioPlayer').on('timeupdate', function() {
            const saved = JSON.parse(localStorage.getItem('currentSong') || '{}');
            if (saved.audio) {
                saved.time = this.currentTime;
                localStorage.setItem('currentSong', JSON.stringify(saved));
            }
        });

        // Mini player play/pause toggle
        miniPlayPauseBtn.on('click', function() {
            if (audioEl.paused) {
                audioEl.play();
                $(this).find('i').removeClass('bi-play-fill').addClass('bi-pause-fill');
                playPauseBtn.find('i').removeClass('bi-play-fill').addClass('bi-pause-fill');
            } else {
                audioEl.pause();
                $(this).find('i').removeClass('bi-pause-fill').addClass('bi-play-fill');
                playPauseBtn.find('i').removeClass('bi-pause-fill').addClass('bi-play-fill');
            }
        });
    });
</script>


<?php include 'footer.php'; ?>