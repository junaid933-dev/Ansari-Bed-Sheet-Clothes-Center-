<?php
require_once 'config.php';

// =========================================================
// CONTACT FORM BACKEND - FIXED
// =========================================================

$form_error = '';
$form_success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $form_error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = 'Please enter a valid email address.';
    } else {
        // Save to database
        $conn = getDBConnection();
        
        if ($conn) {
            $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
                
                if ($stmt->execute()) {
                    $form_success = 'Thank you! Your message has been sent successfully. We\'ll respond within 24 hours.';
                    
                    // Send email - with error suppression
                    $to = "muhammadjunaid6266@gmail.com";
                    $email_subject = "New Contact Message from $name";
                    
                    $email_body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; background: #0b0a08; color: #f5f1e8; padding: 20px; }
                            .container { max-width: 600px; margin: 0 auto; background: #141210; border: 1px solid #d4af37; border-radius: 16px; padding: 30px; }
                            .header { text-align: center; border-bottom: 2px solid #d4af37; padding-bottom: 20px; }
                            .header h2 { color: #d4af37; }
                            .content { padding: 20px 0; }
                            .field { margin: 10px 0; }
                            .label { color: #a79d8a; font-weight: 600; }
                            .value { color: #f5f1e8; }
                            .divider { border: 1px solid rgba(212,175,55,0.2); margin: 15px 0; }
                            .footer { text-align: center; color: #a79d8a; font-size: 12px; border-top: 1px solid rgba(212,175,55,0.2); padding-top: 20px; }
                            .gold { color: #d4af37; }
                            .message-box { background: #0b0a08; padding: 15px; border-radius: 8px; border: 1px solid rgba(212,175,55,0.1); margin-top: 5px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>📩 New Contact Message</h2>
                                <p style='color:#a79d8a;'>A new message has been received from your website</p>
                            </div>
                            <div class='content'>
                                <div class='field'><span class='label'>👤 Name:</span> <span class='value'>" . htmlspecialchars($name) . "</span></div>
                                <div class='field'><span class='label'>📧 Email:</span> <span class='value'>" . htmlspecialchars($email) . "</span></div>
                                <div class='field'><span class='label'>📱 Phone:</span> <span class='value'>" . htmlspecialchars($phone ?: 'Not provided') . "</span></div>
                                <div class='field'><span class='label'>📌 Subject:</span> <span class='value'>" . htmlspecialchars($subject) . "</span></div>
                                <div class='divider'></div>
                                <div class='field'><span class='label'>💬 Message:</span></div>
                                <div class='message-box'>" . nl2br(htmlspecialchars($message)) . "</div>
                                <div class='divider'></div>
                                <div class='field' style='color:#a79d8a; font-size:12px;'>
                                    <span>📅 Received: " . date('F j, Y, g:i A') . "</span><br>
                                    <span>🌐 IP: " . $_SERVER['REMOTE_ADDR'] . "</span>
                                </div>
                            </div>
                            <div class='footer'>
                                <p>This message was sent from the Ansari Contact Form</p>
                                <p style='color:#d4af37;'>www.ansari.pk</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: Ansari Website <noreply@ansari.pk>\r\n";
                    $headers .= "Reply-To: " . $email . "\r\n";
                    
                    @mail($to, $email_subject, $email_body, $headers);
                    
                    // Auto-reply to user
                    $user_subject = "Thank you for contacting Ansari";
                    $user_body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; background: #0b0a08; color: #f5f1e8; padding: 20px; }
                            .container { max-width: 600px; margin: 0 auto; background: #141210; border: 1px solid #d4af37; border-radius: 16px; padding: 30px; }
                            .header { text-align: center; border-bottom: 2px solid #d4af37; padding-bottom: 20px; }
                            .header h2 { color: #d4af37; }
                            .content { padding: 20px 0; }
                            .footer { text-align: center; color: #a79d8a; font-size: 12px; border-top: 1px solid rgba(212,175,55,0.2); padding-top: 20px; }
                            .gold { color: #d4af37; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>✨ Thank You for Contacting Us!</h2>
                            </div>
                            <div class='content'>
                                <p>Dear <strong style='color:#d4af37;'>" . htmlspecialchars($name) . "</strong>,</p>
                                <p>We have received your message and appreciate you reaching out to us.</p>
                                <p>Our team will review your message and get back to you within <strong style='color:#d4af37;'>24 hours</strong>.</p>
                                <p>In the meantime, feel free to:</p>
                                <ul style='color:#a79d8a;'>
                                    <li>📞 Call: <a href='tel:+923271223933' style='color:#d4af37;'>+92 327 1223933</a></li>
                                    <li>💬 WhatsApp: <a href='https://wa.me/923271223933' style='color:#d4af37;'>+92 327 1223933</a></li>
                                </ul>
                                <p style='margin-top:20px;'>Best regards,<br><strong style='color:#d4af37;'>Team Ansari</strong></p>
                            </div>
                            <div class='footer'>
                                <p>© " . date('Y') . " Ansari. All rights reserved.</p>
                                <p style='color:#d4af37;'>www.ansari.pk</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    $user_headers = "MIME-Version: 1.0\r\n";
                    $user_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $user_headers .= "From: Ansari Team <muhammadjunaid6266@gmail.com>\r\n";
                    
                    @mail($email, $user_subject, $user_body, $user_headers);
                    
                    // Clear POST data
                    $_POST = array();
                    
                } else {
                    $form_error = 'Failed to save message. Please try again.';
                }
                $stmt->close();
            } else {
                $form_error = 'Database error. Please try again.';
            }
            $conn->close();
        } else {
            $form_error = 'Database connection failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Contact Ansari — Get in touch with us for inquiries, orders, and feedback.">
<title>Contact Us | Ansari</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
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
    --green: #27ae60;
    --teal: #1abc9c;
    --facebook: #1877f2;
    --tiktok: #000000;
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

/* Page Top Margin */
.page-top-margin {
    margin-top: 80px;
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
    min-height: 80vh;
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
    max-height: 450px;
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

.contact-card {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2rem 1.5rem;
    text-align: center;
    transition: all .4s ease;
    height: 100%;
}
.contact-card:hover {
    transform: translateY(-8px);
    border-color: var(--gold);
    box-shadow: 0 12px 30px rgba(0,0,0,.4);
}
.contact-card i {
    font-size: 2.8rem;
    color: var(--gold);
}
.contact-card h5 {
    color: var(--cream);
    margin-top: 1rem;
    font-weight: 600;
}
.contact-card p {
    color: var(--muted);
    font-size: .9rem;
    margin: 0.2rem 0;
}
.contact-card a {
    color: var(--muted);
    transition: color .3s ease;
}
.contact-card a:hover {
    color: var(--gold);
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

.btn-whatsapp {
    background: linear-gradient(135deg, #25d366, #128c7e);
    border: none;
    color: #fff;
    font-weight: 600;
    padding: .6rem 1.8rem;
    border-radius: 10px;
    transition: all .3s ease;
}
.btn-whatsapp:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(37,211,102,.35);
    color: #fff;
}

/* Social Media Buttons */
.btn-facebook {
    background: linear-gradient(135deg, #1877f2, #0d65d9);
    border: none;
    color: #fff;
    font-weight: 600;
    padding: .6rem 1.8rem;
    border-radius: 10px;
    transition: all .3s ease;
}
.btn-facebook:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(24,119,242,.35);
    color: #fff;
}

.btn-tiktok {
    background: linear-gradient(135deg, #000000, #1a1a1a);
    border: none;
    color: #fff;
    font-weight: 600;
    padding: .6rem 1.8rem;
    border-radius: 10px;
    transition: all .3s ease;
}
.btn-tiktok:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,.35);
    color: #fff;
}

.contact-form-wrap {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2.5rem;
}

.form-control-gold {
    background: var(--bg);
    border: 1px solid var(--stroke);
    color: var(--cream);
    padding: .7rem 1rem;
    border-radius: 8px;
    transition: all .3s ease;
}
.form-control-gold:focus {
    background: var(--bg);
    color: var(--cream);
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,175,55,.15);
}
.form-control-gold::placeholder {
    color: var(--muted);
}
.form-control-gold option {
    background: var(--bg-panel);
    color: var(--cream);
}
.form-label {
    color: var(--muted);
    font-size: .85rem;
    font-weight: 500;
}
.form-label .required {
    color: #c0392b;
}

.alert-success-custom {
    background: rgba(39,174,96,0.15);
    border: 1px solid #27ae60;
    color: #2ecc71;
    border-radius: 10px;
    padding: 12px 18px;
}
.alert-danger-custom {
    background: rgba(192,57,43,0.15);
    border: 1px solid #c0392b;
    color: #e74c3c;
    border-radius: 10px;
    padding: 12px 18px;
}

/* FAQ Section */
.faq-item {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 1.5rem;
    transition: all .3s ease;
    height: 100%;
}
.faq-item:hover {
    border-color: var(--gold);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,.3);
}
.faq-item h6 {
    color: var(--gold);
    font-weight: 600;
}
.faq-item p {
    color: var(--muted);
    font-size: .9rem;
    margin: 0;
}

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

/* ===== SOCIAL MEDIA FLOATING BUTTONS - RIGHT SIDE ===== */
.social-float {
    position: fixed;
    right: 22px;
    z-index: 999;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all .3s ease;
    border: none;
    text-decoration: none;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,.3);
}
.social-float:hover {
    transform: scale(1.1);
    color: #fff;
}

/* WhatsApp */
.social-float.whatsapp {
    background: #25d366;
    bottom: 90px;
    animation: whatsappPulse 2s ease-in-out infinite;
}
.social-float.whatsapp:hover {
    box-shadow: 0 4px 25px rgba(37,211,102,.5);
}

/* Facebook */
.social-float.facebook {
    background: #1877f2;
    bottom: 150px;
    animation: facebookPulse 2.5s ease-in-out infinite;
}
.social-float.facebook:hover {
    box-shadow: 0 4px 25px rgba(24,119,242,.5);
}

/* TikTok */
.social-float.tiktok {
    background: #000000;
    bottom: 210px;
    animation: tiktokPulse 2s ease-in-out infinite;
}
.social-float.tiktok:hover {
    box-shadow: 0 4px 25px rgba(0,0,0,.5);
}

@keyframes whatsappPulse {
    0%,100% { box-shadow: 0 4px 20px rgba(37,211,102,.4); }
    50% { box-shadow: 0 4px 35px rgba(37,211,102,.6); }
}
@keyframes facebookPulse {
    0%,100% { box-shadow: 0 4px 20px rgba(24,119,242,.3); }
    50% { box-shadow: 0 4px 35px rgba(24,119,242,.5); }
}
@keyframes tiktokPulse {
    0%,100% { box-shadow: 0 4px 20px rgba(0,0,0,.3); }
    50% { box-shadow: 0 4px 35px rgba(0,0,0,.5); }
}

.loading-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid #1a1305;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    margin-right: 8px;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .section { padding: 3rem 0; }
    .contact-form-wrap { padding: 1.5rem; }
    .social-float {
        width: 42px;
        height: 42px;
        font-size: 1.2rem;
        right: 12px;
    }
    .social-float.whatsapp { bottom: 75px; }
    .social-float.facebook { bottom: 125px; }
    .social-float.tiktok { bottom: 175px; }
    .page-banner { min-height: 60vh; }
    .banner-hero-image img { max-height: 300px; }
    .page-top-margin { margin-top: 60px; }
}
</style>
</head>
<body>

<!-- SCROLL PROGRESS -->
<div class="scroll-progress" id="scrollProgress"></div>

<!-- =========================================================
     SOCIAL MEDIA FLOATING BUTTONS - RIGHT SIDE
     ========================================================= -->

<!-- 
  ============================================================
  🔵 FACEBOOK - YAHAN APNI FACEBOOK PAGE URL DAALEN
  ============================================================
  👉 https://www.facebook.com/your-page-name
  👉 Example: https://www.facebook.com/AnsariClothing
  ============================================================
-->
<a href="https://www.facebook.com/share/1DYoXCsqK1/" target="_blank" class="social-float facebook" title="Follow us on Facebook">
    <i class="bi bi-facebook"></i>
</a>

<!-- 
  ============================================================
  🎵 TIKTOK - YAHAN APNI TIKTOK PROFILE URL DAALEN
  ============================================================
  👉 https://www.tiktok.com/@your-username
  👉 Example: https://www.tiktok.com/@ansari_clothing
  ============================================================
-->
<a href="https://www.tiktok.com/@muhammadjunaid6266?_r=1&_t=ZS-97xpowLisIn" target="_blank" class="social-float tiktok" title="Follow us on TikTok">
    <i class="bi bi-tiktok"></i>
</a>

<!-- 
  ============================================================
  💬 WHATSAPP - YAHAN APNA WHATSAPP NUMBER DAALEN
  ============================================================
  👉 https://wa.me/923271223933
  👉 Example: https://wa.me/923001234567
  ============================================================
-->
<a href="https://wa.me/923271223933" target="_blank" class="social-float whatsapp" title="Chat on WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<!-- PAGE BANNER WITH TOP MARGIN -->
<section class="page-banner page-top-margin">
    <div class="banner-overlay"></div>
    <div class="banner-shape shape-1"></div>
    <div class="banner-shape shape-2"></div>
    <div class="banner-shape shape-3"></div>
    
    <div class="container position-relative" style="z-index:2;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="eyebrow fade-in-down mt-5">Get in Touch</div>
                <h1 class="text-light fade-in-down delay-1" style="font-size:clamp(2.2rem,4.5vw,3.5rem);">
                    We'd Love to <br><span style="color:var(--gold);">Hear From You</span>
                </h1>
                <div class="divider-line fade-in-down delay-2"></div>
                <p class="text-muted-gold fade-in-down delay-2" style="max-width:500px;font-size:1.05rem;">
                    Have a question about our products? Need help with an order? 
                    We're here for you. Reach out to us through any of the channels below.
                </p>
                
                <div class="d-flex flex-wrap gap-3 mt-4 fade-in-down delay-3">
                    <span class="stat-badge"><i class="bi bi-star-fill"></i> Premium Stitching</span>
                    <span class="stat-badge"><i class="bi bi-truck"></i> Free Delivery on Orders Above Rs 2,000</span>
                    <span class="stat-badge"><i class="bi bi-fire"></i> Summer Sale — Up to 30% Off</span>
                </div>
                
                <div class="d-flex flex-wrap gap-3 mt-4 fade-in-down delay-4">
                    <a href="#contactForm" class="btn btn-gold btn-lg">
                        <i class="bi bi-envelope me-2"></i> Send Message
                    </a>
                    <a href="https://wa.me/923271223933" target="_blank" class="btn btn-whatsapp btn-lg">
                        <i class="bi bi-whatsapp me-2"></i> WhatsApp
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6 text-center banner-hero-image fade-in-up">
                <img src="https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=600&h=500&fit=crop" 
                     alt="Contact Us" 
                     class="img-fluid">
                <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                    <span class="badge" style="background:var(--gold); color:#1a1305; padding:0.4rem 1rem;">
                        <i class="bi bi-clock me-1"></i> Mon-Sat 9AM-7PM
                    </span>
                    <span class="badge" style="border:1px solid var(--gold); color:var(--gold); padding:0.4rem 1rem;">
                        <i class="bi bi-chat me-1"></i> Quick Response
                    </span>
                    <span class="badge" style="border:1px solid var(--green); color:var(--green); padding:0.4rem 1rem;">
                        <i class="bi bi-shield-check me-1"></i> 100% Satisfaction
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT INFO CARDS -->
<section class="section" data-aos="fade-up">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-card">
                    <i class="bi bi-geo-alt"></i>
                    <h5>Visit Us</h5>
                    <p>Aman Ul Allah Market<br>Quaid-e-Azam Road<br>Dokan 1, Mailsi</p>
                    <a href="https://maps.google.com/?q=Mailsi,Pakistan" target="_blank" class="btn btn-outline-gold btn-sm mt-2">Get Directions</a>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="150">
                <div class="contact-card">
                    <i class="bi bi-telephone"></i>
                    <h5>Call Us</h5>
                    <p><a href="tel:+923271223933">+92 327 1223933</a></p>
                    <p><a href="tel:+923076485663">+92 307 6485663</a></p>
                    <p><small class="text-muted-gold">Mon-Sat 9AM-7PM</small></p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-card">
                    <i class="bi bi-whatsapp"></i>
                    <h5>WhatsApp</h5>
                    <p><a href="https://wa.me/923271223933" target="_blank">+92 327 1223933</a></p>
                    <p><a href="https://wa.me/923076485663" target="_blank">+92 307 6485663</a></p>
                    <p><small class="text-muted-gold">Quick response</small></p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="250">
                <div class="contact-card">
                    <i class="bi bi-envelope"></i>
                    <h5>Email Us</h5>
                    <p><a href="mailto:muhammadjunaid6266@gmail.com">muhammadjunaid6266@gmail.com</a></p>
                    <p><small class="text-muted-gold">We reply within 24hrs</small></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT FORM + MAP -->
<section class="section" style="background:var(--bg-panel);" id="contactForm" data-aos="fade-up">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="contact-form-wrap">
                    <div class="eyebrow">Send Message</div>
                    <h2 class="section-title" style="font-size:1.8rem;">Get in Touch</h2>
                    <div class="divider-line"></div>
                    <p class="text-muted-gold" style="font-size:.9rem;">
                        Fill out the form below and we'll get back to you as soon as possible.
                    </p>

                    <?php if (!empty($form_success)): ?>
                        <div class="alert alert-success-custom" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $form_success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($form_error)): ?>
                        <div class="alert alert-danger-custom" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $form_error; ?>
                        </div>
                    <?php endif; ?>

                    <form id="contactForm" method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control form-control-gold" placeholder="Your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control form-control-gold" placeholder="your@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control form-control-gold" placeholder="03XX-XXXXXXX" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject <span class="required">*</span></label>
                                <select name="subject" class="form-control form-control-gold" required>
                                    <option value="">Select a subject</option>
                                    <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Product Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Product Question') ? 'selected' : ''; ?>>Product Question</option>
                                    <option value="Order Status" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Order Status') ? 'selected' : ''; ?>>Order Status</option>
                                    <option value="Return/Exchange" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Return/Exchange') ? 'selected' : ''; ?>>Return/Exchange</option>
                                    <option value="Wholesale Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Wholesale Inquiry') ? 'selected' : ''; ?>>Wholesale Inquiry</option>
                                    <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                    <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message <span class="required">*</span></label>
                                <textarea name="message" class="form-control form-control-gold" rows="5" placeholder="Write your message here..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="submit" class="btn btn-gold w-100 py-2" id="submitBtn">
                                    <i class="bi bi-send me-1"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Map -->
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="100">
                <div class="contact-form-wrap h-100">
                    <div class="eyebrow">Find Us</div>
                    <h2 class="section-title" style="font-size:1.8rem;">Our Location</h2>
                    <div class="divider-line"></div>
                    
                    <iframe class="img-fluid w-100" style="height:350px; border-radius:12px; border:1px solid var(--stroke);" 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d27697.821362469003!2d72.15785547111855!3d29.799966287267285!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x393c8f03f6affdb3%3A0x1b9cf0432728b7c7!2sMailsi%2C%20Pakistan!5e0!3m2!1sen!2s!4v1783299272666!5m2!1sen!2s" 
                        allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>

                    <!-- Social Media Links Below Map -->
                    <div class="mt-4">
                        <p class="text-muted-gold text-center" style="font-size:0.85rem;">
                            <i class="bi bi-share me-1"></i> Follow us on social media
                        </p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="https://www.facebook.com/share/1DYoXCsqK1/" target="_blank" class="btn btn-facebook btn-sm">
                                <i class="bi bi-facebook me-1"></i> Facebook
                            </a>
                            <a href="https://www.tiktok.com/@muhammadjunaid6266?_r=1&_t=ZS-97xpowLisIn" target="_blank" class="btn btn-tiktok btn-sm">
                                <i class="bi bi-tiktok me-1"></i> TikTok
                            </a>
                            <a href="https://wa.me/923271223933" target="_blank" class="btn btn-whatsapp btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     FREQUENTLY ASKED QUESTIONS
     ========================================================= -->
<section class="section" data-aos="fade-up">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="eyebrow">Quick Help</div>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="divider-line mx-auto"></div>
            <p class="text-muted-gold" style="max-width:550px; margin:0 auto;">
                Find answers to the most common questions our customers ask.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="faq-item">
                    <h6><i class="bi bi-question-circle me-2"></i>How can I track my order?</h6>
                    <p>Once your order is shipped, you'll receive a tracking number via email and WhatsApp. You can track your order on our website or through the courier's portal.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="150">
                <div class="faq-item">
                    <h6><i class="bi bi-question-circle me-2"></i>What is your return policy?</h6>
                    <p>We offer a 7-day return policy on all products. Items must be unused and in original packaging. Contact us to initiate a return.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="faq-item">
                    <h6><i class="bi bi-question-circle me-2"></i>How long does delivery take?</h6>
                    <p>Delivery typically takes 2-4 working days within Lahore and 4-7 working days for other cities in Pakistan.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="250">
                <div class="faq-item">
                    <h6><i class="bi bi-question-circle me-2"></i>Do you offer wholesale pricing?</h6>
                    <p>Yes! We offer wholesale pricing for bulk orders. Please contact our sales team for a custom quote.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SCROLL TOP -->
<button class="scroll-top" id="scrollTop">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
// ===== INIT AOS =====
AOS.init({ duration: 700, once: true, offset: 40 });

// ===== SCROLL PROGRESS =====
const scrollProgress = document.getElementById('scrollProgress');
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    scrollProgress.style.transform = `scaleX(${(scrollTop / docHeight) || 0})`;
});

// ===== SCROLL TOP =====
const scrollBtn = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
    scrollBtn.classList.toggle('show', window.scrollY > 400);
});
scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

console.log('📞 Ansari Contact Us Page loaded successfully!');
console.log('📧 Messages saved in database.');
console.log('📱 Social Media buttons added: Facebook, TikTok, WhatsApp');
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>