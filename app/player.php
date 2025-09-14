<?php include 'header.php'; ?>

<link rel="stylesheet" href="./css/player_style.css">

<div class="container-fluid">
    <!-- Compact Main Player Info (Uses Persistent Player) -->
    <div id="card-bg" class="card text-white shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
        <div class="row g-0 align-items-center">
            <!-- Image -->
            <div class="col-md-4 col-12">
                <img id="pageSongImage" src="default.png"
                    class="w-100 h-100 object-fit-cover"
                    alt="Cover"
                    style="max-height:200px;object-fit:cover;">
            </div>

            <!-- Info + Controls -->
            <div class="col-md-8 col-12 p-3">
                <p id="pageSongCategory" class="text-uppercase small mb-1">Category</p>
                <h4 id="pageSongTitle" class="fw-bold text-success mb-2">Song Title</h4>

                <!-- Audio controls use persistent player in footer.php -->
                <p class="text-muted small mb-0">Audio is playing below (persistent player)</p>

                <!-- Prev/Next Buttons for convenience -->
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <button id="pagePrevBtn" class="btn btn-outline-light btn-sm rounded-circle">
                        <i class="bi bi-skip-backward-fill fs-6"></i>
                    </button>
                    <button id="pageNextBtn" class="btn btn-outline-light btn-sm rounded-circle">
                        <i class="bi bi-skip-forward-fill fs-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Up Next -->
    <div id="card-bg" style="border-top: 10px solid #0f4c81; border-radius:10px;" class="card flex justify-start items-start text-start text-white p-2 mt-4 mb-3 shadow-lg border-rounded">
        <h6 class="fw-bold">Similar Audios</h6>
    </div>
    <div id="upNextContainer" class="d-flex flex-row overflow-auto"></div>

    <!-- Audio Info -->
    <div id="card-bg" class="card fs-6 flex justify-start items-start text-start text-white p-2 mb-4 mt-4 shadow-lg border-rounded">
        <h5 class="fw-bold mt-1 mb-1">Audio Info</h5>
        <hr>
        <p style="color: rgba(220, 221, 225,.5);" class="mb-0">Year added</p>
        <p id="yearAdded">-</p>

        <p style="color: rgba(220, 221, 225,.5);" class="mb-0">Name</p>
        <p id="songName">-</p>

        <p style="color: rgba(220, 221, 225,.5);" class="mb-0">Description</p>
        <p id="description">-</p>

        <p style="color: rgba(220, 221, 225,.5);" class="mb-0">Category</p>
        <p id="songCategory">-</p>
    </div>

    <!-- Description Info -->
    <div class="card alert alert-success p-2 mb-4 mt-5 shadow-lg border-rounded">
        <h4 class="font-weight-bold">Essence – Life, Meditate & Relax</h4>
        <hr>
        <p class="lead fs-6">Discover inner peace with guided meditations, calming music, and sleep stories.</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    $(document).ready(function() {
        let upNext = [];
        let currentIndex = 0;

        // Persistent Player elements from footer.php
        const playerContainer = $('#audioPlayerContainer');
        const audioEl = $('#audioPlayer')[0];
        const playerTitleEl = $('#currentSongTitle');
        const playerImgEl = $('#currentSongImage');

        // Player.php page elements (display info only)
        const pageTitleEl = $('#pageSongTitle');
        const pageImgEl = $('#pageSongImage');
        const pageCategoryEl = $('#pageSongCategory');

        // --- Load audio by ID into persistent player ---
        function loadAudioPersistent(id, updateHistory = true) {
            $.ajax({
                url: 'fetch_audio.php',
                type: 'GET',
                data: {
                    id
                },
                dataType: 'json',
                success: function(res) {
                    if (res.error) {
                        alert(res.error);
                        return;
                    }

                    const song = res.current;

                    // Update persistent player
                    playerContainer.show();
                    playerTitleEl.text(song.content_name);
                    playerImgEl.attr('src', song.image_url || 'default.png');

                    if (audioEl.src !== song.content_url) {
                        audioEl.src = song.content_url;
                        audioEl.currentTime = 0;
                        audioEl.play();
                        $('#playPauseBtn').text('⏸️');
                    }

                    // Update page info
                    pageTitleEl.text(song.content_name);
                    pageImgEl.attr('src', song.image_url || 'default.png');
                    pageCategoryEl.text(song.content_type.toUpperCase());
                    $('#yearAdded').text(new Date(song.created_at).toLocaleDateString());
                    $('#songName').text(song.content_name);
                    $('#description').text(song.description || 'No description');
                    $('#songCategory').text(song.content_type);

                    // Up Next
                    upNext = res.upNext || [];
                    currentIndex = 0;
                    renderUpNext();

                    // Save to localStorage
                    localStorage.setItem('currentSong', JSON.stringify({
                        id: song.id,
                        title: song.content_name,
                        image: song.image_url || 'default.png',
                        audio: song.content_url,
                        category: song.content_type,
                        description: song.description || '',
                        time: 0
                    }));

                    // Update browser URL
                    if (updateHistory) window.history.pushState({
                        id
                    }, '', `player.php?id=${id}`);
                }
            });
        }

        function renderUpNext() {
            const container = $('#upNextContainer');
            container.empty();
            upNext.forEach((song, idx) => {
                const card = $(`
                <div class="p-2 text-center">
                    <a href="javascript:void(0)" class="text-decoration-none text-white" data-id="${song.id}">
                        <img src="${song.image_url || 'default.png'}"
                             alt="${song.content_name}"
                             style="width:120px;height:120px;object-fit:cover;border-radius:10px;">
                        <p class="small mt-1">${song.content_name}</p>
                    </a>
                </div>
            `);
                card.click(function() {
                    loadAudioPersistent($(this).find('a').data('id'));
                });
                container.append(card);
            });
        }

        // --- Prev/Next Buttons ---
        $('#pagePrevBtn').click(function() {
            if (currentIndex > 0) {
                loadAudioPersistent(upNext[currentIndex - 1].id);
                currentIndex--;
            }
        });
        $('#pageNextBtn').click(function() {
            if (currentIndex < upNext.length - 1) {
                loadAudioPersistent(upNext[currentIndex + 1].id);
                currentIndex++;
            }
        });

        // Handle browser back/forward buttons
        window.onpopstate = function(event) {
            const id = event.state?.id || new URLSearchParams(window.location.search).get('id');
            if (id) loadAudioPersistent(id, false);
        };

        // Initial load: get ID from URL or from localStorage
        const params = new URLSearchParams(window.location.search);
        const initId = params.get('id');
        const savedSong = JSON.parse(localStorage.getItem('currentSong') || '{}');
        if (initId) loadAudioPersistent(initId, false);
        else if (savedSong.id) loadAudioPersistent(savedSong.id, false);

    });
</script>

<?php include 'footer.php'; ?>