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

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

$order_query = "SELECT o.*, u.username, 
               (SELECT SUM(i.price * oi.quantity) 
                FROM order_items oi 
                JOIN items i ON oi.item_id = i.id 
                WHERE oi.order_id = o.id) as total_price
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($myConnection, $order_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: my_orders.php");
    exit();
}

$items_query = "SELECT i.*, oi.quantity 
               FROM order_items oi
               JOIN items i ON oi.item_id = i.id
               WHERE oi.order_id = ?";
$stmt = mysqli_prepare($myConnection, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($items_result, MYSQLI_ASSOC); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Luna Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* Order Details Section */
        .order-details {
            padding: 50px 0;
            background-color: #fff;
        }
        .order-details h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: rgb(75, 49, 102);
            text-align: center;
            margin-bottom: 20px;
        }
        .order-header {
            background-color: #fff;
            border: 2px solid rgb(122, 102, 143);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .order-header p {
            margin: 5px 0;
            color: #666;
        }
        .order-header .order-status {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .order-items h4 {
            font-size: 1.8rem;
            font-weight: 600;
            color: rgb(75, 49, 102);
            margin-bottom: 20px;
        }
        .order-item {
            border: 2px solid rgb(122, 102, 143);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .order-item-details {
            flex-grow: 1;
        }
        .order-item-details h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgb(75, 49, 102);
            margin-bottom: 5px;
        }
        .order-item-details p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        .order-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: rgb(75, 49, 102);
            text-align: center;
            margin-top: 20px;
        }
        .order-actions {
            text-align: center;
            margin-top: 20px;
        }
        .order-actions .btn-primary {
            background-color: rgb(75, 49, 102);
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
        }
        .order-actions .btn-primary:hover {
            background-color: rgb(122, 102, 143);
        }
        .order-actions .btn-warning {
            background-color: rgb(122, 102, 143);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 20px;
        }
        .order-actions .btn-warning:hover {
            background-color: rgb(100, 80, 120);
        }

        /* Modal Styles */
        .modal-content {
            background-color: #fff;
            border: 2px solid rgb(122, 102, 143);
            border-radius: 15px;
        }
        .modal-header {
            border-bottom: 2px solid rgb(122, 102, 143);
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: rgb(75, 49, 102);
        }
        .modal-body .card {
            border: 1px solid rgb(122, 102, 143);
            border-radius: 10px;
        }
        .modal-body .form-label {
            color: rgb(75, 49, 102);
        }
        .modal-body .form-control,
        .modal-body .form-select {
            border: 1px solid rgb(122, 102, 143);
            border-radius: 5px;
        }
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: rgb(75, 49, 102);
            box-shadow: 0 0 5px rgba(75, 49, 102, 0.3);
        }
        .modal-footer .btn-primary {
            background-color: rgb(75, 49, 102);
            border: none;
            border-radius: 20px;
        }
        .modal-footer .btn-primary:hover {
            background-color: rgb(122, 102, 143);
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

    <!-- Order Details Section -->
    <section class="order-details">
        <div class="container">
            <h2>Order #<?= $order['id'] ?></h2>
            <div class="order-header">
                <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
                <p><strong>Date:</strong> <?= $order['order_date'] ?></p>
                <p><strong>Room:</strong> <?= htmlspecialchars($order['room_number']) ?></p>
                <p><strong>Status:</strong> 
                    <span class="order-status 
                        <?= $order['status'] == 'pending' ? 'bg-warning text-dark' : 
                           ($order['status'] == 'confirmed' ? 'bg-primary' : 
                           ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger')) ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </p>
            </div>

            <div class="order-items">
                <h4>Order Items</h4>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="/CafeteriaPHP_Project/Public/uploads/products/<?= htmlspecialchars($item['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                                <div class="no-image-placeholder text-center py-3">
                                    <i class="bi bi-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="order-item-details">
                                <h5><?= htmlspecialchars($item['name']) ?></h5>
                                <p><?= htmlspecialchars($item['description']) ?></p>
                                <p><strong>Quantity:</strong> <?= $item['quantity'] ?></p>
                                <p><strong>Price:</strong> <?= number_format($item['price'] * $item['quantity'], 2) ?> EGP</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No items found in this order.</p>
                <?php endif; ?>
            </div>

            <div class="order-total">
                Total: <?= number_format($order['total_price'] ?? 0, 2) ?> EGP
            </div>

            <div class="order-actions">
                <a href="my_orders.php" class="btn btn-primary">Back to My Orders</a>
                <?php if ($order['status'] == 'pending'): ?>
                    <button type="button" class="btn btn-warning" id="updateOrderBtn">Update Order</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Update Order Modal -->
    <div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateOrderModalLabel">Update Order #<?= $order['id'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_order.php" method="post">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="room_number" class="form-label">Room Number</label>
                            <select name="room_number" id="room_number" class="form-select" required>
                                <option value="">Select Room</option>
                                <option value="101" <?= $order['room_number'] == '101' ? 'selected' : '' ?>>101</option>
                                <option value="102" <?= $order['room_number'] == '102' ? 'selected' : '' ?>>102</option>
                                <option value="103" <?= $order['room_number'] == '103' ? 'selected' : '' ?>>103</option>
                                <option value="104" <?= $order['room_number'] == '104' ? 'selected' : '' ?>>104</option>
                                <option value="105" <?= $order['room_number'] == '105' ? 'selected' : '' ?>>105</option>
                            </select>
                        </div>
                        
                        <h5>Order Items</h5>
                        <?php foreach ($items as $item): ?>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><?= htmlspecialchars($item['name']) ?></h6>
                                            <p><?= htmlspecialchars($item['description']) ?></p>
                                            <p>Price: <?= number_format($item['price'], 2) ?> EGP</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="quantity_<?= $item['id'] ?>" class="form-label">Quantity</label>
                                            <input type="number" class="form-control" 
                                                   id="quantity_<?= $item['id'] ?>" 
                                                   name="quantities[<?= $item['id'] ?>]" 
                                                   value="<?= $item['quantity'] ?>" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('updateOrderBtn').addEventListener('click', function() {
            var updateModal = new bootstrap.Modal(document.getElementById('updateOrderModal'));
            updateModal.show();
        });
    </script>
</body>
</html>