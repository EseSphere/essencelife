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

<!-- Persistent Audio Player -->
<div id="audioPlayerContainer" style="display:none; position:fixed; bottom:65px; left:0; right:0; z-index:999;">
  <div class="player-main w-100">
    <div id="currentSongInfo">
      <div class="player-controls">
        <div class="row align-items-center">
          <div class="col-3"><img id="currentSongImage" src="default.png" style="width:50px;height:50px;"></div>
          <div class="col-7 text-center">
            <div id="currentSongTitle">Song Title</div>
            <input type="range" id="progressBar" value="0" min="0" max="100" step="0.1" style="width:100%;">
            <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
          </div>
          <div class="col-2 text-end">
            <button id="closePlayerBtn">✖</button>
          </div>
          <div class="col-12 text-center">
            <button id="prevBtn">⏮️</button>
            <button id="playPauseBtn">▶️</button>
            <button id="nextBtn">⏭️</button>
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
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
  AOS.init();
  $(document).ready(function() {
    // Show feelings modal after 6 seconds
    setTimeout(function() {
      var feelingsModal = new bootstrap.Modal(document.getElementById('feelingsModal'));
      feelingsModal.show();
    }, 6000);

    // Mapping moods to recommended audio
    const moodAudioMap = {
      calm: [{
          title: 'Acoustic Sunrise',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
          image: 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?w=400'
        },
        {
          title: 'Calm Breeze',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3',
          image: 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400'
        }
      ],
      happy: [{
          title: 'Ocean Waves',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
          image: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400'
        },
        {
          title: 'Mountain Echoes',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3',
          image: 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=400'
        }
      ],
      tired: [{
          title: 'Thunderstorm',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-15.mp3',
          image: 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=400'
        },
        {
          title: 'Desert Winds',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-16.mp3',
          image: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=400'
        }
      ],
      sad: [{
          title: 'Forest Whisper',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-7.mp3',
          image: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=400'
        },
        {
          title: 'Courage Story',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3',
          image: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=400'
        }
      ],
      excited: [{
          title: 'Morning Motivation',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3',
          image: 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=400'
        },
        {
          title: 'Success Path',
          url: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3',
          image: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400'
        }
      ]
    };

    // Handle emoji button clicks with random selection
    $('.emoji-btn').click(function() {
      var feeling = $(this).data('feeling');

      // Hide modal
      var modalEl = document.getElementById('feelingsModal');
      var modal = bootstrap.Modal.getInstance(modalEl);
      modal.hide();

      // Randomly pick a recommended audio for the mood
      var moodTracks = moodAudioMap[feeling];
      var randomIndex = Math.floor(Math.random() * moodTracks.length);
      var audioData = moodTracks[randomIndex];

      // Show and update audio player
      $('#audioPlayerContainer').show();
      $('#currentSongTitle').text(audioData.title);
      $('#currentSongImage').attr('src', audioData.image);
      var player = document.getElementById('audioPlayer');
      player.src = audioData.url;
      player.play();

      console.log('Playing', feeling, audioData.title);
    });

    // AJAX Navigation
    function loadPage(url) {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
          var newContent = $(data).find('#mainContent').html();
          $('#mainContent').html(newContent);

          $('.navbar-bottom .nav-link').removeClass('active');
          $('.navbar-bottom .nav-link[href="' + url + '"]').addClass('active');

          history.pushState(null, '', url);
          AOS.init();
        },
        error: function() {
          alert('Failed to load page.');
        }
      });
    }

    $('.navbar-bottom .nav-link').click(function(e) {
      e.preventDefault();
      loadPage($(this).attr('href'));
    });

    window.onpopstate = function() {
      loadPage(location.pathname);
    };
  });
</script>
</body>

</html>