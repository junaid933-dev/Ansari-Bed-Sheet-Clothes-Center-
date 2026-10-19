<?php
// wishlist.php - Complete Fixed Wishlist
require_once 'config.php';
requireLogin();
include 'includes/header.php';

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// =========================================================
// HANDLE WISHLIST ACTIONS
// =========================================================

// Remove from wishlist (GET - Page Reload)
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (removeFromWishlist($user_id, $product_id)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item removed from wishlist.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to remove item.'];
    }
    header('Location: wishlist.php');
    exit();
}

// Add to cart from wishlist - FIXED
if (isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    $size = isset($_GET['size']) ? $_GET['size'] : null;
    $color = isset($_GET['color']) ? $_GET['color'] : null;
    
    // Add to cart
    if (addToCart($user_id, $product_id, 1, $size, $color)) {
        // Remove from wishlist after adding to cart
        removeFromWishlist($user_id, $product_id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item added to cart and removed from wishlist!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to add item to cart.'];
    }
    header('Location: wishlist.php');
    exit();
}

// =========================================================
// AJAX REMOVE FROM WISHLIST
// =========================================================
if (isset($_POST['ajax_remove'])) {
    header('Content-Type: application/json');
    
    $product_id = intval($_POST['product_id'] ?? 0);
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    if ($product_id > 0 && $user_id > 0) {
        if (removeFromWishlist($user_id, $product_id)) {
            $new_count = getWishlistCount();
            $response = [
                'success' => true,
                'message' => 'Item removed from wishlist.',
                'count' => $new_count
            ];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove item.'];
        }
    }
    
    echo json_encode($response);
    exit();
}

// =========================================================
// GET WISHLIST ITEMS
// =========================================================
$wishlist_items = getWishlistItems($user_id);
$count = $wishlist_items->num_rows;

$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist | Ansari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--cream);
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
        }
        a { text-decoration: none; color: inherit; }

        .wishlist-wrapper { padding: 2rem 0 4rem; }
        .wishlist-card {
            background: var(--bg-panel);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }
        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--stroke);
        }
        .wishlist-header .title-section h2 {
            color: var(--cream);
            font-family: 'Playfair Display', serif;
            margin: 0;
        }
        .wishlist-header .title-section p {
            color: var(--muted);
            font-size: 0.9rem;
            margin: 0.2rem 0 0;
        }
        .wishlist-header .back-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.2rem;
            background: var(--bg-panel-2);
            border: 1px solid var(--gold);
            border-radius: 8px;
            color: var(--gold);
            font-weight: 500;
            font-size: 0.85rem;
            transition: all .3s ease;
            text-decoration: none;
        }
        .wishlist-header .back-btn:hover {
            background: var(--gold);
            color: #1a1305;
            transform: translateY(-2px);
        }
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        .wishlist-item {
            background: var(--bg-panel-2);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            overflow: hidden;
            transition: all .3s ease;
        }
        .wishlist-item:hover {
            border-color: var(--gold);
            transform: translateY(-4px);
        }
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .wishlist-item .info {
            padding: 1rem;
        }
        .wishlist-item .info h6 {
            color: var(--cream);
            font-size: 0.95rem;
            margin-bottom: 0.2rem;
        }
        .wishlist-item .info .price {
            color: var(--gold);
            font-weight: 600;
            font-size: 1rem;
        }
        .wishlist-item .actions {
            display: flex;
            gap: 0.5rem;
            padding: 0.5rem 1rem 1rem;
        }
        .btn-gold-sm {
            background: linear-gradient(180deg, var(--gold-bright), var(--gold));
            border: none;
            color: #1a1305;
            font-weight: 600;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            border-radius: 6px;
            transition: all .3s ease;
            cursor: pointer;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }
        .btn-gold-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(212,175,55,.3);
            color: #1a1305;
        }
        .btn-remove-sm {
            border: 1px solid #dc3545;
            color: #dc3545;
            background: transparent;
            font-weight: 500;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            border-radius: 6px;
            transition: all .3s ease;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-remove-sm:hover {
            background: #dc3545;
            color: #fff;
        }
        .empty-wishlist {
            text-align: center;
            padding: 3rem 0;
        }
        .empty-wishlist i {
            font-size: 4rem;
            color: var(--muted);
            margin-bottom: 1rem;
        }
        .empty-wishlist h4 { color: var(--cream); }
        .empty-wishlist p { color: var(--muted); }
        .alert {
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            margin-bottom: 1rem;
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
            width: 0.8rem;
            height: 0.8rem;
            border: 2px solid #1a1305;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 767px) {
            .wishlist-card { padding: 1.5rem; margin: 0 1rem; }
            .wishlist-grid { grid-template-columns: repeat(2, 1fr); }
            .wishlist-header { flex-direction: column; text-align: center; }
            .wishlist-header .back-btn { width: 100%; justify-content: center; }
        }
        @media (max-width: 480px) {
            .wishlist-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container wishlist-wrapper">
    <div class="wishlist-card">
        
        <div class="wishlist-header">
            <div class="title-section">
                <h2><i class="bi bi-heart" style="color:var(--gold);"></i> My Wishlist</h2>
                <p id="wishlistCountDisplay"><?php echo $count; ?> item<?php echo $count != 1 ? 's' : ''; ?> saved</p>
            </div>
            <a href="profile.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
        </div>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($count > 0): ?>
            <div class="wishlist-grid" id="wishlistGrid">
                <?php while ($item = $wishlist_items->fetch_assoc()): 
                    $product_image = $item['image_url'] ?? 'assets/images/default-product.jpg';
                ?>
                <div class="wishlist-item" id="wishlist-item-<?php echo $item['product_id']; ?>">
                    <img src="<?php echo $product_image; ?>" 
                         alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>"
                         onerror="this.src='https://via.placeholder.com/220x200?text=No+Image'">
                    <div class="info">
                        <h6><?php echo htmlspecialchars($item['name'] ?? 'Product #' . $item['product_id']); ?></h6>
                        <div class="price">Rs. <?php echo number_format($item['price'] ?? 0); ?></div>
                    </div>
                    <div class="actions">
                        <a href="?add_to_cart=<?php echo $item['product_id']; ?>" class="btn-gold-sm add-to-cart-btn">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </a>
                        <button class="btn-remove-sm remove-btn" data-product-id="<?php echo $item['product_id']; ?>">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <i class="bi bi-heart"></i>
                <h4>Your wishlist is empty</h4>
                <p class="text-muted-gold">Save your favorite items here.</p>
                <a href="index.php" class="btn-gold-sm" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.5rem;font-size:0.9rem;">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // =========================================================
    // REMOVE FROM WISHLIST (AJAX)
    // =========================================================
    var removeButtons = document.querySelectorAll('.remove-btn');
    
    removeButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var productId = this.dataset.productId;
            var originalHtml = this.innerHTML;
            
            this.disabled = true;
            this.innerHTML = '<span class="loading-spinner"></span>';
            
            var formData = new FormData();
            formData.append('ajax_remove', '1');
            formData.append('product_id', productId);
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'wishlist.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        
                        if (data.success) {
                            var itemElement = document.getElementById('wishlist-item-' + productId);
                            if (itemElement) {
                                itemElement.style.transition = 'all 0.3s ease';
                                itemElement.style.opacity = '0';
                                itemElement.style.transform = 'scale(0.8)';
                                
                                setTimeout(function() {
                                    itemElement.remove();
                                    
                                    var countDisplay = document.getElementById('wishlistCountDisplay');
                                    if (countDisplay) {
                                        var count = data.count;
                                        countDisplay.textContent = count + ' item' + (count != 1 ? 's' : '') + ' saved';
                                    }
                                    
                                    var grid = document.getElementById('wishlistGrid');
                                    if (grid && grid.children.length === 0) {
                                        grid.innerHTML = `
                                            <div class="empty-wishlist" style="grid-column:1/-1;">
                                                <i class="bi bi-heart"></i>
                                                <h4>Your wishlist is empty</h4>
                                                <p class="text-muted-gold">Save your favorite items here.</p>
                                                <a href="index.php" class="btn-gold-sm" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.5rem;font-size:0.9rem;">
                                                    Start Shopping
                                                </a>
                                            </div>
                                        `;
                                    }
                                    
                                    showNotification('✅ ' + data.message);
                                    
                                }, 300);
                            }
                        } else {
                            showNotification('❌ ' + data.message);
                        }
                    } catch (e) {
                        console.error('Parse error:', e);
                        showNotification('❌ Error: Invalid response from server');
                    }
                } else {
                    showNotification('❌ Server error (Status: ' + xhr.status + ')');
                }
            };
            
            xhr.onerror = function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                showNotification('❌ Connection error. Please try again.');
            };
            
            xhr.send(formData);
        });
    });

    // =========================================================
    // ADD TO CART - FIXED (No redirect issue)
    // =========================================================
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            var productId = this.href.split('add_to_cart=')[1];
            if (!productId) return;
            
            var originalHtml = this.innerHTML;
            this.innerHTML = '<span class="loading-spinner"></span>';
            this.disabled = true;
            
            // Use AJAX to add to cart
            var formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            
            fetch('wishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.innerHTML = originalHtml;
                this.disabled = false;
                
                if (data.success) {
                    // Remove item from wishlist
                    var itemElement = document.getElementById('wishlist-item-' + productId);
                    if (itemElement) {
                        itemElement.style.transition = 'all 0.3s ease';
                        itemElement.style.opacity = '0';
                        itemElement.style.transform = 'scale(0.8)';
                        
                        setTimeout(function() {
                            itemElement.remove();
                            
                            var countDisplay = document.getElementById('wishlistCountDisplay');
                            if (countDisplay) {
                                var count = data.wishlist_count || 0;
                                countDisplay.textContent = count + ' item' + (count != 1 ? 's' : '') + ' saved';
                            }
                            
                            var grid = document.getElementById('wishlistGrid');
                            if (grid && grid.children.length === 0) {
                                grid.innerHTML = `
                                    <div class="empty-wishlist" style="grid-column:1/-1;">
                                        <i class="bi bi-heart"></i>
                                        <h4>Your wishlist is empty</h4>
                                        <p class="text-muted-gold">Save your favorite items here.</p>
                                        <a href="index.php" class="btn-gold-sm" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.5rem;font-size:0.9rem;">
                                            Start Shopping
                                        </a>
                                    </div>
                                `;
                            }
                            
                            showNotification('✅ ' + data.message);
                            
                        }, 300);
                    } else {
                        showNotification('✅ ' + data.message);
                    }
                } else {
                    showNotification('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = originalHtml;
                this.disabled = false;
                showNotification('❌ Something went wrong');
            });
        });
    });
});

// =========================================================
// NOTIFICATION TOAST
// =========================================================
function showNotification(message) {
    var existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();
    
    var toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

console.log('❤️ Wishlist page loaded successfully!');
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>