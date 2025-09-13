</div> <!-- end of #mainContent -->
</div>
<!-- Bottom Navbar -->
<nav style="background-color: #001F54; position:fixed; bottom:0; width:100%;" class="navbar navbar-expand-lg navbar-bottom">
  <div class="container justify-content-around">
    <a class="nav-link" href="./home"><i class="bi bi-house-door"></i> Home</a>
    <a class="nav-link" href="./playlist"><i class="bi bi-music-note-list"></i> Play List</a>
    <a class="nav-link" href="./discover"><i class="bi bi-compass"></i> Discover</a>
    <a class="nav-link" href="./profile"><i class="bi bi-person-circle"></i> Profile</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- AJAX Navigation Script -->
<script>
  $(document).ready(function() {
    function loadPage(url) {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        success: function(data) {
          var newContent = $(data).find('#mainContent').html();
          $('#mainContent').html(newContent);

          // Update active link
          $('.navbar-bottom .nav-link').removeClass('active');
          $('.navbar-bottom .nav-link[href="' + url + '"]').addClass('active');

          // Update URL
          history.pushState(null, '', url);

          // Reinitialize animations
          AOS.init();
        },
        error: function() {
          alert('Failed to load page.');
        }
      });
    }

    // Intercept navbar clicks
    $('.navbar-bottom .nav-link').click(function(e) {
      e.preventDefault();
      var url = $(this).attr('href');
      loadPage(url);
    });

    // Handle back/forward browser buttons
    window.onpopstate = function() {
      loadPage(location.pathname);
    };
  });
</script>
</body>

</html>