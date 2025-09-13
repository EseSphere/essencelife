<!--Discover.php--->
<?php include 'header.php'; ?>
<style>
    #card-bg {
        background-color: #273c75;
        font-size: 14px;
        border-radius: 15px;
    }
</style>
<div id="card-bg" class="card text-white flex justify-start items-start text-start shadow-lg border-rounded p-4 mb-4">
    <h4 class="fw-bold">Discover</h4>
    <p class="text-white fs-5">Tip: Explore new features and personalized recommendations under Discover.</p>
    <p class="text-white fs-5">• Use Discover to find new audios and tips.</p>
</div>

<!-- Dynamic Content Container -->
<div class="container-fluid">
    <div id="contentContainer">
        <!-- Dynamic content loads here -->
    </div>
</div>

<script>
    $(document).ready(function() {
        let currentIndex = 0;
        let songs = [];

        /*** AJAX content loader ***/
        function loadContents(url = 'fetch_discover.php') {
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#contentContainer').html(data);
                    loadSongs();
                    restoreLastSong();
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
                    audio: $(this).data('audio')
                });
            });
        }

        /*** Play a song ***/
        function playAudio(index, resumeTime = 0) {
            if (localStorage.getItem('audioClosed') === 'true') return;

            const song = songs[index];
            if (!song) return;

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
            $('.song-item').eq(index).addClass('playing');

            saveCurrentSong();
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
                playAudio(parseInt(savedIndex), parseFloat(savedTime));
            }
        }

        // Initial load
        loadContents();

        /*** Song click ***/
        $(document).on('click', '.song-item', function() {
            localStorage.setItem('audioClosed', 'false'); // Reset close flag
            const index = $(this).index('.song-item');
            playAudio(index);
        });

        /*** Redirect to player.php when clicking the song title ***/
        $('#currentSongTitle').on('click', function() {
            const index = currentIndex;
            const song = songs[index];
            const currentTime = $('#audioPlayer')[0].currentTime;

            // Redirect to player.php with ID
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
            playAudio(prevIndex);
        });
        $('#nextBtn').on('click', function() {
            const nextIndex = (currentIndex + 1) % songs.length;
            playAudio(nextIndex);
        });

        /*** Auto-play next song ***/
        $('#audioPlayer')[0].addEventListener('ended', function() {
            const nextIndex = (currentIndex + 1) % songs.length;
            playAudio(nextIndex);
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

        /*** Greeting function ***/
        function getGreeting(name) {
            const hour = new Date().getHours();
            let greeting = hour < 12 ? "Good Morning" : hour < 18 ? "Good Afternoon" : "Good Evening";
            return `${greeting} ${name}`;
        }
        document.getElementById("greeting").textContent = getGreeting("Samson");
    });
</script>

<?php include 'footer.php'; ?>