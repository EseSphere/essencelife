<?php include 'header.php'; ?>
<style>
    #card-bg {
        background-color: #273c75;
        font-size: 14px;
        border-radius: 15px;
    }
</style>

<div data-aos="fade-up" data-aos-anchor-placement="bottom-bottom" data-aos-duration="900" id="card-bg" class="card text-white flex justify-start items-start text-start shadow-lg border-rounded p-4 mb-4">
    <h4 class="fw-bold">Discover</h4>
    <p class="text-white fs-5">Tip: Explore new features and personalized recommendations under Discover. Use Discover to find new audios and tips.</p>
</div>

<!-- Dynamic Content Container -->
<div class="container-fluid">
    <div id="contentContainer">
        <!-- Dynamic content loads here -->
    </div>
</div>

<script>
    $(document).ready(function() {
        let songs = [];

        /*** AJAX content loader ***/
        function loadContents(url = 'fetch_discover.php') {
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#contentContainer').html(data);
                    loadSongs();
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
                    audio: $(this).data('audio'),
                    image: $(this).data('image') || 'default.png',
                    id: $(this).data('id')
                });
            });
        }

        // Initial load
        loadContents();

        /*** Song click handlers: redirect to player.php ***/
        $(document).on('click', '.song-item', function() {
            const songId = $(this).data('id');
            const currentTime = 0; // start at beginning
            window.location.href = `player.php?id=${songId}&time=${currentTime}`;
        });

        /*** Redirect to player.php when clicking the song title in case used elsewhere ***/
        $(document).on('click', '#currentSongTitle', function() {
            const index = 0; // fallback index
            const song = songs[index];
            if (song) {
                window.location.href = `player.php?id=${song.id}&time=0`;
            }
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
            let greeting = hour < 12 ? "Good Morning" :
                hour < 18 ? "Good Afternoon" :
                "Good Evening";
            return `${greeting} ${name}`;
        }

        document.getElementById("greeting").textContent = getGreeting("Samson");
    });
</script>

<?php include 'footer.php'; ?>