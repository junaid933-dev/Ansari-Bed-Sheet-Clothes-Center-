<?php
// orders.php - Complete Orders Page (Stay on Same Page)
require_once 'config.php';
requireLogin();
include 'includes/header.php';

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// =========================================================
// HANDLE ORDER ACTIONS - FIXED (Stay on Same Page)
// =========================================================
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $order_id = intval($_GET['cancel']);
    
    $check_stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $order_data = $check_result->fetch_assoc();
        if ($order_data['status'] == 'pending' || $order_data['status'] == 'processing') {
            // Delete order items
            $delete_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
            $delete_items->bind_param("i", $order_id);
            $delete_items->execute();
            $delete_items->close();
            
            // Delete order
            $delete_order = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
            $delete_order->bind_param("ii", $order_id, $user_id);
            
            if ($delete_order->execute()) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Order cancelled and removed successfully.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Failed to cancel order.'];
            }
            $delete_order->close();
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Cannot cancel this order. It has already been processed.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Order not found.'];
    }
    $check_stmt->close();
    
    // =========================================================
    // HAMESHA ORDERS PAGE PAR REDIRECT KARO (Same as Admin)
    // =========================================================
    header('Location: orders.php');
    exit();
}

// =========================================================
// GET FLASH MESSAGE
// =========================================================
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);

// =========================================================
// GET ALL ORDERS
// =========================================================
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
$total_orders = $orders->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Ansari</title>
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
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }
        a { text-decoration: none; color: inherit; }

        .orders-wrapper { padding: 2rem 0 4rem; }
        .orders-card {
            background: var(--bg-panel);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .orders-card .order-item {
            background: var(--bg);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            transition: all .3s ease;
            margin-bottom: 1rem;
        }
        .orders-card .order-item:hover {
            border-color: var(--gold);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--stroke);
            margin-bottom: 0.8rem;
        }
        .order-header .order-id {
            font-weight: 600;
            color: var(--cream);
        }
        .order-header .order-date {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .order-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }
        .order-body .order-total {
            font-weight: 600;
            color: var(--gold);
            font-size: 1.1rem;
        }
        .order-body .order-items {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .order-thumbs {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .order-thumb {
            width: 55px;
            height: 55px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(212, 175, 55, 0.2);
            flex-shrink: 0;
            background: var(--bg-panel-2);
        }
        .order-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .order-thumb .more-badge {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gold);
            color: #1a1305;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .order-thumb .no-img {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 0.65rem;
        }

        .order-status {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .order-status.pending { background: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .order-status.processing { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
        .order-status.shipped { background: rgba(40, 167, 69, 0.15); color: #28a745; }
        .order-status.delivered { background: rgba(40, 167, 69, 0.15); color: #28a745; }
        .order-status.cancelled { background: rgba(220, 53, 69, 0.15); color: #dc3545; }

        .order-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .order-actions .btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
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
        .btn-gold.btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
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
            padding: .3rem 0.8rem;
            border-radius: 6px;
            transition: all .3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.75rem;
            cursor: pointer;
        }
        .btn-outline-danger:hover {
            background: #dc3545;
            color: #fff;
        }

        .no-orders {
            text-align: center;
            padding: 3rem 0;
        }
        .no-orders i {
            font-size: 4rem;
            color: var(--muted);
            margin-bottom: 1rem;
        }
        .no-orders h4 { color: var(--cream); }
        .no-orders p { color: var(--muted); }

        .alert {
            border-radius: 8px;
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

        .order-detail-modal .modal-content {
            background: var(--bg-panel);
            border: 1px solid var(--stroke);
            border-radius: 20px;
            color: var(--cream);
        }
        .order-detail-modal .modal-header {
            border-bottom: 1px solid var(--stroke);
            padding: 1.2rem 1.5rem;
        }
        .order-detail-modal .modal-header .btn-close {
            filter: invert(1) brightness(2);
        }
        .order-detail-modal .modal-body { padding: 1.5rem; }
        .order-detail-modal .modal-footer {
            border-top: 1px solid var(--stroke);
            padding: 1.2rem 1.5rem;
        }

        .od-item-card {
            background: var(--bg-panel-2);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all .3s ease;
        }
        .od-item-card:hover {
            border-color: var(--gold);
        }
        .od-item-card:last-child { margin-bottom: 0; }
        .od-item-card .item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(212, 175, 55, 0.1);
            flex-shrink: 0;
            display: block;
        }
        .od-item-card .item-info { flex: 1; }
        .od-item-card .item-name {
            color: var(--cream);
            font-weight: 600;
            font-size: 0.95rem;
        }
        .od-item-card .item-meta {
            color: var(--muted);
            font-size: 0.75rem;
        }
        .od-item-card .item-qty {
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-align: center;
            min-width: 50px;
        }
        .od-item-card .item-price {
            color: var(--gold);
            font-weight: 600;
            font-size: 0.95rem;
            text-align: right;
            white-space: nowrap;
        }

        .order-info-box {
            background: var(--bg-panel-2);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .order-info-box p {
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }
        .order-info-box .label {
            color: var(--muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .order-info-box .value {
            color: var(--cream);
        }

        .loading-spinner {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 2px solid var(--gold);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 767px) {
            .orders-card { padding: 1.5rem; margin: 0 1rem; }
            .order-header { flex-direction: column; align-items: flex-start; }
            .order-body { flex-direction: column; align-items: flex-start; }
            .order-actions { margin-top: 0.5rem; width: 100%; }
            .order-actions .btn-sm { flex: 1; text-align: center; }
            .order-thumb { width: 40px; height: 40px; }
            .od-item-card { flex-wrap: wrap; }
            .od-item-card .item-price { text-align: left; width: 100%; }
        }
    </style>
</head>
<body>

<div class="container orders-wrapper">
    <div class="orders-card">
        
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h2 style="color:var(--cream);font-family:'Playfair Display',serif;margin:0;">
                <i class="bi bi-box" style="color:var(--gold);"></i> My Orders
            </h2>
            <a href="dashboard.php" class="btn-outline-gold" style="padding:0.4rem 1.2rem;font-size:0.85rem;">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
        
        <!-- =========================================================
             FLASH MESSAGE - YAHI SHOW HOGA DELETE KE BAAD
             ========================================================= -->
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $flash['type'] == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i> 
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($total_orders > 0): ?>
            <p style="color:var(--muted);margin-bottom:1.5rem;">
                Showing <?php echo $total_orders; ?> order<?php echo $total_orders > 1 ? 's' : ''; ?>
            </p>
            
            <?php while ($order = $orders->fetch_assoc()): ?>
            <?php 
            $order_items = getOrderItems($order['id']);
            $items_count = $order_items->num_rows;
            $items_display = [];
            $order_item_details = [];
            $product_images = [];
            
            while ($item = $order_items->fetch_assoc()) {
                $items_display[] = $item['product_name'] . ' × ' . $item['quantity'];
                $order_item_details[] = $item;
                
                $image_url = null;
                if ($item['product_id'] > 0) {
                    $img_stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
                    $img_stmt->bind_param("i", $item['product_id']);
                    $img_stmt->execute();
                    $img_result = $img_stmt->get_result();
                    if ($img_data = $img_result->fetch_assoc()) {
                        $image_url = $img_data['image_url'];
                    }
                    $img_stmt->close();
                }
                
                if (empty($image_url)) {
                    $img_stmt = $conn->prepare("SELECT image_url FROM products WHERE name LIKE ? LIMIT 1");
                    $search_name = "%" . $item['product_name'] . "%";
                    $img_stmt->bind_param("s", $search_name);
                    $img_stmt->execute();
                    $img_result = $img_stmt->get_result();
                    if ($img_data = $img_result->fetch_assoc()) {
                        $image_url = $img_data['image_url'];
                    }
                    $img_stmt->close();
                }
                
                if (empty($image_url)) {
                    $image_url = 'assets/images/default-product.jpg';
                }
                
                $product_images[] = $image_url;
            }
            
            $items_preview = implode(', ', array_slice($items_display, 0, 3));
            if ($items_count > 3) {
                $items_preview .= ' & ' . ($items_count - 3) . ' more';
            }
            
            $image_html = '';
            $display_images = array_slice($product_images, 0, 3);
            $remaining = count($product_images) - 3;
            $has_images = false;
            
            foreach ($display_images as $img) {
                if (!empty($img)) {
                    $has_images = true;
                    $img_url = $img;
                    if (!str_starts_with($img, 'http') && !str_starts_with($img, '/') && !str_starts_with($img, '..')) {
                        $img_url = $img;
                    }
                    $image_html .= '
                        <div class="order-thumb">
                            <img src="' . $img_url . '" alt="Product" loading="lazy" onerror="this.src=\'https://via.placeholder.com/55?text=No+Image\'">
                        </div>
                    ';
                } else {
                    $image_html .= '
                        <div class="order-thumb">
                            <img src="https://via.placeholder.com/55?text=No+Image" alt="No Image">
                        </div>
                    ';
                }
            }
            
            if ($remaining > 0) {
                $image_html .= '
                    <div class="order-thumb">
                        <div class="more-badge">+' . $remaining . '</div>
                    </div>
                ';
            }
            
            if (empty($product_images) || !$has_images) {
                $image_html = '
                    <div class="order-thumb">
                        <div class="no-img"><i class="bi bi-box"></i></div>
                    </div>
                ';
            }
            ?>
            <div class="order-item">
                <div class="order-header">
                    <span class="order-id">
                        <i class="bi bi-hash" style="color:var(--gold);"></i> Order #<?php echo $order['order_number'] ?? $order['id']; ?>
                    </span>
                    <span class="order-date">
                        <i class="bi bi-calendar3 me-1"></i> <?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?>
                    </span>
                </div>
                <div class="order-body">
                    <div>
                        <div class="order-total">Rs. <?php echo number_format($order['total_amount']); ?></div>
                        <div class="order-items"><?php echo $items_preview; ?></div>
                    </div>
                    
                    <div class="order-thumbs">
                        <?php echo $image_html; ?>
                    </div>
                    
                    <div>
                        <span class="order-status <?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <div class="order-actions">
                        <button class="btn-gold btn-sm view-detail-btn" 
                                data-order-id="<?php echo $order['id']; ?>"
                                data-order-number="<?php echo $order['order_number'] ?? $order['id']; ?>"
                                data-order-total="<?php echo $order['total_amount']; ?>"
                                data-order-status="<?php echo $order['status']; ?>"
                                data-order-date="<?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?>"
                                data-order-address="<?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?>"
                                data-order-city="<?php echo htmlspecialchars($order['shipping_city'] ?? 'N/A'); ?>"
                                data-order-province="<?php echo htmlspecialchars($order['shipping_province'] ?? 'N/A'); ?>"
                                data-order-phone="<?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?>"
                                data-order-items='<?php echo json_encode($order_item_details); ?>'
                                data-product-images='<?php echo json_encode($product_images); ?>'>
                            <i class="bi bi-eye me-1"></i> View Details
                        </button>
                        <?php if ($order['status'] == 'pending' || $order['status'] == 'processing'): ?>
                        <a href="?cancel=<?php echo $order['id']; ?>" 
                           class="btn-outline-danger btn-sm" 
                           onclick="return confirm('Are you sure you want to cancel this order?')">
                            <i class="bi bi-x-circle me-1"></i> Cancel & Remove
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="no-orders">
                <i class="bi bi-box-seam"></i>
                <h4>No orders yet</h4>
                <p>You haven't placed any orders yet. Start shopping now!</p>
                <a href="index.php" class="btn-gold" style="display:inline-block;margin-top:1rem;">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- ORDER DETAILS MODAL -->
<div class="modal fade order-detail-modal" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="odModalTitle" style="font-family:'Playfair Display',serif;color:var(--gold);">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) brightness(2);"></button>
            </div>
            <div class="modal-body" id="odModalBody">
                <div class="text-center py-4">
                    <div class="loading-spinner"></div>
                    <p class="text-muted-gold mt-2">Loading order details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// =========================================================
// ORDER DETAIL MODAL
// =========================================================
const orderDetailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));

document.querySelectorAll('.view-detail-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const orderId = this.dataset.orderId;
        const orderNumber = this.dataset.orderNumber;
        const orderTotal = this.dataset.orderTotal;
        const orderStatus = this.dataset.orderStatus;
        const orderDate = this.dataset.orderDate;
        const orderAddress = this.dataset.orderAddress;
        const orderCity = this.dataset.orderCity;
        const orderProvince = this.dataset.orderProvince;
        const orderPhone = this.dataset.orderPhone;
        const orderItems = JSON.parse(this.dataset.orderItems);
        const productImages = JSON.parse(this.dataset.productImages || '[]');
        
        loadOrderDetails(orderId, orderNumber, orderTotal, orderStatus, orderDate, 
                        orderAddress, orderCity, orderProvince, orderPhone, orderItems, productImages);
    });
});

function loadOrderDetails(orderId, orderNumber, orderTotal, orderStatus, orderDate, 
                          orderAddress, orderCity, orderProvince, orderPhone, orderItems, productImages) {
    const modalBody = document.getElementById('odModalBody');
    const modalTitle = document.getElementById('odModalTitle');
    
    modalTitle.textContent = 'Order #' + orderNumber;
    const statusClass = orderStatus.toLowerCase();
    
    let itemsHtml = '';
    orderItems.forEach(function(item, index) {
        let imgUrl = 'https://via.placeholder.com/60?text=No+Image';
        if (productImages[index]) {
            imgUrl = productImages[index];
            if (!imgUrl.startsWith('http') && !imgUrl.startsWith('/') && !imgUrl.startsWith('..')) {
                imgUrl = imgUrl;
            }
        }
        itemsHtml += `
            <div class="od-item-card">
                <img src="${imgUrl}" alt="${item.product_name}" class="item-img" loading="lazy" onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                <div class="item-info">
                    <div class="item-name">${item.product_name}</div>
                    <div class="item-meta">
                        ${item.size ? 'Size: ' + item.size : ''}
                        ${item.size && item.color ? ' | ' : ''}
                        ${item.color ? 'Color: ' + item.color : ''}
                    </div>
                </div>
                <div class="item-qty">× ${item.quantity}</div>
                <div class="item-price">Rs. ${parseInt(item.price).toLocaleString()}</div>
            </div>
        `;
    });
    
    const fullAddress = orderAddress + (orderCity ? ', ' + orderCity : '') + (orderProvince ? ', ' + orderProvince : '');
    
    modalBody.innerHTML = `
        <div class="row g-4">
            <div class="col-md-6">
                <h6 style="color:var(--gold);font-weight:600;margin-bottom:1rem;">
                    <i class="bi bi-info-circle me-2"></i>Order Information
                </h6>
                <div class="order-info-box">
                    <p><span class="label">Order #</span><br><span class="value">${orderNumber}</span></p>
                    <p><span class="label">Date</span><br><span class="value">${orderDate}</span></p>
                    <p><span class="label">Total</span><br><span class="value" style="color:var(--gold);font-weight:700;font-size:1.1rem;">Rs. ${parseInt(orderTotal).toLocaleString()}</span></p>
                    <p><span class="label">Status</span><br>
                        <span class="order-status ${statusClass}" style="display:inline-block;margin-top:0.2rem;">${orderStatus.charAt(0).toUpperCase() + orderStatus.slice(1)}</span>
                    </p>
                </div>
            </div>
            
            <div class="col-md-6">
                <h6 style="color:var(--gold);font-weight:600;margin-bottom:1rem;">
                    <i class="bi bi-truck me-2"></i>Shipping Details
                </h6>
                <div class="order-info-box">
                    <p><span class="label">Address</span><br><span class="value">${orderAddress}</span></p>
                    ${orderCity ? `<p><span class="label">City</span><br><span class="value">${orderCity}</span></p>` : ''}
                    ${orderProvince ? `<p><span class="label">Province</span><br><span class="value">${orderProvince}</span></p>` : ''}
                    <p><span class="label">Phone</span><br><span class="value">${orderPhone}</span></p>
                </div>
            </div>
            
            <div class="col-12">
                <h6 style="color:var(--gold);font-weight:600;margin-bottom:1rem;">
                    <i class="bi bi-box-seam me-2"></i>Order Items (${orderItems.length})
                </h6>
                ${itemsHtml}
            </div>
        </div>
    `;
    
    orderDetailModal.show();
}

console.log('📦 Orders page loaded successfully!');
</script>

</body>
</html>

<?php include 'includes/footer.php'; ?>