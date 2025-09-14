<?php include 'header.php'; ?>

<div data-aos="fade-up" data-aos-anchor-placement="bottom-bottom" data-aos-duration="900" class="container text-center alert alert-success">
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

        /*** Recently Played ***/
        function getRecentlyPlayed() {
            return JSON.parse(localStorage.getItem('recentlyPlayed') || "[]");
        }

        function renderRecentlyPlayed() {
            const recent = getRecentlyPlayed();
            const section = $('#recentlyPlayedSection');
            const container = $('#recentlyPlayedContainer');
            container.empty();

            if (recent.length === 0) {
                section.hide();
                return;
            } else {
                section.show();
            }

            recent.forEach((song, idx) => {
                const songHtml = `
                <div data-aos="fade-left" data-aos-anchor="#example-anchor" data-aos-offset="500" data-aos-duration="700" style="height:200px; width:200px;" class="song-item recently-played" 
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
        }

        /*** Scroll arrows functionality ***/
        const scrollAmount = 300;
        $('#recentNext').click(() => $('#recentlyPlayedContainer').animate({
            scrollLeft: '+=' + scrollAmount
        }, 300));
        $('#recentPrev').click(() => $('#recentlyPlayedContainer').animate({
            scrollLeft: '-=' + scrollAmount
        }, 300));

        // Initial load
        loadContents();

        /*** Song click handlers: redirect to player.php ***/
        $(document).on('click', '.song-item', function() {
            const songId = $(this).data('id');
            const currentTime = 0; // start at beginning
            window.location.href = `player.php?id=${songId}&time=${currentTime}`;
        });

        $(document).on('click', '#recentlyPlayedContainer .song-item', function() {
            const songId = $(this).data('id');
            const currentTime = 0;
            window.location.href = `player.php?id=${songId}&time=${currentTime}`;
        });

        function getGreeting(name) {
            const hour = new Date().getHours();
            let greeting = hour < 12 ? "Good Morning" :
                hour < 18 ? "Good Afternoon" :
                "Good Evening";
            return `${greeting} ${name}`;
        }

        document.getElementById("greeting").textContent = getGreeting("");
    });
</script>

<?php include 'footer.php'; ?>