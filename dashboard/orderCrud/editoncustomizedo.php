<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    
    // First check order status
    $status_check = $myConnection->prepare("SELECT status FROM orders WHERE id = ?");
    $status_check->bind_param("i", $order_id);
    $status_check->execute();
    $order_status = $status_check->get_result()->fetch_assoc()['status'];
    
    // Don't allow modifications if order is cancelled
    if ($order_status !== 'cancelled') {
        // Update existing item
        if (isset($_POST['update_item'])) {
            $item_id = (int)$_POST['item_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity > 0) {
                $stmt = $myConnection->prepare("UPDATE order_items SET quantity = ? WHERE order_id = ? AND item_id = ?");
                $stmt->bind_param("iii", $quantity, $order_id, $item_id);
                $stmt->execute();
                $_SESSION['message'] = "Item quantity updated successfully";
            } else {
                $_SESSION['error'] = "Quantity must be at least 1";
            }
        } 
        // Remove item
        elseif (isset($_POST['remove_item'])) {
            $item_id = (int)$_POST['item_id'];
            
            $stmt = $myConnection->prepare("DELETE FROM order_items WHERE order_id = ? AND item_id = ?");
            $stmt->bind_param("ii", $order_id, $item_id);
            $stmt->execute();
            $_SESSION['message'] = "Item removed from order";
        } 
        // Add new item
        elseif (isset($_POST['add_item'])) {
            $new_item_id = (int)$_POST['new_item_id'];
            $new_quantity = (int)$_POST['new_quantity'];
            
            // Check if item already exists
            $check = $myConnection->prepare("SELECT 1 FROM order_items WHERE order_id = ? AND item_id = ?");
            $check->bind_param("ii", $order_id, $new_item_id);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $_SESSION['error'] = "This item already exists in the order";
            } elseif ($new_quantity > 0) {
                $stmt = $myConnection->prepare("INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $order_id, $new_item_id, $new_quantity);
                $stmt->execute();
                $_SESSION['message'] = "New item added to order";
            } else {
                $_SESSION['error'] = "Quantity must be at least 1";
            }
        }
    } else {
        $_SESSION['error'] = "Cannot modify a cancelled order";
    }
    header("location:editoncustomizedo.php?order_id=".$order_id);
    
    exit();
}

// Fetch order details
$order = [];
$stmt = $myConnection->prepare("
    SELECT o.id, o.order_date, o.status, u.username, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch order items
$items = [];
$total = 0;
$stmt = $myConnection->prepare("
    SELECT i.id, i.name, i.price, oi.quantity
    FROM order_items oi
    JOIN items i ON oi.item_id = i.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $row['total'] = $row['price'] * $row['quantity'];
    $total += $row['total'];
    $items[] = $row;
}

// Fetch all available items for dropdown
$all_items = [];
$result = $myConnection->query("SELECT id, name, price FROM items ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $all_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order #<?= $order_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-pending { background-color: #ffc107; }
        .badge-completed { background-color: #28a745; color: white; }
        .badge-cancelled { background-color: #dc3545; color: white; }
        .order-header { background-color: #f8f9fa; }
        .total-row { font-weight: bold; background-color: #f8f9fa; }
        .disabled-form {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Order #<?= $order_id ?></h2>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header order-header">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Customer:</strong> <?= htmlspecialchars($order['username'] ?? '') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? '') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong> 
                        <span class="badge badge-<?= strtolower($order['status'] ?? '') ?>">
                            <?= ucfirst($order['status'] ?? '') ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body <?= $order['status'] === 'cancelled' ? 'disabled-form' : '' ?>">
                <h5 class="card-title">Order Items</h5>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <form method="post" class="d-flex">
                                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                               min="1" class="form-control form-control-sm" style="width: 70px;">
                                        <button type="submit" name="update_item" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>$<?= number_format($item['total'], 2) ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Are you sure you want to remove this item?');">
                                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="remove_item" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <tr class="total-row">
                                <td colspan="3" class="text-end"><strong>Order Total:</strong></td>
                                <td>$<?= number_format($total, 2) ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="mt-4">
                    <h5>Add New Item</h5>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        
                        <div class="col-md-5">
                            <select name="new_item_id" class="form-select" required>
                                <option value="">Select an item...</option>
                                <?php foreach ($all_items as $item): ?>
                                    <option value="<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['name']) ?> ($<?= number_format($item['price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <input type="number" name="new_quantity" min="1" value="1" 
                                   class="form-control" placeholder="Quantity" required>
                        </div>
                        
                        <div class="col-md-4">
                            <button type="submit" name="add_item" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>