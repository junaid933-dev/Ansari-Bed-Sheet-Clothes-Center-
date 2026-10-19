<?php
require_once 'config.php';
// Get team members from database or use static data
$team_members = [
    ['name' => 'Hafiz Faiz Rasool Aseem Ansari', 'role' => 'Founder & CEO', 'image' => 'abu.jpg'],
    ['name' => 'Arshad Naeem Ansari', 'role' => 'Design Director', 'image' => 'assets/images/chachu.jpg'],
    ['name' => 'Shoaib Rasool Ansari', 'role' => 'Production Head', 'image' => '11.jpg'],
    ['name' => 'Muhammad Junaid Ansari', 'role' => 'Customer Experience', 'image' => 'assets/images/junaid.jpg'],
];
include 'includes/header.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="About Ansari — premium clothing brand offering quality cotton fabric for men, women and home.">
<title>About Us | Ansari</title>

<!-- ===== BOOTSTRAP CDN ===== -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- AOS Animation -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<style>
/* ===== RESET & ROOT - BLACK & GOLD THEME ===== */
:root{
  --bg:#0b0a08;
  --bg-panel:#141210;
  --bg-panel-2:#1b1815;
  --gold:#d4af37;
  --gold-bright:#f0c419;
  --gold-dim:#8a7226;
  --cream:#f5f1e8;
  --muted:#a79d8a;
  --stroke:rgba(212,175,55,.28);
}
*{ margin:0; padding:0; box-sizing:border-box; }
html{ scroll-behavior:smooth; }
body{
  background:var(--bg);
  color:var(--cream);
  font-family:'Poppins',sans-serif;
  font-weight:300;
  overflow-x:hidden;
}
h1,h2,h3,h4,h5,h6{ font-family:'Playfair Display',serif; }
a{ text-decoration:none; color:inherit; }

/* ===== UTILITY ===== */
.text-gold{ color:var(--gold) !important; }
.text-muted-gold{ color:var(--muted) !important; }
.bg-gold{ background:var(--gold) !important; }
.eyebrow{
  letter-spacing:.22em;
  font-size:.7rem;
  font-weight:600;
  color:var(--gold);
  text-transform:uppercase;
}
.divider-line{
  width:54px;
  height:2px;
  background:var(--gold);
  margin:.9rem 0 1.2rem;
}
.section{ padding:5rem 0; }
.section-title{
  font-size:clamp(1.7rem,3vw,2.4rem);
  font-weight:700;
  color:var(--cream);
}

/* ===== SCROLL PROGRESS ===== */
.scroll-progress{
  position:fixed; top:0; left:0; right:0;
  height:3px;
  background:linear-gradient(90deg, var(--gold), var(--gold-bright));
  z-index:9999;
  transform-origin:0%;
  transform:scaleX(0);
  transition:transform 0.1s ease;
}

/* ===== ANIMATED BACKGROUND ===== */
.bg-animation{
  position:fixed;
  top:0; left:0;
  width:100%; height:100%;
  z-index:-1;
  pointer-events:none;
  overflow:hidden;
}
.bg-orb{
  position:absolute;
  border-radius:50%;
  filter:blur(100px);
  opacity:0.06;
  animation:orbFloat 25s ease-in-out infinite;
}
.bg-orb:nth-child(1){
  width:400px; height:400px;
  background:var(--gold);
  top:-10%; left:-5%;
}
.bg-orb:nth-child(2){
  width:500px; height:500px;
  background:var(--gold-dim);
  bottom:-10%; right:-5%;
  animation-delay:-8s;
}
.bg-orb:nth-child(3){
  width:300px; height:300px;
  background:var(--gold-bright);
  top:50%; left:50%;
  animation-delay:-16s;
}
@keyframes orbFloat{
  0%,100%{ transform:translate(0,0) scale(1); }
  25%{ transform:translate(60px,-40px) scale(1.1); }
  50%{ transform:translate(-30px,50px) scale(0.9); }
  75%{ transform:translate(40px,-20px) scale(1.05); }
}

/* ===== BUTTONS ===== */
.btn-gold{
  background:linear-gradient(180deg,var(--gold-bright),var(--gold));
  border:none;
  color:#1a1305;
  font-weight:600;
  padding:.5rem 1.35rem;
  border-radius:8px;
  transition:transform .25s ease, box-shadow .25s ease;
}
.btn-gold:hover{
  transform:translateY(-2px);
  box-shadow:0 8px 22px rgba(212,175,55,.28);
  color:#1a1305;
}
.btn-outline-gold{
  border:1px solid var(--gold);
  color:var(--gold);
  background:transparent;
  font-weight:600;
  padding:.5rem 1.35rem;
  border-radius:8px;
  transition:all .3s ease;
}
.btn-outline-gold:hover{
  background:var(--gold);
  color:#1a1305;
  transform:translateY(-2px);
  box-shadow:0 8px 22px rgba(212,175,55,.28);
}

/* ===== STITCH DIVIDER ===== */
.stitch-divider{
  position:relative;
  height:70px;
  background:var(--bg);
  display:flex;
  align-items:center;
  overflow:hidden;
}
.stitch-divider svg{ width:100%; height:24px; }
.stitch-path{
  fill:none;
  stroke:var(--gold-dim);
  stroke-width:2;
  stroke-dasharray:10 10;
  animation:stitch-move 2.5s linear infinite;
}
@keyframes stitch-move{ to{ stroke-dashoffset:-40; } }
.needle{
  offset-path: path("M0,12 Q 250,-10 500,12 T 1000,12 T 1500,12 T 2000,12");
  animation:needle-travel 9s linear infinite;
  position:absolute; top:0; left:0;
  color:var(--gold-bright);
  font-size:.95rem;
}
@keyframes needle-travel{ to{ offset-distance:100%; } }

/* ===== ABOUT HERO ===== */
.about-hero{
  background:var(--bg-panel);
  border:1px solid var(--stroke);
  border-radius:30px;
  padding:3rem 2.5rem;
  position:relative;
  overflow:hidden;
}
.about-hero::before{
  content:'"';
  position:absolute;
  right:30px; bottom:-30px;
  font-size:12rem;
  color:var(--gold);
  opacity:.05;
  font-family:'Playfair Display',serif;
}

/* ===== STATS CARDS ===== */
.stat-card{
  background:var(--bg-panel);
  border:1px solid var(--stroke);
  border-radius:16px;
  padding:2rem 1.5rem;
  text-align:center;
  transition:all .4s ease;
}
.stat-card:hover{
  transform:translateY(-8px);
  border-color:var(--gold);
  box-shadow:0 12px 30px rgba(0,0,0,.4);
}
.stat-number{
  font-family:'Playfair Display',serif;
  font-size:2.8rem;
  font-weight:700;
  color:var(--gold);
}
.stat-label{
  color:var(--muted);
  font-size:.85rem;
  margin-top:.3rem;
  display:block;
}

/* ===== TEAM CARDS ===== */
.team-card{
  background:var(--bg-panel);
  border:1px solid var(--stroke);
  border-radius:16px;
  overflow:hidden;
  transition:all .4s ease;
  text-align:center;
}
.team-card:hover{
  transform:translateY(-8px);
  border-color:var(--gold);
  box-shadow:0 12px 30px rgba(0,0,0,.4);
}
.team-card .team-img{
  width:120px;
  height:120px;
  border-radius:50%;
  object-fit:cover;
  margin-top:1.5rem;
  border:3px solid var(--gold);
}
.team-card .team-info{
  padding:1.2rem 1.5rem 1.8rem;
}
.team-card h5{
  color:var(--cream);
  font-weight:600;
}
.team-card small{
  color:var(--gold);
}

/* ===== MILESTONE ===== */
.milestone-item{
  display:flex;
  gap:1.2rem;
  align-items:flex-start;
  padding:1rem 0;
  border-bottom:1px solid var(--stroke);
}
.milestone-item:last-child{ border-bottom:none; }
.milestone-icon{
  width:50px;
  height:50px;
  min-width:50px;
  border-radius:50%;
  border:1px solid var(--gold);
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--gold);
  font-size:1.3rem;
}
.milestone-content h6{
  color:var(--gold);
  font-weight:600;
  margin-bottom:.2rem;
}
.milestone-content p{
  color:var(--muted);
  font-size:.9rem;
  margin:0;
}

/* ===== VALUES ===== */
.value-card{
  background:var(--bg-panel);
  border:1px solid var(--stroke);
  border-radius:16px;
  padding:2rem 1.5rem;
  text-align:center;
  transition:all .4s ease;
  height:100%;
}
.value-card:hover{
  transform:translateY(-6px);
  border-color:var(--gold);
  box-shadow:0 12px 30px rgba(0,0,0,.4);
}
.value-card i{
  font-size:2.8rem;
  color:var(--gold);
}
.value-card h5{
  color:var(--cream);
  margin-top:1rem;
  font-weight:600;
}
.value-card p{
  color:var(--muted);
  font-size:.85rem;
  margin:0;
}

/* ===== TESTIMONIAL ===== */
.testimonial-card{
  background:var(--bg-panel);
  border:1px solid var(--stroke);
  border-radius:16px;
  padding:2rem;
  position:relative;
  transition:all .4s ease;
}
.testimonial-card:hover{
  border-color:var(--gold);
  box-shadow:0 12px 30px rgba(0,0,0,.4);
}
.testimonial-card::before{
  content:'"';
  position:absolute;
  top:10px; right:20px;
  font-size:4rem;
  color:var(--gold);
  opacity:.15;
  font-family:'Playfair Display',serif;
}
.testimonial-card .testi-img{
  width:60px;
  height:60px;
  border-radius:50%;
  object-fit:cover;
}
.testi-stars{ color:var(--gold-bright); letter-spacing:2px; }

/* ===== SCROLL TOP ===== */
.scroll-top{
  position:fixed;
  right:22px; bottom:22px;
  z-index:999;
  width:48px;height:48px;
  border-radius:50%;
  background:linear-gradient(135deg, var(--gold), var(--gold-bright));
  color:#1a1305;
  display:flex;
  align-items:center;
  justify-content:center;
  opacity:0;
  visibility:hidden;
  transform:translateY(10px);
  transition:all .3s ease;
  border:none;
  font-size:1.2rem;
  box-shadow:0 4px 15px rgba(212,175,55,.3);
}
.scroll-top.show{ opacity:1; visibility:visible; transform:translateY(0); }
.scroll-top:hover{ transform:translateY(-3px) scale(1.05); }

/* ===== RESPONSIVE ===== */
@media (max-width:768px){
  .section{ padding:3rem 0; }
  .about-hero{ padding:2rem 1.5rem; }
  .stat-number{ font-size:2rem; }
  .team-card .team-img{ width:90px; height:90px; }
}
</style>
</head>
<body>

<!-- ===== ANIMATED BACKGROUND ORBS ===== -->
<div class="bg-animation">
  <div class="bg-orb"></div>
  <div class="bg-orb"></div>
  <div class="bg-orb"></div>
</div>

<!-- ===== SCROLL PROGRESS BAR ===== -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- ============ STITCH DIVIDER ============ -->


<!-- ============ ABOUT HERO ============ -->
<section class="section pb-0 mt-3" data-aos="fade-up">
  <div class="container">
    <div class="about-hero" data-aos="fade-up">
      <div class="row align-items-center g-5">
        <div class="col-lg-7" data-aos="fade-right">
          <div class="eyebrow mb-2 mt-5">About Ansari</div>
          <h1 class="section-title" style="font-size:clamp(2.2rem,4.5vw,3.2rem);">
            Crafting Comfort <br class="d-none d-sm-block">Since 2010
          </h1>
          <div class="divider-line"></div>
          <p class="text-muted-gold" style="font-size:1.05rem; max-width:550px;">
            Ansari is a premium clothing brand dedicated to providing high-quality 
            cotton fabric apparel for men, women, and your home. We believe in 
            comfort that doesn't compromise on style.
          </p>
          <p class="text-muted-gold" style="max-width:550px;">
            From our humble beginnings in Lahore to becoming a trusted name across 
            Pakistan, we've stayed true to our commitment — premium quality, 
            ethical practices, and customer satisfaction.
          </p>
          <div class="d-flex flex-wrap gap-3 mt-4">
            <a href="index.php" class="btn btn-gold fw-bold">Shop Our Collection</a>
            <a href="#values" class="btn btn-outline-gold fw-bold ">Our Values</a>
          </div>
        </div>
        <div class="col-lg-5 text-center" data-aos="fade-left" data-aos-delay="100">
          <div style="background:var(--bg-panel-2); border-radius:20px; padding:1.5rem; border:1px solid var(--stroke);">
            <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=600&h=500&fit=crop" 
                 alt="Ansari Brand Story" 
                 style="width:100%; max-height:400px; object-fit:cover; border-radius:14px;">
            <div class="mt-3 d-flex justify-content-center gap-3">
              <span class="badge" style="background:var(--gold); color:#1a1305;">Est. 2010</span>
              <span class="badge border text-gold" style="border-color:var(--gold);">100% Cotton</span>
              <span class="badge border text-muted-gold" style="border-color:var(--stroke);">Made in Pakistan</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ STATS SECTION ============ -->
<section class="section" data-aos="fade-up">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card">
          <div class="stat-number counter" data-target="15">0</div>
          <span class="stat-label">Years of Excellence</span>
        </div>
      </div>
      <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="150">
        <div class="stat-card">
          <div class="stat-number counter" data-target="50">0</div>
          <span class="stat-label">Product Categories</span>
        </div>
      </div>
      <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card">
          <div class="stat-number counter" data-target="10000">0</div>
          <span class="stat-label">Happy Customers</span>
        </div>
      </div>
      <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="250">
        <div class="stat-card">
          <div class="stat-number counter" data-target="4.9">0</div>
          <span class="stat-label">Average Rating ★</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ OUR VALUES ============ -->
<section class="section" style="background:var(--bg-panel);" id="values" data-aos="fade-up">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="eyebrow">Our Values</div>
      <h2 class="section-title">What We Stand For</h2>
      <div class="divider-line mx-auto"></div>
    </div>
    <div class="row g-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="value-card">
          <i class="bi bi-gem"></i>
          <h5>Premium Quality</h5>
          <p>We use only the finest Egyptian cotton and materials, ensuring every piece meets our high standards.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="value-card">
          <i class="bi bi-heart"></i>
          <h5>Customer First</h5>
          <p>Your satisfaction is our priority. We listen, we care, and we deliver the best experience possible.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="value-card">
          <i class="bi bi-tree"></i>
          <h5>Sustainability</h5>
          <p>We're committed to ethical practices, reducing waste, and using eco-friendly materials wherever possible.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ OUR MILESTONES ============ -->
<section class="section" data-aos="fade-up">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-md-6" data-aos="fade-right">
        <div class="eyebrow">Our Journey</div>
        <h2 class="section-title">Milestones That Define Us</h2>
        <div class="divider-line"></div>
        
        <div class="milestone-item" data-aos="fade-right" data-aos-delay="100">
          <div class="milestone-icon"><i class="bi bi-calendar-event"></i></div>
          <div class="milestone-content">
            <h6>2010 — Founded in Lahore</h6>
            <p>Started as a small family business with a passion for quality clothing.</p>
          </div>
        </div>
        
        <div class="milestone-item" data-aos="fade-right" data-aos-delay="150">
          <div class="milestone-icon"><i class="bi bi-shop"></i></div>
          <div class="milestone-content">
            <h6>2014 — First Store Opening</h6>
            <p>Expanded to our first retail location in Lahore's main shopping district.</p>
          </div>
        </div>
        
        <div class="milestone-item" data-aos="fade-right" data-aos-delay="200">
          <div class="milestone-icon"><i class="bi bi-globe2"></i></div>
          <div class="milestone-content">
            <h6>2018 — Online Presence Launched</h6>
            <p>Brought our collection to customers nationwide through our online store.</p>
          </div>
        </div>
        
        <div class="milestone-item" data-aos="fade-right" data-aos-delay="250">
          <div class="milestone-icon"><i class="bi bi-house-heart"></i></div>
          <div class="milestone-content">
            <h6>2022 — Home Collection Added</h6>
            <p>Expanded into premium bedding sets and home accessories.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6" data-aos="fade-left" data-aos-delay="100">
        <div style="background:var(--bg-panel); border:1px solid var(--stroke); border-radius:30px; padding:2rem;">
          <div style="background:var(--bg-panel-2); border-radius:20px; padding:1rem; border:1px solid var(--stroke);">
            <img src="assets\images\our.webp" 
                 alt="Our Journey" 
                 style="width:100%; max-height:400px; object-fit:cover; border-radius:14px;">
          </div>
          <div class="mt-3 text-center">
            <p class="text-gold" style="font-style:italic;">"From a small dream to a trusted brand — our journey continues."</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ TEAM SECTION ============ -->
<section class="section" style="background:var(--bg-panel);" data-aos="fade-up">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="eyebrow">Our Team</div>
      <h2 class="section-title">The People Behind Ansari</h2>
      <div class="divider-line mx-auto"></div>
      <p class="text-muted-gold" style="max-width:550px; margin:0 auto;">
        Meet the dedicated team that works tirelessly to bring you the best quality products.
      </p>
    </div>
    <div class="row g-4">
      <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
        <div class="team-card">
          <img src="assets\images\abu.jpg" 
               alt="Team Member" class="team-img">
          <div class="team-info">
            <h5>Hafiz Faiz Rasool Aseem Ansari</h5>
            <small>Founder &amp; CEO</small>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="150">
        <div class="team-card">
          <img src="assets\images\chachu.jpg" 
               alt="Team Member" class="team-img">
          <div class="team-info">
            <h5>Arshad Naeem <br> Ansari</h5>
            <small>Design Director</small>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
        <div class="team-card">
          <img src="assets\images\shoaib.jpg" 
               alt="Team Member" class="team-img">
          <div class="team-info">
            <h5>Shoaib Rasool <br>Ansari</h5>
            <small>Production Head</small>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="250">
        <div class="team-card">
          <img src="assets\images\junaid.jpg" 
               alt="Team Member" class="team-img">
          <div class="team-info">
            <h5>Muhammad Junaid  <br>Ansari</h5>
            <small>Customer Experience</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="section" data-aos="fade-up">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="eyebrow">Testimonials</div>
      <h2 class="section-title">What Our Customers Say</h2>
      <div class="divider-line mx-auto"></div>
    </div>
    <div class="row g-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="testimonial-card">
          <div class="d-flex align-items-center gap-3 mb-3">
            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop&crop=face" 
                 alt="Customer" class="testi-img">
            <div>
              <h6 class="mb-0 text-light">Bilal Ahmed</h6>
              <small class="text-muted-gold">Lahore</small>
            </div>
          </div>
          <div class="testi-stars">★★★★★</div>
          <p class="text-muted-gold mt-2" style="font-size:.9rem;">
            "Ansari's shirts are the best I've ever worn. The quality is unmatched, and the fit is perfect. Highly recommended!"
          </p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="testimonial-card">
          <div class="d-flex align-items-center gap-3 mb-3">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=100&h=100&fit=crop&crop=face" 
                 alt="Customer" class="testi-img">
            <div>
              <h6 class="mb-0 text-light">Sana Tariq</h6>
              <small class="text-muted-gold">Islamabad</small>
            </div>
          </div>
          <div class="testi-stars">★★★★★</div>
          <p class="text-muted-gold mt-2" style="font-size:.9rem;">
            "I absolutely love the women's collection! The fabric is so soft and comfortable. Ansari is my go-to brand now."
          </p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="testimonial-card">
          <div class="d-flex align-items-center gap-3 mb-3">
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" 
                 alt="Customer" class="testi-img">
            <div>
              <h6 class="mb-0 text-light">Ali Raza</h6>
              <small class="text-muted-gold">Karachi</small>
            </div>
          </div>
          <div class="testi-stars">★★★★★</div>
          <p class="text-muted-gold mt-2" style="font-size:.9rem;">
            "The bedding sets are pure luxury! My wife and I are sleeping better than ever. Thank you, Ansari!"
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA SECTION ============ -->
<section class="section" style="background:var(--bg-panel);" data-aos="fade-up">
  <div class="container">
    <div class="text-center" data-aos="fade-up">
      <div class="eyebrow">Join Our Journey</div>
      <h2 class="section-title" style="max-width:600px; margin:0 auto;">
        Ready to Experience <br class="d-none d-sm-block">the Ansari Difference?
      </h2>
      <div class="divider-line mx-auto"></div>
      <p class="text-muted-gold mb-4" style="max-width:500px; margin:0 auto;">
        Explore our collection and discover why thousands of customers trust Ansari.
      </p>
      <div class="d-flex flex-wrap gap-3 justify-content-center">
        <a href="men-clothes.php" class="btn btn-gold px-5 py-2 fw-bold">Shop Men</a>
        <a href="women-clothes.php" class="btn btn-gold px-5 py-2 fw-bold">Shop Women</a>
        <a href="bedding-set.php" class="btn btn-outline-gold px-5 py-2 fw-bold">Shop Bedding</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ SCROLL TOP ============ -->
<button class="scroll-top" id="scrollTop"><i class="bi bi-arrow-up"></i></button>

<!-- ============ SCRIPTS ============ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// ===== INIT AOS =====
AOS.init({ duration: 700, once: true, offset: 40 });

// ===== SCROLL PROGRESS =====
var scrollProgress = document.getElementById('scrollProgress');
window.addEventListener('scroll', function() {
  var scrollTop = window.scrollY;
  var docHeight = document.documentElement.scrollHeight - window.innerHeight;
  scrollProgress.style.transform = 'scaleX(' + ((scrollTop / docHeight) || 0) + ')';
});

// ===== SCROLL TOP =====
var scrollBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', function() {
  if(window.scrollY > 400) {
    scrollBtn.classList.add('show');
  } else {
    scrollBtn.classList.remove('show');
  }
});
scrollBtn.addEventListener('click', function() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ===== COUNTER ANIMATION =====
function animateCounters() {
  var counters = document.querySelectorAll('.counter');
  counters.forEach(function(counter) {
    var target = parseFloat(counter.dataset.target);
    var current = 0;
    var increment = Math.ceil(target / 40);
    var isDecimal = target % 1 !== 0;
    
    var timer = setInterval(function() {
      current += increment;
      if(current >= target) {
        current = target;
        clearInterval(timer);
      }
      if(isDecimal) {
        counter.textContent = current.toFixed(1);
      } else {
        counter.textContent = Math.floor(current);
      }
    }, 40);
  });
}

// Run counter on load with delay
setTimeout(animateCounters, 500);

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', function(e) {
  if(e.key === 'Escape') {
    document.querySelectorAll('.modal.show').forEach(function(m) {
      var modal = bootstrap.Modal.getInstance(m);
      if(modal) modal.hide();
    });
  }
});

// ===== CONSOLE =====
console.log('🏷️ Ansari About Us Page loaded successfully!');
console.log('📖 Learn more about our brand.');
</script>
</body>
</html>

<?php include 'includes/footer.php'; ?>