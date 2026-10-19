<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If logged in, redirect to home page
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$login_error = '';
$signup_error = '';
$signup_success = '';

// ===== LOGIN =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $email = trim($_POST['login_email'] ?? '');
    $password = $_POST['login_password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $login_error = "Please fill all fields.";
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, full_name, email, password, profile_image FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
                header('Location: index.php');
                exit();
            } else {
                $login_error = "Incorrect password.";
            }
        } else {
            $login_error = "Email not found. Please sign up first.";
        }
    }
}

// ===== SIGNUP =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup_submit'])) {
    $full_name = trim($_POST['signup_name'] ?? '');
    $email = trim($_POST['signup_email'] ?? '');
    $phone = trim($_POST['signup_phone'] ?? '');
    $cnic = trim($_POST['signup_cnic'] ?? '');
    $city = trim($_POST['signup_city'] ?? '');
    $province = trim($_POST['signup_province'] ?? '');
    $gender = trim($_POST['signup_gender'] ?? '');
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['signup_confirm_password'] ?? '';
    
    // Profile Image Upload
    $profile_image = 'default.png';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['profile_image']['size'];
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        
        if (in_array($ext, $allowed) && $file_size <= 2097152) {
            if (!is_dir('uploads/profile')) {
                mkdir('uploads/profile', 0777, true);
            }
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($tmp_name, 'uploads/profile/' . $new_filename)) {
                $profile_image = $new_filename;
            }
        }
    }
    
    // Validation
    $errors = [];
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($password)) $errors[] = "Password is required.";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $signup_error = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (full_name, email, phone, cnic, city, province, gender, password, profile_image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $full_name, $email, $phone, $cnic, $city, $province, $gender, $hashed_password, $profile_image);
            
            if ($stmt->execute()) {
                $signup_success = "✅ Account created successfully! Please login.";
                $active_tab = 'login';
            } else {
                $signup_error = "Database error: " . $conn->error;
            }
        }
    } else {
        $signup_error = implode("<br>", $errors);
    }
}

$active_tab = isset($_GET['tab']) && $_GET['tab'] == 'signup' ? 'signup' : 'login';
if (isset($signup_success) && !empty($signup_success)) {
    $active_tab = 'login';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login / Sign Up - Ansari</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
    --black: #0B0B0C; --black-2: #1A1A1C; --ink: #050505; --white: #F5F1E8;
    --muted: #8F8A7D; --gold: #D4AF37; --gold-light: #F0D77B;
    --gold-dim: rgba(212,175,55,0.15); --bg: #0b0a08; --bg-panel: #141210;
    --bg-panel-2: #1b1815; --stroke: rgba(212,175,55,.28);
    --cream: #f5f1e8; --gold-bright: #e8c94a;
}
*{box-sizing:border-box;}
body{ font-family:'Inter', sans-serif; margin:0; background:var(--bg); color:var(--cream); overflow-x:hidden; min-height:100vh; }
a{ text-decoration:none; }

.notice-bar { background: var(--black-2); border-bottom: 1px solid var(--gold-dim); padding: 6px 0; font-size: 0.75rem; color: var(--gold-light); overflow: hidden; white-space: nowrap; text-align: center; }
.notice-bar .notice-text { display: inline-block; animation: scrollNotice 20s linear infinite; }
@keyframes scrollNotice { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }

.ansari-navbar{ background: linear-gradient(180deg, var(--black-2) 0%, var(--black) 100%); border-bottom: 1px solid rgba(212,175,55,0.25); box-shadow: 0 12px 34px rgba(0,0,0,0.6); padding: 0.6rem 0; position: fixed; top: 0; left: 0; right: 0; z-index: 9999; width: 100%; }

.ansari-brand{ display:flex; align-items:center; gap:0.7rem; text-decoration:none; }
.ansari-brand .mark{ width:42px; height:42px; border-radius:9px; background: var(--black); border: 1px solid var(--gold); display:flex; align-items:center; justify-content:center; flex-shrink:0; transition: transform .35s ease, box-shadow .35s ease; }
.ansari-brand .mark svg{ width: 23px; height: 23px; stroke: var(--gold); fill: none; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
.ansari-brand:hover .mark{ transform: rotate(-8deg) scale(1.08); box-shadow: 0 0 16px rgba(212,175,55,0.55); }
.ansari-brand .word{ font-family:'Fraunces', serif; line-height:1.05; }
.ansari-brand .word .b{ font-size: 1.15rem; font-weight: 600; color: var(--white); display:block; letter-spacing: 0.2px; }
.ansari-brand .word .s{ font-family:'Inter', sans-serif; font-size: 0.6rem; font-weight: 500; letter-spacing: 0.16em; color: var(--gold); text-transform: uppercase; }

.navbar-toggler{ border-color: var(--gold) !important; }
.navbar-toggler-icon{ filter: invert(1) sepia(1) saturate(4) hue-rotate(5deg) brightness(1.3); }

.ansari-nav{ gap: 0.15rem; }
.ansari-nav .nav-link{ position: relative; display:flex; align-items:center; gap:0.45rem; color: var(--muted) !important; font-weight: 500; font-size: 0.92rem; padding: 0.6rem 0.9rem !important; border-radius: 6px; transition: color .2s ease, background .2s ease; }
.ansari-nav .nav-link svg{ width: 16px; height: 16px; stroke: currentColor; fill:none; stroke-width: 1.7; transition: transform .25s ease, stroke .25s ease; }
.ansari-nav .nav-link::after{ content:""; position:absolute; left: 0.9rem; right: 0.9rem; bottom: 0.32rem; height: 2px; background: linear-gradient(90deg, var(--gold), var(--gold-light)); transform: scaleX(0); transform-origin: left; transition: transform .25s ease; }
.ansari-nav .nav-link:hover{ color: var(--gold-light) !important; background: var(--gold-dim); }
.ansari-nav .nav-link:hover svg{ transform: translateY(-2px) scale(1.12); stroke: var(--gold-light); }
.ansari-nav .nav-link:hover::after{ transform: scaleX(1); }
.ansari-nav .nav-link.active{ color: var(--gold-light) !important; }
.ansari-nav .nav-link.active::after{ transform: scaleX(1); }

.user-icon-btn { 
    background: transparent; 
    border: 1px solid var(--stroke); 
    border-radius: 50%; 
    width: 40px; 
    height: 40px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    color: var(--gold); 
    font-size: 1.1rem; 
    transition: all .3s ease; 
    cursor: pointer; 
    text-decoration: none; 
    position: relative;
}
.user-icon-btn:hover { 
    border-color: var(--gold); 
    background: var(--gold-dim); 
    transform: scale(1.05); 
    color: var(--gold-light); 
}
.user-icon-btn .badge-count {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc3545;
    color: #fff;
    font-size: 0.6rem;
    padding: 0.15rem 0.4rem;
    border-radius: 50%;
    min-width: 18px;
    text-align: center;
    font-weight: 600;
}

.page-wrapper { padding-top: 82px; min-height: 100vh; }

.auth-card { background: var(--bg-panel); border: 1px solid var(--stroke); border-radius: 16px; padding: 2.5rem; max-width: 520px; margin: 2rem auto; }
.auth-card .nav-tabs { border-bottom: 1px solid var(--stroke); margin-bottom: 1.5rem; }
.auth-card .nav-tabs .nav-link { color: var(--muted); background: transparent; border: none; padding: 0.7rem 1.5rem; font-weight: 600; font-size: 0.95rem; border-bottom: 2px solid transparent; transition: all .3s ease; }
.auth-card .nav-tabs .nav-link.active { color: var(--gold); background: transparent; border-bottom-color: var(--gold); }
.auth-card .nav-tabs .nav-link:hover { color: var(--gold-light); }

.form-control-ansari, .form-select-ansari { background: var(--bg); border: 1px solid var(--stroke); color: var(--cream); border-radius: 8px; padding: .65rem 1rem; }
.form-control-ansari:focus, .form-select-ansari:focus { background: var(--bg); color: var(--cream); border-color: var(--gold); box-shadow: 0 0 0 .2rem rgba(212,175,55,.15); }
.form-control-ansari::placeholder { color: var(--muted); }
.form-select-ansari option { background: var(--bg-panel); color: var(--cream); }
.form-label-ansari { color: var(--muted); font-size: .85rem; margin-bottom: .4rem; }

.profile-upload-wrapper { display: flex; flex-direction: column; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding: 1.5rem; background: var(--bg); border-radius: 12px; border: 1px dashed var(--stroke); transition: border-color .3s ease; }
.profile-upload-wrapper:hover { border-color: var(--gold); }
.profile-preview { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid var(--gold); display: flex; align-items: center; justify-content: center; background: var(--bg-panel-2); }
.profile-preview img { width: 100%; height: 100%; object-fit: cover; }
.profile-preview .placeholder-icon { font-size: 3.5rem; color: var(--muted); }
.profile-upload-label { color: var(--gold); cursor: pointer; font-weight: 500; font-size: 0.9rem; padding: 0.4rem 1.2rem; border: 1px solid var(--stroke); border-radius: 8px; transition: all .3s ease; background: transparent; }
.profile-upload-label:hover { background: var(--gold-dim); border-color: var(--gold); }
.profile-upload-label i { margin-right: 0.5rem; }
#profileImageInput { display: none; }
.file-name { color: var(--muted); font-size: 0.75rem; margin-top: 0.2rem; }

.btn-gold { background: linear-gradient(180deg, var(--gold-bright), var(--gold)); border: none; color: #1a1305; font-weight: 600; padding: .6rem 1.5rem; border-radius: 8px; transition: transform .25s ease, box-shadow .25s ease; width: 100%; }
.btn-gold:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(212,175,55,.28); color: #1a1305; }

.btn-outline-gold {
    border: 1px solid var(--gold);
    color: var(--gold);
    background: transparent;
    font-weight: 500;
    padding: .4rem 1.2rem;
    border-radius: 8px;
    transition: all .3s ease;
    text-decoration: none;
}
.btn-outline-gold:hover {
    background: var(--gold);
    color: #1a1305;
}

.alert { border-radius: 8px; font-size: 0.9rem; }
.alert-danger { background: rgba(220, 53, 69, 0.15); border-color: #dc3545; color: #dc3545; }
.alert-success { background: rgba(40, 167, 69, 0.15); border-color: #28a745; color: #28a745; }
.text-muted-gold { color: var(--muted); }
.required-star { color: #dc3545; margin-left: 2px; }

@media (max-width: 991.98px){ .page-wrapper { padding-top: 72px; } }
@media (max-width: 575.98px){ .page-wrapper { padding-top: 62px; } .auth-card { padding: 1.5rem; margin: 1rem; } .profile-preview { width: 80px; height: 80px; } }
</style>
</head>
<body>

<div class="notice-bar"><div class="notice-text">✨ Premium Stitching ✨ &nbsp;&nbsp;|&nbsp;&nbsp; 📦 Free Delivery on Orders Above Rs 2,000 &nbsp;&nbsp;|&nbsp;&nbsp; 🔥 Summer Sale — Up to 40% Off</div></div>

<header class="ansari-navbar">
<nav class="navbar navbar-expand-lg container-fluid px-3 px-lg-5">
<a class="ansari-brand" href="index.php">
<span class="mark"><svg viewBox="0 0 24 24"><path d="M12 3.5a1.5 1.5 0 1 1 1.5 1.5"/><path d="M12 5 3 10.5c-.6.4-.9 1.1-.6 1.8.2.5.7.8 1.2.8H6l1 8h10l1-8h2.4c.5 0 1-.3 1.2-.8.3-.7 0-1.4-.6-1.8L12 5Z"/><path d="M9 10.5h6"/></svg></span>
<span class="word"><span class="b">Ansari</span><span class="s">Clothes &amp; Bed Sheet</span></span>
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ansariNavbar"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="ansariNavbar">
<ul class="navbar-nav ansari-nav mx-lg-auto my-2 my-lg-0">
<li class="nav-item"><a class="nav-link" href="index.php"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.7;"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Home</a></li>
<li class="nav-item"><a class="nav-link" href="women-clothes.php"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.7;"><path d="M12 3 9 5 4 8l2 3 3-1.5V21h6V9.5L18 11l2-3-5-3-3-2Z"/></svg>Women</a></li>
<li class="nav-item"><a class="nav-link" href="men-clothes.php"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.7;"><path d="M8 4 4 7l1.5 2.5L8 8.5V21h8V8.5l2.5 1L20 7l-4-3-2 1.5h-4L8 4Z"/></svg>Men</a></li>
<li class="nav-item"><a class="nav-link" href="bedding-set.php"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.7;"><path d="M2 12h20"/><path d="M2 12v6h20v-6"/><path d="M4 12V8a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v4"/><path d="M6 12V9h4v3"/></svg>Bedding</a></li>
<li class="nav-item"><a class="nav-link" href="about-us.php"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.7;"><circle cx="12" cy="8" r="4"/><path d="M5.3 18.3C6.8 16.5 9.2 15 12 15s5.2 1.5 6.7 3.3"/></svg>About</a></li>
<li class="nav-item"><a class="nav-link" href="contact-us.php"><svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.7;"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Contact</a></li>
</ul>
<a href="index.php" class="user-icon-btn" title="Go to Home"><i class="bi bi-house"></i></a>
</div>
</nav>
</header>

<div class="page-wrapper">
<div class="container">
<div class="auth-card">
<ul class="nav nav-tabs" role="tablist">
<li class="nav-item"><button class="nav-link <?php echo $active_tab == 'login' ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">Login</button></li>
<li class="nav-item"><button class="nav-link <?php echo $active_tab == 'signup' ? 'active' : ''; ?>" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup" type="button" role="tab">Sign Up</button></li>
</ul>

<div class="tab-content">

<!-- LOGIN TAB -->
<div class="tab-pane fade <?php echo $active_tab == 'login' ? 'show active' : ''; ?>" id="login" role="tabpanel">
<h4 class="text-light mb-3">Welcome Back</h4>
<p class="text-muted-gold mb-4">Login to your account to manage orders.</p>
<?php if ($login_error): ?><div class="alert alert-danger"><?php echo $login_error; ?></div><?php endif; ?>
<?php if ($signup_success): ?><div class="alert alert-success"><?php echo $signup_success; ?></div><?php endif; ?>
<form method="POST">
<div class="mb-3"><label class="form-label-ansari">Email Address <span class="required-star">*</span></label><input type="email" name="login_email" class="form-control form-control-ansari" placeholder="your@email.com" required></div>
<div class="mb-3"><label class="form-label-ansari">Password <span class="required-star">*</span></label><input type="password" name="login_password" class="form-control form-control-ansari" placeholder="Enter your password" required></div>
<button type="submit" name="login_submit" class="btn btn-gold">Login</button>
</form>
<p class="text-center text-muted-gold mt-3" style="font-size:0.85rem;">Don't have an account? <a href="?tab=signup" style="color:var(--gold);">Sign Up</a></p>
</div>

<!-- SIGNUP TAB -->
<div class="tab-pane fade <?php echo $active_tab == 'signup' ? 'show active' : ''; ?>" id="signup" role="tabpanel">
<h4 class="text-light mb-3">Create Account</h4>
<p class="text-muted-gold mb-4">Join Ansari for exclusive offers and updates.</p>
<?php if ($signup_error): ?><div class="alert alert-danger"><?php echo $signup_error; ?></div><?php endif; ?>
<form method="POST" enctype="multipart/form-data">

<div class="profile-upload-wrapper">
<div class="profile-preview" id="profilePreview">
<i class="bi bi-person-circle placeholder-icon" id="placeholderIcon"></i>
<img src="" alt="Profile" id="profileImagePreview" style="display:none;">
</div>
<label class="profile-upload-label" for="profileImageInput"><i class="bi bi-camera"></i> Choose Profile Photo</label>
<input type="file" name="profile_image" id="profileImageInput" accept="image/*" onchange="previewImage(this)">
<span class="file-name" id="fileName">No file selected</span>
<small style="color:var(--muted);font-size:0.65rem;">Supported: JPG, PNG, GIF, WEBP (Max 2MB)</small>
</div>

<div class="mb-3"><label class="form-label-ansari">Full Name <span class="required-star">*</span></label><input type="text" name="signup_name" class="form-control form-control-ansari" placeholder="Your full name" required></div>
<div class="mb-3"><label class="form-label-ansari">Email Address <span class="required-star">*</span></label><input type="email" name="signup_email" class="form-control form-control-ansari" placeholder="your@email.com" required></div>
<div class="mb-3"><label class="form-label-ansari">Phone Number <span class="required-star">*</span></label><input type="text" name="signup_phone" class="form-control form-control-ansari" placeholder="03XX-XXXXXXX" required></div>
<div class="mb-3"><label class="form-label-ansari">CNIC Number</label><input type="text" name="signup_cnic" class="form-control form-control-ansari" placeholder="12345-1234567-1"><small style="color:var(--muted);font-size:0.7rem;"> Format: 12345-1234567-1</small></div>
<div class="mb-3"><label class="form-label-ansari">City</label><input type="text" name="signup_city" class="form-control form-control-ansari" placeholder="e.g. Lahore, Karachi"></div>
<div class="mb-3"><label class="form-label-ansari">Province</label><select name="signup_province" class="form-select form-select-ansari"><option value="">Select Province</option><option value="Punjab">Punjab</option><option value="Sindh">Sindh</option><option value="Khyber Pakhtunkhwa">Khyber Pakhtunkhwa</option><option value="Balochistan">Balochistan</option><option value="Gilgit-Baltistan">Gilgit-Baltistan</option><option value="Azad Kashmir">Azad Kashmir</option><option value="Islamabad">Islamabad</option></select></div>
<div class="mb-3"><label class="form-label-ansari">Gender</label><select name="signup_gender" class="form-select form-select-ansari"><option value="">Select Gender</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
<div class="mb-3"><label class="form-label-ansari">Password <span class="required-star">*</span></label><input type="password" name="signup_password" class="form-control form-control-ansari" placeholder="Min 6 characters" required></div>
<div class="mb-3"><label class="form-label-ansari">Confirm Password <span class="required-star">*</span></label><input type="password" name="signup_confirm_password" class="form-control form-control-ansari" placeholder="Confirm your password" required></div>

<button type="submit" name="signup_submit" class="btn btn-gold">Create Account</button>
</form>
<p class="text-center text-muted-gold mt-3" style="font-size:0.85rem;">Already have an account? <a href="?tab=login" style="color:var(--gold);">Login</a></p>
</div>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImage(input) {
    const preview = document.getElementById('profileImagePreview');
    const placeholder = document.getElementById('placeholderIcon');
    const fileName = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            fileName.textContent = input.files[0].name;
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
        fileName.textContent = 'No file selected';
    }
}
</script>
</body>
</html>