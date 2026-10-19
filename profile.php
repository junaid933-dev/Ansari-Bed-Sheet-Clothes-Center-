<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login-signup.php');
    exit();
}

$user = getCurrentUser();
$success_msg = '';
$error_msg = '';
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// =========================================================
// GET CART, WISHLIST AND ORDER COUNTS
// =========================================================
$cart_count = getCartCount();
$wishlist_count = getWishlistCount();

// Get order count
$order_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order_data = $order_result->fetch_assoc();
$total_orders = $order_data['total'] ?? 0;

// =========================================================
// UPDATE PROFILE INFORMATION
// =========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $province = sanitize($_POST['province'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    
    if (empty($full_name) || empty($phone)) {
        $error_msg = 'Name and phone are required.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, city = ?, province = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $full_name, $phone, $city, $province, $gender, $user_id);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $full_name;
            $success_msg = '✅ Profile updated successfully!';
            $user = getCurrentUser();
        } else {
            $error_msg = 'Failed to update profile.';
        }
    }
}

// =========================================================
// CHANGE PASSWORD
// =========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($current) || empty($new) || empty($confirm)) {
        $error_msg = 'All password fields are required.';
    } elseif (strlen($new) < 6) {
        $error_msg = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error_msg = 'Passwords do not match.';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if (password_verify($current, $data['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $user_id);
            if ($update->execute()) {
                $success_msg = '✅ Password changed successfully!';
            } else {
                $error_msg = 'Failed to change password.';
            }
        } else {
            $error_msg = 'Current password is incorrect.';
        }
    }
}

// =========================================================
// PROFILE IMAGE UPLOAD
// =========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_image'])) {
    $profile_image = $_SESSION['profile_image'] ?? 'default.png';
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['profile_image']['size'];
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        
        // Validate file
        if (!in_array($ext, $allowed)) {
            $error_msg = "Invalid file format. Please upload JPG, PNG, GIF, or WEBP.";
        } elseif ($file_size > 2097152) {
            $error_msg = "File too large. Max size is 2MB.";
        } else {
            // Create directory if not exists
            if (!is_dir('uploads/profile')) {
                mkdir('uploads/profile', 0777, true);
            }
            
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            
            if (move_uploaded_file($tmp_name, 'uploads/profile/' . $new_filename)) {
                // Delete old image if not default
                if ($profile_image != 'default.png' && file_exists('uploads/profile/' . $profile_image)) {
                    unlink('uploads/profile/' . $profile_image);
                }
                
                $profile_image = $new_filename;
                $_SESSION['profile_image'] = $profile_image;
                
                // Update database
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $profile_image, $user_id);
                
                if ($stmt->execute()) {
                    $success_msg = "✅ Profile image updated successfully!";
                    $user = getCurrentUser();
                } else {
                    $error_msg = "Database error. Please try again.";
                }
            } else {
                $error_msg = "Failed to upload image. Please try again.";
            }
        }
    } else {
        $error_msg = "Please select an image to upload.";
    }
}

// =========================================================
// REMOVE PROFILE IMAGE
// =========================================================
if (isset($_GET['remove_image'])) {
    $profile_image = $_SESSION['profile_image'] ?? 'default.png';
    if ($profile_image != 'default.png' && file_exists('uploads/profile/' . $profile_image)) {
        unlink('uploads/profile/' . $profile_image);
    }
    $_SESSION['profile_image'] = 'default.png';
    
    $stmt = $conn->prepare("UPDATE users SET profile_image = 'default.png' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $success_msg = "Profile image removed successfully!";
    $user = getCurrentUser();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | Ansari</title>

<style>
/* =========================================================
   PROFILE PAGE STYLES
   ========================================================= */
.profile-wrapper {
    padding: 2rem 0 4rem;
}

.profile-card {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2.5rem;
    max-width: 100%;
    margin: 0 auto;
    transition: all .3s ease;
}

.profile-card:hover {
    border-color: var(--gold);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* =========================================================
   PROFILE HEADER
   ========================================================= */
.profile-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--stroke);
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.profile-avatar-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.profile-avatar-lg {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--gold);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-panel-2);
}

.profile-avatar-lg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-lg .avatar-placeholder-lg {
    font-size: 3.5rem;
    color: var(--gold);
    font-weight: 700;
    text-transform: uppercase;
}

.btn-change-profile {
    font-size: 0.7rem;
    color: var(--gold);
    background: transparent;
    border: 1px solid var(--stroke);
    padding: 0.3rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    transition: all .3s ease;
    text-decoration: none;
}

.btn-change-profile:hover {
    background: var(--gold-dim);
    border-color: var(--gold);
}

.btn-change-profile i {
    margin-right: 0.3rem;
    font-size: 0.6rem;
}

#profileImageInput {
    display: none;
}

.profile-info h2 {
    font-family: 'Playfair Display', serif;
    color: var(--cream);
    margin: 0;
    font-size: 1.4rem;
}

.profile-info p {
    color: var(--muted);
    margin: 0.3rem 0;
    font-size: 0.9rem;
}

.profile-info .badge-user {
    background: rgba(212,175,55,0.15);
    color: var(--gold);
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
    display: inline-block;
    margin-top: 0.3rem;
}

/* =========================================================
   STATS GRID - CLICKABLE
   ========================================================= */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-item {
    background: var(--bg-panel-2);
    border: 1px solid var(--stroke);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all .3s ease;
    cursor: pointer;
    text-decoration: none;
    display: block;
}

.stat-item:hover {
    border-color: var(--gold);
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.stat-item .stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gold);
    font-family: 'Playfair Display', serif;
}

.stat-item .stat-label {
    font-size: 0.8rem;
    color: var(--muted);
    margin-top: 0.2rem;
}

.stat-item .stat-icon {
    font-size: 1.8rem;
    color: var(--gold-dim);
    margin-bottom: 0.5rem;
}

/* =========================================================
   BUTTONS
   ========================================================= */
.btn-gold {
    background: linear-gradient(180deg, var(--gold-bright), var(--gold));
    border: none;
    color: #1a1305;
    font-weight: 600;
    padding: .6rem 1.5rem;
    border-radius: 8px;
    transition: all .3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(212,175,55,.28);
    color: #1a1305;
}

.btn-outline-gold {
    border: 1px solid var(--gold);
    color: var(--gold);
    background: transparent;
    font-weight: 500;
    padding: .6rem 1.5rem;
    border-radius: 8px;
    transition: all .3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-outline-gold:hover {
    background: var(--gold);
    color: #1a1305;
}

/* =========================================================
   QUICK ACTIONS
   ========================================================= */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.2rem;
    background: var(--bg-panel-2);
    border: 1px solid var(--stroke);
    border-radius: 12px;
    color: var(--cream);
    transition: all .3s ease;
    text-decoration: none;
}

.quick-action-btn:hover {
    border-color: var(--gold);
    background: rgba(212,175,55,0.05);
    color: var(--gold-light);
    transform: translateY(-3px);
}

.quick-action-btn i {
    font-size: 1.4rem;
    color: var(--gold);
    width: 30px;
    text-align: center;
}

.quick-action-btn .action-text {
    font-weight: 500;
    font-size: 0.9rem;
}

.quick-action-btn .action-sub {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================
   ALERTS
   ========================================================= */
.alert {
    border-radius: 8px;
    font-size: 0.9rem;
    padding: 0.8rem 1.2rem;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(40, 167, 69, 0.15);
    border: 1px solid #28a745;
    color: #28a745;
}

.alert-danger {
    background: rgba(220, 53, 69, 0.15);
    border: 1px solid #dc3545;
    color: #dc3545;
}

/* =========================================================
   TAB BUTTONS
   ========================================================= */
.tab-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    border: 1px solid var(--stroke);
    background: transparent;
    color: var(--muted);
    font-weight: 500;
    transition: all .3s ease;
    cursor: pointer;
}

.tab-btn:hover {
    border-color: var(--gold);
    color: var(--gold);
}

.tab-btn.active {
    background: var(--gold);
    color: #1a1305;
    border-color: var(--gold);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-control-gold {
    background: var(--bg);
    border: 1px solid var(--stroke);
    color: var(--cream);
    padding: .7rem 1rem;
    border-radius: 8px;
    width: 100%;
}

.form-control-gold:focus {
    background: var(--bg);
    color: var(--cream);
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,175,55,.15);
    outline: none;
}

.form-control-gold:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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

.required-star {
    color: #dc3545;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 767px) {
    .profile-card {
        padding: 1.5rem;
        margin: 0 1rem;
    }
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    .profile-avatar-lg {
        width: 100px;
        height: 100px;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    .quick-actions {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    .quick-actions {
        grid-template-columns: 1fr;
    }
    .profile-avatar-lg {
        width: 80px;
        height: 80px;
    }
    .profile-avatar-lg .avatar-placeholder-lg {
        font-size: 2.5rem;
    }
    .profile-info h2 {
        font-size: 1.1rem;
    }
}
</style>
</head>
<body>

<div class="container profile-wrapper">
    <div class="profile-card">
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <!-- =========================================================
             PROFILE HEADER
             ========================================================= -->
        <div class="profile-header">
            <div class="profile-avatar-wrapper">
                <!-- Profile Avatar -->
                <div class="profile-avatar-lg">
                    <?php 
                    $profile_img = $_SESSION['profile_image'] ?? 'default.png';
                    $img_path = 'uploads/profile/' . $profile_img;
                    $has_image = ($profile_img != 'default.png' && file_exists($img_path));
                    ?>
                    <?php if ($has_image): ?>
                        <img src="<?php echo $img_path; ?>?v=<?php echo time(); ?>" alt="Profile">
                    <?php else: ?>
                        <span class="avatar-placeholder-lg">
                            <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Change Profile Button -->
                <button class="btn-change-profile" onclick="document.getElementById('profileImageInput').click();">
                    <i class="bi bi-camera"></i> Change Profile
                </button>
                
                <!-- Hidden File Input -->
                <form method="POST" enctype="multipart/form-data" id="imageUploadForm" action="">
                    <input type="file" name="profile_image" id="profileImageInput" 
                           accept="image/*" onchange="document.getElementById('imageUploadForm').submit();">
                    <input type="hidden" name="update_image" value="1">
                </form>
                
                <?php if ($has_image): ?>
                    <a href="?remove_image=1" class="btn-change-profile" style="color:#dc3545;border-color:#dc3545;" onclick="return confirm('Remove profile image?');">
                        <i class="bi bi-trash"></i> Remove
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="profile-info">
                <h2>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>! 👋</h2>
                <p><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                <p><i class="bi bi-phone me-1"></i> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                <span class="badge-user"><i class="bi bi-check-circle-fill me-1"></i> Verified Member</span>
            </div>
        </div>
        
        <!-- =========================================================
             STATS GRID - CONNECTED TO CART, WISHLIST & ORDERS
             ========================================================= -->
        <div class="stats-grid">
            <!-- Orders - Clickable -->
            <a href="orders.php" class="stat-item">
                <div class="stat-icon"><i class="bi bi-box"></i></div>
                <div class="stat-number"><?php echo (int)$total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </a>
            
            <!-- Wishlist - Clickable -->
            <a href="wishlist.php" class="stat-item">
                <div class="stat-icon"><i class="bi bi-heart"></i></div>
                <div class="stat-number"><?php echo (int)$wishlist_count; ?></div>
                <div class="stat-label">Wishlist Items</div>
            </a>
            
            <!-- Cart - Clickable -->
            <a href="cart.php" class="stat-item">
                <div class="stat-icon"><i class="bi bi-cart"></i></div>
                <div class="stat-number"><?php echo (int)$cart_count; ?></div>
                <div class="stat-label">Cart Items</div>
            </a>
            
            <!-- Rating -->
            <div class="stat-item" style="cursor:default;">
                <div class="stat-icon"><i class="bi bi-star"></i></div>
                <div class="stat-number">4.8</div>
                <div class="stat-label">Rating</div>
            </div>
        </div>
        
        <!-- =========================================================
             QUICK ACTIONS - CONNECTED TO CART, WISHLIST & ORDERS
             ========================================================= -->
        <h5 style="color:var(--muted);margin-bottom:1rem;font-weight:500;">
            <i class="bi bi-grid me-2"></i> Quick Actions
        </h5>
        <div class="quick-actions">
            <!-- Dashboard -->
            <a href="dashboard.php" class="quick-action-btn">
                <i class="bi bi-speedometer2"></i>
                <div>
                    <div class="action-text">Dashboard</div>
                    <div class="action-sub">Go to dashboard</div>
                </div>
            </a>
            
            <!-- My Orders - Connected -->
            <a href="orders.php" class="quick-action-btn">
                <i class="bi bi-box"></i>
                <div>
                    <div class="action-text">My Orders</div>
                    <div class="action-sub"><?php echo (int)$total_orders; ?> orders placed</div>
                </div>
            </a>
            
            <!-- Wishlist - Connected -->
            <a href="wishlist.php" class="quick-action-btn">
                <i class="bi bi-heart"></i>
                <div>
                    <div class="action-text">Wishlist</div>
                    <div class="action-sub"><?php echo (int)$wishlist_count; ?> items saved</div>
                </div>
            </a>
            
            <!-- Cart - Connected -->
            <a href="cart.php" class="quick-action-btn">
                <i class="bi bi-cart"></i>
                <div>
                    <div class="action-text">Cart</div>
                    <div class="action-sub"><?php echo (int)$cart_count; ?> items in cart</div>
                </div>
            </a>
            
            <!-- Logout -->
            <a href="logout.php" class="quick-action-btn" style="border-color:rgba(220,53,69,0.3);">
                <i class="bi bi-box-arrow-right" style="color:#dc3545;"></i>
                <div>
                    <div class="action-text" style="color:#dc3545;">Logout</div>
                    <div class="action-sub">Sign out of account</div>
                </div>
            </a>
        </div>
        
        <!-- =========================================================
             EDIT PROFILE TABS
             ========================================================= -->
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--stroke);">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="edit">Edit Profile</button>
                <button class="tab-btn" data-tab="password">Change Password</button>
            </div>
            
            <!-- Edit Profile Tab -->
            <div class="tab-content active" id="tab-edit">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="required-star">*</span></label>
                            <input type="text" name="full_name" class="form-control-gold" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control-gold" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="required-star">*</span></label>
                            <input type="text" name="phone" class="form-control-gold" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC</label>
                            <input type="text" class="form-control-gold" value="<?php echo htmlspecialchars($user['cnic'] ?? ''); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control-gold" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Province</label>
                            <select name="province" class="form-control-gold">
                                <option value="">Select Province</option>
                                <option value="Punjab" <?php echo ($user['province'] ?? '') == 'Punjab' ? 'selected' : ''; ?>>Punjab</option>
                                <option value="Sindh" <?php echo ($user['province'] ?? '') == 'Sindh' ? 'selected' : ''; ?>>Sindh</option>
                                <option value="Khyber Pakhtunkhwa" <?php echo ($user['province'] ?? '') == 'Khyber Pakhtunkhwa' ? 'selected' : ''; ?>>Khyber Pakhtunkhwa</option>
                                <option value="Balochistan" <?php echo ($user['province'] ?? '') == 'Balochistan' ? 'selected' : ''; ?>>Balochistan</option>
                                <option value="Gilgit-Baltistan" <?php echo ($user['province'] ?? '') == 'Gilgit-Baltistan' ? 'selected' : ''; ?>>Gilgit-Baltistan</option>
                                <option value="Azad Kashmir" <?php echo ($user['province'] ?? '') == 'Azad Kashmir' ? 'selected' : ''; ?>>Azad Kashmir</option>
                                <option value="Islamabad" <?php echo ($user['province'] ?? '') == 'Islamabad' ? 'selected' : ''; ?>>Islamabad</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-control-gold">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($user['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($user['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="update_profile" class="btn-gold">
                                <i class="bi bi-save me-2"></i> Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Change Password Tab -->
            <div class="tab-content" id="tab-password">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Current Password <span class="required-star">*</span></label>
                            <input type="password" name="current_password" class="form-control-gold" placeholder="Enter current password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password <span class="required-star">*</span></label>
                            <input type="password" name="new_password" class="form-control-gold" placeholder="Min 6 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password <span class="required-star">*</span></label>
                            <input type="password" name="confirm_password" class="form-control-gold" placeholder="Confirm new password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="change_password" class="btn-gold">
                                <i class="bi bi-key me-2"></i> Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<script>
// =========================================================
// TAB SWITCHING
// =========================================================
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        document.querySelectorAll('.tab-content').forEach(function(t) {
            t.classList.remove('active');
        });
        
        this.classList.add('active');
        var tabId = 'tab-' + this.dataset.tab;
        document.getElementById(tabId).classList.add('active');
    });
});

console.log('📸 Profile page loaded successfully!');
console.log('🛒 Cart items: <?php echo (int)$cart_count; ?>');
console.log('❤️ Wishlist items: <?php echo (int)$wishlist_count; ?>');
console.log('📦 Total orders: <?php echo (int)$total_orders; ?>');
</script>

<?php include 'includes/footer.php'; ?>