<?php include 'header.php'; ?>

<link rel="stylesheet" href="./css/player_style.css">

<div class="container-fluid">
    <!-- Compact Main Player -->
    <div id="card-bg" class="card text-white shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
        <div class="row g-0 align-items-center">
            <!-- Image -->
            <div class="col-md-4 col-12">
                <img id="currentSongImage" src="default.png"
                    class="w-100 h-100 object-fit-cover"
                    alt="Cover"
                    style="max-height:200px;object-fit:cover;">
            </div>

            <!-- Info + Controls -->
            <div class="col-md-8 col-12 p-3">
                <p id="currentCategory" class="text-uppercase small mb-1">Category</p>
                <h4 id="currentSongTitle" class="fw-bold text-success mb-2">Song Title</h4>

                <!-- Audio Player -->
                <audio id="audioPlayer" controls autoplay style="width:100%;">
                    <source src="" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>

                <!-- Controls -->
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <button id="prevBtn" class="btn btn-outline-light btn-sm rounded-circle">
                        <i class="bi bi-skip-backward-fill fs-6"></i>
                    </button>
                    <button id="nextBtn" class="btn btn-outline-light btn-sm rounded-circle">
                        <i class="bi bi-skip-forward-fill fs-6"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Up Next -->
    <div id="card-bg" class="card flex justify-start items-start text-start text-white p-2 mt-4 mb-3 shadow-lg border-rounded">
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

        /*** Load audio by ID ***/
        function loadAudio(id, updateHistory = true) {
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
                    $('#currentSongTitle').text(song.content_name);
                    $('#currentCategory').text(song.content_type.toUpperCase());
                    $('#currentSongImage').attr('src', song.image_url || 'default.png');
                    $('#audioPlayer source').attr('src', song.content_url);
                    $('#audioPlayer')[0].load();
                    $('#audioPlayer')[0].play();

                    $('#yearAdded').text(new Date(song.created_at).toLocaleDateString());
                    $('#songName').text(song.content_name);
                    $('#description').text(song.description || 'No description');
                    $('#songCategory').text(song.content_type);

                    upNext = res.upNext || [];
                    currentIndex = 0;
                    renderUpNext();

                    // Update browser URL without reloading
                    if (updateHistory) {
                        const newUrl = `player.php?id=${id}`;
                        window.history.pushState({
                            id
                        }, '', newUrl);
                    }
                }
            });
        }

        /*** Render Up Next songs ***/
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
                    loadAudio($(this).find('a').data('id'));
                });
                container.append(card);
            });
        }

        /*** Prev / Next Buttons ***/
        $('#prevBtn').click(function() {
            if (currentIndex > 0) {
                loadAudio(upNext[currentIndex - 1].id);
                currentIndex--;
            }
        });

        $('#nextBtn').click(function() {
            if (currentIndex < upNext.length - 1) {
                loadAudio(upNext[currentIndex + 1].id);
                currentIndex++;
            }
        });

        /*** Handle browser back/forward buttons ***/
        window.onpopstate = function(event) {
            const id = event.state?.id || new URLSearchParams(window.location.search).get('id');
            if (id) loadAudio(id, false);
        };

        // Initial load: first audio ID from URL parameter
        const params = new URLSearchParams(window.location.search);
        const initId = params.get('id') || 1;
        loadAudio(initId, false);
    });
</script>

<?php include 'footer.php'; ?>