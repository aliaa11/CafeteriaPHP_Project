<?php
session_start();
include_once './config/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$latest_order_query = "SELECT orders.*, SUM(items.price * orders.quantity) as total_price 
                       FROM orders 
                       JOIN items ON orders.item_id = items.id 
                       WHERE orders.user_id = $user_id 
                       GROUP BY orders.id 
                       ORDER BY orders.order_date DESC 
                       LIMIT 1";
$latest_order_result = mysqli_query($myConnection, $latest_order_query);
$latest_order = mysqli_fetch_assoc($latest_order_result);

if (isset($_POST['update_quantity'])) {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];

    if ($action === 'increase') {
        $_SESSION['cart'][$item_id]++;
    } elseif ($action === 'decrease' && $_SESSION['cart'][$item_id] > 1) {
        $_SESSION['cart'][$item_id]--;
    } elseif ($action === 'decrease' && $_SESSION['cart'][$item_id] <= 1) {
        unset($_SESSION['cart'][$item_id]);
    }
    header("Location: cart.php");
    exit();
}

if (isset($_POST['remove_from_cart'])) {
    $item_id = $_POST['remove_from_cart'];
    unset($_SESSION['cart'][$item_id]);
    header("Location: cart.php");
    exit();
}

// Confirm order processing
if (isset($_POST['confirm_order'])) {
    $room_number = $_POST['room'];
    $cart_items = $_SESSION['cart'];
    
    mysqli_begin_transaction($myConnection);
    
        $order_query = "INSERT INTO orders (user_id, status, room_number) 
                       VALUES (?, 'pending', ?)";
        $stmt = mysqli_prepare($myConnection, $order_query);
        mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $room_number);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($myConnection);
        
        // 2. Add all items to order_items
        foreach ($cart_items as $item_id => $quantity) {
            $item_query = "INSERT INTO order_items (order_id, item_id, quantity)
                          VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($myConnection, $item_query);
            mysqli_stmt_bind_param($stmt, "iii", $order_id, $item_id, $quantity);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_affected_rows($myConnection) === 0) {
                throw new Exception("Failed to insert item $item_id");
            }
        }
        
        mysqli_commit($myConnection);
        $_SESSION['cart'] = [];
        header("Location: order_details.php?order_id=$order_id");
        exit();
        
    
}
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feane Cafeteria - Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: rgb(126, 105, 155);
            --accent-color: #f8a5c2;
            --light-bg: rgb(231, 231, 231);
            --card-bg: #ffffff;
            --text-dark: rgb(67, 38, 109);
            --text-light: #f5f6fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background-color: rgb(75, 49, 102);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
        
        .nav-link {
            color: white;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--accent-color);
        }
        
        .btn-order-online {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
        }
        
        .btn-order-online:hover {
            background-color: var(--secondary-color);
        }
        
        .cart-icon {
            position: relative;
            margin-left: 15px;
        }
        
        .cart-icon i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }
        
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
        
        .profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        /* Latest Order Section */
        .latest-order {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            margin: 100px auto 20px;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .latest-order h5 {
            color: var(--primary-color);
            border-bottom: 1px solid var(--secondary-color);
            padding-bottom: 10px;
        }
        
        /* Cart Section */
        .cart-section {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            margin:  auto;
            width: 30%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .cart-section h3 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 15px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        
        .cart-item-details {
            flex-grow: 1;
        }
        
        .cart-item-details h6 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-controls button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-remove {
            background-color: #dc3545 !important;
            margin-left: 10px;
        }
        
        .total-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 20px 0;
            text-align: right;
        }
        
        .btn-order {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            font-weight: bold;
            border-radius: 25px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-order:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-title {
            font-weight: bold;
        }
        
        .modal-body .cart-item {
            padding: 10px 0;
        }
        
        .form-select {
            border: 1px solid var(--secondary-color);
            padding: 10px;
            margin-top: 15px;
        }
        
        .btn-confirm {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
        }
        
        footer {
            background-color: var(--text-dark);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: auto;
        }
        
        footer a {
            color: var(--accent-color);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Cafeteria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php#products-section">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_orders.php">My Orders</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="welcome-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-order-online">Logout</a>
                    <a href="cart.php" class="cart-icon">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?= $cart_count ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Latest Order Section -->
    <?php if ($latest_order): ?>
        <section class="latest-order">
            <h5>Latest Order</h5>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($latest_order['order_date']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge 
                    <?php 
                    switch($latest_order['status']) {
                        case 'pending': echo 'bg-warning'; break;
                        case 'processing': echo 'bg-info'; break;
                        case 'completed': echo 'bg-success'; break;
                        case 'cancelled': echo 'bg-danger'; break;
                        default: echo 'bg-secondary';
                    }
                    ?>">
                    <?php echo htmlspecialchars($latest_order['status']); ?>
                </span>
            </p>
            <p><strong>Total:</strong> <?php echo htmlspecialchars($latest_order['total_price']); ?> $</p>
        </section>
    <?php endif; ?>

    <!-- Cart Section -->
    <section class="cart-section">
        <h3 class="text-center">Your Cart</h3>
        <?php
        $total_price = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item_id => $quantity) {
                $item_query = "SELECT * FROM items WHERE id = $item_id";
                $item_result = mysqli_query($myConnection, $item_query);
                $item = mysqli_fetch_assoc($item_result);

                $subtotal = $item['price'] * $quantity;
                $total_price += $subtotal;
        ?>
            <div class="cart-item">
                <?php if (!empty($item['image_url'])): ?>
                    <img src="/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/<?= htmlspecialchars($item['image_url']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 8px;">
                        <i class="bi bi-cup-hot text-muted"></i>
                    </div>
                <?php endif; ?>
                
                <div class="cart-item-details">
                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                    <div class="text-muted">$ <?php echo $item['price']; ?>  x <?php echo $quantity; ?></div>
                </div>
                
                <div class="quantity-controls">
                    <form method="post" class="d-inline">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        <input type="hidden" name="action" value="decrease">
                        <button type="submit" name="update_quantity" class="btn btn-sm">-</button>
                    </form>
                    
                    <span><?php echo $quantity; ?></span>
                    
                    <form method="post" class="d-inline">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        <input type="hidden" name="action" value="increase">
                        <button type="submit" name="update_quantity" class="btn btn-sm">+</button>
                    </form>
                </div>
                
                <form method="post" class="d-inline">
                    <input type="hidden" name="remove_from_cart" value="<?php echo $item_id; ?>">
                    <button type="submit" class="btn btn-sm btn-remove">X</button>
                </form>
            </div>
        <?php
            }
        } else {
            echo '<div class="text-center py-4">
                    <i class="bi bi-cart-x" style="font-size: 3rem; color: var(--accent-color);"></i>
                    <h5 class="mt-3">Your cart is empty</h5>
                    <a href="home.php#products-section" class="btn btn-order-online mt-2">Browse Menu</a>
                  </div>';
        }
        ?>
        
        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="total-price">
                Total: $ <?php echo number_format($total_price, 2); ?> 
            </div>
            
            <button type="button" class="btn btn-order" data-bs-toggle="modal" data-bs-target="#confirmOrderModal">
                <i class="bi bi-bag-check me-2"></i> Place Order
            </button>
        <?php endif; ?>
    </section>

    <!-- Confirm Order Modal -->
    <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Your Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <?php
                        $modal_total_price = 0;
                        if (!empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item_id => $quantity) {
                                $item_query = "SELECT * FROM items WHERE id = $item_id";
                                $item_result = mysqli_query($myConnection, $item_query);
                                $item = mysqli_fetch_assoc($item_result);

                                $subtotal = $item['price'] * $quantity;
                                $modal_total_price += $subtotal;
                        ?>
                            <div class="cart-item">
                                <span><?php echo htmlspecialchars($item['name']); ?> </span>
                              
                            </div>
                        <?php
                            }
                        }
                        ?>
                        <div class="total-price">
                            Total: $<?php echo number_format($modal_total_price, 2); ?> 
                        </div>

                        <div class="mb-3">
                            <label for="room_number" class="form-label">Room Number</label>
                            <select name="room" id="room_number" class="form-select" required>
                                <option value="">Select Room</option>
                                <option value="101">101</option>
                                <option value="102">102</option>
                                <option value="103">103</option>
                                <option value="104">104</option>
                                <option value="105">105</option>
                                <option value="106">106</option>
                                <option value="107">107</option>
                                <option value="108">108</option>
                                <option value="109">109</option>
                                <option value="110">110</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="confirm_order" class="btn btn-confirm">
                            <i class="bi bi-check-circle me-1"></i> Confirm Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>