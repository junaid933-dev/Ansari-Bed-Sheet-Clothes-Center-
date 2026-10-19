<?php
// =========================================================
// ANSARI | COMPLETE CONFIGURATION FILE
// =========================================================

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ansari_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'Ansari Clothes & Bed Sheet');
define('SITE_URL', 'http://localhost/ansari/');
define('ADMIN_EMAIL', 'info@ansari.pk');
define('WHATSAPP_NUMBER', '923271223933');

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =========================================================
// AJAX HANDLERS (Must be at top before any output)
// =========================================================
if (isset($_POST['ajax']) && $_POST['ajax'] == '1' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] == 'add_to_cart' && isLoggedIn()) {
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        $size = $_POST['size'] ?? null;
        $color = $_POST['color'] ?? null;
        
        if ($product_id > 0) {
            if (addToCart($_SESSION['user_id'], $product_id, $quantity, $size, $color)) {
                removeFromWishlist($_SESSION['user_id'], $product_id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Item added to cart and removed from wishlist!',
                    'cart_count' => getCartCount(),
                    'wishlist_count' => getWishlistCount()
                ]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
                exit();
            }
        }
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit();
    }
}

// =========================================================
// DATABASE CONNECTION
// =========================================================
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// =========================================================
// USER AUTHENTICATION
// =========================================================
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login-signup.php');
        exit();
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

// =========================================================
// USER BLOCK FUNCTIONS
// =========================================================
function isUserBlocked($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT is_blocked FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return ($data && $data['is_blocked'] == 1);
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// VALIDATION
// =========================================================
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// =========================================================
// FLASH MESSAGES
// =========================================================
function setMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getMessage() {
    if (isset($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $msg;
    }
    return null;
}

function displayMessage() {
    $msg = getMessage();
    if ($msg) {
        $class = $msg['type'] == 'success' ? 'alert-success' : 
                ($msg['type'] == 'error' ? 'alert-danger' : 'alert-warning');
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($msg['message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}

// =========================================================
// CART FUNCTIONS
// =========================================================
function getCartCount() {
    if (!isLoggedIn()) return 0;
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) as total FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return (int)($data['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getCartItems($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT c.*, p.name, p.price, p.image_url, p.sub_category, p.description, p.category
            FROM cart c 
            LEFT JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getCartTotal($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(c.quantity * p.price), 0) as total 
            FROM cart c 
            LEFT JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return (float)($data['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function addToCart($user_id, $product_id, $quantity = 1, $size = null, $color = null) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $new_qty = $row['quantity'] + $quantity;
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_qty, $row['id']);
            return $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("iiiss", $user_id, $product_id, $quantity, $size, $color);
            return $insert->execute();
        }
    } catch (Exception $e) {
        return false;
    }
}

function updateCartQuantity($cart_id, $quantity, $user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function removeFromCart($cart_id, $user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function clearCart($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// WISHLIST FUNCTIONS
// =========================================================
function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return (int)($data['total'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getWishlistItems($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT w.*, p.name, p.price, p.image_url, p.sub_category, p.description, p.category
            FROM wishlist w 
            LEFT JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function addToWishlist($user_id, $product_id) {
    try {
        $conn = getDBConnection();
        $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) return false;
        
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function removeFromWishlist($user_id, $product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function isInWishlist($user_id, $product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// ORDER FUNCTIONS
// =========================================================
function generateOrderNumber() {
    return 'ANS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// ONLY ONE createOrder FUNCTION - REMOVED THE DUPLICATE
function createOrder($user_id, $address, $city, $province, $phone, $notes = '', $product_id = null, $quantity = 1, $product_price = null) {
    try {
        $conn = getDBConnection();
        
        if ($product_id && $product_price) {
            $product = getProductById($product_id);
            if (!$product) return false;
            
            $total = $product_price * $quantity;
            $order_number = generateOrderNumber();
            
            $stmt = $conn->prepare("
                INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_province, shipping_phone, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->bind_param("sidsisss", $order_number, $user_id, $total, $address, $city, $province, $phone, $notes);
            if (!$stmt->execute()) return false;
            
            $order_id = $conn->insert_id;
            
            $insert = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert->bind_param("iisid", $order_id, $product_id, $product['name'], $quantity, $product_price);
            $insert->execute();
            $insert->close();
            
            return $order_id;
        }
        
        // Cart order
        $cart_result = getCartItems($user_id);
        if ($cart_result->num_rows == 0) return false;
        
        $total = 0;
        $items = [];
        while ($item = $cart_result->fetch_assoc()) {
            $item_total = $item['price'] * $item['quantity'];
            $total += $item_total;
            $items[] = $item;
        }
        if ($total <= 0) return false;
        
        $order_number = generateOrderNumber();
        $stmt = $conn->prepare("
            INSERT INTO orders (order_number, user_id, total_amount, shipping_address, shipping_city, shipping_province, shipping_phone, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("sidsisss", $order_number, $user_id, $total, $address, $city, $province, $phone, $notes);
        if (!$stmt->execute()) return false;
        
        $order_id = $conn->insert_id;
        foreach ($items as $item) {
            $insert = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size, color) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insert->bind_param("iisidss", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['size'], $item['color']);
            $insert->execute();
            $insert->close();
        }
        
        clearCart($user_id);
        return $order_id;
    } catch (Exception $e) {
        error_log("Order creation error: " . $e->getMessage());
        return false;
    }
}

function getUserOrders($user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getOrderById($order_id, $user_id = null) {
    try {
        $conn = getDBConnection();
        if ($user_id) {
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $order_id, $user_id);
        } else {
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

function getOrderItems($order_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function cancelOrder($order_id, $user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending', 'processing')");
        $stmt->bind_param("ii", $order_id, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// PRODUCT FUNCTIONS
// =========================================================
function getProductById($id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

function getAllProducts($limit = null, $offset = 0) {
    try {
        $conn = getDBConnection();
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
        }
        return $conn->query($sql);
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getProductsByCategory($category, $limit = null) {
    try {
        $conn = getDBConnection();
        $sql = "SELECT * FROM products WHERE category = ? ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getFeaturedProducts($limit = 4) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getNewProducts($limit = 4) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM products WHERE is_new = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getSaleProducts($limit = 4) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM products WHERE is_sale = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    } catch (Exception $e) {
        $empty = new stdClass();
        $empty->num_rows = 0;
        return $empty;
    }
}

function getProductColors($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT colors FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        if ($data && $data['colors']) {
            return json_decode($data['colors'], true);
        }
        return ['#d4af37', '#e74c3c', '#2c3e50'];
    } catch (Exception $e) {
        return ['#d4af37', '#e74c3c', '#2c3e50'];
    }
}

// =========================================================
// PROFILE FUNCTIONS
// =========================================================
function updateUserProfile($user_id, $full_name, $phone, $city, $province, $gender) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, city = ?, province = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $full_name, $phone, $city, $province, $gender, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function changeUserPassword($user_id, $new_password) {
    try {
        $conn = getDBConnection();
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function verifyUserPassword($user_id, $password) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        if ($data) {
            return password_verify($password, $data['password']);
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function updateProfileImage($user_id, $image_name) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $image_name, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// ADMIN FUNCTIONS
// =========================================================
function isAdmin() {
    if (!isLoggedIn()) return false;
    $user = getCurrentUser();
    if (!$user) return false;
    return isset($user['is_admin']) && $user['is_admin'] == 1;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin/login.php');
        exit();
    }
}

function getAdminStats() {
    try {
        $conn = getDBConnection();
        $stats = [];
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $stats['products'] = $result->fetch_assoc()['count'] ?? 0;
        $result = $conn->query("SELECT COUNT(*) as count FROM orders");
        $stats['orders'] = $result->fetch_assoc()['count'] ?? 0;
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['users'] = $result->fetch_assoc()['count'] ?? 0;
        $result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
        $stats['messages'] = $result->fetch_assoc()['count'] ?? 0;
        $result = $conn->query("SELECT COUNT(*) as count FROM subscribers");
        $stats['subscribers'] = $result->fetch_assoc()['count'] ?? 0;
        $result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'");
        $stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;
        $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $result->fetch_assoc()['count'] ?? 0;
        return $stats;
    } catch (Exception $e) {
        return ['products' => 0, 'orders' => 0, 'users' => 0, 'messages' => 0, 'subscribers' => 0, 'revenue' => 0, 'pending_orders' => 0];
    }
}

// =========================================================
// SUBSCRIBER FUNCTIONS
// =========================================================
function addSubscriber($email) {
    try {
        $conn = getDBConnection();
        $check = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) return false;
        
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// CONTACT FUNCTIONS
// =========================================================
function saveContactMessage($name, $email, $phone, $subject, $message) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// =========================================================
// AJAX HANDLER
// =========================================================
function handleAjaxRequest() {
    if (!isset($_GET['ajax']) && !isset($_POST['ajax'])) return;
    
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch($action) {
        case 'get_product_detail':
            $product_id = (int)($_GET['product_id'] ?? 0);
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid product'];
                break;
            }
            
            $product = getProductById($product_id);
            if (!$product) {
                $response = ['success' => false, 'message' => 'Product not found'];
                break;
            }
            
            $colors = json_decode($product['colors'] ?? '["#d4af37","#e74c3c","#2c3e50"]', true);
            $response = [
                'success' => true,
                'product' => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'description' => $product['description'] ?? 'Premium quality product from Ansari.',
                    'price' => $product['price'],
                    'old_price' => $product['old_price'],
                    'image_url' => $product['image_url'] ?? 'assets/images/default-product.jpg',
                    'category' => $product['category'],
                    'sub_category' => $product['sub_category'],
                    'stock' => $product['stock'] ?? 0,
                    'colors' => $colors,
                    'rating' => (float)($product['rating'] ?? 0),
                    'is_new' => (bool)$product['is_new'],
                    'is_sale' => (bool)$product['is_sale'],
                    'is_featured' => (bool)$product['is_featured']
                ]
            ];
            break;
            
        case 'add_to_cart':
            if (!isLoggedIn()) {
                $response = ['success' => false, 'message' => 'Please login first'];
                break;
            }
            
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $size = $_POST['size'] ?? null;
            $color = $_POST['color'] ?? null;
            
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid product'];
                break;
            }
            
            if (addToCart($_SESSION['user_id'], $product_id, $quantity, $size, $color)) {
                $response = [
                    'success' => true,
                    'message' => 'Product added to cart',
                    'cart_count' => getCartCount()
                ];
            } else {
                $response = ['success' => false, 'message' => 'Failed to add to cart'];
            }
            break;
            
        case 'order_now':
            if (!isLoggedIn()) {
                $response = ['success' => false, 'message' => 'Please login first'];
                break;
            }
            
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $address = sanitize($_POST['address'] ?? '');
            $city = sanitize($_POST['city'] ?? '');
            $province = sanitize($_POST['province'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $notes = sanitize($_POST['notes'] ?? '');
            
            if ($product_id <= 0 || empty($address) || empty($city) || empty($province) || empty($phone)) {
                $response = ['success' => false, 'message' => 'Please fill in all required fields'];
                break;
            }
            
            $product = getProductById($product_id);
            if (!$product) {
                $response = ['success' => false, 'message' => 'Product not found'];
                break;
            }
            
            $order_id = createOrder($_SESSION['user_id'], $address, $city, $province, $phone, $notes, $product_id, $quantity, $product['price']);
            if ($order_id) {
                $response = [
                    'success' => true,
                    'message' => 'Order placed successfully!',
                    'order_id' => $order_id
                ];
            } else {
                $response = ['success' => false, 'message' => 'Failed to place order'];
            }
            break;
            
        case 'toggle_wishlist':
            if (!isLoggedIn()) {
                $response = ['success' => false, 'message' => 'Please login first'];
                break;
            }
            
            $product_id = (int)($_POST['product_id'] ?? 0);
            $action_type = $_POST['type'] ?? 'add';
            
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid product'];
                break;
            }
            
            if ($action_type == 'add') {
                $result = addToWishlist($_SESSION['user_id'], $product_id);
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Added to wishlist' : 'Already in wishlist',
                    'wishlist_count' => getWishlistCount(),
                    'action' => 'add'
                ];
            } else {
                $result = removeFromWishlist($_SESSION['user_id'], $product_id);
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Removed from wishlist' : 'Not in wishlist',
                    'wishlist_count' => getWishlistCount(),
                    'action' => 'remove'
                ];
            }
            break;
            
        case 'get_wishlist_status':
            if (!isLoggedIn()) {
                $response = ['success' => false, 'message' => 'Not logged in'];
                break;
            }
            
            $product_id = (int)($_GET['product_id'] ?? 0);
            if ($product_id > 0) {
                $response = [
                    'success' => true,
                    'in_wishlist' => isInWishlist($_SESSION['user_id'], $product_id)
                ];
            }
            break;
            
        case 'subscribe':
            $email = $_POST['email'] ?? '';
            if (!validateEmail($email)) {
                $response = ['success' => false, 'message' => 'Please enter a valid email'];
                break;
            }
            
            if (addSubscriber($email)) {
                $response = ['success' => true, 'message' => 'Subscribed successfully!'];
            } else {
                $response = ['success' => false, 'message' => 'Email already subscribed or invalid'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
    }
    
    echo json_encode($response);
    exit();
}

// Call AJAX handler if needed
handleAjaxRequest();
?>