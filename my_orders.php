<?php
session_start();
include_once 'db.php';

// التأكد من إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// حساب عدد العناصر في السلة
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// جلب الطلبات الخاصة بالمستخدم الحالي
$user_id = $_SESSION['user_id'];
$query = "SELECT orders.*, items.name AS item_name, items.price AS item_price 
          FROM orders 
          JOIN items ON orders.item_id = items.id 
          WHERE orders.user_id = ? 
          ORDER BY orders.order_date DESC";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// تجميع الطلبات بنفس الـ order_date و room_number
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $order_key = $row['order_date'] . '|' . $row['room_number'];
    if (!isset($orders[$order_key])) {
        $orders[$order_key] = [
            'order_date' => $row['order_date'],
            'room_number' => $row['room_number'],
            'status' => $row['status'],
            'items' => [],
            'total_price' => 0
        ];
    }
    $orders[$order_key]['items'][] = [
        'name' => $row['item_name'],
        'quantity' => $row['quantity'],
        'price' => $row['item_price'],
        'subtotal' => $row['item_price'] * $row['quantity']
    ];
    $orders[$order_key]['total_price'] += $row['item_price'] * $row['quantity'];
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feane Cafeteria - My Orders</title>
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

        /* Orders Section */
        .orders-section {
            background-color: #5C4033;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 100px auto 30px auto;
            max-width: 800px;
        }
        .orders-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #8d5524;
            padding-bottom: 10px;
            text-align: center;
        }
        .order {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .order:last-child {
            border-bottom: none;
        }
        .order-details {
            margin-bottom: 10px;
        }
        .order-details p {
            margin: 5px 0;
        }
        .order-items {
            margin-left: 20px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .order-item span {
            color: #d2b48c;
        }
        .status-confirmed {
            color: #d2b48c;
            font-weight: bold;
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
                        <a class="nav-link active" href="my_orders.php">MY ORDERS</a>
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

    <!-- Orders Section -->
    <section class="orders-section">
        <h3>My Orders</h3>
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order">
                    <div class="order-details">
                        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                        <p><strong>Room Number:</strong> <?php echo htmlspecialchars($order['room_number']); ?></p>
                        <p><strong>Status:</strong> <span class="status-confirmed"><?php echo htmlspecialchars($order['status']); ?></span></p>
                    </div>
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                <span>$<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-details">
                        <p><strong>Total:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You have no orders yet.</p>
        <?php endif; ?>
    </section>

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



