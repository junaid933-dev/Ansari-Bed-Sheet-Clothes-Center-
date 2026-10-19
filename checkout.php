<?php
require_once 'config.php';
requireLogin();
include 'includes/header.php';

$user = getCurrentUser();
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);

if ($cart_items->num_rows == 0) {
    setMessage('error', 'Your cart is empty. Please add items first.');
    header('Location: cart.php');
    exit();
}

// =========================================================
// PROCESS ORDER
// =========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $province = sanitize($_POST['province'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validate
    if (empty($address) || empty($city) || empty($province) || empty($phone)) {
        setMessage('error', 'Please fill in all required fields.');
    } else {
        $order_id = createOrder($user_id, $address, $city, $province, $phone, $notes);
        if ($order_id) {
            setMessage('success', '✅ Order placed successfully! Order #' . generateOrderNumber());
            header('Location: orders.php');
            exit();
        } else {
            setMessage('error', 'Failed to place order. Please try again.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Ansari</title>
<style>
.checkout-wrapper { padding: 2rem 0 4rem; }
.checkout-card {
    background: var(--bg-panel);
    border: 1px solid var(--stroke);
    border-radius: 16px;
    padding: 2.5rem;
}
.order-summary {
    background: var(--bg-panel-2);
    border-radius: 12px;
    padding: 1.5rem;
}
.order-summary .item-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--stroke);
    font-size: 0.9rem;
}
.order-summary .item-row:last-child { border-bottom: none; }
.order-summary .total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-weight: 600;
}
.order-summary .grand-total {
    color: var(--gold);
    font-size: 1.2rem;
    border-top: 1px solid var(--stroke);
    padding-top: 1rem;
    margin-top: 0.5rem;
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
.form-control-gold::placeholder { color: var(--muted); }
.form-control-gold option { background: var(--bg-panel); color: var(--cream); }
.form-label { color: var(--muted); font-size: .85rem; font-weight: 500; }
.required-star { color: #dc3545; }
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
@media (max-width: 767px) {
    .checkout-card { padding: 1.5rem; margin: 0 1rem; }
}
</style>
</head>
<body>

<div class="container checkout-wrapper">
    <div class="checkout-card">
        <h2 style="color:var(--cream);font-family:'Playfair Display',serif;margin-bottom:0.5rem;">
            <i class="bi bi-credit-card" style="color:var(--gold);"></i> Checkout
        </h2>
        
        <?php displayMessage(); ?>
        
        <div class="row g-4">
            <!-- Billing Details -->
            <div class="col-lg-7">
                <h5 style="color:var(--cream);margin-bottom:1rem;">Billing Details</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="required-star">*</span></label>
                        <input type="text" class="form-control-gold" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="required-star">*</span></label>
                        <input type="email" class="form-control-gold" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="required-star">*</span></label>
                        <input type="text" name="phone" class="form-control-gold" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shipping Address <span class="required-star">*</span></label>
                        <textarea name="address" class="form-control-gold" rows="3" required placeholder="Street, house number, area"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">City <span class="required-star">*</span></label>
                            <input type="text" name="city" class="form-control-gold" placeholder="e.g. Lahore" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Province <span class="required-star">*</span></label>
                            <select name="province" class="form-control-gold" required>
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
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Order Notes (Optional)</label>
                        <textarea name="notes" class="form-control-gold" rows="2" placeholder="Any special instructions..."></textarea>
                    </div>
                    <button type="submit" name="place_order" class="btn-gold w-100 py-2">
                        <i class="bi bi-check-circle me-2"></i> Place Order
                    </button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-5">
                <h5 style="color:var(--cream);margin-bottom:1rem;">Order Summary</h5>
                <div class="order-summary">
                    <?php 
                    $subtotal = 0;
                    $cart_items->data_seek(0);
                    while ($item = $cart_items->fetch_assoc()): 
                        $item_total = $item['price'] * $item['quantity'];
                        $subtotal += $item_total;
                    ?>
                    <div class="item-row">
                        <span><?php echo $item['name']; ?> × <?php echo $item['quantity']; ?></span>
                        <span>Rs. <?php echo number_format($item_total); ?></span>
                    </div>
                    <?php endwhile; ?>
                    
                    <div class="total-row mt-2">
                        <span>Subtotal</span>
                        <span>Rs. <?php echo number_format($subtotal); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Delivery</span>
                        <span><?php echo $subtotal >= 3000 ? 'Free' : 'Rs. 300'; ?></span>
                    </div>
                    <div class="grand-total">
                        <span>Total</span>
                        <span>Rs. <?php echo number_format($subtotal >= 3000 ? $subtotal : $subtotal + 300); ?></span>
                    </div>
                    <div class="mt-3">
                        <small style="color:var(--muted);">
                            <i class="bi bi-shield-check me-1"></i> Cash on delivery available
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>