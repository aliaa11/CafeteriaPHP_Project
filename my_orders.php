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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color:rgb(126, 105, 155);
            --accent-color: #f8a5c2;
            --light-bg:rgb(231, 231, 231);
            --card-bg: #ffffff;
            --text-dark:rgb(67, 38, 109);
            --text-light: #f5f6fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
        }
       
        .navbar {
            background-color: rgb(75, 49, 102);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .nav-link {
            color: var(--primary-color);
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--secondary-color);
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
        
        .welcome-text {
            color: var(--primary-color);
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
        
        .orders-section {
            padding: 100px 0 50px;
            min-height: 100vh;
        }
        
        .heading-container h2 {
            font-family: 'Playfair Display', serif;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .filter-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .form-control {
            background-color: #f0f0f0;
            border: 1px solid var(--primary-color);
            border-radius: 5px;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(141, 85, 36, 0.25);
            border-color: var(--primary-color);
        }
        
        .order-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border: none;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .order-header {
            background-color: #6a4c93;
            padding: 15px;
            color:#ffff;
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-footer {
            padding: 15px;
            background-color: rgba(141, 85, 36, 0.05);
            border-radius: 0 0 12px 12px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .order-id {
            font-weight: bold;
            color:#ffff;
        }
        
        .order-date {
            color:#ffff;
            font-size: 0.9rem;
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-delivered {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .status-canceled {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .order-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }
        
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .order-total {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .btn-view {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
        }
        
        .btn-view:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
        }
        
        .btn-cancel:hover {
            background-color: #bb2d3b;
            color: white;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-link {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            margin: 0 5px;
            border-radius: 20px !important;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            border-color: #dee2e6;
        }
        
        footer {
            background-color: var(--text-dark);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        footer a {
            color: var(--accent-color);
            text-decoration: none;
            margin: 0 10px;
        }
        
        footer a:hover {
            color: var(--primary-color);
        }
        
        .no-orders {
            text-align: center;
            padding: 50px 0;
        }
        
        .no-orders i {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
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

    <!-- Orders Section -->
    <section class="orders-section">
        <div class="container">
            <div class="heading-container">
                <h2>My Orders</h2>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
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
                            <a href="my_orders.php" class="btn btn-outline-secondary ms-2">Clear</a>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="page" value="1">
                </form>
            </div>

            <!-- Orders Cards -->
            <div class="row">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="order-card h-100">
                                <div class="order-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="order-id">Order #<?= $order['id'] ?></span>
                                        <span class="order-date ms-2"><?= date('M j, Y', strtotime($order['order_date'])) ?></span>
                                    </div>
                                    <span class="order-status status-<?= strtolower($order['status']) ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                                <div class="order-body">
                                    <h6 class="mb-3">Items Ordered:</h6>
                                    <?php 
                                    $items = explode(', ', $order['items_list']);
                                    foreach ($items as $item): ?>
                                        <div class="order-item">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <?= htmlspecialchars($item) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="order-footer d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted">Room:</span>
                                        <strong class="ms-2"><?= htmlspecialchars($order['room_number']) ?></strong>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted">Total:</span>
                                        <span class="order-total ms-2"><?= number_format($order['total_price'], 2) ?> EGP</span>
                                    </div>
                                </div>
                                <div class="p-3 d-flex justify-content-between">
                                    <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-view">
                                        <i class="bi bi-eye-fill me-1"></i> View Details
                                    </a>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <a href="delete_order.php?order_id=<?= $order['id'] ?>" class="btn btn-cancel" onclick="return confirm('Are you sure you want to cancel this order?');">
                                            <i class="bi bi-x-circle-fill me-1"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-orders col-12">
                        <i class="bi bi-clipboard-x"></i>
                        <h4>No orders found</h4>
                        <p class="text-muted">Try adjusting your date filters</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
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
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Luna Cafeteria. All Rights Reserved.</p>
            <p>
                <a href="#">Contact Us</a> | 
                <a href="#">About Us</a> | 
                <a href="#">Privacy Policy</a>
            </p>
        </div>
    </footer>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
