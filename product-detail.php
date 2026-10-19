<?php
require_once 'config.php';
include 'includes/header.php';

$conn = getDBConnection();

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = getProductById($product_id);

if (!$product) {
    setMessage('error', 'Product not found.');
    header('Location: index.php');
    exit();
}

$is_in_wishlist = false;
if (isLoggedIn()) {
    $is_in_wishlist = isInWishlist($_SESSION['user_id'], $product_id);
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    requireLogin();
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $size = $_POST['size'] ?? null;
    $color = $_POST['color'] ?? null;
    
    if (addToCart($_SESSION['user_id'], $product_id, $quantity, $size, $color)) {
        setMessage('success', 'Product added to cart!');
    } else {
        setMessage('error', 'Failed to add product to cart.');
    }
    header('Location: cart.php');
    exit();
}

// Handle wishlist toggle
if (isset($_GET['wishlist'])) {
    requireLogin();
    if ($is_in_wishlist) {
        removeFromWishlist($_SESSION['user_id'], $product_id);
        setMessage('success', 'Removed from wishlist.');
    } else {
        addToWishlist($_SESSION['user_id'], $product_id);
        setMessage('success', 'Added to wishlist!');
    }
    header('Location: product-detail.php?id=' . $product_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $product['name']; ?> | Ansari</title>
<style>
.product-wrapper { padding: 2rem 0 4rem; }
.product-card {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2.5rem;
}
.product-image {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 12px;
}
.product-price {
    color: var(--gold);
    font-size: 2rem;
    font-weight: 700;
}
.product-old-price {
    color: var(--muted);
    text-decoration: line-through;
    font-size: 1.2rem;
    margin-left: 1rem;
}
.btn-gold {
    background: linear-gradient(180deg, var(--gold-bright), var(--gold));
    border: none;
    color: #1a1305;
    font-weight: 600;
    padding: .6rem 1.5rem;
    border-radius: 8px;
    transition: all .3s ease;
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
}
.btn-outline-gold:hover {
    background: var(--gold);
    color: #1a1305;
}
.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
    background: transparent;
    font-weight: 500;
    padding: .6rem 1.5rem;
    border-radius: 8px;
    transition: all .3s ease;
}
.btn-outline-danger:hover {
    background: #dc3545;
    color: #fff;
}
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
</style>
</head>
<body>

<div class="container product-wrapper">
    <div class="product-card">
        <?php displayMessage(); ?>
        
        <div class="row g-4">
            <!-- Product Image -->
            <div class="col-md-6">
                <img src="<?php echo $product['image_url'] ?? 'assets/images/placeholder.jpg'; ?>" 
                     alt="<?php echo $product['name']; ?>" 
                     class="product-image">
            </div>
            
            <!-- Product Info -->
            <div class="col-md-6">
                <h1 style="color:var(--cream);font-family:'Playfair Display',serif;"><?php echo $product['name']; ?></h1>
                
                <div class="mt-2">
                    <span class="product-price">Rs. <?php echo number_format($product['price']); ?></span>
                    <?php if ($product['old_price']): ?>
                        <span class="product-old-price">Rs. <?php echo number_format($product['old_price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <span class="badge" style="background:var(--gold);color:#1a1305;">
                        <?php echo ucfirst($product['category']); ?>
                    </span>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge" style="background:#28a745;color:#fff;">In Stock</span>
                    <?php else: ?>
                        <span class="badge" style="background:#dc3545;color:#fff;">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <p class="text-muted-gold mt-3"><?php echo $product['description'] ?? 'Premium quality product from Ansari.'; ?></p>
                
                <?php if ($product['stock'] > 0): ?>
                <form method="POST" class="mt-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" style="color:var(--muted);">Quantity</label>
                            <input type="number" name="quantity" class="form-control" style="background:var(--bg);border:1px solid var(--stroke);color:var(--cream);" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-3 flex-wrap">
                        <button type="submit" name="add_to_cart" class="btn-gold">
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>
                        
                        <?php if (isLoggedIn()): ?>
                            <a href="?wishlist=1" class="<?php echo $is_in_wishlist ? 'btn-outline-danger' : 'btn-outline-gold'; ?>">
                                <i class="bi <?php echo $is_in_wishlist ? 'bi-heart-fill' : 'bi-heart'; ?> me-2"></i>
                                <?php echo $is_in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                            </a>
                        <?php else: ?>
                            <a href="login-signup.php" class="btn-outline-gold">
                                <i class="bi bi-heart me-2"></i> Login to Wishlist
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-danger mt-3">This product is currently out of stock.</div>
                <?php endif; ?>
                
                <div class="mt-4 pt-3" style="border-top:1px solid var(--stroke);">
                    <small style="color:var(--muted);">
                        <i class="bi bi-truck me-1"></i> Free delivery on orders above Rs. 3,000
                    </small><br>
                    <small style="color:var(--muted);">
                        <i class="bi bi-arrow-repeat me-1"></i> 7-day easy return policy
                    </small><br>
                    <small style="color:var(--muted);">
                        <i class="bi bi-shield-check me-1"></i> Cash on delivery available
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>