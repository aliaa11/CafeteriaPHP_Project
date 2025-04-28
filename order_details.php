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

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

$order_query = "SELECT o.*, u.username, u.email,
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

$items_query = "SELECT i.id, i.name, i.price, i.image_url, i.description, oi.quantity, (i.price * oi.quantity) as subtotal
               FROM order_items oi
               JOIN items i ON oi.item_id = i.id
               WHERE oi.order_id = ?
               ORDER BY i.name";
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
    <title>Order Details - Feane Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color: #f8a5c2;
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
            --text-dark: #2d3436;
            --text-light: #f5f6fa;
            --dark-color:rgb(73, 40, 121);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(135deg, #f9f7f7 0%, #e8f4f8 100%);
            margin-top: 6% !important;

        }
        
        .order-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 30px;
            margin-top: 30px;
            border: none;
            animation: fadeInUp 0.6s ease-out;
            transition: all 0.3s ease;
        }
        
        .order-container:hover {
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }
        
        .order-header {
            border-bottom: 1px dashed var(--secondary-color);
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-pending {
            background-color: #FFF3E0;
            color: #E65100;
            border: 1px solid #FFCC80;
        }
        
        .status-confirmed {
            background-color: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #A5D6A7;
        }
        
        .status-delivered {
            background-color: #E3F2FD;
            color: #1565C0;
            border: 1px solid #90CAF9;
        }
        
        .status-canceled {
            background-color: #FFEBEE;
            color: #C62828;
            border: 1px solid #EF9A9A;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: var(--text-light);
            border-bottom: none;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .table:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease;
        }
        
        .item-image:hover {
            transform: scale(1.05);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-color);
            border-color: var(--dark-color);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: #43A047;
            border-color: #43A047;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background-color: #2E7D32;
            border-color: #2E7D32;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: #E53935;
            border-color: #E53935;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background-color: #C62828;
            border-color: #C62828;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #757575;
            border-color: #757575;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #616161;
            border-color: #616161;
            transform: translateY(-2px);
        }
        
        h2, h4 {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        h2 {
            position: relative;
            display: inline-block;
        }
        
        h2:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--accent-color);
            border-radius: 3px;
        }
        
        .customer-info {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-color);
            transition: all 0.3s ease;
        }
        
        .customer-info:hover {
            transform: translateX(5px);
        }
        
        .animate-bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .floating-btn {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {transform: translateY(0px);}
            50% {transform: translateY(-5px);}
            100% {transform: translateY(0px);}
        }
        
        .badge-animate {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {box-shadow: 0 0 0 0 rgba(0,0,0,0.1);}
            70% {box-shadow: 0 0 0 10px rgba(0,0,0,0);}
            100% {box-shadow: 0 0 0 0 rgba(0,0,0,0);}
        }
        
        /* Navbar styles */
        .navbar {
            background-color: transparent;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 3;
        }
        .navbar .navbar-brand {
            color: var(--primary-color); 
        }
        .navbar .nav-link {
            color: var(--primary-color);
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
            color: var(--secondary-color);
        }
        .navbar .btn-order-online {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
        }
        .navbar .btn-order-online:hover {
            background-color: var(--secondary-color);
        }
        .navbar .welcome-text {
            color: var(--primary-color); 
        }
        .cart-icon {
            position: relative;
            margin-left: 10px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: var(--primary-color); 
        }
        .cart-icon .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
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
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-clipboard-list me-2"></i>Order Details</h2>
            <a href="my_orders.php" class="btn btn-outline-secondary floating-btn">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
        
        <div class="order-container">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-2">Order #<?= $order['id'] ?></h2>
                        <span class="status-badge badge-animate status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                    <div class="text-end">
                        <p class="mb-1"><strong><i class="far fa-calendar-alt me-2"></i>Order Date:</strong> <?= $order['order_date'] ?></p>
                        <p class="mb-0"><strong><i class="fas fa-receipt me-2"></i>Total:</strong> <span class="fw-bold"><?= number_format($order['total_price'], 2) ?> EGP</span></p>
                    </div>
                </div>
                
                <div class="customer-info mt-4 animate__animated animate__fadeIn">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2"><strong><i class="far fa-user me-2"></i>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
                            <p class="mb-0"><strong><i class="far fa-envelope me-2"></i>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-0"><strong><i class="fas fa-door-open me-2"></i>Room:</strong> <?= htmlspecialchars($order['room_number']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <h4 class="mb-3 animate__animated animate__fadeIn"><i class="fas fa-list-ul me-2"></i>Order Items</h4>
            <?php if (!empty($items)): ?>
                <div class="table-responsive animate__animated animate__fadeInUp">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="animate__animated animate__fadeIn">
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/<?= htmlspecialchars($item['image_url']) ?>" class="item-image" alt="<?= htmlspecialchars($item['name']) ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/60?text=No+Image" class="item-image" alt="No image">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td class="text-end"><?= number_format($item['price'], 2) ?> EGP</td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end"><?= number_format($item['subtotal'], 2) ?> EGP</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold"><?= number_format($order['total_price'], 2) ?> EGP</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning animate__animated animate__shakeX">No items found in this order.</div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                <a href="my_orders.php" class="btn btn-secondary animate-bounce">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
                <div>
                    <?php if ($order['status'] == 'pending'): ?>
                        <button type="button" class="btn btn-primary me-2" id="updateOrderBtn">
                            <i class="fas fa-edit me-2"></i>Update Order
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Order Modal -->
    <div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateOrderModalLabel">Update Order #<?= $order['id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="edit_order.php" method="post">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="room_number" class="form-label">Room Number</label>
                            <select name="room" id="room_number" class="form-select" required>
                                <option value="">Select Room</option>
                                <option value="101" <?= ($order['room_number'] == '101') ? 'selected' : '' ?>>101</option>
                                <option value="102" <?= ($order['room_number'] == '102') ? 'selected' : '' ?>>102</option>
                                <option value="103" <?= ($order['room_number'] == '103') ? 'selected' : '' ?>>103</option>
                                <option value="104" <?= ($order['room_number'] == '104') ? 'selected' : '' ?>>104</option>
                                <option value="105" <?= ($order['room_number'] == '105') ? 'selected' : '' ?>>105</option>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Show update modal when button is clicked
            document.getElementById('updateOrderBtn')?.addEventListener('click', function() {
                var updateModal = new bootstrap.Modal(document.getElementById('updateOrderModal'));
                updateModal.show();
            });
        });
    </script>
</body>
</html>