<?php include 'header.php'; ?>

<div data-aos="fade-up" data-aos-anchor-placement="bottom-bottom" data-aos-duration="900" class="container text-center alert alert-success">
    <h4 class="display-4 font-weight-bold">Essence – Life, <br><small>Meditate & Relax</small></h4>
    <p class="lead">Discover inner peace with guided meditations, calming music, and sleep stories.</p>
</div>

<div id="greeting" style="font-size: 1.5rem; width:100%; font-weight: 600; color: #fff; text-align: left; margin: 40px 0;"></div>

<!-- Recently Played Section -->
<div id="recentlyPlayedSection" class="container-fluid mt-5 mb-3">
    <h5 class="font-weight-bold w-100 flex justify-start text-start items-start">Recently Played</h5>
    <div class="recently-played-wrapper position-relative mt-4">
        <button id="recentPrev" class="scroll-btn left-btn">◀</button>
        <div id="recentlyPlayedContainer"></div>
        <button id="recentNext" class="scroll-btn right-btn">▶</button>
    </div>
</div>

<div class="container-fluid mt-5">
    <div id="contentContainer" class="row"></div>
</div>

<div class="card alert alert-success p-2 mb-4 mt-5 shadow-lg border-rounded">
    <h4 class="font-weight-bold">Essence – Life, Meditate & Relax</h4>
    <hr>
    <p class="lead fs-6">Discover inner peace with guided meditations, calming music, and sleep stories.</p>
</div>

<div class="card alert alert-danger p-2 mb-4 mt-5 shadow-lg border-rounded">
    <p class="lead fs-6">Essence is your sanctuary for mindfulness and relaxation. Immerse yourself in guided meditations designed to help you find balance and tranquility. Reduce stress, improve focus, and enhance well-being with our sessions.</p>
</div>

<script>
    $(document).ready(function() {
        const playerContainer = $('#audioPlayerContainer');
        const miniPlayer = $('#miniPlayer');
        const audioEl = $('#audioPlayer')[0];
        const playerTitleEl = $('#currentSongTitle');
        const playerImgEl = $('#currentSongImage');
        const playerCategoryEl = $('#currentSongCategory');
        const playerDescriptionEl = $('#currentSongDescription');

        let songs = [];

        /*** AJAX content loader ***/
        function loadContents(url = 'fetch_contents.php') {
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#contentContainer').html(data);
                    loadSongs();
                    renderRecentlyPlayed();
                },
                error: function() {
                    $('#contentContainer').html('<p class="text-danger">Failed to load content.</p>');
                }
            });
        }

        function loadSongs() {
            songs = [];
            $('.song-item').each(function() {
                songs.push({
                    title: $(this).data('title'),
                    audio: $(this).data('audio'),
                    image: $(this).data('image'),
                    id: $(this).data('id'),
                    category: $(this).data('category') || '',
                    description: $(this).data('description') || '',
                    isNew: $(this).data('isnew') || false
                });
            });
        }

        /*** Recently Played ***/
        function getRecentlyPlayed() {
            return JSON.parse(localStorage.getItem('recentlyPlayed') || "[]");
        }

        function saveRecentlyPlayed(song) {
            let recent = getRecentlyPlayed();
            recent = recent.filter(item => item.id !== song.id);
            recent.unshift(song);
            if (recent.length > 10) recent.pop();
            localStorage.setItem('recentlyPlayed', JSON.stringify(recent));
        }

        function renderRecentlyPlayed() {
            const recent = getRecentlyPlayed();
            const section = $('#recentlyPlayedSection');
            const container = $('#recentlyPlayedContainer');
            container.empty();
            if (recent.length === 0) {
                section.hide();
                return;
            }
            section.show();
            recent.forEach((song, idx) => {
                container.append(`
                <div class="song-item recently-played" data-id="${song.id}" data-title="${song.title}" data-audio="${song.audio}" data-image="${song.image || 'default.png'}" data-category="${song.category || ''}" data-description="${song.description || ''}" data-index="${idx}" style="height:200px;width:200px;">
                    <img src="${song.image || 'default.png'}" alt="${song.title}">
                    <div class="song-info">
                        <p class="song-title">${song.title} ${song.isNew ? '<span class="badge bg-danger badge-new">New</span>' : ''}</p>
                        <p class="song-description">${song.description || ''}</p>
                    </div>
                </div>
            `);
            });
        }

        /*** Scroll buttons ***/
        $('#recentNext').click(() => $('#recentlyPlayedContainer').animate({
            scrollLeft: '+=300'
        }, 300));
        $('#recentPrev').click(() => $('#recentlyPlayedContainer').animate({
            scrollLeft: '-=300'
        }, 300));

        /*** Play song in persistent player ***/
        function playSong(song) {
            localStorage.setItem('currentSong', JSON.stringify(song));
            saveRecentlyPlayed(song);

            // Show mini player only; do NOT auto-expand
            miniPlayer.show();
            playerContainer.hide();

            playerTitleEl.text(song.title);
            playerImgEl.attr('src', song.image || 'default.png');
            playerCategoryEl.text(song.category || '');
            playerDescriptionEl.text(song.description || 'No description available.');

            if (audioEl.src !== song.audio) {
                audioEl.src = song.audio;
                audioEl.currentTime = song.time || 0;
            }
            audioEl.play();
            $('#playPauseBtn i, #miniPlayPauseBtn i').removeClass('bi-play-fill').addClass('bi-pause-fill');
        }

        $(document).on('click', '.song-item, .recently-played', function() {
            const song = {
                id: $(this).data('id'),
                title: $(this).data('title'),
                audio: $(this).data('audio'),
                image: $(this).data('image') || 'default.png',
                category: $(this).data('category') || '',
                description: $(this).data('description') || '',
                time: 0
            };
            playSong(song);
        });

        /*** Load persistent player from localStorage on page load ***/
        const savedSong = JSON.parse(localStorage.getItem('currentSong') || '{}');
        if (savedSong.audio) {
            miniPlayer.show();
            playerContainer.hide();
            playerTitleEl.text(savedSong.title);
            playerImgEl.attr('src', savedSong.image || 'default.png');
            playerCategoryEl.text(savedSong.category || '');
            playerDescriptionEl.text(savedSong.description || 'No description available.');
            if (audioEl.src !== savedSong.audio) {
                audioEl.src = savedSong.audio;
                audioEl.currentTime = savedSong.time || 0;
            }
            // Keep playing
            audioEl.play();
            $('#playPauseBtn i, #miniPlayPauseBtn i').removeClass('bi-play-fill').addClass('bi-pause-fill');
        }

        /*** Update current time continuously ***/
        $('#audioPlayer').on('timeupdate', function() {
            const saved = JSON.parse(localStorage.getItem('currentSong') || '{}');
            if (saved.audio) {
                saved.time = this.currentTime;
                localStorage.setItem('currentSong', JSON.stringify(saved));
            }
        });

        /*** Greeting ***/
        function getGreeting() {
            const hour = new Date().getHours();
            return hour < 12 ? "Good Morning" : hour < 18 ? "Good Afternoon" : "Good Evening";
        }
        $('#greeting').text(getGreeting());

        // Initial AJAX content load
        loadContents();
    });
</script>

<?php include 'footer.php'; ?>