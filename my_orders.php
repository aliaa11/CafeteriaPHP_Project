<?php
session_start();
include_once './config/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = array_sum($_SESSION['cart']);

$user_id = $_SESSION['user_id'];

$orders_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $orders_per_page;

// Date filtering
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause with prepared statements
$where_clause = "WHERE o.user_id = ?";
$params = [$user_id];
$types = "i";

if ($date_from && $date_to) {
    $where_clause .= " AND o.order_date BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

// Count total orders
$count_query = "SELECT COUNT(*) as total FROM orders o $where_clause";
$stmt = mysqli_prepare($myConnection, $count_query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$count_row = mysqli_fetch_assoc($count_result);
$total_orders = $count_row['total'];
$total_pages = ceil($total_orders / $orders_per_page);

// Get orders with items and calculated totals
$query = "SELECT o.id, o.order_date, o.room_number, o.status,
          SUM(i.price * oi.quantity) as total_price,
          GROUP_CONCAT(i.name SEPARATOR ', ') as items_list
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN items i ON oi.item_id = i.id
          $where_clause
          GROUP BY o.id
          ORDER BY o.order_date DESC
          LIMIT ? OFFSET ?";

$params[] = $orders_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($myConnection, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Feane Cafeteria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
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

        /* Orders Section */
        .orders-section {
            padding: 50px 0;
            background-color: #F5F5DC;
        }
        .heading_container h2 {
            font-family: 'Playfair Display', serif;
            color: #5C4033;
            text-align: center;
        }
        .orders-section .form-label {
            font-family: 'Playfair Display', serif;
            color: #5C4033;
        }
        .orders-section .form-control {
            background-color: #f0f0f0;
            color: #5C4033;
            border: 1px solid #8d5524;
            border-radius: 5px;
            padding: 8px;
        }
        .orders-section .form-control:focus {
            background-color: #f0f0f0;
            color: #5C4033;
            box-shadow: none;
            border-color: #6d3e1a;
        }
        .orders-section .btn-primary {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 25px;
        }
        .orders-section .btn-primary:hover {
            background-color: #6d3e1a;
        }
        .orders-section .btn-danger {
            background-color: #8d5524;
            border: none;
            border-radius: 25px;
            padding: 5px 10px;
        }
        .orders-section .btn-danger:hover {
            background-color: #6d3e1a;
        }
        .orders-section .btn-warning {
            background-color: #d2b48c;
            color: #5C4033;
            border: none;
            border-radius: 25px;
            padding: 5px 10px;
        }
        .orders-section .btn-warning:hover {
            background-color: #b8976b;
        }

        /* Pagination Styles */
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .pagination .page-link {
            background-color: #8d5524;
            color: white;
            border: none;
            border-radius: 25px;
            margin: 0 5px;
            padding: 8px 15px;
        }
        .pagination .page-link:hover {
            background-color: #6d3e1a;
        }
        .pagination .page-item.disabled .page-link {
            background-color: #6d4c41;
            color: #d2b48c;
            opacity: 0.7;
        }
        .pagination .page-item.active .page-link {
            background-color: #d2b48c;
            color: #5C4033;
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

    <!-- Orders Section -->
    <section class="orders-section">
        <div class="container">
            <div class="heading_container">
                <h2>My Orders</h2>
            </div>

            <!-- Date Range Filter Form (keep existing) -->

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="table">
                    <tr>
                        <th>Order ID</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($order = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['items_list']) ?></td>
                            <td><?= number_format($order['total_price'], 2) ?> EGP</td>
                            <td><?= htmlspecialchars($order['room_number']) ?></td>
                            <td>
                                <span class="badge 
                                    <?= $order['status'] == 'pending' ? 'bg-warning text-dark' : 
                                       ($order['status'] == 'confirmed' ? 'bg-primary' : 
                                       ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger')) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td><?= $order['order_date'] ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-info btn-sm">View</a>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <a href="cancel_order.php?order_id=<?= $order['id'] ?>" class="btn btn-danger btn-sm">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>

                <!-- Pagination (keep existing) -->
            <?php else: ?>
                <p>No orders found for the selected date range.</p>
            <?php endif; ?>
        </div>
    </section>


    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>









