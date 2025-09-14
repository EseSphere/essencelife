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

        /*** AJAX content loader ***/
        function loadContents(url = 'fetch_discover.php') {
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#contentContainer').html(data);
                },
                error: function() {
                    $('#contentContainer').html('<p class="text-danger">Failed to load content.</p>');
                }
            });
        }

        // --- Show only mini player on this page ---
        $('#miniPlayer').show(); // Always show mini player
        $('#audioPlayerContainer').hide(); // Do not auto-expand

        // Initial load
        loadContents();

        // --- Song click handlers for this page ---
        $(document).on('click', '.song-item', function() {
            // Use the playPersistentAudio() function already defined in footer.php
            const songData = {
                id: $(this).data('id'),
                title: $(this).data('title'),
                audio: $(this).data('audio'),
                image: $(this).data('image') || 'default.png',
                category: $(this).data('category') || '',
                description: $(this).data('description') || '',
                related: $(this).data('related') ? JSON.parse($(this).attr('data-related')) : [],
                time: 0
            };
            window.playPersistentAudio(songData); // Footer handles audio playback
        });

    });
</script>

<?php include 'footer.php'; ?>