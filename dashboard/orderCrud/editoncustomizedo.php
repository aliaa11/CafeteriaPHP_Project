<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";
// Initialize variables
// Handle item deletion
if (isset($_GET['delete_item']) && $order_id > 0) {
    $item_id = (int)$_GET['delete_item'];
    
    mysqli_begin_transaction($myConnection);
    
    try {
        $delete_item = mysqli_prepare($myConnection, "DELETE FROM order_items WHERE order_id = ? AND item_id = ?");
        mysqli_stmt_bind_param($delete_item, 'ii', $order_id, $item_id);
        mysqli_stmt_execute($delete_item);
        
        mysqli_commit($myConnection);
        
        $message = "Item removed successfully!";
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => 'success'
        ];
        header("Location: editorder.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        mysqli_rollback($myConnection);
        $message = "Error removing item: " . $e->getMessage();
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => 'danger'
        ];
    }
}
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order_details = [];
$order_items = [];
$usersList = [];
$itemsList = [];
$message = '';

// Fetch all users and items
$usersList = mysqli_query($myConnection, "SELECT id, username FROM users ORDER BY username");
$itemsList = mysqli_query($myConnection, "SELECT id, name, price FROM items ORDER BY name");

// Fetch order details if order_id is provided
if ($order_id > 0) {
    // Get order basic info
    $order_query = mysqli_prepare($myConnection, "SELECT id, user_id, room_number, status FROM orders WHERE id = ?");
    mysqli_stmt_bind_param($order_query, 'i', $order_id);
    mysqli_stmt_execute($order_query);
    $order_result = mysqli_stmt_get_result($order_query);
    $order_details = mysqli_fetch_assoc($order_result);

    if ($order_details) {
        // Get order items
        $items_query = mysqli_prepare($myConnection, "SELECT item_id, quantity FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($items_query, 'i', $order_id);
        mysqli_stmt_execute($items_query);
        $items_result = mysqli_stmt_get_result($items_query);
        
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[$item['item_id']] = $item['quantity'];
        }
    } else {
        $message = "Order not found!";
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => 'danger'
        ];
        header("Location: orders.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $user_id = (int)$_POST['user_id'];
    $room_number = mysqli_real_escape_string($myConnection, $_POST['room_number']);
    $status = mysqli_real_escape_string($myConnection, $_POST['status']);
    $selectedItems = $_POST['items'];

    mysqli_begin_transaction($myConnection);

    try {
        // Update order basic info
        $update_order = mysqli_prepare($myConnection, "UPDATE orders SET user_id = ?, room_number = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_order, 'issi', $user_id, $room_number, $status, $order_id);
        mysqli_stmt_execute($update_order);

        // Delete existing order items
        $delete_items = mysqli_prepare($myConnection, "DELETE FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($delete_items, 'i', $order_id);
        mysqli_stmt_execute($delete_items);

        // Insert new order items
        foreach ($selectedItems as $item_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $insert_item = mysqli_prepare($myConnection, "INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($insert_item, 'iii', $order_id, $item_id, $quantity);
                mysqli_stmt_execute($insert_item);
            }
        }

        mysqli_commit($myConnection);
        
        $message = "Order updated successfully!";
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => 'success'
        ];
        header("Location: orders.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($myConnection);
        $message = "Error updating order: " . $e->getMessage();
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => 'danger'
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color: #f8a5c2;
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
            --text-dark: #2d3436;
            --text-light: #f5f6fa;
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(135deg, #f9f7f7 0%, #e8f4f8 100%);
        }
        .header-title {
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }
        .header-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--accent-color);
            border-radius: 3px;
        }
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #5a3d7a;
            border-color: #5a3d7a;
            transform: translateY(-2px);
        }
        .btn-outline-secondary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-outline-secondary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .status-pending {
            background-color: #fff0f6;
            color: #c44569;
            border: 1px solid #f8a5c2;
        }
        .status-confirmed {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #90caf9;
        }
        .status-preparing {
            background-color: #fff8e1;
            color: #ff8f00;
            border: 1px solid #ffcc80;
        }
        .status-out_for_delivery {
            background-color: #e8f5e9;
            color: #388e3c;
            border: 1px solid #a5d6a7;
        }
        .status-delivered {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #81c784;
        }
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background-color: var(--card-bg);
        }
        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        .quantity-control {
            width: 80px;
        }
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title animate__animated animate__fadeInLeft">
                <i class="fas fa-edit me-2"></i>Edit Order #<?= $order_id ?>
            </h2>
            <div class="animate__animated animate__fadeInRight">
                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> animate__animated animate__fadeInDown">
            <?= $message ?>
        </div>
        <?php endif; ?>

        <div class="card animate__animated animate__fadeInUp">
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-user me-2"></i>Select Customer</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                <?php 
                                mysqli_data_seek($usersList, 0); // Reset pointer to beginning
                                while ($u = mysqli_fetch_assoc($usersList)): ?>
                                    <option value="<?= $u['id'] ?>" <?= (isset($order_details['user_id']) && $order_details['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['username']) ?> (ID: <?= $u['id'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="invalid-feedback">Please select a customer.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-door-open me-2"></i>Room Number</label>
                            <input type="text" name="room_number" class="form-control" 
                                   value="<?= isset($order_details['room_number']) ? htmlspecialchars($order_details['room_number']) : '' ?>" 
                                   required>
                            <div class="invalid-feedback">Room number is required.</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-info-circle me-2"></i>Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?= (isset($order_details['status']) && $order_details['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= (isset($order_details['status']) && $order_details['status'] === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                <option value="preparing" <?= (isset($order_details['status']) && $order_details['status'] === 'preparing') ? 'selected' : '' ?>>Preparing</option>
                                <option value="out_for_delivery" <?= (isset($order_details['status']) && $order_details['status'] === 'out_for_delivery') ? 'selected' : '' ?>>Out for Delivery</option>
                                <option value="delivered" <?= (isset($order_details['status']) && $order_details['status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= (isset($order_details['status']) && $order_details['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <h4 class="mb-3" style="color: var(--primary-color);">
                        <i class="fas fa-utensils me-2"></i>Select Items
                    </h4>

                    <div class="row">
                        <?php 
                        mysqli_data_seek($itemsList, 0); // Reset pointer to beginning
                        while ($item = mysqli_fetch_assoc($itemsList)): ?>
                            <div class="col-md-4">
                                <div class="product-card">
                                    <h5 style="color: var(--primary-color);">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </h5>
                                    <p class="text-muted">
                                        <i class="fas fa-tag me-2"></i>
                                        <?= number_format($item['price'], 2) ?> EGP
                                    </p>
                                    <div class="input-group">
                                        <span class="input-group-text">Qty:</span>
                                        <input type="number" 
                                               name="items[<?= $item['id'] ?>]" 
                                               class="form-control quantity-control" 
                                               min="0" 
                                               value="<?= isset($order_items[$item['id']]) ? $order_items[$item['id']] : 0 ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', e => {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>