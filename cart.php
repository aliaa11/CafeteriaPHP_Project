<?php
session_start();
include_once 'db.php';

// إنشاء جلسة للسلة لو مش موجودة
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// التأكد من إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// تعديل الكمية في السلة
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

// إزالة منتج من السلة
if (isset($_POST['remove_from_cart'])) {
    $item_id = $_POST['remove_from_cart'];
    unset($_SESSION['cart'][$item_id]);
    header("Location: cart.php");
    exit();
}

// تسجيل الطلب لما نضغط "Confirm" في الـ Modal
if (isset($_POST['confirm_order'])) {
    $room_number = $_POST['room'];
    $cart_items = $_SESSION['cart'];
    $user_id = $_SESSION['user_id'];

    // إضافة سجل لكل منتج في جدول orders
    foreach ($cart_items as $item_id => $quantity) {
        $query = "INSERT INTO orders (user_id, item_id, quantity, status, room_number) VALUES (?, ?, ?, 'confirmed', ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "iiis", $user_id, $item_id, $quantity, $room_number);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // تفريغ السلة
    $_SESSION['cart'] = [];

    // إعادة توجيه المستخدم لصفحة My Orders
    header("Location: my_orders.php");
    exit();
}

// حساب عدد العناصر في السلة
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
        .navbar .nav-link {
            color: white;
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
            color: #8d5524;
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
        .cart-icon {
            position: relative;
            margin-left: 10px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: white;
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

        /* Cart Section */
        .cart-section {
            background-color: #5C4033;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 100px auto 30px auto;
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
        .quantity-controls button:hover {
            background-color: #6d3e1a;
        }
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
            <a class="navbar-brand text-white" href="#">Feane</a>
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
                        <a class="nav-link" href="#">ABOUT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">BOOK TABLE</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_orders.php">MY ORDERS</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <a href="logout.php" class="btn btn-order-online">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-order-online">Login</a>
                    <?php endif; ?>
                    <a href="cart.php" class="cart-icon" onclick="window.location.href='cart.php'; return false;">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Cart Section -->
    <section class="cart-section">
        <h3>Your Cart</h3>
        <?php
        $total_price = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item_id => $quantity) {
                $item_query = "SELECT * FROM items WHERE id = ?";
                $stmt = mysqli_prepare($connection, $item_query);
                mysqli_stmt_bind_param($stmt, "i", $item_id);
                mysqli_stmt_execute($stmt);
                $item_result = mysqli_stmt_get_result($stmt);
                $item = mysqli_fetch_assoc($item_result);
                mysqli_stmt_close($stmt);

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
                    <div class="subtotal">$<?php echo $subtotal; ?></div>
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
            Total: $<span id="total-price"><?php echo number_format($total_price, 2); ?></span>
        </div>

        <!-- Order Now Button to Open Modal -->
        <?php if (!empty($_SESSION['cart'])): ?>
            <button type="button" class="btn btn-order" data-bs-toggle="modal" data-bs-target="#confirmOrderModal">Order Now</button>
        <?php endif; ?>
    </section>

    <!-- Confirm Order Modal -->
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
                                $item_query = "SELECT * FROM items WHERE id = ?";
                                $stmt = mysqli_prepare($connection, $item_query);
                                mysqli_stmt_bind_param($stmt, "i", $item_id);
                                mysqli_stmt_execute($stmt);
                                $item_result = mysqli_stmt_get_result($stmt);
                                $item = mysqli_fetch_assoc($item_result);
                                mysqli_stmt_close($stmt);

                                $subtotal = $item['price'] * $quantity;
                                $modal_total_price += $subtotal;
                        ?>
                            <div class="cart-item">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                <span class="cart-quantity"><?php echo $quantity; ?></span>
                                <span>$<?php echo $subtotal; ?></span>
                            </div>
                        <?php
                            }
                        }
                        ?>
                        <div class="total-price">
                            Total: $<span><?php echo number_format($modal_total_price, 2); ?></span>
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

    <!-- Footer -->
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


