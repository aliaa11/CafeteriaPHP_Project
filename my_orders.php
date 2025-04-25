<?php
session_start();
<<<<<<< HEAD
include_once './config/dbConnection.php';

=======
include_once 'db.php';

// التأكد من إن المستخدم مسجل دخول
>>>>>>> b0afb19 (home,logout,cart,order)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
<<<<<<< HEAD
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

=======

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
>>>>>>> b0afb19 (home,logout,cart,order)

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
<<<<<<< HEAD
    <title>My Orders - Feane Cafeteria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
=======
    <title>Feane Cafeteria - My Orders</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
>>>>>>> b0afb19 (home,logout,cart,order)
=======
    <title>My Orders - Feane Cafeteria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
<<<<<<< HEAD
<<<<<<< HEAD
=======
        body {
            background-color: #F5F5DC;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

>>>>>>> b0afb19 (home,logout,cart,order)
=======
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
        /* Navigation Bar */
        .navbar {
            background-color: transparent;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 3;
        }
<<<<<<< HEAD
<<<<<<< HEAD
        .navbar .navbar-brand {
            color: #d2b48c; /* درجة بني فاتحة عشان كلمة Feane تبقى واضحة */
        }
        .navbar .nav-link {
            color: #8d5524; /* درجة بني غامق وواضح */
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
            color: #6d3e1a;
=======
=======
        .navbar .navbar-brand {
            color: #d2b48c; /* درجة بني فاتحة عشان كلمة Feane تبقى واضحة */
        }
>>>>>>> 0fd8969 (lastupdate)
        .navbar .nav-link {
            color: #8d5524; /* درجة بني غامق وواضح */
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
<<<<<<< HEAD
            color: #8d5524;
>>>>>>> b0afb19 (home,logout,cart,order)
=======
            color: #6d3e1a;
>>>>>>> 0fd8969 (lastupdate)
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
        .navbar .welcome-text {
            color: #8d5524; /* نفس لون الروابط */
        }
=======
>>>>>>> b0afb19 (home,logout,cart,order)
=======
        .navbar .welcome-text {
            color: #8d5524; /* نفس لون الروابط */
        }
>>>>>>> 0fd8969 (lastupdate)
        .cart-icon {
            position: relative;
            margin-left: 10px;
        }
        .cart-icon i {
            font-size: 1.5rem;
<<<<<<< HEAD
<<<<<<< HEAD
            color: #d2b48c; /* درجة بني فاتحة عشان الأيقونة تبان */
=======
            color: white;
>>>>>>> b0afb19 (home,logout,cart,order)
=======
            color: #d2b48c; /* درجة بني فاتحة عشان الأيقونة تبان */
>>>>>>> 0fd8969 (lastupdate)
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
<<<<<<< HEAD
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
<<<<<<< HEAD
=======
            background-color: #5C4033;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 100px auto 30px auto;
            max-width: 800px;
=======

        /* Orders Section */
        .orders-section {
            padding: 50px 0;
            background-color: #F5F5DC;
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
        }
        .heading_container h2 {
            font-family: 'Playfair Display', serif;
            color: #5C4033;
            text-align: center;
        }
<<<<<<< HEAD
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
>>>>>>> b0afb19 (home,logout,cart,order)
        }
=======
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
=======
        }
>>>>>>> 16a93a9 (updatemyorder&cart)

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
<<<<<<< HEAD
<<<<<<< HEAD
            <a class="navbar-brand" href="#">Feane</a>
=======
            <a class="navbar-brand text-white" href="#">Feane</a>
>>>>>>> b0afb19 (home,logout,cart,order)
=======
            <a class="navbar-brand" href="#">Feane</a>
>>>>>>> 0fd8969 (lastupdate)
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
<<<<<<< HEAD
=======
                        <a class="nav-link" href="#">ABOUT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">BOOK TABLE</a>
                    </li>
                    <li class="nav-item">
>>>>>>> b0afb19 (home,logout,cart,order)
                        <a class="nav-link active" href="my_orders.php">MY ORDERS</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
                    <span class="welcome-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-order-online">Logout</a>
=======
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <a href="logout.php" class="btn btn-order-online">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-order-online">Login</a>
                    <?php endif; ?>
>>>>>>> b0afb19 (home,logout,cart,order)
=======
                    <span class="welcome-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-order-online">Logout</a>
>>>>>>> 0fd8969 (lastupdate)
                    <a href="cart.php" class="cart-icon" onclick="window.location.href='cart.php'; return false;">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
<<<<<<< HEAD
=======
                    <a href="logout.php" class="btn btn-order-online">Logout</a>
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
=======
>>>>>>> 0fd8969 (lastupdate)
                </div>
            </div>
        </div>
    </nav>

    <!-- Orders Section -->
    <section class="orders-section">
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
        <div class="container">
            <div class="heading_container">
                <h2>My Orders</h2>
            </div>
<<<<<<< HEAD
<<<<<<< HEAD

            <div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-5">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($date_from || $date_to): ?>
                    <a href="my_orders.php" class="btn btn-secondary ms-2">Clear</a>
                <?php endif; ?>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>
</div>

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
                                <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-primary ">View</a>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <a href="delete_order.php?order_id=<?= $order['id'] ?>" class="btn btn-danger p-2">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>

                <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php 
            // Show page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            if ($start_page > 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            
            for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; 
            
            if ($end_page < $total_pages) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            ?>

            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
                       aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
            <?php else: ?>
                <p>No orders found for the selected date range.</p>
            <?php endif; ?>
        </div>
    </section>


=======
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
=======
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
=======

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
>>>>>>> 16a93a9 (updatemyorder&cart)
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
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
    </section>

>>>>>>> b0afb19 (home,logout,cart,order)
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

<<<<<<< HEAD
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        
        if (dateFrom && dateTo) {
            dateFrom.addEventListener('change', function() {
                if (this.value && dateTo.value && this.value > dateTo.value) {
                    dateTo.value = this.value;
                }
            });
            
            dateTo.addEventListener('change', function() {
                if (this.value && dateFrom.value && this.value < dateFrom.value) {
                    dateFrom.value = this.value;
                }
            });
        }
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
=======
    <!-- Bootstrap JS -->
<<<<<<< HEAD
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> b0afb19 (home,logout,cart,order)
=======
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
</body>
</html>



<<<<<<< HEAD
<<<<<<< HEAD





<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> b0afb19 (home,logout,cart,order)
=======



>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
=======
>>>>>>> 16a93a9 (updatemyorder&cart)
=======

>>>>>>> 0fd8969 (lastupdate)
