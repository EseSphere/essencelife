<?php
session_start();
include 'header.php';
?>

<!-- Cancel button -->
<a href="./home" class="cancel-btn">
    <i class="bi bi-x"></i>
</a>

<!-- Background Canvas for subtle particles -->
<canvas id="bgCanvas" style="position:fixed; top:0; left:0; width:100%; height:100%; z-index:-1;"></canvas>

<div class="container-fluid text-center" style="min-height: 80vh; display:flex; flex-direction:column; justify-content:center; align-items:center; position:relative;">

    <h3 class="mb-3 fw-bold text-white">Complete Your Subscription</h3>
    <p class="mb-4 text-light">Unlock full access to Essence by subscribing to premium.</p>

    <div class="d-flex flex-column flex-md-row justify-content-center gap-4 mb-4 plan-container" style="max-width:1000px; width:100%;">
        <!-- Individual Plan -->
        <div class="plan-card" data-plan="individual">
            <h5>Individual</h5>
            <p class="price">$9.99 / month</p>
            <p class="plan-desc">Full access for a single user.</p>
        </div>

        <!-- Family Plan -->
        <div class="plan-card" data-plan="family">
            <h5>Family</h5>
            <p class="price">$19.99 / month</p>
            <p class="plan-desc">Share premium with up to 5 family members.</p>
        </div>

        <!-- Monthly Plan -->
        <div class="plan-card" data-plan="monthly">
            <h5>Monthly</h5>
            <p class="price">$14.99 / month</p>
            <p class="plan-desc">Flexible month-to-month access.</p>
        </div>
    </div>

    <div id="paymentStatus" class="mt-3 text-success fw-medium" style="font-size:1.1rem;"></div>
</div>

<style>
    body {
        background: #192a56;
        font-family: 'Segoe UI', sans-serif;
        overflow-x: hidden;
    }

    /* All cards use same gradient as last card */
    .plan-card {
        padding: 2rem;
        border-radius: 1.2rem;
        flex: 1;
        min-width: 220px;
        transition: transform 0.4s, box-shadow 0.4s, background 0.4s;
        text-align: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        opacity: 0;
        transform: translateY(50px);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.2);
        background: linear-gradient(135deg, #43cea2, #185a9d);
        background-size: 400% 400%;
        animation: gradientMove 12s ease infinite;
    }

    /* Gradient animation keyframes */
    @keyframes gradientMove {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    /* Hover effect */
    .plan-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        filter: brightness(1.15);
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Selected card effect */
    .plan-card.selected {
        border: 3px solid #28a745;
        box-shadow: 0 20px 50px rgba(0, 255, 100, 0.3);
        transform: translateY(-15px) scale(1.05);
    }

    /* Slide-in animation on page load */
    .plan-card.show {
        opacity: 1;
        transform: translateY(0);
        transition: all 0.6s ease-out;
    }

    /* Ripple effect */
    .plan-card::after {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 1.2rem;
        pointer-events: none;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 10%, transparent 10.01%);
        background-repeat: no-repeat;
        background-position: 50%;
        transform: scale(10);
        opacity: 0;
        transition: transform 0.5s, opacity 1s;
    }

    .plan-card.ripple::after {
        transform: scale(0);
        opacity: 0.3;
        transition: 0s;
    }

    /* Typography */
    .plan-card h5 {
        margin-bottom: 0.5rem;
        font-size: 1.4rem;
    }

    .plan-card .price {
        font-size: 1.7rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .plan-card .plan-desc {
        margin-bottom: 0;
        font-size: 1rem;
        color: #cfd8dc;
    }

    /* Particle background canvas */
    #bgCanvas {
        z-index: -1;
    }

    /* Cancel button top-right */
    .cancel-btn {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 1.3rem;
        color: #fff;
        background: rgba(0, 0, 0, 0.4);
        padding: 0.2rem 0.4rem;
        border-radius: 50%;
        transition: background 0.3s, transform 0.2s;
        z-index: 10;
        opacity: 0;
        transform: translateY(-20px);
        animation: fadeSlideIn 0.8s forwards 0.5s;
        /* 0.5s delay to appear after cards */
    }

    /* Hover effect */
    .cancel-btn:hover {
        background: rgba(0, 0, 0, 0.7);
        transform: scale(1.1);
        text-decoration: none;
        color: #fff;
    }

    /* Fade + slide-in animation */
    @keyframes fadeSlideIn {
        0% {
            opacity: 0;
            transform: translateY(-20px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Slide-in animation for cards
        $('.plan-card').each(function(i) {
            setTimeout(() => {
                $(this).addClass('show');
            }, i * 150);
        });

        function triggerRipple(element) {
            element.addClass('ripple');
            setTimeout(() => {
                element.removeClass('ripple');
            }, 500);
        }

        // Click event for cards
        $('.plan-card').click(function() {
            triggerRipple($(this));
            $('.plan-card').removeClass('selected');
            $(this).addClass('selected');

            var plan = $(this).data('plan');

            // Simulate payment integration
            setTimeout(function() {
                $('#paymentStatus').html('Payment successful! You have subscribed to the ' + plan + ' plan.');
            }, 1200);
        });

        // Particle background
        const canvas = document.getElementById('bgCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        for (let i = 0; i < 80; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 2 + 1,
                dx: (Math.random() - 0.5) * 0.5,
                dy: (Math.random() - 0.5) * 0.5
            });
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = "rgba(255,255,255,0.2)";
                ctx.fill();
                p.x += p.dx;
                p.y += p.dy;
                if (p.x < 0 || p.x > canvas.width) p.dx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.dy *= -1;
            });
            requestAnimationFrame(animateParticles);
        }
        animateParticles();

        $(window).resize(function() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    });
</script>

<?php include 'footer.php'; ?>