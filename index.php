<?php
// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require config
require_once 'config.php';

// Get featured products from database
$featured_products = [];
$conn = getDBConnection();
$result = $conn->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 4");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}

// Get category counts
$category_counts = [];
$cats = ['women', 'men', 'bedsheet', 'bedding'];
foreach ($cats as $cat) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category = ?");
    $stmt->bind_param("s", $cat);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $category_counts[$cat] = $data['count'] ?? 0;
}

// Get user data if logged in
$user_name = $_SESSION['user_name'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$profile_image = $_SESSION['profile_image'] ?? 'default.png';
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Include header
include 'includes/header.php';
?>

<style>
/* =========================================================
   INDEX.PHP CUSTOM STYLES - WITH ANIMATIONS
   ========================================================= */

:root {
    --bg: #0b0a08;
    --bg-panel: #141210;
    --bg-panel-2: #1b1815;
    --gold: #d4af37;
    --gold-bright: #f0c419;
    --gold-dim: #8a7226;
    --cream: #f5f1e8;
    --muted: #a79d8a;
    --stroke: rgba(212, 175, 55, .28);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
    background: var(--bg);
    color: var(--cream);
    font-family: 'Poppins', sans-serif;
    font-weight: 300;
    overflow-x: hidden;
}
h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
a { text-decoration: none; color: inherit; }

.text-gold { color: var(--gold) !important; }
.text-muted-gold { color: var(--muted) !important; }
.eyebrow {
    letter-spacing: .22em;
    font-size: .7rem;
    font-weight: 600;
    color: var(--gold);
    text-transform: uppercase;
}
.divider-line {
    width: 54px;
    height: 2px;
    background: var(--gold);
    margin: .9rem 0 1.2rem;
}
.section { padding: 5rem 0; }
.section-title {
    font-size: clamp(1.7rem, 3vw, 2.4rem);
    font-weight: 700;
    color: var(--cream);
}

/* =========================================================
   REVEAL ANIMATIONS
   ========================================================= */
.reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.reveal.in-view {
    opacity: 1;
    transform: translateY(0);
}

.reveal-delay-1 { transition-delay: 0.15s; }
.reveal-delay-2 { transition-delay: 0.3s; }
.reveal-delay-3 { transition-delay: 0.45s; }
.reveal-delay-4 { transition-delay: 0.6s; }
.reveal-delay-5 { transition-delay: 0.75s; }

.reveal-scale {
    opacity: 0;
    transform: scale(0.9);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.reveal-scale.in-view {
    opacity: 1;
    transform: scale(1);
}

.reveal-ltr {
    opacity: 0;
    transform: translateX(-50px);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.reveal-ltr.in-view {
    opacity: 1;
    transform: translateX(0);
}

.reveal-rtl {
    opacity: 0;
    transform: translateX(50px);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.reveal-rtl.in-view {
    opacity: 1;
    transform: translateX(0);
}

.reveal-rotate {
    opacity: 0;
    transform: rotate(-5deg) scale(0.95);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
.reveal-rotate.in-view {
    opacity: 1;
    transform: rotate(0deg) scale(1);
}

/* =========================================================
   HERO SECTION
   ========================================================= */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    padding-top: 2rem;
}

.hero-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    background: 
        linear-gradient(135deg, rgba(11,10,8,0.92) 0%, rgba(11,10,8,0.5) 40%, rgba(11,10,8,0.88) 100%),
        radial-gradient(ellipse at 30% 50%, rgba(212,175,55,0.08) 0%, transparent 60%),
        radial-gradient(ellipse at 70% 80%, rgba(212,175,55,0.05) 0%, transparent 50%),
        url('https://images.unsplash.com/photo-1618220179428-22790b461013?w=1920&q=80') center center / cover no-repeat;
    animation: heroZoom 25s ease-in-out infinite alternate;
}

.hero-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        repeating-linear-gradient(45deg, transparent, transparent 50px, rgba(0,0,0,0.02) 50px, rgba(0,0,0,0.02) 51px),
        repeating-linear-gradient(-45deg, transparent, transparent 50px, rgba(0,0,0,0.02) 50px, rgba(0,0,0,0.02) 51px);
    z-index: 1;
}

.hero-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(ellipse at 20% 30%, rgba(212,175,55,0.06) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 70%, rgba(212,175,55,0.04) 0%, transparent 40%);
    z-index: 1;
}

@keyframes heroZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.08); }
}

.hero-swatch {
    position: absolute;
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.12);
    background: rgba(20, 18, 16, 0.4);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: float 7s ease-in-out infinite;
    z-index: 1;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.hero-swatch.s1 { width: 90px; height: 120px; top: 14%; left: 6%; animation-delay: 0s; }
.hero-swatch.s2 { width: 70px; height: 70px; top: 65%; left: 11%; border-radius: 50%; animation-delay: 1.4s; }
.hero-swatch.s3 { width: 100px; height: 70px; top: 20%; right: 7%; animation-delay: .7s; }
.hero-swatch.s4 { width: 60px; height: 60px; top: 70%; right: 12%; border-radius: 50%; animation-delay: 2s; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-18px) rotate(4deg); }
}

.hero .container { position: relative; z-index: 2; }

.hero h1 {
    font-size: clamp(2.5rem, 6vw, 4.2rem);
    font-weight: 800;
    background: linear-gradient(90deg, var(--gold-bright), var(--cream) 40%, var(--gold) 80%, var(--gold-bright));
    background-size: 300% auto;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: shine 8s linear infinite;
    text-shadow: 0 0 40px rgba(212, 175, 55, 0.1);
}

@keyframes shine {
    to { background-position: 300% center; }
}

.hero p.lead-text {
    color: rgba(245, 241, 232, 0.85);
    font-size: 1.1rem;
    max-width: 560px;
    line-height: 1.8;
    text-shadow: 0 2px 20px rgba(0,0,0,0.5);
}

.hero p.lead-text .highlight { color: var(--gold); font-weight: 500; }

.hero-stats { gap: 2.5rem; }
.hero-stats .num { 
    font-family: 'Playfair Display', serif; 
    font-size: 1.8rem; 
    color: var(--gold); 
    font-weight: 700; 
}
.hero-stats .lbl { 
    font-size: .72rem; 
    color: rgba(167, 157, 138, 0.8); 
    letter-spacing: .08em; 
    text-transform: uppercase; 
}

.hero-stats .stat-item {
    position: relative;
    padding: 0.3rem 1.5rem;
    border-right: 1px solid rgba(212, 175, 55, 0.15);
}
.hero-stats .stat-item:last-child { border-right: none; }
.hero-stats .stat-item .num { text-shadow: 0 0 30px rgba(212, 175, 55, 0.15); }

/* =========================================================
   BUTTONS
   ========================================================= */
.btn-gold {
    background: linear-gradient(180deg, var(--gold-bright), var(--gold));
    border: none;
    color: #1a1305 !important;
    font-weight: 600;
    padding: .7rem 2.2rem;
    border-radius: 8px;
    transition: all .4s ease;
    text-decoration: none !important;
    display: inline-block;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.15);
    cursor: pointer;
}
.btn-gold::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg) translateX(-100%);
    transition: transform 0.6s ease;
}
.btn-gold:hover::before { transform: rotate(45deg) translateX(100%); }
.btn-gold:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(212, 175, 55, 0.4);
    color: #1a1305 !important;
}

.btn-outline-gold {
    background: rgba(212, 175, 55, 0.08);
    border: 1.5px solid var(--gold);
    color: var(--gold) !important;
    font-weight: 600;
    padding: .7rem 2.2rem;
    border-radius: 8px;
    transition: all .4s ease;
    text-decoration: none !important;
    display: inline-block;
    backdrop-filter: blur(10px);
    cursor: pointer;
}
.btn-outline-gold:hover {
    background: var(--gold);
    color: #1a1305 !important;
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(212, 175, 55, 0.25);
}

.btn-sm { padding: 0.4rem 1.2rem !important; font-size: 0.85rem !important; }

/* =========================================================
   WELCOME BANNER
   ========================================================= */
.welcome-banner {
    background: linear-gradient(135deg, rgba(212,175,55,0.18), rgba(212,175,55,0.05));
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin: 2rem auto;
    max-width: 1200px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
    animation: welcomeSlideDown 0.8s ease-out;
}

@keyframes welcomeSlideDown {
    0% { opacity: 0; transform: translateY(-30px); }
    100% { opacity: 1; transform: translateY(0); }
}

.welcome-banner .welcome-text {
    display: flex;
    align-items: center;
    gap: 1.2rem;
}

.welcome-banner .welcome-text .welcome-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--gold);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-panel-2);
}

.welcome-banner .welcome-text .welcome-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.welcome-banner .welcome-text .welcome-avatar .avatar-placeholder {
    font-size: 1.5rem;
    color: var(--gold);
    font-weight: 700;
    text-transform: uppercase;
}

.welcome-banner .welcome-text h3 {
    color: var(--cream);
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    margin: 0;
}

.welcome-banner .welcome-text h3 span { color: var(--gold); }

.welcome-banner .welcome-text p {
    color: var(--muted);
    margin: 0.2rem 0 0;
    font-size: 0.95rem;
}

.welcome-banner .welcome-actions {
    display: flex;
    gap: 0.8rem;
    flex-wrap: wrap;
}

/* =========================================================
   CATEGORIES SECTION - WITH BACKGROUND IMAGE
   ========================================================= */
.categories-section {
    position: relative;
    padding: 5rem 0;
    overflow: hidden;
}

.categories-section .section-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    background: 
        linear-gradient(135deg, rgba(11,10,8,0.92) 0%, rgba(11,10,8,0.6) 50%, rgba(11,10,8,0.85) 100%),
        radial-gradient(ellipse at 60% 40%, rgba(212,175,55,0.06) 0%, transparent 60%),
        url('https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=1920&q=80') center center / cover no-repeat;
    animation: sectionBgZoom 30s ease-in-out infinite alternate;
}

@keyframes sectionBgZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.05); }
}

.categories-section .section-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(ellipse at 30% 20%, rgba(212,175,55,0.04) 0%, transparent 50%),
        radial-gradient(ellipse at 70% 80%, rgba(212,175,55,0.03) 0%, transparent 40%);
}

.categories-section .container { position: relative; z-index: 1; }

/* =========================================================
   CATEGORY CARDS
   ========================================================= */
.cat-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--stroke);
    background: var(--bg-panel);
    height: 360px;
    display: flex;
    align-items: flex-end;
    transition: transform .45s ease, border-color .45s ease, box-shadow .45s ease;
    cursor: pointer;
}

.cat-card:hover {
    transform: translateY(-12px) scale(1.02);
    border-color: var(--gold);
    box-shadow: 0 20px 50px rgba(0,0,0,0.6), 0 0 40px rgba(212, 175, 55, 0.1);
}

.cat-card .cat-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
    z-index: 0;
    opacity: 0.5;
    transition: opacity 0.6s ease, transform 0.8s ease;
}

.cat-card:hover .cat-image {
    opacity: 0.9;
    transform: scale(1.08);
}

.cat-card .cat-overlay {
    position: absolute;
    inset: 0;
    background: var(--cat-grad);
    opacity: 0.6;
    z-index: 1;
    transition: opacity 0.5s ease;
}

.cat-card:hover .cat-overlay { opacity: 0.1; }

.cat-card .cat-icon {
    position: absolute;
    top: 18px;
    right: 18px;
    font-size: 1.8rem;
    color: var(--gold);
    opacity: 0.6;
    z-index: 2;
    transition: transform 0.4s ease, opacity 0.4s ease;
}

.cat-card:hover .cat-icon {
    transform: rotate(20deg) scale(1.2);
    opacity: 1;
}

.cat-body {
    position: relative;
    z-index: 2;
    padding: 1.6rem;
    width: 100%;
    background: linear-gradient(0deg, rgba(11,10,8,.95), transparent 70%);
}

.cat-body h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: .15rem;
    color: var(--cream);
    font-size: 1.2rem;
}

.cat-body span {
    font-size: .78rem;
    color: var(--gold);
    font-weight: 600;
    letter-spacing: 0.05em;
}

.cat-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: 1px solid var(--gold);
    border-radius: 50%;
    color: var(--gold);
    float: right;
    transition: all .4s ease;
    background: rgba(212, 175, 55, 0.05);
}

.cat-card:hover .cat-arrow {
    background: var(--gold);
    color: #1a1305;
    transform: rotate(45deg) scale(1.1);
}

/* =========================================================
   WHY CHOOSE US & TESTIMONIALS - WITH BACKGROUND IMAGE
   ========================================================= */
.why-testi-section {
    position: relative;
    padding: 5rem 0;
    overflow: hidden;
}

.why-testi-section .section-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    background: 
        linear-gradient(135deg, rgba(11,10,8,0.94) 0%, rgba(11,10,8,0.5) 40%, rgba(11,10,8,0.88) 100%),
        radial-gradient(ellipse at 40% 60%, rgba(212,175,55,0.05) 0%, transparent 60%),
        url('https://images.unsplash.com/photo-1509631179647-0177331693ae?w=1920&q=80') center center / cover no-repeat;
    animation: sectionBgZoom2 35s ease-in-out infinite alternate;
}

@keyframes sectionBgZoom2 {
    0% { transform: scale(1) rotate(0deg); }
    100% { transform: scale(1.08) rotate(0.5deg); }
}

.why-testi-section .section-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(ellipse at 50% 30%, rgba(212,175,55,0.03) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 70%, rgba(212,175,55,0.02) 0%, transparent 40%);
}

.why-testi-section .container { position: relative; z-index: 1; }

/* =========================================================
   WHY CHOOSE US ITEMS - COMPLETELY VISIBLE (NO HOVER NEEDED)
   ========================================================= */
.why-item { 
    text-align: center; 
    padding: 2rem 1.5rem;
    border-radius: 16px;
    background: #0a0806;
    border: 1px solid var(--stroke);
    transition: all .4s ease;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Already fully visible - no hover needed */
.why-item .why-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 1.2rem;
    border: 1px solid var(--stroke);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--gold);
    background: rgba(212, 175, 55, 0.05);
}

.why-item h6 {
    color: var(--cream);
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.3rem;
}

.why-item small {
    color: var(--muted);
    font-size: 0.8rem;
}

/* Hover effect - optional enhancement */
.why-item:hover {
    border-color: var(--gold);
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.why-item:hover .why-icon {
    border-color: var(--gold);
    background: rgba(212, 175, 55, 0.08);
}

/* =========================================================
   STITCH DIVIDER
   ========================================================= */
.stitch-divider {
    position: relative; height: 70px; background: var(--bg);
    display: flex; align-items: center; overflow: hidden;
}
.stitch-divider svg { width: 100%; height: 24px; }
.stitch-path {
    fill: none; stroke: var(--gold); stroke-width: 2;
    stroke-dasharray: 10 10;
    animation: stitch-move 2.5s linear infinite;
}
@keyframes stitch-move { to { stroke-dashoffset: -40; } }
.needle {
    offset-path: path("M0,12 Q 250,-10 500,12 T 1000,12 T 1500,12 T 2000,12");
    offset-rotate: 0deg;
    animation: needle-travel 9s linear infinite;
    position: absolute; top: 0; left: 0;
    color: var(--gold-bright); font-size: .95rem;
}
@keyframes needle-travel { to { offset-distance: 100%; } }

/* =========================================================
   OFFER BANNER
   ========================================================= */
.offer-banner {
    background: linear-gradient(135deg, #1a1305, #0b0a08);
    border: 2px solid var(--gold);
    border-radius: 16px;
    padding: 3rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin: 3rem 0;
    animation: pulseGlow 3s ease-in-out infinite;
}

@keyframes pulseGlow {
    0%, 100% { box-shadow: 0 0 30px rgba(212, 175, 55, 0.08); }
    50% { box-shadow: 0 0 60px rgba(212, 175, 55, 0.2); }
}

.offer-banner::before {
    content: "🔥";
    position: absolute;
    font-size: 10rem;
    opacity: 0.04;
    right: -30px;
    top: -30px;
    animation: floatIcon 6s ease-in-out infinite;
}

@keyframes floatIcon {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(10deg); }
}

.offer-banner h2 {
    color: var(--gold);
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    animation: textGlow 2s ease-in-out infinite alternate;
}

@keyframes textGlow {
    0% { text-shadow: 0 0 20px rgba(212, 175, 55, 0.15); }
    100% { text-shadow: 0 0 50px rgba(212, 175, 55, 0.3); }
}

.offer-banner p {
    color: var(--muted);
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.offer-banner .offer-timer {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin: 1.5rem 0;
}

.offer-banner .offer-timer .time-box {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 12px;
    padding: 1rem 2rem;
    min-width: 80px;
    animation: timerPulse 2s ease-in-out infinite;
}

.offer-banner .offer-timer .time-box:nth-child(2) { animation-delay: 0.2s; }
.offer-banner .offer-timer .time-box:nth-child(3) { animation-delay: 0.4s; }
.offer-banner .offer-timer .time-box:nth-child(4) { animation-delay: 0.6s; }

@keyframes timerPulse {
    0%, 100% { transform: scale(1); border-color: var(--stroke); }
    50% { transform: scale(1.05); border-color: var(--gold); }
}

.offer-banner .offer-timer .time-box .num {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--gold);
    font-family: 'Playfair Display', serif;
}
.offer-banner .offer-timer .time-box .label {
    font-size: 0.65rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

/* =========================================================
   BRAND PARTNERS
   ========================================================= */
.brand-partners {
    padding: 2.5rem 0;
    border-top: 1px solid var(--stroke);
    border-bottom: 1px solid var(--stroke);
    background: var(--bg-panel);
}
.brand-partners .partner-grid {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 4rem;
    flex-wrap: wrap;
}
.brand-partners .partner-item {
    color: var(--muted);
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 0.1em;
    opacity: 0.5;
    transition: all .4s ease;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.brand-partners .partner-item:hover {
    opacity: 1;
    color: var(--gold);
    transform: translateY(-4px) scale(1.05);
}
.brand-partners .partner-item i { font-size: 2rem; color: var(--gold); }

/* =========================================================
   TESTIMONIALS
   ========================================================= */
.testi-card {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2rem;
    height: 100%;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.testi-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    border-color: var(--gold);
}
.testi-stars { color: var(--gold); font-size: .9rem; }
.testi-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-bright), var(--gold-dim));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #1a1305;
    font-size: 1.2rem;
}

/* =========================================================
   NEWSLETTER BAND
   ========================================================= */
.news-band {
    background: linear-gradient(120deg, rgba(212,175,55,.12), rgba(212,175,55,.02));
    border-top: 1px solid var(--stroke);
    border-bottom: 1px solid var(--stroke);
    padding: 3.5rem 0;
}
.form-gold {
    background: var(--bg);
    border: 1px solid var(--stroke);
    color: var(--cream);
    border-radius: 8px 0 0 8px;
    padding: .8rem 1.2rem;
}
.form-gold:focus {
    background: var(--bg);
    color: var(--cream);
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
}
.form-gold::placeholder { color: var(--muted); }

/* =========================================================
   SCROLL TO TOP
   ========================================================= */
.scroll-top {
    position: fixed;
    right: 25px;
    bottom: 25px;
    z-index: 999;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold), var(--gold-bright));
    color: #1a1305;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all .3s ease;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
}
.scroll-top:hover {
    transform: translateY(-4px) scale(1.1);
    box-shadow: 0 8px 35px rgba(212, 175, 55, 0.5);
}
.scroll-top.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 992px) {
    .hero { min-height: 70vh; }
    .hero-swatch { transform: scale(0.7); }
    .cat-card { height: 280px; }
    .welcome-banner { flex-direction: column; text-align: center; margin: 1rem; }
    .welcome-banner .welcome-text { flex-direction: column; text-align: center; }
    .brand-partners .partner-grid { gap: 2rem; }
    .offer-banner .offer-timer .time-box { padding: 0.8rem 1.2rem; min-width: 60px; }
}

@media (max-width: 768px) {
    .hero { min-height: 60vh; padding-top: 1rem; }
    .hero-swatch { display: none; }
    .hero h1 { font-size: 2.2rem; }
    .hero p.lead-text { font-size: 0.95rem; }
    .hero-stats { gap: 1.5rem; flex-wrap: wrap; }
    .hero-stats .num { font-size: 1.3rem; }
    .hero-stats .stat-item { padding: 0.3rem 1rem; }
    .cat-card { height: 220px; }
    .cat-body { padding: 1rem; }
    .cat-body h5 { font-size: 1rem; }
    .section { padding: 3.5rem 0; }
    .offer-banner { padding: 1.5rem; margin: 1.5rem; }
    .offer-banner h2 { font-size: 1.8rem; }
    .offer-banner .offer-timer { gap: 0.8rem; flex-wrap: wrap; }
    .offer-banner .offer-timer .time-box { padding: 0.6rem 1rem; min-width: 60px; }
    .offer-banner .offer-timer .time-box .num { font-size: 1.5rem; }
    .welcome-banner { padding: 1.2rem; }
    .welcome-banner .welcome-text .welcome-avatar { width: 50px; height: 50px; }
    .brand-partners .partner-grid { gap: 1.5rem; }
    .brand-partners .partner-item { font-size: 0.75rem; }
    .brand-partners .partner-item i { font-size: 1.5rem; }
    .categories-section .section-bg { background-position: center; }
    .why-testi-section .section-bg { background-position: center; }
}

@media (max-width: 480px) {
    .hero { min-height: 50vh; }
    .hero h1 { font-size: 1.8rem; }
    .hero p.lead-text { font-size: 0.85rem; }
    .hero-stats { gap: 1rem; }
    .hero-stats .num { font-size: 1.1rem; }
    .hero-stats .lbl { font-size: 0.6rem; }
    .hero-stats .stat-item { padding: 0.2rem 0.8rem; }
    .cat-card { height: 170px; }
    .cat-body { padding: 0.8rem; }
    .cat-body h5 { font-size: 0.85rem; }
    .cat-body span { font-size: 0.6rem; }
    .cat-arrow { width: 28px; height: 28px; font-size: 0.8rem; }
    .section { padding: 2.5rem 0; }
    .section-title { font-size: 1.5rem; }
    .welcome-banner .welcome-text h3 { font-size: 1.1rem; }
    .welcome-banner .welcome-actions .btn-sm { font-size: 0.75rem; padding: 0.3rem 0.8rem; }
    .offer-banner h2 { font-size: 1.4rem; }
    .offer-banner .offer-timer .time-box .num { font-size: 1.2rem; }
    .offer-banner .offer-timer .time-box { min-width: 50px; padding: 0.4rem 0.6rem; }
    .btn-gold, .btn-outline-gold { padding: 0.5rem 1.5rem; font-size: 0.9rem; }
}

@media (max-width: 360px) {
    .hero h1 { font-size: 1.4rem; }
    .cat-card { height: 140px; }
    .cat-body h5 { font-size: 0.7rem; }
    .offer-banner .offer-timer .time-box { min-width: 45px; padding: 0.3rem 0.5rem; }
    .offer-banner .offer-timer .time-box .num { font-size: 1rem; }
}

@media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
}
</style>

<!-- =========================================================
     WELCOME BANNER FOR LOGGED IN USERS
     ========================================================= -->
<?php if ($is_logged_in): 
    $first_letter = strtoupper(substr($user_name, 0, 1));
    $image_path = 'uploads/profile/' . $profile_image;
    $image_exists = file_exists($image_path) && $profile_image != 'default.png' && !empty($profile_image);
?>
<div class="container">
    <div class="welcome-banner">
        <div class="welcome-text">
            <div class="welcome-avatar">
                <?php if ($image_exists): ?>
                    <img src="<?php echo $image_path; ?>" alt="<?php echo $user_name; ?>">
                <?php else: ?>
                    <span class="avatar-placeholder"><?php echo $first_letter; ?></span>
                <?php endif; ?>
            </div>
            <div>
                <h3>Welcome back, <span><?php echo htmlspecialchars($user_name); ?></span>! 👋</h3>
                <p>We're happy to see you again. Check out our latest collection.</p>
            </div>
        </div>
        <div class="welcome-actions">
            <a href="profile.php" class="btn-gold btn-sm"><i class="bi bi-person"></i> My Profile</a>
            <a href="women-clothes.php" class="btn-outline-gold btn-sm"><i class="bi bi-bag"></i> Shop Now</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============ HERO WITH BACKGROUND IMAGE ============ -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="hero-swatch s1"></div>
    <div class="hero-swatch s2"></div>
    <div class="hero-swatch s3"></div>
    <div class="hero-swatch s4"></div>
    <div class="container text-center position-relative" style="z-index:2;">
        <div class="eyebrow mb-3 reveal in-view">✨ Premium Quality Unstitched Fabrics</div>
        <h1 class="mb-3 reveal in-view">Welcome to Ansari<br>Clothes &amp; Bed Sheet</h1>
        <p class="lead-text mx-auto mb-4 reveal in-view reveal-delay-1">
            <span class="highlight">Premium quality fabrics</span> for the whole family — everyday wear, festive clothing, and bedding sets
            crafted for comfort. Discover pieces made to be lived in.
        </p>
        <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap reveal in-view reveal-delay-2">
            <a href="women-clothes.php" class="btn-gold fw-bold"><i class="bi bi-bag-heart"></i> Shop Women Clothes</a>
            <a href="bedding-set.php" class="btn-outline-gold fw-bold"><i class="bi bi-bed"></i> Explore Bedding Set</a>
        </div>
        <div class="d-flex justify-content-center hero-stats flex-wrap reveal in-view reveal-delay-3">
            <div class="stat-item"><div class="num">12k+</div><div class="lbl">Happy Customers</div></div>
            <div class="stat-item"><div class="num">450+</div><div class="lbl">Products</div></div>
            <div class="stat-item"><div class="num">100%</div><div class="lbl">Cotton Fabric</div></div>
            <div class="stat-item"><div class="num">4.8★</div><div class="lbl">Customer Rating</div></div>
        </div>
    </div>
</section>

<!-- ============ STITCH DIVIDER ============ -->
<div class="stitch-divider">
    <svg viewBox="0 0 1000 24" preserveAspectRatio="none">
        <path class="stitch-path" d="M0,12 Q 250,-10 500,12 T 1000,12"></path>
    </svg>
    <i class="bi bi-scissors needle"></i>
</div>

<!-- ============ OFFER BANNER ============ -->
<div class="container">
    <div class="offer-banner reveal-scale">
        <h2>🔥 Summer Sale - Up to 30% Off</h2>
        <p>Get the best deals on premium fabrics and bedding sets. Limited time offer!</p>
        <div class="offer-timer">
            <div class="time-box"><div class="num" id="days">00</div><div class="label">Days</div></div>
            <div class="time-box"><div class="num" id="hours">00</div><div class="label">Hours</div></div>
            <div class="time-box"><div class="num" id="minutes">00</div><div class="label">Mins</div></div>
            <div class="time-box"><div class="num" id="seconds">00</div><div class="label">Secs</div></div>
        </div>
        <a href="women-clothes.php" class="btn-gold" style="width:auto;padding:0.8rem 2.5rem;display:inline-block;">
            <i class="bi bi-bag"></i> Shop Now
        </a>
    </div>
</div>

<!-- ============ CATEGORIES SECTION - WITH BACKGROUND IMAGE ============ -->
<section class="categories-section" id="women">
    <div class="section-bg"></div>
    
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow text-warning">✨ Shop by category</div>
            <h2 class="section-title text-light">Everything for your wardrobe &amp; bed</h2>
            <div class="divider-line mx-auto"></div>
            <p class="text-muted-gold mx-auto" style="max-width:560px;">From daily wear to bedroom comfort, every Ansari piece is chosen for fabric quality and finish.</p>
        </div>
        <div class="row g-4">
            <div class="col-6 col-lg-3 reveal-rotate">
                <a href="women-clothes.php" class="cat-card d-block text-decoration-none" style="--cat-grad:linear-gradient(155deg,#3a2f10,#0b0a08);">
                    <img src="assets/images/21.webp" alt="Women Clothes" class="cat-image">
                    <div class="cat-overlay"></div>
                    <i class="bi bi-gender-female cat-icon"></i>
                    <div class="cat-body">
                        <span>WOMEN</span>
                        <h5>Women Clothes</h5>
                        <span class="cat-arrow"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 reveal-rotate reveal-delay-1" id="men">
                <a href="men-clothes.php" class="cat-card d-block text-decoration-none" style="--cat-grad:linear-gradient(155deg,#2c2a24,#0b0a08);">
                    <img src="assets/images/m2.jfif" alt="Men Clothes" class="cat-image">
                    <div class="cat-overlay"></div>
                    <i class="bi bi-gender-male cat-icon"></i>
                    <div class="cat-body">
                        <span>MEN</span>
                        <h5>Men Clothes</h5>
                        <span class="cat-arrow"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 reveal-rotate reveal-delay-2" id="bedsheet">
                <a href="bedding-set.php" class="cat-card d-block text-decoration-none" style="--cat-grad:linear-gradient(155deg,#332a12,#0b0a08);">
                    <img src="assets/images/b1.webp" alt="Bed Sheets" class="cat-image">
                    <div class="cat-overlay"></div>
                    <i class="bi bi-inboxes cat-icon"></i>
                    <div class="cat-body">
                        <span>HOME</span>
                        <h5>Bed Sheets</h5>
                        <span class="cat-arrow"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 reveal-rotate reveal-delay-3" id="bedding">
                <a href="bedding-set.php" class="cat-card d-block text-decoration-none" style="--cat-grad:linear-gradient(155deg,#2a241a,#0b0a08);">
                    <img src="assets/images/b2.jfif" alt="Bedding Sets" class="cat-image">
                    <div class="cat-overlay"></div>
                    <i class="bi bi-layers cat-icon"></i>
                    <div class="cat-body">
                        <span>HOME</span>
                        <h5>Bedding Sets</h5>
                        <span class="cat-arrow"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ============ BRAND PARTNERS ============ -->
<section class="brand-partners">
    <div class="container">
        <div class="partner-grid">
            <div class="partner-item reveal-scale"><i class="bi bi-trophy"></i> Premium Quality</div>
            <div class="partner-item reveal-scale reveal-delay-1"><i class="bi bi-truck"></i> Fast Delivery</div>
            <div class="partner-item reveal-scale reveal-delay-2"><i class="bi bi-shield-check"></i> Secure Payments</div>
            <div class="partner-item reveal-scale reveal-delay-3"><i class="bi bi-arrow-repeat"></i> Easy Returns</div>
            <div class="partner-item reveal-scale reveal-delay-4"><i class="bi bi-star-fill"></i> 4.8★ Rating</div>
        </div>
    </div>
</section>

<!-- ============ WHY CHOOSE US & TESTIMONIALS - WITH BACKGROUND IMAGE ============ -->
<section class="why-testi-section">
    <div class="section-bg"></div>
    
    <div class="container">
        <!-- Why Choose Us - COMPLETELY VISIBLE -->
        <div class="text-center mb-5 reveal">
            <div class="eyebrow text-warning">Why Ansari</div>
            <h2 class="section-title text-light">Made for comfort, built to last</h2>
            <div class="divider-line mx-auto"></div>
        </div>
        <div class="row mb-5">
            <div class="col-6 col-lg-3 reveal-ltr">
                <div class="why-item">
                    <div class="why-icon"><i class="bi bi-flower2"></i></div>
                    <h6 class="mb-1 text-light">Premium Fabric</h6>
                    <small class="text-muted-gold">100% quality tested cotton</small>
                </div>
            </div>
            <div class="col-6 col-lg-3 reveal-ltr reveal-delay-1">
                <div class="why-item">
                    <div class="why-icon"><i class="bi bi-truck"></i></div>
                    <h6 class="mb-1 text-light">Fast Delivery</h6>
                    <small class="text-muted-gold">Nationwide shipping</small>
                </div>
            </div>
            <div class="col-6 col-lg-3 reveal-ltr reveal-delay-2">
                <div class="why-item">
                    <div class="why-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <h6 class="mb-1 text-light">Easy Returns</h6>
                    <small class="text-muted-gold">7 day exchange policy</small>
                </div>
            </div>
            <div class="col-6 col-lg-3 reveal-ltr reveal-delay-3">
                <div class="why-item">
                    <div class="why-icon"><i class="bi bi-shield-check"></i></div>
                    <h6 class="mb-1 text-light">Secure Payment</h6>
                    <small class="text-muted-gold">Cash on delivery available</small>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="text-center mb-5 reveal">
            <div class="eyebrow">💬 Testimonials</div>
            <h2 class="section-title text-light">What our customers say</h2>
            <div class="divider-line mx-auto"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-4 reveal-rtl">
                <div class="testi-card">
                    <div class="testi-stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                    <p class="text-muted-gold">"The bed sheet quality is amazing, so soft and the color hasn't faded even after many washes."</p>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="testi-avatar">A</div>
                        <div><strong style="font-size:.9rem;" class="text-light">Ayesha K.</strong><br><small class="text-muted-gold">Lahore</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 reveal-rtl reveal-delay-1">
                <div class="testi-card">
                    <div class="testi-stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i></div>
                    <p class="text-muted-gold">"Great fitting shirts and quick delivery. Ansari is now my go-to for men's clothing."</p>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="testi-avatar">B</div>
                        <div><strong style="font-size:.9rem;" class="text-light">Bilal R.</strong><br><small class="text-muted-gold">Multan</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 reveal-rtl reveal-delay-2">
                <div class="testi-card">
                    <div class="testi-stars mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                    <p class="text-muted-gold">"Bought a full bedding set for my new home — the finishing and stitching is top notch."</p>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="testi-avatar">S</div>
                        <div><strong style="font-size:.9rem;" class="text-light">Sana M.</strong><br><small class="text-muted-gold">Bahawalpur</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ NEWSLETTER BAND ============ -->
<section class="news-band">
    <div class="container text-center reveal">
        <h3 class="mb-2 text-light">Join the Ansari family</h3>
        <p class="text-muted-gold mb-4">Get early access to new arrivals and exclusive festive offers.</p>
        <form class="d-flex justify-content-center mx-auto" style="max-width:460px;" method="POST" action="subscribe.php">
            <input type="email" name="email" class="form-control form-gold" placeholder="Your email address" required>
            <button class="btn-gold" style="border-radius:0 8px 8px 0;"><i class="bi bi-envelope"></i> Subscribe</button>
        </form>
    </div>
</section>

<button class="scroll-top" id="scrollTop"><i class="bi bi-arrow-up"></i></button>

<script>
// =========================================================
// SCROLL REVEAL ANIMATION - ENHANCED
// =========================================================
document.addEventListener('DOMContentLoaded', function() {
    // All reveal elements
    const revealElements = document.querySelectorAll('.reveal, .reveal-scale, .reveal-ltr, .reveal-rtl, .reveal-rotate');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
            }
        });
    }, { 
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    revealElements.forEach(el => revealObserver.observe(el));

    // =========================================================
    // SCROLL TO TOP BUTTON
    // =========================================================
    const scrollBtn = document.getElementById('scrollTop');
    if (scrollBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) scrollBtn.classList.add('show');
            else scrollBtn.classList.remove('show');
        });
        scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // =========================================================
    // OFFER COUNTDOWN TIMER
    // =========================================================
    function startCountdown() {
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 7);
        targetDate.setHours(23, 59, 59, 0);

        function updateTimer() {
            const now = new Date().getTime();
            const distance = targetDate.getTime() - now;

            if (distance < 0) {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    if (document.getElementById('days')) {
        startCountdown();
    }

    // =========================================================
    // PARALLAX EFFECT ON HERO
    // =========================================================
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const heroBg = hero.querySelector('.hero-bg');
            if (heroBg) {
                heroBg.style.transform = `scale(${1 + scrolled * 0.0003}) translateY(${scrolled * 0.05}px)`;
            }
        });
    }

    // =========================================================
    // COUNTER ANIMATION FOR STATS
    // =========================================================
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const step = target / (duration / 16);
        const timer = setInterval(() => {
            start += step;
            if (start >= target) {
                clearInterval(timer);
                element.textContent = target + '+';
            } else {
                element.textContent = Math.floor(start) + '+';
            }
        }, 16);
    }

    const stats = document.querySelectorAll('.hero-stats .stat-item .num');
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const text = el.textContent;
                const number = parseInt(text.replace(/[^0-9]/g, ''));
                if (number > 0) {
                    el.textContent = '0';
                    animateCounter(el, number);
                }
                statsObserver.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => statsObserver.observe(stat));

    console.log('✅ All animations loaded successfully!');
});
</script>

<?php
include 'includes/footer.php';
?>