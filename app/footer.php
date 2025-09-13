<!-- Bottom Navbar -->
<nav style="background-color: #001F54; position:fixed; bottom:0; right:0; left:0;" class="navbar navbar-expand-lg navbar-bottom">
  <div class="container justify-content-around">
    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == './home') {
                          echo 'active';
                        } ?>" href="./home"><i class="bi bi-house-door"></i> Home</a>
    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == './playlist') {
                          echo 'active';
                        } ?>" href="./playlist"><i class="bi bi-music-note-list"></i> Play List</a>
    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == './discover') {
                          echo 'active';
                        } ?>" href="./discover"><i class="bi bi-compass"></i> Discover</a>
    <a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == './profile') {
                          echo 'active';
                        } ?>" href="./profile"><i class="bi bi-person-circle"></i> Profile</a>
  </div>
</nav>

<!-- Persistent Audio Player -->
<div id="audioPlayerContainer" style="display:none; position:fixed; bottom:65px; right:0px; left:0px; z-index:999;">
  <div class="player-main w-100">
    <div id="currentSongInfo">
      <div class="player-controls">
        <div class="row w-100 align-items-center">
          <div class="col-3">
            <img id="currentSongImage" src="default.png" alt="Now Playing" style="width:50px; height:50px;">
          </div>
          <div class="col-7 p-0 text-center">
            <div id="currentSongTitle" style="cursor:pointer; text-decoration:underline;">Song Title</div>
            <input type="range" id="progressBar" value="0" min="0" max="100" step="0.1" style="width:100%;">
            <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
          </div>
          <div class="col-2 p-0 flex justify-end items-end text-end">
            <button id="closePlayerBtn">✖</button>
          </div>
          <div class="col-12 p-0 flex justify-center items-center text-center">
            <p class="w-100 m-0">
              <button id="prevBtn">⏮️</button>
              <button id="playPauseBtn">▶️</button>
              <button id="nextBtn">⏭️</button>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <audio id="audioPlayer"></audio>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

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