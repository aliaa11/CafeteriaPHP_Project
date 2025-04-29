<?php
// Include database connection and start session
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Initialize variables
$order = null;
$order_items = [];
$total_price = 0;

// Check if order ID is provided and valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch order data to confirm before deletion
    $sql = "SELECT o.id, o.order_date, o.status, u.username 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?";
    $stmt = mysqli_prepare($myConnection, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($stmt);

    if ($order) {
        // Fetch order items for display
        $items_sql = "SELECT i.name, oi.quantity, i.price 
                     FROM order_items oi
                     JOIN items i ON oi.item_id = i.id
                     WHERE oi.order_id = ?";
        $items_stmt = mysqli_prepare($myConnection, $items_sql);
        mysqli_stmt_bind_param($items_stmt, 'i', $id);
        mysqli_stmt_execute($items_stmt);
        $items_result = mysqli_stmt_get_result($items_stmt);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[] = $item;
            $total_price += $item['price'] * $item['quantity'];
        }
    } else {
        echo "<div class='alert alert-danger m-4'>Order not found.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger m-4'>Invalid order ID.</div>";
    exit;
}

// Delete order if the form is submitted
if (isset($_POST['confirmDelete'])) {
    $id = intval($_POST['id']);
    
    // First delete order items
    $sql_delete_items = "DELETE FROM order_items WHERE order_id = ?";
    $stmt_items = mysqli_prepare($myConnection, $sql_delete_items);
    mysqli_stmt_bind_param($stmt_items, 'i', $id);
    mysqli_stmt_execute($stmt_items);
    
    // Then delete the order
    $sql_delete_order = "DELETE FROM orders WHERE id = ?";
    $stmt_order = mysqli_prepare($myConnection, $sql_delete_order);
    mysqli_stmt_bind_param($stmt_order, 'i', $id);
    
    if (mysqli_stmt_execute($stmt_order)) {
        echo "<div class='alert alert-success m-4'>Order deleted successfully</div>";
        echo "<script>setTimeout(() => { window.location.href='orders.php'; }, 1500);</script>";
    } else {
        echo "<div class='alert alert-danger m-4'>Error deleting order: " . mysqli_error($myConnection) . "</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .item-row {
            border-bottom: 1px solid #dee2e6;
            padding: 8px 0;
        }
        .item-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow rounded-4 border-danger">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-center text-danger">Delete Order</h4>

                    <?php if ($order): ?>
                    <div class="order-details mb-4">
                        <h5 class="text-center mb-3">Order #<?= htmlspecialchars($order['id']) ?></h5>
                        <p><strong>User:</strong> <?= htmlspecialchars($order['username']) ?></p>
                        <p><strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?= 
                                $order['status'] === 'pending' ? 'warning' : 
                                ($order['status'] === 'confirmed' ? 'primary' : 
                                ($order['status'] === 'delivered' ? 'success' : 'danger')) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </p>
                        
                        <h6 class="mt-4">Order Items:</h6>
                        <?php foreach ($order_items as $item): ?>
                            <div class="item-row">
                                <div class="d-flex justify-content-between">
                                    <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                                    <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-3 pt-2 border-top">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span>$<?= number_format($total_price, 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <p class="text-center fs-5 text-danger fw-bold">Are you sure you want to delete this order?</p>

                    <form method="POST" class="text-center mt-4">
                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                        <button type="submit" name="confirmDelete" class="btn btn-danger px-4">
                            <i class="fas fa-trash-alt me-2"></i>Yes, Delete
                        </button>
                        <a href="orders.php" class="btn btn-secondary px-4">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>