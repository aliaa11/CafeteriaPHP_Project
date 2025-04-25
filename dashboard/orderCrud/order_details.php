<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Check admin privileges
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: login.php");
//     exit();
// }

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['order_id'];

$order_query = "SELECT o.*, u.username, u.email, 
               (SELECT SUM(i.price * oi.quantity) 
                FROM order_items oi 
                JOIN items i ON oi.item_id = i.id 
                WHERE oi.order_id = o.id) as total_price
               FROM orders o
               JOIN users u ON o.user_id = u.id
               WHERE o.id = ?";
$stmt = mysqli_prepare($myConnection, $order_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: orders.php");
    exit();
}

$items_query = "SELECT i.id, i.name, i.price, oi.quantity, (i.price * oi.quantity) as subtotal
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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .order-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .order-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-delivered {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .item-row {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .item-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    
    <div class="container">
    <h2 class="mb-5 mt-5">Order Details</h2>
        <div class="order-container">
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Order #<?= $order['id'] ?></h2>
                    <span class="status-badge status-<?= $order['status'] ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
                        <p><strong>Room:</strong> <?= htmlspecialchars($order['room_number']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Total:</strong> <?= number_format($order['total_price'], 2) ?> $</p>
                    </div>
                </div>
            </div>
            
            <h4>Order Items</h4>
            <?php if (!empty($items)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= number_format($item['price'], 2) ?> $</td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['subtotal'], 2) ?> $</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th><?= number_format($order['total_price'], 2) ?> $</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No items found in this order.</div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                <div>
                    <?php if ($order['status'] == 'pending'): ?>
                        <a href="update_status.php?order_id=<?= $order['id'] ?>&status=confirmed" class="btn btn-success">Confirm Order</a>
                        <a href="update_status.php?order_id=<?= $order['id'] ?>&status=canceled" class="btn btn-danger">Cancel Order</a>
                    <?php elseif ($order['status'] == 'confirmed'): ?>
                        <a href="update_status.php?order_id=<?= $order['id'] ?>&status=delivered" class="btn btn-primary">Mark as Delivered</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>