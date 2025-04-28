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
    <title>My Orders - Luna Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Header Styles */
        .navbar {
            background-color: rgb(75, 49, 102);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important;
        }
        .navbar-nav .nav-link {
            color: #fff !important;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .navbar-nav .nav-link:hover {
            color: rgb(122, 102, 143) !important;
        }
        .btn-auth {
            background-color: rgb(122, 102, 143);
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-auth:hover {
            background-color: rgb(100, 80, 120);
            color: #fff;
        }
        .cart-icon {
            position: relative;
            margin-left: 15px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: #fff;
        }
        .cart-icon .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: rgb(122, 102, 143);
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }

        /* Orders Section */
        .orders-section {
            padding: 50px 0;
            background-color: #fff;
        }
        .orders-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: rgb(75, 49, 102);
            text-align: center;
            margin-bottom: 30px;
        }
        .orders-section .form-label {
            font-weight: 500;
            color: rgb(75, 49, 102);
        }
        .orders-section .form-control {
            border: 1px solid rgb(122, 102, 143);
            border-radius: 5px;
            padding: 8px;
        }
        .orders-section .form-control:focus {
            border-color: rgb(75, 49, 102);
            box-shadow: 0 0 5px rgba(75, 49, 102, 0.3);
        }
        .orders-section .btn-primary {
            background-color: rgb(75, 49, 102);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
        }
        .orders-section .btn-primary:hover {
            background-color: rgb(122, 102, 143);
        }
        .orders-section .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
        }
        .orders-section .btn-secondary:hover {
            background-color: #5a6268;
        }
        .order-card {
            border: 2px solid rgb(122, 102, 143);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: #fff;
            margin-bottom: 20px;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .order-card .card-body {
            padding: 20px;
        }
        .order-card .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: rgb(75, 49, 102);
            margin-bottom: 10px;
        }
        .order-card .card-text {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        .order-card .badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .order-card .btn-view {
            background-color: rgb(75, 49, 102);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
        }
        .order-card .btn-view:hover {
            background-color: rgb(122, 102, 143);
        }
        .order-card .btn-cancel {
            background-color: rgb(122, 102, 143);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
        }
        .order-card .btn-cancel:hover {
            background-color: rgb(100, 80, 120);
        }

        /* Pagination Styles */
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        .pagination .page-link {
            background-color: rgb(75, 49, 102);
            color: #fff;
            border: none;
            border-radius: 20px;
            margin: 0 5px;
            padding: 8px 15px;
            transition: background-color 0.3s;
        }
        .pagination .page-link:hover {
            background-color: rgb(122, 102, 143);
        }
        .pagination .page-item.disabled .page-link {
            background-color: #ccc;
            color: #666;
        }
        .pagination .page-item.active .page-link {
            background-color: rgb(122, 102, 143);
            color: #fff;
        }

        /* Footer Styles */
        footer {
            background-color: rgb(75, 49, 102);
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        footer a {
            color: rgb(122, 102, 143);
            text-decoration: none;
            margin: 0 10px;
        }
        footer a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Luna Cafeteria</a>
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
                    <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                    <a href="logout.php" class="btn btn-auth">Logout</a>
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
            <h2>My Orders</h2>

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
                <div class="row">
                    <?php while ($order = mysqli_fetch_assoc($result)): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="order-card">
                                <div class="card-body">
                                    <h5 class="card-title">Order #<?= $order['id'] ?></h5>
                                    <p class="card-text"><strong>Items:</strong> <?= htmlspecialchars($order['items_list']) ?></p>
                                    <p class="card-text"><strong>Total:</strong> <?= number_format($order['total_price'], 2) ?> EGP</p>
                                    <p class="card-text"><strong>Room:</strong> <?= htmlspecialchars($order['room_number']) ?></p>
                                    <p class="card-text">
                                        <strong>Status:</strong>
                                        <span class="badge 
                                            <?= $order['status'] == 'pending' ? 'bg-warning text-dark' : 
                                               ($order['status'] == 'confirmed' ? 'bg-primary' : 
                                               ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger')) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </p>
                                    <p class="card-text"><strong>Date:</strong> <?= $order['order_date'] ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-view">View</a>
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <a href="delete_order.php?order_id=<?= $order['id'] ?>" class="btn btn-cancel">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                                       aria-label="First">
                                        <span aria-hidden="true">««</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                                       aria-label="Previous">
                                        <span aria-hidden="true">«</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php 
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
                                        <span aria-hidden="true">»</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                                       aria-label="Last">
                                        <span aria-hidden="true">»»</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center text-muted">No orders found for the selected date range.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
