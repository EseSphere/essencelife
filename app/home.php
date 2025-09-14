<?php include 'header.php'; ?>

<div class="container text-center alert alert-success">
    <h4 class="display-4 font-weight-bold">Essence – Life, <br><small>Meditate & Relax</small></h4>
    <p class="lead">Discover inner peace with guided meditations, calming music, and sleep stories.</p>
</div>

<!-- Greeting Container -->
<div id="greeting" style="font-size: 1.5rem; width:100%; font-weight: 600; color: #fff; text-align: left; margin: 40px 0;"></div>

<!-- Recently Played Section -->
<div id="recentlyPlayedSection" class="container-fluid mt-5 mb-3">
    <h5 class="font-weight-bold w-100 flex justify-start text-start items-start">Recently Played</h5>

    <div class="recently-played-wrapper position-relative mt-4">
        <button id="recentPrev" class="scroll-btn left-btn">◀</button>
        <div id="recentlyPlayedContainer">
            <!-- Recently played songs will be appended here -->
        </div>
        <button id="recentNext" class="scroll-btn right-btn">▶</button>
    </div>
</div>

<!-- Dynamic Content Container -->
<div class="container-fluid mt-5">
    <div id="contentContainer" class="row">
        <!-- Dynamic content loads here -->
    </div>
</div>

<!-- Description Info -->
<div class="card alert alert-success p-2 mb-4 mt-5 shadow-lg border-rounded">
    <h4 class="font-weight-bold">Essence – Life, Meditate & Relax</h4>
    <hr>
    <p class="lead fs-6">Discover inner peace with guided meditations, calming music, and sleep stories.</p>
</div>

<script>
    $(document).ready(function() {
        let currentIndex = 0;
        let songs = [];

        /*** AJAX content loader ***/
        function loadContents(url = 'fetch_contents.php') {
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#contentContainer').html(data);
                    loadSongs();
                    restoreLastSong();
                    renderRecentlyPlayed(); // show recently played on load
                },
                error: function() {
                    $('#contentContainer').html('<p class="text-danger">Failed to load content.</p>');
                }
            });
        }

        /*** Load songs from page ***/
        function loadSongs() {
            songs = [];
            $('.song-item').each(function() {
                songs.push({
                    title: $(this).data('title'),
                    image: $(this).data('image'),
                    audio: $(this).data('audio'),
                    id: $(this).data('id'),
                    category: $(this).data('category') || '',
                    content_type: $(this).data('content_type') || '',
                    description: $(this).data('description') || '',
                    isNew: $(this).data('isnew') || false
                });
            });
        }

        /*** Play a song by song object (works for main and recently played) ***/
        function playAudioBySong(song, resumeTime = 0) {
            if (localStorage.getItem('audioClosed') === 'true') return;

            const index = songs.findIndex(s => s.audio === song.audio);
            if (index === -1) return; // song not in current list

            $('#currentSongTitle').text(song.title);
            $('#currentSongImage').attr('src', song.image || 'default.png');
            $('#audioPlayer').attr('src', song.audio);
            $('#audioPlayerContainer').fadeIn();

            const audioEl = $('#audioPlayer')[0];
            audioEl.currentTime = resumeTime;
            audioEl.play();
            $('#playPauseBtn').text('⏸️');
            currentIndex = index;

            $('.song-item').removeClass('playing');
            $('.song-item').filter(`[data-audio="${song.audio}"]`).addClass('playing');

            saveCurrentSong();
            saveRecentlyPlayed(song);
            scrollToRecentSong(song.audio);
        }

        /*** Save current song state ***/
        function saveCurrentSong() {
            const audioEl = $('#audioPlayer')[0];
            localStorage.setItem('currentSongIndex', currentIndex);
            localStorage.setItem('currentSongTime', audioEl.currentTime);
        }

        /*** Restore last song state ***/
        function restoreLastSong() {
            const savedIndex = localStorage.getItem('currentSongIndex');
            const savedTime = localStorage.getItem('currentSongTime');
            const audioClosed = localStorage.getItem('audioClosed');

            if (audioClosed === 'true') {
                $('#audioPlayerContainer').hide();
                return;
            }

            if (savedIndex !== null && songs[savedIndex]) {
                playAudioBySong(songs[savedIndex], parseFloat(savedTime));
            }
        }

        /*** Save song to recently played list in localStorage ***/
        function saveRecentlyPlayed(song) {
            let recent = JSON.parse(localStorage.getItem('recentlyPlayed') || "[]");
            recent = recent.filter(item => item.audio !== song.audio);
            recent.unshift(song);
            if (recent.length > 10) recent.pop();
            localStorage.setItem('recentlyPlayed', JSON.stringify(recent));
            renderRecentlyPlayed();
        }

        /*** Retrieve recently played songs ***/
        function getRecentlyPlayed() {
            return JSON.parse(localStorage.getItem('recentlyPlayed') || "[]");
        }

        /*** Render Recently Played Songs (horizontal row) ***/
        function renderRecentlyPlayed() {
            const recent = getRecentlyPlayed();
            const section = $('#recentlyPlayedSection');
            const container = $('#recentlyPlayedContainer');
            container.empty();

            if (recent.length === 0) {
                section.hide(); // Hide entire section
                return;
            } else {
                section.show(); // Show section if there is content
            }

            recent.forEach((song, idx) => {
                const songHtml = `
        <div style="height:200px; width:200px;" class="song-item recently-played" 
             data-title="${song.title}" 
             data-audio="${song.audio}" 
             data-image="${song.image || 'default.png'}" 
             data-id="${song.id}" 
             data-category="${song.category || ''}"
             data-content_type="${song.content_type || ''}"
             data-description="${song.description || ''}"
             data-isnew="${song.isNew}"
             data-index="${idx}">
            <img src="${song.image || 'default.png'}" alt="${song.title}">
            <div class="song-info">
                <p class="song-title">
                    ${song.title}
                    ${song.isNew ? '<span class="badge bg-danger badge-new">New</span>' : ''}
                </p>
                <p class="song-type">${song.content_type || ''}</p>
                <p class="song-description">${song.description || ''}</p>
            </div>
        </div>
        `;
                container.append(songHtml);
            });

            // Highlight currently playing song
            const currentSong = songs[currentIndex];
            if (currentSong) {
                container.find(`.song-item[data-audio="${currentSong.audio}"]`).addClass('playing');
                scrollToRecentSong(currentSong.audio);
            }
        }


        /*** Scroll to currently playing song in recently played ***/
        function scrollToRecentSong(audio) {
            const container = $('#recentlyPlayedContainer');
            const songEl = container.find(`.song-item[data-audio="${audio}"]`);
            if (songEl.length) {
                const left = songEl.position().left + container.scrollLeft() - 50; // padding
                container.animate({
                    scrollLeft: left
                }, 300);
            }
        }

        /*** Scroll arrows functionality ***/
        const scrollAmount = 300; // adjust scroll distance per click
        $('#recentNext').on('click', function() {
            $('#recentlyPlayedContainer').animate({
                scrollLeft: '+=' + scrollAmount
            }, 300);
        });

        $('#recentPrev').on('click', function() {
            $('#recentlyPlayedContainer').animate({
                scrollLeft: '-=' + scrollAmount
            }, 300);
        });

        // Initial load
        loadContents();

        /*** Song click handlers ***/
        // Main content
        $(document).on('click', '.song-item', function() {
            localStorage.setItem('audioClosed', 'false');
            const song = {
                title: $(this).data('title'),
                audio: $(this).data('audio'),
                image: $(this).data('image') || 'default.png',
                id: $(this).data('id'),
                category: $(this).data('category') || '',
                content_type: $(this).data('content_type') || '',
                description: $(this).data('description') || '',
                isNew: $(this).data('isnew') || false
            };
            playAudioBySong(song);
        });

        // Recently played
        $(document).on('click', '#recentlyPlayedContainer .song-item', function() {
            const song = {
                title: $(this).data('title'),
                audio: $(this).data('audio'),
                image: $(this).data('image') || 'default.png',
                id: $(this).data('id'),
                category: $(this).data('category') || '',
                content_type: $(this).data('content_type') || '',
                description: $(this).data('description') || '',
                isNew: $(this).data('isnew') || false
            };
            localStorage.setItem('audioClosed', 'false');
            playAudioBySong(song);
        });

        /*** Redirect to player.php when clicking the song title ***/
        $('#currentSongTitle').on('click', function() {
            const index = currentIndex;
            const song = songs[index];
            const currentTime = $('#audioPlayer')[0].currentTime;
            const url = `player.php?id=${$('.song-item').eq(index).data('id')}&time=${currentTime}`;
            window.location.href = url;
        });

        /*** Play/Pause button ***/
        $('#playPauseBtn').on('click', function() {
            const audio = $('#audioPlayer')[0];
            if (audio.paused) {
                audio.play();
                $(this).text('⏸️');
            } else {
                audio.pause();
                $(this).text('▶️');
            }
        });

        /*** Close player ***/
        $('#closePlayerBtn').on('click', function() {
            $('#audioPlayer')[0].pause();
            $('#audioPlayerContainer').fadeOut();
            $('.song-item').removeClass('playing');
            localStorage.setItem('audioClosed', 'true');
        });

        /*** Previous/Next buttons ***/
        $('#prevBtn').on('click', function() {
            const prevIndex = (currentIndex - 1 + songs.length) % songs.length;
            playAudioBySong(songs[prevIndex]);
        });
        $('#nextBtn').on('click', function() {
            const nextIndex = (currentIndex + 1) % songs.length;
            playAudioBySong(songs[nextIndex]);
        });

        /*** Auto-play next song ***/
        $('#audioPlayer')[0].addEventListener('ended', function() {
            const nextIndex = (currentIndex + 1) % songs.length;
            playAudioBySong(songs[nextIndex]);
        });

        /*** Update progress bar ***/
        const audio = $('#audioPlayer')[0];
        audio.addEventListener('timeupdate', function() {
            const progress = (audio.currentTime / audio.duration) * 100;
            $('#progressBar').val(progress || 0);

            const minutes = Math.floor(audio.currentTime / 60);
            const seconds = Math.floor(audio.currentTime % 60).toString().padStart(2, '0');
            $('#currentTime').text(`${minutes}:${seconds}`);

            const durMinutes = Math.floor(audio.duration / 60) || 0;
            const durSeconds = Math.floor(audio.duration % 60).toString().padStart(2, '0');
            $('#duration').text(`${durMinutes}:${durSeconds}`);

            saveCurrentSong();
        });

        $('#progressBar').on('input', function() {
            audio.currentTime = (audio.duration * $(this).val()) / 100;
            saveCurrentSong();
        });

        /*** Search/filter songs ***/
        $('#searchInput').on('keyup', function() {
            const filter = $(this).val().toLowerCase();
            $('.song-item').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(filter));
            });
        });

        // Pass PHP variable to JS
        const userName = "<?php echo $_SESSION['name']; ?>";

        function getGreeting(name) {
            const hour = new Date().getHours();
            let greeting = hour < 12 ? "Good Morning" :
                hour < 18 ? "Good Afternoon" :
                "Good Evening";
            return `${greeting} ${name}`;
        }

        document.getElementById("greeting").textContent = getGreeting('');

    });
</script>

<?php include 'footer.php'; ?>