<?php 
// bedding-set.php - Complete Dynamic Page
require_once 'config.php';
include 'includes/header.php';

// Get ALL products from database for BEDDING category
$conn = getDBConnection();
$products_result = $conn->query("SELECT * FROM products WHERE category = 'bedding' ORDER BY created_at DESC");
$total_products = $products_result->num_rows;

// Get wishlist status if logged in
$wishlistIds = [];
if (isLoggedIn()) {
    $wishlist = getWishlistItems($_SESSION['user_id']);
    if ($wishlist && $wishlist->num_rows > 0) {
        while ($item = $wishlist->fetch_assoc()) {
            $wishlistIds[] = $item['product_id'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bedding Sets | Ansari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
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
            --teal: #1abc9c;
            --green: #27ae60;
        }
        .page-top-margin { margin-top: 80px; }
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
        .text-green { color: var(--green) !important; }
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
        .scroll-progress {
            position: fixed; top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-bright), var(--teal));
            z-index: 9999;
            transform-origin: 0%;
            transform: scaleX(0);
            transition: transform 0.1s ease;
        }
        .page-banner {
            position: relative;
            min-height: 85vh;
            display: flex;
            align-items: center;
            background: radial-gradient(ellipse 70% 80% at 30% 20%, rgba(212,175,55,.10), transparent 60%), var(--bg);
            overflow: hidden;
            border-bottom: 1px solid var(--stroke);
        }
        .banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(11,10,8,0.8) 0%, rgba(11,10,8,0.2) 100%);
            z-index: 1;
        }
        .banner-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(212,175,55,0.04);
            animation: floatShape 8s ease-in-out infinite;
            z-index: 0;
        }
        .banner-shape.shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; animation-delay: 0s; }
        .banner-shape.shape-2 { width: 250px; height: 250px; bottom: -50px; left: 10%; animation-delay: 2s; }
        .banner-shape.shape-3 { width: 150px; height: 150px; top: 40%; right: 20%; animation-delay: 4s; }
        @keyframes floatShape {
            0%, 100% { transform: translate(0) scale(1); opacity: 0.4; }
            50% { transform: translate(-30px, 20px) scale(1.2); opacity: 0.8; }
        }
        .banner-hero-image {
            position: relative;
            z-index: 2;
            animation: floatImage 6s ease-in-out infinite;
        }
        @keyframes floatImage {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(2deg); }
        }
        .banner-hero-image img {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            border: 1px solid var(--stroke);
        }
        .fade-in-down {
            opacity: 0;
            animation: fadeInDown 0.8s ease forwards;
        }
        .fade-in-down.delay-1 { animation-delay: 0.2s; }
        .fade-in-down.delay-2 { animation-delay: 0.4s; }
        .fade-in-down.delay-3 { animation-delay: 0.6s; }
        .fade-in-down.delay-4 { animation-delay: 0.8s; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            opacity: 0;
            animation: fadeInUp 0.8s ease forwards;
        }
        .fade-in-up.delay-1 { animation-delay: 0.2s; }
        .fade-in-up.delay-2 { animation-delay: 0.4s; }
        .fade-in-up.delay-3 { animation-delay: 0.6s; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.2rem;
            border: 1px solid var(--stroke);
            border-radius: 50px;
            font-size: 0.85rem;
            color: var(--muted);
            transition: all .3s ease;
        }
        .stat-badge:hover {
            border-color: var(--gold);
            color: var(--gold);
            transform: translateY(-2px);
        }
        .stat-badge i { color: var(--gold); font-size: 1.1rem; }
        .trend-ticker {
            background: var(--bg-panel);
            border-top: 1px solid var(--stroke);
            border-bottom: 1px solid var(--stroke);
            overflow: hidden;
            white-space: nowrap;
            padding: .8rem 0;
        }
        .trend-track {
            display: inline-flex;
            gap: 2.5rem;
            animation: ticker-scroll 22s linear infinite;
        }
        .trend-track span {
            color: var(--muted);
            font-size: .85rem;
            letter-spacing: .05em;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }
        .trend-track span i { color: var(--gold); }
        @keyframes ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .sale-banner {
            background: linear-gradient(135deg, rgba(39,174,96,.12), rgba(212,175,55,.08));
            border: 1px solid var(--stroke);
            border-radius: 20px;
            padding: 2.5rem 2.5rem;
            position: relative;
            overflow: hidden;
            transition: all .4s ease;
        }
        .sale-banner:hover {
            border-color: var(--gold);
            box-shadow: 0 10px 40px rgba(212,175,55,.1);
        }
        .sale-banner::before {
            content: '🛏️';
            position: absolute;
            right: 30px; top: 30px;
            font-size: 4rem;
            opacity: .15;
            animation: sparkle 3s ease-in-out infinite;
        }
        @keyframes sparkle {
            0%,100% { transform: scale(1) rotate(0deg); opacity: .15; }
            50% { transform: scale(1.3) rotate(20deg); opacity: .3; }
        }
        .prod-card {
            background: var(--bg-panel);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            overflow: hidden;
            transition: all .4s ease;
            height: 100%;
            position: relative;
        }
        .prod-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, .5);
            border-color: var(--gold);
        }
        .prod-thumb {
            height: 260px;
            position: relative;
            overflow: hidden;
            background: var(--bg-panel-2);
        }
        .prod-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .7s ease;
        }
        .prod-card:hover .prod-thumb img { transform: scale(1.08); }
        .prod-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .05em;
            padding: .3rem .9rem;
            border-radius: 30px;
            z-index: 2;
            text-transform: uppercase;
            animation: badgePulse 2s ease-in-out infinite;
        }
        .prod-badge.new {
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            color: #1a1305;
        }
        .prod-badge.sale {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: #fff;
        }
        .prod-badge.bestseller {
            background: linear-gradient(135deg, #2980b9, #3498db);
            color: #fff;
        }
        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .prod-actions {
            position: absolute;
            bottom: -80px;
            left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.95));
            padding: 30px 16px 16px;
            transition: bottom .4s ease;
            display: flex;
            gap: 8px;
        }
        .prod-card:hover .prod-actions { bottom: 0; }
        .wishlist-btn {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--stroke);
            background: rgba(11,10,8,0.7);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .3s ease;
            cursor: pointer;
            z-index: 2;
            backdrop-filter: blur(6px);
        }
        .wishlist-btn:hover {
            background: var(--gold);
            color: #1a1305;
            transform: scale(1.15);
        }
        .wishlist-btn.active {
            background: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }
        .wishlist-btn.active i { color: #fff; }
        .prod-info {
            padding: 1.2rem 1.2rem 1.4rem;
        }
        .prod-info h6 {
            font-weight: 600;
            margin-bottom: .2rem;
            color: var(--cream);
            font-size: 1rem;
        }
        .prod-info small { color: var(--muted); font-size: .75rem; }
        .prod-price {
            color: var(--gold);
            font-weight: 600;
            font-size: 1.1rem;
        }
        .prod-price .old-price {
            color: var(--muted);
            text-decoration: line-through;
            font-size: .8rem;
            margin-left: 6px;
            font-weight: 300;
        }
        .color-dots {
            display: flex;
            gap: 6px;
            margin: 8px 0;
        }
        .color-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid var(--stroke);
            cursor: pointer;
            transition: all .2s ease;
        }
        .color-dot:hover {
            transform: scale(1.2);
            border-color: var(--gold);
        }
        .rating-stars {
            color: var(--gold-bright);
            font-size: .8rem;
            letter-spacing: 2px;
        }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold-bright), var(--gold));
            border: none;
            color: #1a1305;
            font-weight: 600;
            padding: .6rem 1.8rem;
            border-radius: 10px;
            transition: all .3s ease;
        }
        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212,175,55,.35);
            color: #1a1305;
        }
        .btn-gold.btn-sm {
            padding: .4rem 1rem;
            font-size: .8rem;
        }
        .btn-outline-gold {
            border: 1.5px solid var(--gold);
            color: var(--gold);
            background: transparent;
            font-weight: 600;
            padding: .6rem 1.8rem;
            border-radius: 10px;
            transition: all .3s ease;
        }
        .btn-outline-gold:hover {
            background: var(--gold);
            color: #1a1305;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212,175,55,.25);
        }
        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--stroke);
            color: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .3s ease;
            background: transparent;
            flex-shrink: 0;
        }
        .icon-btn:hover {
            background: var(--gold);
            color: #1a1305;
            border-color: var(--gold);
            transform: scale(1.1);
        }
        .news-band {
            background: linear-gradient(120deg, rgba(212,175,55,.12), rgba(212,175,55,.03));
            border-top: 1px solid var(--stroke);
            border-bottom: 1px solid var(--stroke);
            padding: 3.5rem 0;
        }
        .form-gold {
            background: var(--bg);
            border: 1px solid var(--stroke);
            color: var(--cream);
            border-radius: 10px 0 0 10px;
            padding: .8rem 1.2rem;
        }
        .form-gold:focus {
            background: var(--bg);
            color: var(--cream);
            border-color: var(--gold);
            box-shadow: none;
        }
        .form-gold::placeholder { color: var(--muted); }
        .scroll-top {
            position: fixed;
            right: 24px; bottom: 24px;
            z-index: 999;
            width: 50px; height: 50px;
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
            box-shadow: 0 4px 20px rgba(212,175,55,.3);
            cursor: pointer;
        }
        .scroll-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .scroll-top:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 30px rgba(212,175,55,.4);
        }
        .quick-view-modal .modal-content {
            background: var(--bg-panel);
            border: 1px solid var(--stroke);
            border-radius: 20px;
            color: var(--cream);
        }
        .quick-view-modal .modal-header {
            border-bottom: 1px solid var(--stroke);
            padding: 1.2rem 1.5rem;
        }
        .quick-view-modal .modal-header .btn-close { filter: invert(1) brightness(2); }
        .quick-view-modal .modal-body { padding: 1.5rem; }
        .quick-view-modal .modal-footer {
            border-top: 1px solid var(--stroke);
            padding: 1.2rem 1.5rem;
        }
        .qv-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }
        .qv-price {
            color: var(--gold);
            font-size: 1.8rem;
            font-weight: 700;
        }
        .qv-old-price {
            color: var(--muted);
            text-decoration: line-through;
            font-size: 1.2rem;
            margin-left: 1rem;
        }
        .qv-rating {
            color: var(--gold-bright);
            font-size: 1.1rem;
        }
        .qv-color-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid var(--stroke);
            cursor: pointer;
            transition: all .2s ease;
        }
        .qv-color-dot:hover {
            transform: scale(1.15);
            border-color: var(--gold);
        }
        .qv-color-dot.active {
            border-color: var(--gold);
            box-shadow: 0 0 15px rgba(212,175,55,0.3);
        }
        .qv-badge {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .qv-badge.new {
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            color: #1a1305;
        }
        .qv-badge.sale { background: #dc3545; color: #fff; }
        .qv-badge.bestseller { background: #2980b9; color: #fff; }
        .toast-notification {
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: var(--bg-panel);
            border: 1px solid var(--gold);
            color: var(--cream);
            padding: 0.8rem 1.8rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            pointer-events: none;
        }
        .toast-notification.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid #1a1305;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .order-form-input {
            background: var(--bg);
            border: 1px solid var(--stroke);
            color: var(--cream);
            padding: .7rem 1rem;
            border-radius: 8px;
            width: 100%;
        }
        .order-form-input:focus {
            background: var(--bg);
            color: var(--cream);
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212,175,55,.15);
            outline: none;
        }
        .order-form-input::placeholder { color: var(--muted); }
        .order-form-input option {
            background: var(--bg-panel);
            color: var(--cream);
        }
        .order-form-label {
            color: var(--muted);
            font-size: .85rem;
            font-weight: 500;
        }
        .color-select-label {
            color: var(--muted);
            font-size: .85rem;
            font-weight: 500;
            display: block;
            margin-bottom: 0.5rem;
        }
        .color-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .color-option {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid var(--stroke);
            cursor: pointer;
            transition: all .3s ease;
            position: relative;
        }
        .color-option:hover {
            transform: scale(1.15);
            border-color: var(--gold);
        }
        .color-option.active {
            border-color: var(--gold);
            box-shadow: 0 0 15px rgba(212,175,55,0.4);
            transform: scale(1.1);
        }
        .color-option .check-mark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: 0.7rem;
            display: none;
        }
        .color-option.active .check-mark { display: block; }
        @media (max-width: 991px) {
            .page-banner { min-height: 70vh; }
            .banner-hero-image img { max-height: 350px; }
            .section { padding: 3.5rem 0; }
            .page-top-margin { margin-top: 60px; }
        }
        @media (max-width: 767px) {
            .page-banner { min-height: 60vh; text-align: center; }
            .page-banner .text-lg-start { text-align: center !important; }
            .page-banner .divider-line { margin: .9rem auto 1.2rem; }
            .prod-thumb { height: 200px; }
            .sale-banner { padding: 1.8rem; text-align: center; }
            .banner-hero-image img { max-height: 250px; }
            .banner-shape { display: none; }
            .qv-image { max-height: 250px; }
            .page-top-margin { margin-top: 50px; }
        }
        @media (max-width: 480px) {
            .prod-thumb { height: 160px; }
            .prod-info { padding: .8rem .9rem 1rem; }
            .prod-info h6 { font-size: .85rem; }
            .prod-info small { font-size: .65rem; }
            .prod-price { font-size: .9rem; }
            .btn-gold { font-size: .75rem; padding: .4rem 1rem; }
            .icon-btn { width: 30px; height: 30px; font-size: .8rem; }
            .page-banner h1 { font-size: 2rem; }
            .page-banner { min-height: 50vh; }
            .sale-banner { padding: 1.2rem; }
            .sale-banner h2 { font-size: 1.4rem; }
            .wishlist-btn { width: 30px; height: 30px; font-size: .7rem; }
            .qv-price { font-size: 1.4rem; }
            .page-top-margin { margin-top: 40px; }
        }
    </style>
</head>
<body>

<!-- SCROLL PROGRESS -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- PAGE BANNER -->
<section class="page-banner page-top-margin">
    <div class="banner-overlay"></div>
    <div class="banner-shape shape-1"></div>
    <div class="banner-shape shape-2"></div>
    <div class="banner-shape shape-3"></div>
    
    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="eyebrow fade-in-down mt-5">Home Collection 2026</div>
                <h1 class="text-light fade-in-down delay-1" style="font-size:clamp(2.2rem,4.5vw,3.5rem);">
                    Luxurious <br><span style="color:var(--gold);">Bedding Sets</span>
                </h1>
                <div class="divider-line fade-in-down delay-2"></div>
                <p class="text-muted-gold fade-in-down delay-2" style="max-width:500px;font-size:1.05rem;">
                    Discover our premium collection of bedding sets — from soft cotton sheets to 
                    luxurious quilt covers. Each piece is crafted for ultimate comfort and style.
                </p>
                <div class="d-flex flex-wrap gap-3 mt-4 fade-in-down delay-3">
                    <span class="stat-badge"><i class="bi bi-star-fill"></i> 4.9/5 Rating</span>
                    <span class="stat-badge"><i class="bi bi-people"></i> 10k+ Happy Customers</span>
                    <span class="stat-badge"><i class="bi bi-flower2"></i> 100% Cotton</span>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-4 fade-in-down delay-4">
                    <a href="#collection" class="btn btn-gold btn-lg">
                        <i class="bi bi-bag me-2"></i> Shop Now
                    </a>
                    <a href="#collection" class="btn btn-outline-gold btn-lg">
                        <i class="bi bi-grid me-2"></i> View Collection
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center banner-hero-image fade-in-up">
                <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=600&h=500&fit=crop" 
                     alt="Bedding Collection" 
                     class="img-fluid">
                <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                    <span class="badge" style="background:var(--gold); color:#1a1305; padding:0.4rem 1rem;">
                        <i class="bi bi-fire me-1"></i> New Arrivals
                    </span>
                    <span class="badge" style="border:1px solid var(--gold); color:var(--gold); padding:0.4rem 1rem;">
                        <i class="bi bi-tags me-1"></i> Up to 40% Off
                    </span>
                    <span class="badge" style="border:1px solid var(--green); color:var(--green); padding:0.4rem 1rem;">
                        <i class="bi bi-gem me-1"></i> Premium Quality
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TRENDING TICKER -->
<div class="trend-ticker">
    <div class="trend-track">
        <span><i class="bi bi-fire"></i> Trending: Cotton Bed Sheets</span>
        <span><i class="bi bi-star-fill"></i> Bestseller: Premium Bedding Set</span>
        <span><i class="bi bi-lightning-charge-fill"></i> Flash Sale: Quilt Covers</span>
        <span><i class="bi bi-truck"></i> Free Delivery over Rs. 3,000</span>
        <span><i class="bi bi-gem"></i> New Arrivals: Luxury Collection</span>
        <span><i class="bi bi-fire"></i> Trending: Cotton Bed Sheets</span>
        <span><i class="bi bi-star-fill"></i> Bestseller: Premium Bedding Set</span>
        <span><i class="bi bi-lightning-charge-fill"></i> Flash Sale: Quilt Covers</span>
        <span><i class="bi bi-truck"></i> Free Delivery over Rs. 3,000</span>
        <span><i class="bi bi-gem"></i> New Arrivals: Luxury Collection</span>
    </div>
</div>

<!-- SALE BANNER -->
<section class="section pt-0" data-aos="fade-up">
    <div class="container mt-5">
        <div class="sale-banner">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="eyebrow text-gold">🔥 Limited Time Offer</div>
                    <h2 class="section-title mb-2" style="color:var(--gold-bright);">
                        Big Sale on Bedding Collection
                    </h2>
                    <p class="text-muted-gold mb-0" style="font-size:1.05rem;">
                        Up to 40% off on selected bed sheets, bedding sets &amp; quilt covers. 
                        <span style="color:var(--gold);">Hurry, don't miss out!</span>
                    </p>
                    <div class="d-flex gap-3 mt-3 flex-wrap">
                        <span class="stat-badge"><i class="bi bi-clock"></i> Ends in: 4 Days</span>
                        <span class="stat-badge"><i class="bi bi-tags"></i> 25+ Items on Sale</span>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="#collection" class="btn btn-gold btn-lg px-5 py-3" style="font-size:1.1rem;">
                        <i class="bi bi-tags me-2"></i> Grab Sale Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     PRODUCT GRID - DYNAMIC FROM DATABASE
     ========================================================= -->
<section class="section" style="background:var(--bg-panel);" id="collection">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5" data-aos="fade-up">
            <div>
                <div class="eyebrow">Home</div>
                <h2 class="section-title mb-0">Our Collection</h2>
                <p class="text-muted-gold mt-1" style="font-size:0.9rem;">
                    Showing <?php echo $total_products; ?> products
                </p>
            </div>
        </div>

        <div class="row g-4" id="productGrid">
            <?php if ($products_result && $products_result->num_rows > 0): ?>
                <?php 
                while ($product = $products_result->fetch_assoc()): 
                    $colors = json_decode($product['colors'] ?? '["#d4af37","#e74c3c","#2c3e50"]', true);
                    $is_in_wishlist = in_array($product['id'], $wishlistIds);
                    $image_url = !empty($product['image_url']) ? $product['image_url'] : 'assets/images/b1.webp';
                    $rating = $product['rating'] ?? 0;
                ?>
                <div class="col-sm-6 col-lg-3 product-item" 
                     data-category="<?php echo $product['sub_category'] ?? 'all'; ?>" 
                     data-product-id="<?php echo $product['id']; ?>">
                    <div class="prod-card">
                        <div class="prod-thumb">
                            <img src="<?php echo $image_url; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php if ($product['is_new']): ?>
                                <span class="prod-badge new">New</span>
                            <?php endif; ?>
                            <?php if ($product['is_sale']): ?>
                                <span class="prod-badge sale">Sale</span>
                            <?php endif; ?>
                            <?php if ($product['is_featured']): ?>
                                <span class="prod-badge bestseller">Bestseller</span>
                            <?php endif; ?>
                            <button class="wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" 
                                    onclick="toggleWishlist(this, <?php echo $product['id']; ?>)">
                                <i class="bi bi-<?php echo $is_in_wishlist ? 'heart-fill' : 'heart'; ?>"></i>
                            </button>
                            <div class="prod-actions">
                                <button class="btn btn-gold flex-grow-1 btn-sm quick-view-btn" 
                                        data-product-id="<?php echo $product['id']; ?>">
                                    <i class="bi bi-eye me-1"></i> Quick View
                                </button>
                                <button class="icon-btn add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="prod-info">
                            <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= round($rating) ? '★' : '☆';
                                } ?>
                            </div>
                            <small class="text-muted-gold">Home - <?php echo ucfirst($product['sub_category'] ?? 'Collection'); ?></small>
                            <div class="color-dots">
                                <?php 
                                $display_colors = array_slice($colors, 0, 3);
                                foreach ($display_colors as $color): 
                                ?>
                                    <span class="color-dot" style="background:<?php echo $color; ?>;"></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="my-2">
                                <span class="prod-price">Rs. <?php echo number_format($product['price']); ?>
                                    <?php if ($product['old_price']): ?>
                                        <span class="old-price">Rs. <?php echo number_format($product['old_price']); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <button class="btn btn-gold w-100 btn-sm order-now-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="bi bi-bag-check me-1"></i> Order Now
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box" style="font-size:3rem;color:var(--muted);"></i>
                    <h5>No products found in this category</h5>
                    <p class="text-muted">Check back later for new arrivals.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- WHY CUSTOMERS LOVE ANSARI BEDDING -->
<section class="section" data-aos="fade-up">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-6" data-aos="fade-right">
                <div class="eyebrow">why customers love ansari bedding</div>
                <h2 class="section-title">Comfort that transforms your bedroom</h2>
                <div class="divider-line"></div>
                <ul class="list-unstyled text-muted-gold" style="font-size:.98rem;">
                    <li class="mb-3" data-aos="fade-right" data-aos-delay="100">
                        <i class="bi bi-check-circle-fill text-gold me-2"></i>100% pure cotton for ultimate comfort
                    </li>
                    <li class="mb-3" data-aos="fade-right" data-aos-delay="150">
                        <i class="bi bi-check-circle-fill text-gold me-2"></i>Premium quality that lasts for years
                    </li>
                    <li class="mb-3" data-aos="fade-right" data-aos-delay="200">
                        <i class="bi bi-check-circle-fill text-gold me-2"></i>Easy care — machine washable
                    </li>
                    <li class="mb-3" data-aos="fade-right" data-aos-delay="250">
                        <i class="bi bi-check-circle-fill text-gold me-2"></i>Stylish designs for every home
                    </li>
                </ul>
                <a href="#collection" class="btn btn-gold mt-2" data-aos="fade-up" data-aos-delay="300">
                    <i class="bi bi-arrow-right me-2"></i> Explore the Collection
                </a>
            </div>
            <div class="col-md-6 text-center" data-aos="fade-left">
                <div style="background:var(--bg-panel); border:1px solid var(--stroke); border-radius:30px; padding:2.5rem 2rem;">
                    <i class="bi bi-gem" style="font-size:3.6rem; color:var(--gold-bright); opacity:.3;"></i>
                    <p class="mt-3 mb-0 text-muted-gold" style="font-style:italic;font-size:1.05rem;">
                        "Ansari's bedding sets are incredibly soft and comfortable — best sleep I've ever had!"
                    </p>
                    <small class="text-gold">— Fatima, Lahore</small>
                    <div class="mt-3">
                        <span class="text-gold" style="font-size:1.2rem;">★★★★★</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section class="news-band" data-aos="fade-up">
    <div class="container text-center">
        <h3 class="mb-2 text-light" style="font-size:clamp(1.5rem,2.5vw,2rem);">Join the Ansari Family</h3>
        <p class="text-muted-gold mb-4" style="font-size:1.05rem;">
            Get early access to new arrivals and exclusive festive offers.
        </p>
        <form class="d-flex justify-content-center mx-auto" style="max-width:480px;" id="subscribeForm">
            <input type="email" class="form-control form-gold" id="subscribeEmail" placeholder="Your email address" required>
            <button class="btn btn-gold" style="border-radius:0 10px 10px 0; padding:0.8rem 2rem;" type="submit">
                Subscribe
            </button>
        </form>
        <div id="subscribeMessage" class="mt-3"></div>
    </div>
</section>

<!-- QUICK VIEW MODAL -->
<div class="modal fade quick-view-modal" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="qvModalBody">
                <div class="text-center py-4">
                    <div class="loading-spinner" style="width:2rem;height:2rem;"></div>
                    <p class="text-muted-gold mt-2">Loading product details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ORDER NOW MODAL -->
<div class="modal fade quick-view-modal" id="orderNowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bag-check text-gold"></i> Order Now</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <div class="text-center py-4">
                    <div class="loading-spinner" style="width:2rem;height:2rem;"></div>
                    <p class="text-muted-gold mt-2">Loading order details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCROLL TOP -->
<button class="scroll-top" id="scrollTop">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// =========================================================
// CONFIGURATION
// =========================================================
const CONFIG = {
    baseUrl: '<?php echo SITE_URL; ?>',
    isLoggedIn: <?php echo isLoggedIn() ? 'true' : 'false'; ?>,
    cartCount: <?php echo getCartCount(); ?>,
    wishlistCount: <?php echo getWishlistCount(); ?>
};

// =========================================================
// INIT AOS
// =========================================================
AOS.init({ duration: 700, once: true, offset: 40 });

// =========================================================
// SCROLL PROGRESS
// =========================================================
const scrollProgress = document.getElementById('scrollProgress');
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    scrollProgress.style.transform = `scaleX(${(scrollTop / docHeight) || 0})`;
});

// =========================================================
// SCROLL TOP
// =========================================================
const scrollBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    scrollBtn.classList.toggle('show', window.scrollY > 400);
});
scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// =========================================================
// QUICK VIEW
// =========================================================
const quickViewModal = new bootstrap.Modal(document.getElementById('quickViewModal'));

document.querySelectorAll('.quick-view-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.dataset.productId;
        loadQuickView(productId);
    });
});

function loadQuickView(productId) {
    const modalBody = document.getElementById('qvModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="loading-spinner" style="width:2rem;height:2rem;"></div>
            <p class="text-muted-gold mt-2">Loading product details...</p>
        </div>
    `;
    quickViewModal.show();
    
    fetch(`${CONFIG.baseUrl}bedding-set.php?ajax=1&action=get_product_detail&product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const p = data.product;
                const imageUrl = p.image_url || 'assets/images/b1.webp';
                const colors = p.colors || ['#d4af37', '#e74c3c', '#2c3e50'];
                const rating = p.rating || 0;
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    starsHtml += i <= Math.round(rating) ? '★' : '☆';
                }
                
                let badgeHtml = '';
                if (p.is_new) badgeHtml = `<span class="qv-badge new me-2">New</span>`;
                if (p.is_sale) badgeHtml += `<span class="qv-badge sale me-2">Sale</span>`;
                if (p.is_featured) badgeHtml += `<span class="qv-badge bestseller">Bestseller</span>`;
                
                let colorsHtml = colors.map(c => 
                    `<span class="qv-color-dot" style="background:${c};"></span>`
                ).join('');
                
                modalBody.innerHTML = `
                    <div class="row g-4">
                        <div class="col-md-6">
                            <img src="${imageUrl}" alt="${p.name}" class="qv-image">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">${badgeHtml}</div>
                            <h3 style="color:var(--cream);font-family:'Playfair Display',serif;">${p.name}</h3>
                            <div class="qv-rating mb-2">${starsHtml} <span style="color:var(--muted);font-size:0.8rem;">(${rating})</span></div>
                            <div>
                                <span class="qv-price">Rs. ${p.price.toLocaleString()}</span>
                                ${p.old_price ? `<span class="qv-old-price">Rs. ${p.old_price.toLocaleString()}</span>` : ''}
                            </div>
                            <p class="text-muted-gold mt-3">${p.description}</p>
                            <div class="mb-3">
                                <small class="text-muted-gold">Category: ${p.sub_category || p.category}</small>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted-gold d-block mb-2">Available Colors:</small>
                                <div class="d-flex gap-2">${colorsHtml}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted-gold">Stock: ${p.stock > 0 ? '<span style="color:#28a745;">In Stock</span>' : '<span style="color:#dc3545;">Out of Stock</span>'}</small>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-gold flex-grow-1 order-now-btn" data-product-id="${p.id}">
                                    <i class="bi bi-bag-check me-2"></i> Order Now
                                </button>
                                <button class="icon-btn add-to-cart" data-product-id="${p.id}" style="width:50px;height:50px;border-radius:50%;">
                                    <i class="bi bi-cart-plus" style="font-size:1.2rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                modalBody.querySelector('.order-now-btn')?.addEventListener('click', function() {
                    quickViewModal.hide();
                    setTimeout(() => loadOrderNow(this.dataset.productId), 300);
                });
                
                modalBody.querySelector('.add-to-cart')?.addEventListener('click', handleAddToCart);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `<div class="text-center py-4"><i class="bi bi-exclamation-triangle" style="font-size:2rem;color:#dc3545;"></i><p class="text-muted-gold mt-2">Error loading product details</p></div>`;
        });
}

// =========================================================
// ORDER NOW
// =========================================================
const orderNowModal = new bootstrap.Modal(document.getElementById('orderNowModal'));

document.querySelectorAll('.order-now-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.dataset.productId;
        loadOrderNow(productId);
    });
});

function loadOrderNow(productId) {
    const modalBody = document.getElementById('orderModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="loading-spinner" style="width:2rem;height:2rem;"></div>
            <p class="text-muted-gold mt-2">Loading...</p>
        </div>
    `;
    orderNowModal.show();
    
    fetch(`${CONFIG.baseUrl}bedding-set.php?ajax=1&action=get_product_detail&product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const p = data.product;
                const imageUrl = p.image_url || 'assets/images/b1.webp';
                const colors = p.colors || ['#d4af37', '#e74c3c', '#2c3e50'];
                
                let colorsHtml = colors.map((c, index) => 
                    `<span class="color-option" style="background:${c};" data-color="${c}" onclick="selectOrderColor(this, ${index})">
                        <span class="check-mark">✓</span>
                    </span>`
                ).join('');
                
                modalBody.innerHTML = `
                    <div class="row g-4">
                        <div class="col-md-4">
                            <img src="${imageUrl}" alt="${p.name}" class="qv-image" style="max-height:200px;">
                            <div class="mt-2 text-center">
                                <h6 style="color:var(--cream);">${p.name}</h6>
                                <span class="qv-price">Rs. ${p.price.toLocaleString()}</span>
                                ${p.old_price ? `<span class="qv-old-price">Rs. ${p.old_price.toLocaleString()}</span>` : ''}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <form id="orderForm">
                                <input type="hidden" name="product_id" value="${p.id}">
                                <input type="hidden" name="ajax" value="1">
                                <input type="hidden" name="action" value="order_now">
                                <input type="hidden" name="color" id="orderColor" value="${colors[0] || ''}">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="order-form-label">Quantity</label>
                                        <input type="number" name="quantity" class="order-form-input" value="1" min="1" max="${p.stock || 10}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="order-form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="order-form-input" placeholder="03XX-XXXXXXX" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="color-select-label">Select Color:</label>
                                        <div class="color-options" id="orderColorOptions">${colorsHtml}</div>
                                        <small class="text-muted-gold">Selected: <span id="orderColorDisplay" style="color:var(--gold);">${colors[0] || 'None'}</span></small>
                                    </div>
                                    <div class="col-12">
                                        <label class="order-form-label">Shipping Address <span class="text-danger">*</span></label>
                                        <textarea name="address" class="order-form-input" rows="2" placeholder="Street, house number, area" required></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="order-form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="order-form-input" placeholder="e.g. Lahore" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="order-form-label">Province <span class="text-danger">*</span></label>
                                        <select name="province" class="order-form-input" required>
                                            <option value="">Select Province</option>
                                            <option value="Punjab">Punjab</option>
                                            <option value="Sindh">Sindh</option>
                                            <option value="Khyber Pakhtunkhwa">Khyber Pakhtunkhwa</option>
                                            <option value="Balochistan">Balochistan</option>
                                            <option value="Gilgit-Baltistan">Gilgit-Baltistan</option>
                                            <option value="Azad Kashmir">Azad Kashmir</option>
                                            <option value="Islamabad">Islamabad</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="order-form-label">Order Notes (Optional)</label>
                                        <textarea name="notes" class="order-form-input" rows="2" placeholder="Any special instructions..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-gold w-100 py-2" id="placeOrderBtn">
                                            <i class="bi bi-check-circle me-2"></i> Place Order
                                        </button>
                                        <div id="orderMessage" class="mt-2"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                document.querySelectorAll('#orderColorOptions .color-option').forEach((el, index) => {
                    if (index === 0) el.classList.add('active');
                });
                
                document.getElementById('orderForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitOrder(this);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `<div class="text-center py-4"><i class="bi bi-exclamation-triangle" style="font-size:2rem;color:#dc3545;"></i><p class="text-muted-gold mt-2">Error loading order details</p></div>`;
        });
}

function selectOrderColor(element, index) {
    document.querySelectorAll('#orderColorOptions .color-option').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    const color = element.dataset.color;
    document.getElementById('orderColor').value = color;
    document.getElementById('orderColorDisplay').textContent = color;
}

function submitOrder(form) {
    const btn = document.getElementById('placeOrderBtn');
    const messageDiv = document.getElementById('orderMessage');
    
    btn.innerHTML = '<span class="loading-spinner"></span> Placing Order...';
    btn.disabled = true;
    messageDiv.innerHTML = '';
    
    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'order_now');
    
    fetch(CONFIG.baseUrl + 'bedding-set.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Place Order';
        btn.disabled = false;
        
        if (data.success) {
            messageDiv.innerHTML = `
                <div class="alert alert-success">
                    ✅ ${data.message}<br>
                    <small>Order ID: #${data.order_id}</small>
                </div>
            `;
            form.reset();
            setTimeout(() => {
                orderNowModal.hide();
                window.location.href = CONFIG.baseUrl + 'orders.php';
            }, 3000);
            showNotification('🎉 ' + data.message);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Place Order';
        btn.disabled = false;
        messageDiv.innerHTML = `<div class="alert alert-danger">Something went wrong. Please try again.</div>`;
    });
}

// =========================================================
// WISHLIST TOGGLE
// =========================================================
function toggleWishlist(btn, productId) {
    if (!CONFIG.isLoggedIn) {
        showNotification('Please login to add to wishlist');
        setTimeout(() => {
            window.location.href = CONFIG.baseUrl + 'login-signup.php';
        }, 1500);
        return;
    }
    
    const isActive = btn.classList.contains('active');
    const action = isActive ? 'remove' : 'add';
    const icon = btn.querySelector('i');
    
    btn.classList.toggle('active');
    if (btn.classList.contains('active')) {
        icon.className = 'bi bi-heart-fill';
    } else {
        icon.className = 'bi bi-heart';
    }
    
    fetch(CONFIG.baseUrl + 'bedding-set.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax=1&action=toggle_wishlist&product_id=${productId}&type=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            btn.classList.toggle('active');
            if (btn.classList.contains('active')) {
                icon.className = 'bi bi-heart-fill';
            } else {
                icon.className = 'bi bi-heart';
            }
            showNotification(data.message || 'Error updating wishlist');
        } else {
            showNotification(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong');
    });
}

// =========================================================
// ADD TO CART
// =========================================================
function handleAddToCart(e) {
    e.stopPropagation();
    const btn = this;
    const productId = btn.dataset.productId;
    if (!productId) return;
    
    if (!CONFIG.isLoggedIn) {
        showNotification('Please login to add to cart');
        setTimeout(() => {
            window.location.href = CONFIG.baseUrl + 'login-signup.php';
        }, 1500);
        return;
    }
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="loading-spinner"></span>';
    btn.disabled = true;
    
    fetch(CONFIG.baseUrl + 'bedding-set.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax=1&action=add_to_cart&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = data.success ? '<i class="bi bi-check-lg"></i>' : originalHtml;
        btn.disabled = false;
        if (data.success) {
            showNotification('🛍️ ' + data.message);
            setTimeout(() => { btn.innerHTML = originalHtml; }, 1500);
        } else {
            showNotification(data.message || 'Error adding to cart');
            setTimeout(() => { btn.innerHTML = originalHtml; }, 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        showNotification('Something went wrong');
    });
}

document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.removeEventListener('click', handleAddToCart);
    btn.addEventListener('click', handleAddToCart);
});

// =========================================================
// SUBSCRIBE FORM
// =========================================================
document.getElementById('subscribeForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('subscribeEmail').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('subscribeMessage');
    
    submitBtn.innerHTML = '<span class="loading-spinner"></span>';
    submitBtn.disabled = true;
    messageDiv.innerHTML = '';
    
    fetch(CONFIG.baseUrl + 'bedding-set.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `ajax=1&action=subscribe&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.innerHTML = '<i class="bi bi-send me-2"></i> Subscribe';
        submitBtn.disabled = false;
        
        if (data.success) {
            messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            document.getElementById('subscribeEmail').value = '';
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
        setTimeout(() => { messageDiv.innerHTML = ''; }, 5000);
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.innerHTML = '<i class="bi bi-send me-2"></i> Subscribe';
        submitBtn.disabled = false;
        messageDiv.innerHTML = `<div class="alert alert-danger">Something went wrong</div>`;
    });
});

// =========================================================
// NOTIFICATION TOAST
// =========================================================
function showNotification(message) {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

console.log('🛏️ Bedding Collection loaded successfully!');
console.log('👤 Logged in: <?php echo isLoggedIn() ? 'Yes' : 'No'; ?>');
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>