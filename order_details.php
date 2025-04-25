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
    <title>Order Details - Feane Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
               .navbar {
            background-color: transparent;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 3;
        }
        .navbar .navbar-brand {
            color: #d2b48c; 
        }
        .navbar .nav-link {
            color: #8d5524;
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
            color: #8d5524; 
        }
        .cart-icon {
            position: relative;
            margin-left: 10px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: #d2b48c; 
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

        body {
            background-color: #F5F5DC;
        }
        .order-details {
            background-color: #5C4033;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 80px auto 30px auto;
            max-width: 800px;
        }
        .order-header {
            border-bottom: 2px solid #8d5524;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #FFC107;
            color: #000;
        }
        .status-confirmed {
            background-color: #28A745;
            color: #FFF;
        }
        .status-cancelled {
            background-color: #DC3545;
            color: #FFF;
        }
        .status-completed {
            background-color: #17A2B8;
            color: #FFF;
        }
<<<<<<< HEAD
        .item-image
        {
            width:100px;
        }
=======
>>>>>>> 000af98 (order status done)
    </style>
</head>
<body>
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
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
<div class="container">
        <div class="order-details">
            <div class="order-header">
                <h2>Order #<?php echo $order['id']; ?></h2>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                <p><strong>Date:</strong> <?php echo $order['order_date']; ?></p>
                <p><strong>Room:</strong> <?php echo htmlspecialchars($order['room_number']); ?></p>
                <p><strong>Status:</strong> 
                    <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
            </div>

            <h4>Order Items</h4>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div>
<<<<<<< HEAD
                        <?php if (!empty($item['image_url'])): ?>
                            <img class='item-image' src="/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/<?= htmlspecialchars($item['image_url']) ?> " 
                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                    class="order-item-img">
                            <?php else: ?>
                                <div class="no-image-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
=======
>>>>>>> 000af98 (order status done)
                            <h5><?= htmlspecialchars($item['name']) ?></h5>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                        </div>
                        <div>
                            <p>Quantity: <?= $item['quantity'] ?></p>
                            <p>Price: <?= number_format($item['price'] * $item['quantity'], 2) ?> EGP</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No items found in this order.</p>
            <?php endif; ?>

            <div class="order-total mt-4">
                <h4>Total: <?= number_format($order['total_price'] ?? 0, 2) ?> EGP</h4>
            </div>
            <div class="mt-4">
                <a href="my_orders.php" class="btn btn-primary">Back to My Orders</a>
                <?php if ($order['status'] == 'pending'): ?>
<<<<<<< HEAD
                    <button type="button" class="btn btn-warning" id="updateOrderBtn">Update Order</button>
                <?php endif; ?>
            </div>
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
                                <option value="101">101</option>
                                <option value="102">102</option>
                                <option value="103">103</option>
                                <option value="104">104</option>
                                <option value="105">105</option>
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
    // Show update modal when button is clicked
    document.getElementById('updateOrderBtn').addEventListener('click', function() {
        var updateModal = new bootstrap.Modal(document.getElementById('updateOrderModal'));
        updateModal.show();
    });
</script>
=======
                    <a href="cancel_order.php?order_id=<?php echo $order_id; ?>" class="btn btn-danger">Cancel Order</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> 000af98 (order status done)
</body>
</html>