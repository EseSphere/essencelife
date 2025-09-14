</div> <!-- end of #mainContent -->
</div>
<?php
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
  $user = $conn->query("SELECT * FROM users WHERE user_id='$user_id'")->fetch_assoc();
  if ($user && !empty($user['image'])) {
    $profileImage = $user['image'];
  }
}
?>
<!-- Bottom Navbar -->
<nav style="background-color: #0f4c81; position:fixed; bottom:0; width:100%;" class="navbar navbar-expand-lg navbar-bottom">
  <div class="container justify-content-around">
    <a class="nav-link" href="./home"><i class="bi bi-house-door"></i> Home</a>
    <a class="nav-link" href="./playlist"><i class="bi bi-music-note-list"></i> Play List</a>
    <a class="nav-link" href="./discover"><i class="bi bi-compass"></i> Discover</a>
    <a class="nav-link" href="./profile" style="display:flex; align-items:center; gap:5px;">
      <img
        id="profileImg"
        src="<?= htmlspecialchars($profileImage) ?>"
        alt="Profile"
        class="rounded-circle"
        style="width:20px; height:20px; object-fit:cover; cursor:pointer;"
        onerror="this.style.display='none'; document.getElementById('profileIcon').style.display='inline-block';">
      <i id="profileIcon" class="bi bi-person-circle" style="font-size:20px; display:none;"></i>
      Profile
    </a>
  </div>
</nav>

<!-- Mini Player (Collapsed) -->
<div id="miniPlayer"
  style="display:none; position:fixed; bottom:65px; left:0; right:0; z-index:1000; 
            background:rgba(15,76,129,0.95); backdrop-filter:blur(8px); 
            color:#fff; border-top-left-radius:15px; border-top-right-radius:15px; 
            box-shadow:0 -4px 12px rgba(0,0,0,0.3); padding:10px;">
  <div class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <img id="miniSongImage" src="default.png"
        class="rounded me-2 shadow-sm"
        style="width:40px;height:40px;object-fit:cover;">
      <div class="me-2">
        <div id="miniSongTitle" class="fw-bold small text-truncate" style="max-width:150px;">Song Title</div>
        <small id="miniSongCategory" class="text-light">Category</small>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button id="miniPlayPauseBtn" class="btn btn-light btn-sm rounded-circle">
        <i class="bi bi-play-fill text-dark"></i>
      </button>
      <button id="expandPlayerBtn" class="btn btn-outline-light btn-sm rounded-circle">
        <i class="bi bi-chevron-up"></i>
      </button>
    </div>
  </div>
</div>

<!-- Full Player (Expanded) -->
<div id="audioPlayerContainer"
  style="display:none; position:fixed; bottom:65px; left:0; right:0; z-index:999; 
            background:rgba(15,76,129,0.95); backdrop-filter:blur(10px); 
            color:#fff; border-top-left-radius:20px; border-top-right-radius:20px; 
            box-shadow:0 -5px 20px rgba(0,0,0,0.4); font-family:'Segoe UI',sans-serif;">

  <!-- Player Header -->
  <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color:rgba(255,255,255,0.1)!important;">
    <div class="d-flex align-items-center">
      <img id="currentSongImage" src="default.png"
        class="rounded shadow-sm me-3"
        style="width:55px;height:55px;object-fit:cover;">
      <div>
        <div id="currentSongTitle" class="fw-bold text-truncate" style="max-width:200px; font-size:1rem;">Song Title</div>
        <small id="currentSongCategory" class="text-light">Category</small>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button id="minimizePlayerBtn" class="btn btn-sm btn-outline-light rounded-circle">
        <i class="bi bi-chevron-down"></i>
      </button>
      <button id="closePlayerBtn" class="btn btn-sm btn-outline-light rounded-circle">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  </div>

  <!-- Progress Bar -->
  <div class="px-3 mt-2">
    <input type="range" id="progressBar" value="0" min="0" max="100" step="0.1"
      class="form-range"
      style="accent-color:#ffce54; background:linear-gradient(to right,#ffce54,#ff6f61);">
    <div class="d-flex justify-content-between small text-light">
      <span id="currentTime">0:00</span>
      <span id="duration">0:00</span>
    </div>
  </div>

  <!-- Player Controls -->
  <div class="text-center py-3">
    <button id="prevBtn" class="btn btn-outline-light btn-lg rounded-circle mx-2 shadow-sm">
      <i class="bi bi-skip-backward-fill"></i>
    </button>
    <button id="playPauseBtn" class="btn btn-light btn-lg rounded-circle mx-3 shadow">
      <i class="bi bi-play-fill text-dark"></i>
    </button>
    <button id="nextBtn" class="btn btn-outline-light btn-lg rounded-circle mx-2 shadow-sm">
      <i class="bi bi-skip-forward-fill"></i>
    </button>
  </div>

  <!-- Expandable Details + Related -->
  <div class="accordion accordion-flush" id="playerDetailsAccordion">
    <!-- Song Details -->
    <div class="accordion-item" style="background:transparent; color:#fff;">
      <h2 class="accordion-header" id="headingDetails">
        <button class="accordion-button collapsed text-white" type="button"
          data-bs-toggle="collapse" data-bs-target="#collapseDetails"
          aria-expanded="false" aria-controls="collapseDetails"
          style="background:rgba(255,255,255,0.05); font-weight:500;">
          <i class="bi bi-info-circle me-2"></i> Song Details
        </button>
      </h2>
      <div id="collapseDetails" class="accordion-collapse collapse" aria-labelledby="headingDetails" data-bs-parent="#playerDetailsAccordion">
        <div class="accordion-body text-light">
          <p id="currentSongDescription" class="mb-0">No description available.</p>
        </div>
      </div>
    </div>

    <!-- Related Audios -->
    <div class="accordion-item" style="background:transparent; color:#fff;">
      <h2 class="accordion-header" id="headingRelated">
        <button class="accordion-button collapsed text-white" type="button"
          data-bs-toggle="collapse" data-bs-target="#collapseRelated"
          aria-expanded="false" aria-controls="collapseRelated"
          style="background:rgba(255,255,255,0.05); font-weight:500;">
          <i class="bi bi-music-note-list me-2"></i> Related Audios
        </button>
      </h2>
      <div id="collapseRelated" class="accordion-collapse collapse" aria-labelledby="headingRelated" data-bs-parent="#playerDetailsAccordion">
        <div class="accordion-body">
          <div id="relatedAudios" class="d-flex flex-wrap gap-3">
            <!-- Related audios dynamically injected -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <audio id="audioPlayer"></audio>
</div>

<!-- Feelings Modal -->
<div class="modal fade" id="feelingsModal" tabindex="-1" aria-labelledby="feelingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background-color: #0f4c81; color: #fff; border-radius: 15px;">
      <div class="modal-header border-0">
        <h5 class="modal-title w-100 text-center" id="feelingsModalLabel" style="font-size:1.3rem;">How are you feeling right now?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="d-flex justify-content-around flex-wrap mt-3">
          <div class="text-center m-2">
            <button class="btn btn-dark emoji-btn" data-feeling="calm" style="font-size:1.5rem;">😌</button>
            <div style="margin-top:5px; font-weight:bold;">Calm</div>
          </div>
          <div class="text-center m-2">
            <button class="btn btn-dark emoji-btn" data-feeling="happy" style="font-size:1.5rem;">😊</button>
            <div style="margin-top:5px; font-weight:bold;">Happy</div>
          </div>
          <div class="text-center m-2">
            <button class="btn btn-dark emoji-btn" data-feeling="tired" style="font-size:1.5rem;">😴</button>
            <div style="margin-top:5px; font-weight:bold;">Tired</div>
          </div>
          <div class="text-center m-2">
            <button class="btn btn-dark emoji-btn" data-feeling="sad" style="font-size:1.5rem;">😢</button>
            <div style="margin-top:5px; font-weight:bold;">Sad</div>
          </div>
          <div class="text-center m-2">
            <button class="btn btn-dark emoji-btn" data-feeling="excited" style="font-size:1.5rem;">🤩</button>
            <div style="margin-top:5px; font-weight:bold;">Excited</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  AOS.init();

  $(document).ready(function() {
    // Elements
    const playerContainer = $('#audioPlayerContainer');
    const miniPlayer = $('#miniPlayer');
    const audioEl = $('#audioPlayer')[0];

    const titleEl = $('#currentSongTitle');
    const imgEl = $('#currentSongImage');
    const categoryEl = $('#currentSongCategory');
    const descEl = $('#currentSongDescription');
    const relatedEl = $('#relatedAudios');

    const miniTitleEl = $('#miniSongTitle');
    const miniImgEl = $('#miniSongImage');
    const miniCategoryEl = $('#miniSongCategory');

    // Restore panel state
    function restorePanelState() {
      const state = localStorage.getItem('audioPanelState');
      if (state === 'expanded') {
        playerContainer.show();
        miniPlayer.hide();
      } else if (state === 'collapsed') {
        playerContainer.hide();
        miniPlayer.show();
      }
    }

    // Show / restore audio
    function restoreAudio() {
      const savedSong = JSON.parse(localStorage.getItem('currentSong') || '{}');
      if (!savedSong.audio) return;

      // Mini Player
      miniTitleEl.text(savedSong.title || '');
      miniImgEl.attr('src', savedSong.image || 'default.png');
      miniCategoryEl.text(savedSong.category || '');
      miniPlayer.show();

      // Full Player
      titleEl.text(savedSong.title || '');
      imgEl.attr('src', savedSong.image || 'default.png');
      categoryEl.text(savedSong.category || '');
      descEl.text(savedSong.description || 'No description available.');

      // Related Audios
      relatedEl.empty();
      if (savedSong.related && savedSong.related.length) {
        savedSong.related.forEach(rel => {
          relatedEl.append(`
            <div class="card bg-transparent border-light text-white shadow-sm" style="width:100px;cursor:pointer;" 
                 onclick='playPersistentAudio(${JSON.stringify(rel)})'>
              <img src="${rel.image || 'default.png'}" class="card-img-top rounded" style="height:80px;object-fit:cover;">
              <div class="card-body p-1 text-center">
                <small class="fw-bold d-block text-truncate">${rel.title}</small>
              </div>
            </div>
          `);
        });
      }

      // Play audio
      if (audioEl.src !== savedSong.audio) {
        audioEl.src = savedSong.audio;
        audioEl.currentTime = savedSong.time || 0;
        audioEl.play();
        $('#playPauseBtn i, #miniPlayPauseBtn i').removeClass('bi-play-fill').addClass('bi-pause-fill');
      }
    }

    // Save audio
    function saveAudio() {
      const savedSong = JSON.parse(localStorage.getItem('currentSong') || '{}');
      savedSong.title = titleEl.text();
      savedSong.image = imgEl.attr('src');
      savedSong.audio = audioEl.src;
      savedSong.category = categoryEl.text();
      savedSong.description = descEl.text();
      savedSong.time = audioEl.currentTime;
      localStorage.setItem('currentSong', JSON.stringify(savedSong));
    }

    // Play / Pause buttons
    $('#playPauseBtn, #miniPlayPauseBtn').click(function() {
      if (audioEl.paused) {
        audioEl.play();
        $('#playPauseBtn i, #miniPlayPauseBtn i').removeClass('bi-play-fill').addClass('bi-pause-fill');
      } else {
        audioEl.pause();
        $('#playPauseBtn i, #miniPlayPauseBtn i').removeClass('bi-pause-fill').addClass('bi-play-fill');
      }
    });

    audioEl.addEventListener('timeupdate', function() {
      const progress = (audioEl.currentTime / audioEl.duration) * 100 || 0;
      $('#progressBar').val(progress);
      const minutes = Math.floor(audioEl.currentTime / 60);
      const seconds = Math.floor(audioEl.currentTime % 60).toString().padStart(2, '0');
      $('#currentTime').text(`${minutes}:${seconds}`);
      const durMinutes = Math.floor(audioEl.duration / 60) || 0;
      const durSeconds = Math.floor(audioEl.duration % 60).toString().padStart(2, '0');
      $('#duration').text(`${durMinutes}:${durSeconds}`);
      saveAudio();
    });

    $('#progressBar').on('input', function() {
      audioEl.currentTime = (audioEl.duration * $(this).val()) / 100;
      saveAudio();
    });

    // Expand / Collapse / Close with state saving
    $('#expandPlayerBtn').click(function() {
      miniPlayer.slideUp(300);
      playerContainer.slideDown(300);
      localStorage.setItem('audioPanelState', 'expanded');
    });

    $('#minimizePlayerBtn').click(function() {
      playerContainer.slideUp(300);
      miniPlayer.slideDown(300);
      localStorage.setItem('audioPanelState', 'collapsed');
    });

    $('#closePlayerBtn').click(function() {
      playerContainer.slideUp(300);
      miniPlayer.slideUp(300);
      audioEl.pause();
      localStorage.removeItem('currentSong');
      localStorage.removeItem('audioPanelState');
      $('#playPauseBtn i, #miniPlayPauseBtn i').removeClass('bi-pause-fill').addClass('bi-play-fill');
    });

    // Play persistent audio
    window.playPersistentAudio = function(song) {
      localStorage.setItem('currentSong', JSON.stringify({
        id: song.id || 0,
        title: song.title,
        image: song.image || 'default.png',
        audio: song.url,
        category: song.category || '',
        content_type: song.content_type || '',
        description: song.description || '',
        related: song.related || [],
        time: 0
      }));
      restoreAudio();
      restorePanelState();
    };

    restoreAudio();
    restorePanelState();

    // --- Feelings Modal ---
    if (!sessionStorage.getItem('feelingsModalShown')) {
      setTimeout(function() {
        const modalEl = document.getElementById('feelingsModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        sessionStorage.setItem('feelingsModalShown', 'true');
      }, 6000);
    }

    // --- AJAX Page Loading ---
    // --- AJAX Page Loading (SPA) ---
    function loadPage(url) {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
          // Replace #mainContent
          const newContent = $(data).find('#mainContent').html();
          $('#mainContent').html(newContent);

          // Re-initialize AOS
          AOS.init();

          // Re-bind dynamic songs
          bindDynamicSongs();

          // Re-initialize playlist module if playlist page loaded
          if ($('#playlistPage').length && window.PlaylistModule) {
            window.PlaylistModule.init();
          }

          // Highlight active bottom navbar link
          $('.navbar-bottom .nav-link').removeClass('active');
          $('.navbar-bottom .nav-link[href="' + url + '"]').addClass('active');

          // Update browser URL without reload
          history.pushState(null, '', url);
        },
        error: function() {
          alert('Failed to load page.');
        }
      });
    }

    // Bottom navbar SPA navigation
    $('.navbar-bottom .nav-link').click(function(e) {
      e.preventDefault();
      const url = $(this).attr('href');
      loadPage(url);
    });

    // Handle browser back/forward buttons
    window.onpopstate = function() {
      loadPage(location.pathname);
    };


    // Bind dynamic songs
    function bindDynamicSongs() {
      $(document).on('click', '.song-item', function() {
        const songData = {
          id: $(this).data('id'),
          title: $(this).data('title'),
          image: $(this).data('image') || 'default.png',
          url: $(this).data('audio'),
          category: $(this).data('category') || '',
          content_type: $(this).data('content_type') || '',
          description: $(this).data('description') || '',
          related: $(this).data('related') ? JSON.parse($(this).attr('data-related')) : []
        };
        playPersistentAudio(songData);
      });
    }

    bindDynamicSongs();
  });
</script>