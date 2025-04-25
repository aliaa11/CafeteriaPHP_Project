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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #F5F5DC;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Navigation Bar */
        .navbar {
            background-color: transparent;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 3;
        }
        .navbar .navbar-brand {
            color: #d2b48c; /* درجة بني فاتحة عشان كلمة Feane تبقى واضحة */
        }
        .navbar .nav-link {
            color: #8d5524; /* درجة بني غامق وواضح */
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
            color: #6d3e1a;
        }
        .navbar .btn-order-online {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
        }
        .navbar .btn-order-online:hover {
            background-color: #6d3e1a;
        }
        .navbar .welcome-text {
            color: #8d5524; /* نفس لون الروابط */
        }
        .cart-icon {
            position: relative;
            margin-left: 10px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: #d2b48c; /* درجة بني فاتحة عشان الأيقونة تبان */
        }
        .cart-icon .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #8d5524;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }

        /* Latest Order Section */
        .latest-order {
            background-color: #5C4033;
            color: white;
            padding: 15px;
            border-radius: 15px;
            margin: 80px auto 20px auto;
            max-width: 600px;
        }
        .latest-order h5 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            margin-bottom: 10px;
            border-bottom: 1px solid #8d5524;
            padding-bottom: 5px;
        }
        .latest-order p {
            margin: 5px 0;
            color: #d2b48c;
        }

        /* Cart Section */
        .cart-section {
            background-color: #5C4033;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 20px auto 30px auto;
            max-width: 600px;
        }
        .cart-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #8d5524;
            padding-bottom: 10px;
            text-align: center;
        }
        .cart-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .cart-item-details h6 {
            margin: 0;
            font-size: 1rem;
        }
        .cart-item-details .subtotal {
            font-size: 0.9rem;
            color: #d2b48c;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .quantity-controls button {
            background-color: #8d5524;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* شيلنا تأثير الـ Hover */
        .cart-section .btn-danger {
            background-color: #8d5524;
            border: none;
            border-radius: 25px;
            padding: 5px 10px;
        }
        /* شيلنا تأثير الـ Hover */
        .cart-section .total-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #d2b48c;
            margin-top: 15px;
            text-align: center;
        }
        .cart-section .btn-order {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 10px;
            font-weight: bold;
            border-radius: 25px;
            margin-top: 15px;
            width: 100%;
            transition: background-color 0.3s;
        }
        .cart-section .btn-order:hover {
            background-color: #6d3e1a;
        }

        /* Modal Styles */
        .modal-content {
            background-color: #5C4033;
            color: white;
            border-radius: 15px;
        }
        .modal-header {
            border-bottom: 2px solid #8d5524;
        }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
        }
        .modal-body .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .modal-body .cart-item:last-child {
            border-bottom: none;
        }
        .modal-body .total-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #d2b48c;
            margin-top: 15px;
            text-align: center;
        }
        .modal-body .form-select {
            background-color: #6d4c41;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px;
        }
        .modal-body .form-select:focus {
            background-color: #6d4c41;
            color: white;
            box-shadow: none;
            border: 1px solid #8d5524;
        }
        .modal-footer .btn-confirm {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
        }
        .modal-footer .btn-confirm:hover {
            background-color: #6d3e1a;
        }

        /* Footer */
        footer {
            background-color: #5C4033;
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        footer a {
            color: #d2b48c;
            text-decoration: none;
        }
        footer a:hover {
            color: #8d5524;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">Feane</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">MENU</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_orders.php">MY ORDERS</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="welcome-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-order-online">Logout</a>
                    <a href="cart.php" class="cart-icon" onclick="window.location.href='cart.php'; return false;">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
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
            <p><strong>Status:</strong> <?php echo htmlspecialchars($latest_order['status']); ?></p>
            <p><strong>Total:</strong> <?php echo htmlspecialchars($latest_order['total_price']); ?> EGP</p>
        </section>
    <?php endif; ?>

    <!-- Cart Section -->
    <section class="cart-section">
        <h3>Your Cart</h3>
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
                <?php
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/cafateriapro/uploads/' . $item['image_url'];
                if (file_exists($image_path)):
                ?>
                    <img src="/cafateriapro/uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/50" alt="Placeholder">
                <?php endif; ?>
                <div class="cart-item-details">
                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                    <div class="subtotal"><?php echo $subtotal; ?> EGP</div>
                </div>
                <div class="quantity-controls">
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        <input type="hidden" name="action" value="decrease">
                        <button type="submit" name="update_quantity">-</button>
                    </form>
                    <span class="cart-quantity"><?php echo $quantity; ?></span>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                        <input type="hidden" name="action" value="increase">
                        <button type="submit" name="update_quantity">+</button>
                    </form>
                </div>
                <form method="post" style="display: inline; margin-left: 10px;">
                    <button type="submit" name="remove_from_cart" value="<?php echo $item_id; ?>" class="btn btn-sm btn-danger">X</button>
                </form>
            </div>
        <?php
            }
        } else {
            echo "<p>Your cart is empty</p>";
        }
        ?>
        <div class="total-price">
            Total: <span id="total-price"><?php echo number_format($total_price, 2); ?> EGP</span>
        </div>

        <?php if (!empty($_SESSION['cart'])): ?>
            <button type="button" class="btn btn-order" data-bs-toggle="modal" data-bs-target="#confirmOrderModal">Order Now</button>
        <?php endif; ?>
    </section>

    <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmOrderModalLabel">Confirm Your Order</h5>
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
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                <span class="cart-quantity"><?php echo $quantity; ?></span>
                                <span><?php echo $subtotal; ?> EGP</span>
                            </div>
                        <?php
                            }
                        }
                        ?>
                        <div class="total-price">
                            Total: <span><?php echo number_format($modal_total_price, 2); ?> EGP</span>
                        </div>

                        <!-- Room Number Dropdown -->
                        <div class="mt-3">
                            <label for="room_number" class="form-label">Room Number</label>
                            <select name="room" id="room_number" class="form-select" required>
                                <option value="">Select Room</option>
                                <option value="101">101</option>
                                <option value="102">102</option>
                                <option value="103">103</option>
                                <option value="104">104</option>
                                <option value="105">105</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="confirm_order" class="btn btn-confirm">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>








