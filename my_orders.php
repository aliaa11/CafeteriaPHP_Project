<?php
session_start();
include_once 'db.php';

// التأكد من إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// حساب عدد العناصر في السلة لو كانت موجودة
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = array_sum($_SESSION['cart']);

// عدد الأوردارات في كل صفحة
$orders_per_page = 5;

// جلب الصفحة الحالية من الـ URL
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// حساب الـ Offset
$offset = ($current_page - 1) * $orders_per_page;

// جلب قيم التاريخ من الفورم لو موجودة
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// بناء الـ Query بناءً على فلترة التاريخ
$where_clause = "WHERE orders.user_id = $user_id";
if ($date_from && $date_to) {
    $where_clause .= " AND orders.order_date BETWEEN '$date_from' AND '$date_to'";
}

// جلب عدد الأوردارات الكلي بعد الفلترة
$count_query = "SELECT COUNT(*) as total FROM orders $where_clause";
$count_result = mysqli_query($connection, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_orders = $count_row['total'];

// حساب عدد الصفحات الكلي
$total_pages = ceil($total_orders / $orders_per_page);

// جلب الأوردارات مع الـ LIMIT والـ OFFSET
$query = "SELECT orders.*, items.name AS item_name, items.price AS item_price 
          FROM orders 
          JOIN items ON orders.item_id = items.id 
          $where_clause 
          LIMIT $orders_per_page OFFSET $offset";
$result = mysqli_query($connection, $query);
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

            <!-- Date Range Filter Form -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="table">
                    <tr>
                        <th>Order ID</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Room Number</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Delete</th>
                        <th>Edit</th>
                    </tr>
                    <?php while ($order = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                            <td>$<?php echo htmlspecialchars($order['item_price']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td>$<?php echo htmlspecialchars($order['quantity'] * $order['item_price']); ?></td>
                            <td><?php echo htmlspecialchars($order['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><a href="delete_order.php?orderid=<?php echo $order['id']; ?>" class="btn btn-danger">Delete</a></td>
                            <td><a href="edit_order.php?orderid=<?php echo $order['id']; ?>" class="btn btn-warning">Edit</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous Button -->
                        <li class="page-item <?php if ($current_page == 1) echo 'disabled'; ?>">
                            <a class="page-link" href="my_orders.php?page=<?php echo $current_page - 1; ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">Previous</a>
                        </li>

                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                                <a class="page-link" href="my_orders.php?page=<?php echo $i; ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <li class="page-item <?php if ($current_page == $total_pages) echo 'disabled'; ?>">
                            <a class="page-link" href="my_orders.php?page=<?php echo $current_page + 1; ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
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









