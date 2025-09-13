<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
include 'header-panel.php';
include 'dbconnections.php';

$userId = $_SESSION['user_id'];

// Define all questions
$questions = [
    "1" => [
        'title' => 'What brings you to Essence?',
        'subtitle' => 'Select all that apply to you',
        'options' => [
            "Reduce Stress" => "bi-clock-history",
            "Improve Sleep" => "bi-arrow-repeat",
            "Increase Focus" => "bi-lightning-fill",
            "Practice Mindfulness" => "bi-slash-circle",
            "General Relaxation" => "bi-slash-circle"
        ]
    ],
    "2" => [
        'title' => 'Do you often struggle to sleep?',
        'subtitle' => 'Select all that apply to you',
        'options' => [
            "Occasionally" => "bi-clock-history",
            "Sometimes" => "bi-arrow-repeat",
            "Frequently" => "bi-lightning-fill",
            "Never" => "bi-slash-circle"
        ]
    ],
    "3" => [
        'title' => 'Have you tried to improve your sleep?',
        'subtitle' => 'You have come to the right place for help.',
        'options' => [
            "I don\'t know how to" => "bi-clock-history",
            "Sometimes" => "bi-arrow-repeat",
            "I rely on sleep aids" => "bi-lightning-fill",
            "Never" => "bi-slash-circle"
        ]
    ],
    "4" => [
        'title' => 'How would you like to wake up?',
        'subtitle' => 'Tip: Imagine if you didn\'t struggle sleeping.',
        'options' => [
            "Energized and refreshed" => "bi-clock-history",
            "Somewhat rested" => "bi-arrow-repeat",
            "No stress" => "bi-lightning-fill",
            "All of the above" => "bi-slash-circle"
        ]
    ]
];

// Fetch previous answers
$previousAnswers = [];
$stmt = $conn->prepare("SELECT question_id, answer FROM user_answers WHERE user_id = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $previousAnswers[$row['question_id']][] = $row['answer'];
}
$stmt->close();
?>

<!-- Background Music -->
<audio id="bgMusic" loop>
    <source src="assets/calm-music.mp3" type="audio/mpeg">
</audio>

<!-- Transition Sound -->
<audio id="transitionSound">
    <source src="assets/soft-swoosh.mp3" type="audio/mpeg">
</audio>

<!-- Gradient Progress Bar -->
<div class="progress-container mb-4">
    <div class="progress-bar-gradient" style="width: 0%;"></div>
</div>

<div class="container-fluid text-center" style="position: relative; min-height: 400px;">
    <div id="question-container">
        <?php $firstQid = array_key_first($questions); ?>
        <?php foreach ($questions as $qid => $q): ?>
            <div class="question-block <?= ($qid == $firstQid) ? 'active' : '' ?>" data-qid="<?= $qid ?>">
                <h3 class="mb-2 fw-bold text-white"><?= $q['title'] ?></h3>
                <p class="mb-4 text-light"><?= $q['subtitle'] ?></p>

                <div class="d-flex flex-column align-items-center gap-3 mb-3">
                    <?php foreach ($q['options'] as $label => $icon):
                        $checked = in_array($label, $previousAnswers[$qid] ?? []) ? 'checked' : '';
                        $active = in_array($label, $previousAnswers[$qid] ?? []) ? 'active' : '';
                        $id = "q{$qid}_" . str_replace(' ', '', $label);
                        $tooltip = htmlspecialchars($label);
                    ?>
                        <input type="checkbox" class="btn-check" name="question_<?= $qid ?>[]" id="<?= $id ?>" value="<?= $label ?>" autocomplete="off" <?= $checked ?>>
                        <label class="btn btn-outline-primary <?= $active ?>" for="<?= $id ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $tooltip ?>">
                            <i class="bi <?= $icon ?>"></i> <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="action-btn prev-btn" <?= ($qid == $firstQid) ? 'disabled' : '' ?>>
                        <i class="bi bi-arrow-left-circle"></i> Previous
                    </button>

                    <button class="action-btn next-btn">
                        <i class="bi bi-check-circle"></i> <?= ($qid == array_key_last($questions)) ? 'Submit' : 'Next' ?>
                    </button>

                    <button type="button" class="action-btn skip-btn">
                        <i class="bi bi-skip-forward-circle"></i> Skip
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="selectedAnswers" class="mt-4 text-success fw-medium" style="font-size:1.1rem;"></div>
</div>

<style>
    /* Buttons */
    .action-btn {
        color: #fff;
        border: none;
        padding: 0.6rem 1.2rem;
        font-size: 1rem;
        border-radius: .5rem;
        transition: transform .2s, box-shadow .2s, background-position .5s;
        background-size: 200% 200%;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .action-btn i {
        font-size: 1.2rem;
    }

    .next-btn {
        background: linear-gradient(45deg, #4facfe, #00f2fe, #4facfe);
    }

    .next-btn:hover {
        background-position: right center;
    }

    .prev-btn {
        background: #192a56;
    }

    .prev-btn:hover {
        filter: brightness(1.1);
    }

    .skip-btn {
        background: linear-gradient(45deg, #28a745, #71e073);
    }

    .skip-btn:hover {
        background-position: right center;
    }

    .btn-check:checked+label {
        filter: brightness(1.1);
    }

    /* Progress Bar */
    .progress-container {
        width: 100%;
        height: 12px;
        background: #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .progress-bar-gradient {
        height: 100%;
        width: 0%;
        background: linear-gradient(270deg, #4facfe, #00f2fe, #a18cd1, #fbc2eb, #28a745, #71e073);
        background-size: 600% 100%;
        border-radius: 6px;
        transition: width .5s ease;
        animation: flowGradient 6s linear infinite;
    }

    @keyframes flowGradient {
        0% {
            background-position: 0% 0%;
        }

        100% {
            background-position: 100% 0%;
        }
    }

    /* Calm animation */
    .question-block {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        opacity: 0;
        transform: translateX(50px);
        transition: opacity .6s ease, transform .6s ease;
        pointer-events: none;
    }

    .question-block.active {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
        position: relative;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        var totalQuestions = $('.question-block').length;

        var bgMusic = document.getElementById('bgMusic');
        var transitionSound = document.getElementById('transitionSound');
        bgMusic.volume = 0.2;
        transitionSound.volume = 0.5;

        // Start music on first user click
        $(document).one('click', function() {
            bgMusic.play().catch(() => {});
        });

        function playTransitionSound() {
            transitionSound.currentTime = 0;
            transitionSound.play().catch(() => {});
        }

        function updateProgress() {
            var currentIndex = $('.question-block.active').index() + 1;
            $('.progress-bar-gradient').css('width', (currentIndex / totalQuestions * 100) + '%');
        }
        updateProgress();

        function showQuestion(container) {
            $('.question-block').removeClass('active');
            container.addClass('active');
            updateProgress();
        }

        function saveAnswers(qid, selectedAnswers) {
            return $.ajax({
                url: 'save_answer.php',
                type: 'POST',
                data: {
                    answers: {
                        [qid]: selectedAnswers
                    }
                },
                dataType: 'json'
            });
        }

        $('.next-btn').click(function() {
            playTransitionSound();
            var container = $(this).closest('.question-block');
            var qid = container.data('qid');
            var selectedAnswers = [];
            container.find('input:checked').each(function() {
                selectedAnswers.push($(this).val());
            });

            saveAnswers(qid, selectedAnswers).done(function(response) {
                $('#selectedAnswers').html(response.message);
                var next = container.next('.question-block');
                if (next.length) {
                    showQuestion(next);
                } else {
                    window.location.href = './payment.php';
                }
            });
        });

        $('.prev-btn').click(function() {
            playTransitionSound();
            var container = $(this).closest('.question-block');
            var prev = container.prev('.question-block');
            if (prev.length) {
                showQuestion(prev);
            }
        });

        $('.skip-btn').click(function() {
            playTransitionSound();
            var container = $(this).closest('.question-block');
            var qid = container.data('qid');
            var selectedAnswers = [];
            container.find('input:checked').each(function() {
                selectedAnswers.push($(this).val());
            });

            saveAnswers(qid, selectedAnswers).done(function(response) {
                $('#selectedAnswers').html(response.message);
                var next = container.next('.question-block');
                if (next.length) {
                    showQuestion(next);
                } else {
                    window.location.href = './payment.php';
                }
            });
        });

        $('.btn-check').change(function() {
            var label = $(this).next('label');
            if ($(this).is(':checked')) label.addClass('active');
            else label.removeClass('active');
        });

        // Fade out music on exit
        window.onbeforeunload = function() {
            var step = 0.05;
            var interval = 50;
            var fadeAudio = setInterval(function() {
                if (bgMusic.volume > step) {
                    bgMusic.volume -= step;
                } else {
                    bgMusic.pause();
                    clearInterval(fadeAudio);
                }
            }, interval);
        };
    });
</script>

<?php include 'footer-panel.php'; ?>