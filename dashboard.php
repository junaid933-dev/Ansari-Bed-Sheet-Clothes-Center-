<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login-signup.php');
    exit();
}

// Get user data
$user = getCurrentUser();

// Get counts
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Order count
$order_stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order_data = $order_result->fetch_assoc();
$total_orders = $order_data['total'] ?? 0;

// Wishlist count
$wishlist_count = getWishlistCount();

// Cart count
$cart_count = getCartCount();

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Ansari</title>

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
/* =========================================================
   ALL STYLES INLINE
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

.dashboard-wrapper {
    padding: 2rem 0 4rem;
}

.dashboard-card {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2rem;
    transition: all .3s ease;
    height: 100%;
}
.dashboard-card:hover {
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
    gap: 1.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--stroke);
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.profile-avatar-lg {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--gold);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-panel-2);
    flex-shrink: 0;
}
.profile-avatar-lg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-avatar-lg .avatar-placeholder-lg {
    font-size: 3rem;
    color: var(--gold);
    font-weight: 700;
    text-transform: uppercase;
}

.profile-info h2 {
    font-family: 'Playfair Display', serif;
    color: var(--cream);
    margin: 0;
}
.profile-info p {
    color: var(--muted);
    margin: 0.2rem 0;
}
.profile-info .badge-user {
    background: rgba(212,175,55,0.15);
    color: var(--gold);
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
    margin-top: 0.3rem;
}

/* =========================================================
   STATS GRID
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
    cursor: default;
}
.stat-item:hover {
    border-color: var(--gold);
    transform: translateY(-2px);
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
   QUICK ACTIONS
   ========================================================= */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
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
    color: var(--gold);
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
.quick-action-btn.logout-btn {
    border-color: rgba(220, 53, 69, 0.3);
}
.quick-action-btn.logout-btn i {
    color: #dc3545;
}
.quick-action-btn.logout-btn .action-text {
    color: #dc3545;
}
.quick-action-btn.logout-btn:hover {
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

/* =========================================================
   RECENT ORDERS
   ========================================================= */
.recent-orders {
    margin-top: 2rem;
}
.recent-orders table {
    width: 100%;
    border-collapse: collapse;
}
.recent-orders th {
    color: var(--muted);
    font-weight: 500;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.8rem 0.5rem;
    border-bottom: 1px solid var(--stroke);
    text-align: left;
}
.recent-orders td {
    padding: 0.8rem 0.5rem;
    border-bottom: 1px solid rgba(212,175,55,0.05);
    color: var(--cream);
    font-size: 0.9rem;
}
.order-status {
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-block;
}
.order-status.pending {
    background: rgba(255, 193, 7, 0.15);
    color: #ffc107;
}
.order-status.processing {
    background: rgba(23, 162, 184, 0.15);
    color: #17a2b8;
}
.order-status.shipped {
    background: rgba(40, 167, 69, 0.15);
    color: #28a745;
}
.order-status.delivered {
    background: rgba(40, 167, 69, 0.15);
    color: #28a745;
}
.order-status.cancelled {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}

.no-orders {
    text-align: center;
    padding: 2rem 0;
}
.no-orders i {
    font-size: 3rem;
    color: var(--muted);
    margin-bottom: 1rem;
}
.no-orders h5 {
    color: var(--cream);
}
.no-orders p {
    color: var(--muted);
}

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
}
.btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(212,175,55,.28);
    color: #1a1305;
}

@media (max-width: 767px) {
    .dashboard-card {
        padding: 1.2rem;
        margin: 0 1rem;
    }
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    .profile-avatar-lg {
        width: 80px;
        height: 80px;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
    }
    .quick-actions {
        grid-template-columns: 1fr;
    }
    .recent-orders {
        overflow-x: auto;
    }
    .recent-orders table {
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    .stat-item {
        padding: 1rem;
    }
    .stat-item .stat-number {
        font-size: 1.5rem;
    }
    .quick-action-btn {
        padding: 0.8rem 1rem;
    }
    .profile-avatar-lg {
        width: 70px;
        height: 70px;
    }
    .profile-avatar-lg .avatar-placeholder-lg {
        font-size: 2rem;
    }
}
</style>
</head>
<body>

<div class="container dashboard-wrapper">
    <div class="dashboard-card">
        
        <!-- =========================================================
             PROFILE HEADER
             ========================================================= -->
        <div class="profile-header">
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
            
            <div class="profile-info">
                <h2>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>! 👋</h2>
                <p><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                <p><i class="bi bi-phone me-1"></i> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                <span class="badge-user"><i class="bi bi-check-circle-fill me-1"></i> Verified Member</span>
            </div>
        </div>
        
        <!-- =========================================================
             QUICK ACTIONS - ALL WORKING LINKS
             ========================================================= -->
        <h5 style="color:var(--muted);margin-bottom:1rem;font-weight:500;">
            <i class="bi bi-grid me-2"></i> Quick Actions
        </h5>
        <div class="quick-actions">
            <!-- My Profile -->
            <a href="profile.php" class="quick-action-btn">
                <i class="bi bi-person"></i>
                <div>
                    <div class="action-text">My Profile</div>
                    <div class="action-sub">View &amp; edit profile</div>
                </div>
            </a>
            
            <!-- My Orders -->
            <a href="orders.php" class="quick-action-btn">
                <i class="bi bi-box"></i>
                <div>
                    <div class="action-text">My Orders</div>
                    <div class="action-sub">View order history</div>
                </div>
            </a>
            
            <!-- Shop Now -->
            <a href="women-clothes.php" class="quick-action-btn">
                <i class="bi bi-bag"></i>
                <div>
                    <div class="action-text">Shop Now</div>
                    <div class="action-sub">Browse collection</div>
                </div>
            </a>
            
            <!-- Logout -->
            <a href="logout.php" class="quick-action-btn logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <div>
                    <div class="action-text">Logout</div>
                    <div class="action-sub">Sign out of account</div>
                </div>
            </a>
        </div>
        
        <!-- =========================================================
             STATS GRID - ALL WORKING
             ========================================================= -->
        <div class="stats-grid" style="margin-top:2rem;">
            <!-- Total Orders - Clickable -->
            <a href="orders.php" class="stat-item" style="cursor:pointer;text-decoration:none;">
                <div class="stat-icon"><i class="bi bi-box"></i></div>
                <div class="stat-number"><?php echo (int)$total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </a>
            
            <!-- Wishlist Items - Clickable -->
            <a href="wishlist.php" class="stat-item" style="cursor:pointer;text-decoration:none;">
                <div class="stat-icon"><i class="bi bi-heart"></i></div>
                <div class="stat-number"><?php echo (int)$wishlist_count; ?></div>
                <div class="stat-label">Wishlist Items</div>
            </a>
            
            <!-- Cart Items - Clickable -->
            <a href="cart.php" class="stat-item" style="cursor:pointer;text-decoration:none;">
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
             RECENT ORDERS
             ========================================================= -->
        <div class="recent-orders">
            <h5 style="color:var(--muted);margin:1.5rem 0 1rem;font-weight:500;">
                <i class="bi bi-clock-history me-2"></i> Recent Orders
            </h5>
            
            <?php
            // Get recent orders
            $order_list = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
            $order_list->bind_param("i", $_SESSION['user_id']);
            $order_list->execute();
            $orders = $order_list->get_result();
            
            if ($orders->num_rows > 0):
            ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $order['order_number'] ?? $order['id']; ?></strong></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>Rs. <?php echo number_format($order['total_amount']); ?></td>
                            <td><span class="order-status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><a href="orders.php" style="color:var(--gold);font-size:0.8rem;">View</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-orders">
                <i class="bi bi-box-seam"></i>
                <h5>No orders yet</h5>
                <p>Start shopping to see your orders here.</p>
                <a href="women-clothes.php" class="btn-gold" style="display:inline-block;margin-top:0.5rem;padding:0.4rem 1.2rem;font-size:0.85rem;">
                    <i class="bi bi-bag me-1"></i> Shop Now
                </a>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
console.log('📊 Dashboard loaded successfully!');
console.log('📦 Total Orders: <?php echo (int)$total_orders; ?>');
console.log('❤️ Wishlist Items: <?php echo (int)$wishlist_count; ?>');
console.log('🛒 Cart Items: <?php echo (int)$cart_count; ?>');
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>