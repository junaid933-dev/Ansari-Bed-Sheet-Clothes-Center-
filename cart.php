<?php
// cart.php - Complete Fixed Cart Page with Auto Reload
require_once 'config.php';
requireLogin();
include 'includes/header.php';

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// =========================================================
// HANDLE AJAX REQUESTS
// =========================================================
if (isset($_POST['ajax_update'])) {
    header('Content-Type: application/json');
    
    $cart_id = intval($_POST['cart_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    if ($cart_id > 0 && $user_id > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        
        if ($stmt->execute()) {
            $new_cart_total = getCartTotal($user_id);
            $new_cart_count = getCartCount();
            
            $stmt2 = $conn->prepare("SELECT c.quantity, p.price, p.image_url FROM cart c LEFT JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
            $stmt2->bind_param("ii", $cart_id, $user_id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $item_data = $result->fetch_assoc();
            
            $item_total = 0;
            $new_qty = $quantity;
            if ($item_data) {
                $item_total = $item_data['quantity'] * $item_data['price'];
                $new_qty = $item_data['quantity'];
            }
            
            $response = [
                'success' => true,
                'message' => 'Cart updated successfully!',
                'cart_total' => $new_cart_total,
                'cart_count' => $new_cart_count,
                'item_total' => $item_total,
                'quantity' => $new_qty
            ];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update cart.'];
        }
    }
    
    echo json_encode($response);
    exit();
}

// =========================================================
// HANDLE REMOVE ITEM
// =========================================================
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    if (removeFromCart($cart_id, $user_id)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item removed from cart.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to remove item.'];
    }
    header('Location: cart.php');
    exit();
}

// =========================================================
// HANDLE CLEAR CART
// =========================================================
if (isset($_GET['clear'])) {
    if (clearCart($user_id)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cart cleared successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to clear cart.'];
    }
    header('Location: cart.php');
    exit();
}

// =========================================================
// HANDLE PLACE ORDER - FIXED
// =========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Get form data
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    if (empty($address) || empty($city) || empty($province) || empty($phone)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please fill in all required fields.'];
        header('Location: cart.php');
        exit();
    }
    
    // Create order
    $order_id = createOrder($user_id, $address, $city, $province, $phone, $notes);
    
    if ($order_id) {
        // Clear cart
        clearCart($user_id);
        
        // Set success message
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Order placed successfully! Order #' . $order_id];
        
        // Redirect to cart page to show empty cart with success message
        header('Location: cart.php?order_placed=1');
        exit();
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to place order. Please try again.'];
        header('Location: cart.php');
        exit();
    }
}

// =========================================================
// GET CART ITEMS
// =========================================================
$cart_items = getCartItems($user_id);
$cart_total = getCartTotal($user_id);
$cart_count = $cart_items->num_rows;

$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);

// Check if order was just placed
$order_placed = isset($_GET['order_placed']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Ansari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
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
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            background:var(--bg);
            color:var(--cream);
            font-family:'Poppins',sans-serif;
            font-weight:300;
        }
        a{text-decoration:none;color:inherit;}
        .cart-wrapper{padding:2rem 0 4rem;}
        .cart-card{
            background:var(--bg-panel);
            border:1px solid var(--stroke);
            border-radius:16px;
            padding:2.5rem;
            max-width:1100px;
            margin:0 auto;
        }
        .cart-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:1rem;
            margin-bottom:1.5rem;
            padding-bottom:1rem;
            border-bottom:1px solid var(--stroke);
        }
        .cart-header .title-section h2{
            color:var(--cream);
            font-family:'Playfair Display',serif;
            margin:0;
        }
        .cart-header .title-section p{
            color:var(--muted);
            font-size:0.9rem;
            margin:0.2rem 0 0;
        }
        .cart-header .back-btn{
            display:flex;
            align-items:center;
            gap:0.5rem;
            padding:0.5rem 1.2rem;
            background:var(--bg-panel-2);
            border:1px solid var(--gold);
            border-radius:8px;
            color:var(--gold);
            font-weight:500;
            font-size:0.85rem;
            transition:all .3s ease;
            text-decoration:none;
        }
        .cart-header .back-btn:hover{
            background:var(--gold);
            color:#1a1305;
            transform:translateY(-2px);
        }
        .cart-item{
            display:flex;
            gap:1rem;
            padding:1rem 0;
            border-bottom:1px solid var(--stroke);
            align-items:center;
            flex-wrap:wrap;
        }
        .cart-item:last-child{border-bottom:none;}
        .cart-item-img{
            width:70px;
            height:70px;
            border-radius:10px;
            overflow:hidden;
            flex-shrink:0;
            background:var(--bg-panel-2);
            border:1px solid var(--stroke);
        }
        .cart-item-img img{
            width:100%;
            height:100%;
            object-fit:cover;
        }
        .cart-item-info{
            flex:1;
            min-width:120px;
        }
        .cart-item-info h6{
            color:var(--cream);
            margin-bottom:0.2rem;
            font-size:0.95rem;
        }
        .cart-item-info small{
            color:var(--muted);
            font-size:0.7rem;
        }
        .cart-item-price{
            color:var(--gold);
            font-weight:600;
            font-size:1rem;
        }
        .cart-item-qty{
            display:flex;
            align-items:center;
            gap:0.4rem;
        }
        .cart-item-qty input{
            width:50px;
            padding:0.3rem;
            background:var(--bg);
            border:1px solid var(--stroke);
            color:var(--cream);
            border-radius:6px;
            text-align:center;
            font-size:0.85rem;
        }
        .cart-item-qty input:focus{
            border-color:var(--gold);
            outline:none;
        }
        .cart-item-qty .btn-update{
            padding:0.25rem 0.6rem;
            font-size:0.7rem;
            border-radius:6px;
            background:linear-gradient(180deg,var(--gold-bright),var(--gold));
            border:none;
            color:#1a1305;
            font-weight:600;
            transition:all .3s ease;
            cursor:pointer;
        }
        .cart-item-qty .btn-update:hover{
            transform:translateY(-2px);
            box-shadow:0 4px 15px rgba(212,175,55,.3);
        }
        .cart-item-qty .btn-update:disabled{
            opacity:0.6;
            cursor:not-allowed;
        }
        .cart-item-total{
            color:var(--gold);
            font-weight:600;
            font-size:0.95rem;
            min-width:80px;
            text-align:right;
        }
        .btn-remove{
            border:1px solid #dc3545;
            color:#dc3545;
            background:transparent;
            font-weight:500;
            padding:0.2rem 0.6rem;
            border-radius:6px;
            transition:all .3s ease;
            font-size:0.7rem;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
        }
        .btn-remove:hover{
            background:#dc3545;
            color:#fff;
        }
        .cart-summary{
            background:var(--bg-panel-2);
            border-radius:12px;
            padding:1.5rem;
            margin-top:1.5rem;
            width:100%;
        }
        .cart-summary .total-row{
            display:flex;
            justify-content:space-between;
            padding:0.5rem 0;
            color:var(--muted);
        }
        .cart-summary .grand-total{
            font-size:1.3rem;
            color:var(--gold);
            font-weight:700;
            border-top:1px solid var(--stroke);
            padding-top:1rem;
            margin-top:0.5rem;
        }
        .btn-gold{
            background:linear-gradient(180deg,var(--gold-bright),var(--gold));
            border:none;
            color:#1a1305;
            font-weight:600;
            padding:.6rem 1.5rem;
            border-radius:8px;
            transition:all .3s ease;
            text-decoration:none;
            display:inline-block;
            text-align:center;
            cursor:pointer;
        }
        .btn-gold:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 22px rgba(212,175,55,.28);
            color:#1a1305;
        }
        .btn-gold.w-100{
            width:100%;
            display:block;
        }
        .btn-outline-danger{
            border:1px solid #dc3545;
            color:#dc3545;
            background:transparent;
            font-weight:500;
            padding:0.3rem 0.8rem;
            border-radius:6px;
            transition:all .3s ease;
            font-size:0.75rem;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
        }
        .btn-outline-danger:hover{
            background:#dc3545;
            color:#fff;
        }
        .empty-cart{
            text-align:center;
            padding:3rem 0;
        }
        .empty-cart i{
            font-size:4rem;
            color:var(--muted);
            margin-bottom:1rem;
        }
        .empty-cart h4{
            color:var(--cream);
        }
        .empty-cart p{
            color:var(--muted);
        }
        .alert{
            border-radius:8px;
            padding:0.8rem 1.2rem;
            margin-bottom:1rem;
        }
        .alert-success{
            background:rgba(40,167,69,0.15);
            border:1px solid #28a745;
            color:#28a745;
        }
        .alert-danger{
            background:rgba(220,53,69,0.15);
            border:1px solid #dc3545;
            color:#dc3545;
        }
        .order-form-input{
            background:var(--bg);
            border:1px solid var(--stroke);
            color:var(--cream);
            padding:.7rem 1rem;
            border-radius:8px;
            width:100%;
        }
        .order-form-input:focus{
            background:var(--bg);
            color:var(--cream);
            border-color:var(--gold);
            box-shadow:0 0 0 3px rgba(212,175,55,.15);
            outline:none;
        }
        .order-form-input::placeholder{
            color:var(--muted);
        }
        .order-form-input option{
            background:var(--bg-panel);
            color:var(--cream);
        }
        .order-form-label{
            color:var(--muted);
            font-size:.85rem;
            font-weight:500;
        }
        .loading-spinner{
            display:inline-block;
            width:0.8rem;
            height:0.8rem;
            border:2px solid #1a1305;
            border-top-color:transparent;
            border-radius:50%;
            animation:spin 0.6s linear infinite;
        }
        @keyframes spin{
            to{transform:rotate(360deg);}
        }
        .order-success-box{
            text-align:center;
            padding:2rem 1rem;
        }
        .order-success-box .icon{
            font-size:4rem;
            color:#28a745;
            display:block;
            margin-bottom:1rem;
        }
        .order-success-box h3{
            color:var(--cream);
            font-family:'Playfair Display',serif;
            margin-bottom:0.5rem;
        }
        .order-success-box p{
            color:var(--muted);
        }
        .order-success-box .order-id{
            color:var(--gold);
            font-weight:600;
            font-size:1.2rem;
        }
        .order-success-box .btn-group{
            display:flex;
            gap:1rem;
            justify-content:center;
            margin-top:1.5rem;
            flex-wrap:wrap;
        }
        .order-success-box .btn-group a{
            padding:0.6rem 1.5rem;
            border-radius:8px;
            text-decoration:none;
            display:inline-block;
        }
        @media(max-width:767px){
            .cart-card{padding:1.5rem;margin:0 1rem;}
            .cart-item{flex-direction:column;text-align:center;}
            .cart-item-img{width:80px;height:80px;margin:0 auto;}
            .cart-item-qty{justify-content:center;}
            .cart-item-total{text-align:center;min-width:auto;}
            .cart-header{flex-direction:column;text-align:center;}
            .cart-header .back-btn{width:100%;justify-content:center;}
            .cart-item-info{text-align:center;}
            .btn-remove{margin-top:0.5rem;}
            .order-success-box .btn-group{flex-direction:column;align-items:center;}
        }
    </style>
</head>
<body>

<div class="container cart-wrapper">
    <div class="cart-card">
        
        <div class="cart-header">
            <div class="title-section">
                <h2><i class="bi bi-cart" style="color:var(--gold);"></i> Shopping Cart</h2>
                <p id="cartCountDisplay"><?php echo $cart_count; ?> item<?php echo $cart_count != 1 ? 's' : ''; ?> in your cart</p>
            </div>
            <a href="profile.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back to Profile
            </a>
        </div>
        
        <!-- =========================================================
             ORDER PLACED SUCCESS - Shows when order_placed=1
             ========================================================= -->
        <?php if ($order_placed && $cart_count == 0): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $flash ? $flash['message'] : 'Order placed successfully!'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
            <div class="empty-cart">
                <i class="bi bi-cart-check"></i>
                <h4>Order Placed Successfully! 🎉</h4>
                <p class="text-muted-gold">Your order has been placed and will be processed soon.</p>
                <div style="display:flex;gap:1rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap;">
                    <a href="orders.php" class="btn-gold" style="display:inline-block;">
                        <i class="bi bi-box me-2"></i> View My Orders
                    </a>
                    <a href="index.php" class="btn-outline-gold" style="display:inline-block;border:1px solid var(--gold);color:var(--gold);padding:0.6rem 1.5rem;border-radius:8px;text-decoration:none;">
                        <i class="bi bi-house me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
        <?php elseif ($flash && $flash['type'] == 'error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- =========================================================
             CART WITH ITEMS
             ========================================================= -->
        <?php if ($cart_count > 0 && !$order_placed): ?>
            <div class="mb-3">
                <a href="?clear=1" class="btn-outline-danger" onclick="return confirm('Clear all items from cart?')">
                    <i class="bi bi-trash"></i> Clear Cart
                </a>
            </div>
            
            <div id="cartItemsContainer">
                <?php while ($item = $cart_items->fetch_assoc()): 
                    $product_image = $item['image_url'] ?? 'assets/images/default-product.jpg';
                ?>
                <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                    <div class="cart-item-img">
                        <img src="<?php echo $product_image; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    
                    <div class="cart-item-info">
                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                        <?php if ($item['size']): ?>
                            <small>Size: <?php echo htmlspecialchars($item['size']); ?></small>
                        <?php endif; ?>
                        <?php if ($item['color']): ?>
                            <small> | Color: <?php echo htmlspecialchars($item['color']); ?></small>
                        <?php endif; ?>
                        <div class="cart-item-price">Rs. <?php echo number_format($item['price']); ?></div>
                    </div>
                    
                    <div class="cart-item-qty">
                        <input type="number" class="qty-input" data-cart-id="<?php echo $item['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                        <button type="button" class="btn-update update-btn" data-cart-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                            <i class="bi bi-arrow-clockwise me-1"></i> Update
                        </button>
                    </div>
                    
                    <div class="cart-item-total" id="item-total-<?php echo $item['id']; ?>">
                        Rs. <?php echo number_format($item['price'] * $item['quantity']); ?>
                    </div>
                    
                    <a href="?remove=<?php echo $item['id']; ?>" class="btn-remove" onclick="return confirm('Remove this item?')">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="cart-summary" id="cartSummary">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay">Rs. <?php echo number_format($cart_total); ?></span>
                </div>
                <div class="total-row">
                    <span>Delivery</span>
                    <span id="deliveryDisplay"><?php echo $cart_total >= 3000 ? 'Free' : 'Rs. 300'; ?></span>
                </div>
                <div class="grand-total">
                    <span>Total</span>
                    <span id="totalDisplay">Rs. <?php echo number_format($cart_total >= 3000 ? $cart_total : $cart_total + 300); ?></span>
                </div>
                
                <!-- ORDER FORM -->
                <form method="POST" id="orderForm" class="mt-3" style="display:block;">
                    <input type="hidden" name="place_order" value="1">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
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
                        <div class="col-md-12">
                            <label class="order-form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="order-form-input" placeholder="03XX-XXXXXXX" required>
                        </div>
                        <div class="col-12">
                            <label class="order-form-label">Order Notes (Optional)</label>
                            <textarea name="notes" class="order-form-input" rows="2" placeholder="Any special instructions..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-gold w-100 py-2" id="placeOrderBtn">
                                <i class="bi bi-credit-card me-2"></i> Place Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
        <?php elseif ($cart_count == 0 && !$order_placed): ?>
            <!-- EMPTY CART (Normal empty state) -->
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted-gold">Start shopping to add items to your cart.</p>
                <a href="index.php" class="btn-gold" style="display:inline-block;margin-top:1rem;">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    // =========================================================
    // UPDATE CART QUANTITY
    // =========================================================
    document.querySelectorAll('.update-btn').forEach(function(btn){
        btn.addEventListener('click',function(e){
            e.preventDefault();
            var cartId=this.dataset.cartId;
            var qtyInput=document.querySelector('.qty-input[data-cart-id="'+cartId+'"]');
            var quantity=parseInt(qtyInput.value);
            if(quantity<1){qtyInput.value=1;showNotification('Quantity must be at least 1');return;}
            var originalHtml=this.innerHTML;
            this.disabled=true;
            this.innerHTML='<span class="loading-spinner"></span>';
            var formData=new FormData();
            formData.append('ajax_update','1');
            formData.append('cart_id',cartId);
            formData.append('quantity',quantity);
            var xhr=new XMLHttpRequest();
            xhr.open('POST','cart.php',true);
            xhr.onload=function(){
                btn.disabled=false;
                btn.innerHTML=originalHtml;
                if(xhr.status===200){
                    try{
                        var data=JSON.parse(xhr.responseText);
                        if(data.success){
                            var itemTotalElement=document.getElementById('item-total-'+cartId);
                            if(itemTotalElement){itemTotalElement.textContent='Rs. '+data.item_total.toLocaleString();}
                            qtyInput.value=data.quantity;
                            var subtotalElement=document.getElementById('subtotalDisplay');
                            if(subtotalElement){subtotalElement.textContent='Rs. '+data.cart_total.toLocaleString();}
                            var deliveryElement=document.getElementById('deliveryDisplay');
                            if(deliveryElement){deliveryElement.textContent=data.cart_total>=3000?'Free':'Rs. 300';}
                            var totalElement=document.getElementById('totalDisplay');
                            if(totalElement){var finalTotal=data.cart_total>=3000?data.cart_total:data.cart_total+300;totalElement.textContent='Rs. '+finalTotal.toLocaleString();}
                            var cartCountElement=document.getElementById('cartCountDisplay');
                            if(cartCountElement){var count=data.cart_count;cartCountElement.textContent=count+' item'+(count!=1?'s':'')+' in your cart';}
                            showNotification('✅ '+data.message);
                        }else{showNotification('❌ '+data.message);}
                    }catch(e){showNotification('❌ Error: Invalid response from server');}
                }else{showNotification('❌ Server error (Status: '+xhr.status+')');}
            };
            xhr.onerror=function(){btn.disabled=false;btn.innerHTML=originalHtml;showNotification('❌ Connection error. Please try again.');};
            xhr.send(formData);
        });
    });

    // =========================================================
    // PLACE ORDER - Show loading and let form submit
    // =========================================================
    var orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            var btn = document.getElementById('placeOrderBtn');
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Placing Order...';
            btn.disabled = true;
            // Form will submit naturally
        });
    }
    
    // =========================================================
    // AUTO HIDE SUCCESS ALERT AFTER 5 SECONDS
    // =========================================================
    var alertElement = document.querySelector('.alert-success');
    if (alertElement) {
        setTimeout(function() {
            alertElement.style.transition = 'opacity 0.5s ease';
            alertElement.style.opacity = '0';
            setTimeout(function() {
                alertElement.style.display = 'none';
            }, 500);
        }, 5000);
    }
});

// =========================================================
// NOTIFICATION TOAST
// =========================================================
function showNotification(message){
    var existing=document.querySelector('.toast-notification');
    if(existing)existing.remove();
    var toast=document.createElement('div');
    toast.className='toast-notification';
    toast.innerHTML=message;
    document.body.appendChild(toast);
    setTimeout(function(){toast.classList.add('show');},100);
    setTimeout(function(){toast.classList.remove('show');setTimeout(function(){toast.remove();},300);},3000);
}

console.log('🛒 Cart page loaded successfully!');
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>