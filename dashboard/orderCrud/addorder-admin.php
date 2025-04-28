<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Fetch all users and products
$users = mysqli_query($myConnection, "SELECT id, username FROM users ORDER BY username");
$products = mysqli_query($myConnection, "SELECT id, name, price FROM items ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $items = $_POST['items'];
    $room_number = mysqli_real_escape_string($myConnection, $_POST['room_number']);
    
    // Start transaction
    mysqli_begin_transaction($myConnection);
    
    try {
        // 1. Create the order
        $order_query = "INSERT INTO orders (user_id, room_number, status) VALUES (?, ?, 'pending')";
        $stmt = mysqli_prepare($myConnection, $order_query);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $room_number);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($myConnection);
        
        // 2. Add order items
        foreach ($items as $item_id => $quantity) {
            if ($quantity > 0) {
                $item_query = "INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($myConnection, $item_query);
                mysqli_stmt_bind_param($stmt, "iii", $order_id, $item_id, $quantity);
                mysqli_stmt_execute($stmt);
            }
        }
        
        // Commit transaction
        mysqli_commit($myConnection);
        
        $_SESSION['flash_message'] = [
            'message' => 'Order created successfully!',
            'type' => 'success'
        ];
        header("Location: orders.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($myConnection);
        $_SESSION['flash_message'] = [
            'message' => 'Error creating order: ' . $e->getMessage(),
            'type' => 'danger'
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        .quantity-control {
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Create New Order</h2>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
        
        <div class="card shadow">
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Select Customer</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                    <option value="<?= $user['id'] ?>">
                                        <?= htmlspecialchars($user['username']) ?> (ID: <?= $user['id'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                    </div>
                    
                    <h4 class="mb-3"><i class="fas fa-utensils me-2"></i>Select Items</h4>
                    
                    <div class="row">
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <div class="col-md-4">
                                <div class="product-card">
                                    <h5><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="text-muted">Price: <?= number_format($product['price'], 2) ?> EGP</p>
                                    <div class="input-group">
                                        <span class="input-group-text">Qty:</span>
                                        <input type="number" 
                                               name="items[<?= $product['id'] ?>]" 
                                               class="form-control quantity-control" 
                                               min="0" 
                                               value="0">
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>