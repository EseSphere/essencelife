<?php
include 'dbconnections.php'; // Your database connection

$categories = [
    'song' => 'Songs',
    'meditation' => 'Meditation',
    'sleep' => 'Sleep',
    'motivation' => 'Motivation',
    'story' => 'Stories',
    'wisdom' => 'Wisdom',
    'relaxation' => 'Relaxation'
];

foreach ($categories as $type => $title) {
    $stmt = $conn->prepare("SELECT * FROM contents WHERE content_type=? AND status='active' ORDER BY created_at DESC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="category-row">';
        echo '<div class="category-title w-100 justify-start items-flex-start text-start">';
        echo $title;
        echo '<p class="fs-6 text-start">';
        switch ($type) {
            case 'song':
                echo 'Relaxing and inspiring tracks to lift your mood and energize your day.';
                break;
            case 'meditation':
                echo 'Soothing tracks designed for mindfulness, meditation, and inner calm.';
                break;
            case 'sleep':
                echo 'Peaceful melodies and sounds to help you sleep deeply.';
                break;
            case 'motivation':
                echo 'Uplifting content to boost your energy and positivity.';
                break;
            case 'story':
                echo 'Engaging stories to entertain and inspire.';
                break;
            case 'wisdom':
                echo 'Inspirational quotes and insights to enrich your life.';
                break;
            case 'relaxation':
                echo 'Unwind with peaceful visuals and calming imagery.';
                break;
        }
        echo '</p></div>';
        echo '<div class="slider">';

        while ($row = $result->fetch_assoc()) {
            $content_name = htmlspecialchars($row['content_name']);
            $image_url = htmlspecialchars($row['image_url']);
            $description = htmlspecialchars($row['description']);
            $content_url = htmlspecialchars($row['content_url']); // audio file
            $id = (int)$row['id'];

            echo '<div class="song-item" style="margin-bottom:20px;" 
          data-id="' . $id . '" 
          data-title="' . $content_name . '" 
          data-image="' . $image_url . '" 
          data-audio="' . $content_url . '">';
            echo '<img src="' . $image_url . '" alt="' . $content_name . '" style="width:100%; height:200px; display:block;">';
            echo '<p>' . $content_name . '<br><span style="font-size:13px;">' . $description . '</span></p>';
            echo '</div>';
        }

        echo '</div></div>';
    }
}
