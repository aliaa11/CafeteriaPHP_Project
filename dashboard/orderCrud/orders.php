<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_stmt = mysqli_prepare($myConnection, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, 'si', $new_status, $order_id);
    mysqli_stmt_execute($update_stmt);
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle order deletion (only for pending orders)
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    
    // First check if order is pending
    $check_stmt = mysqli_prepare($myConnection, "SELECT status FROM orders WHERE id = ?");
    mysqli_stmt_bind_param($check_stmt, 'i', $order_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $order = mysqli_fetch_assoc($result);
    
    if ($order && $order['status'] == 'pending') {
        // Delete order items first (due to foreign key constraint)
        $delete_items_stmt = mysqli_prepare($myConnection, "DELETE FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($delete_items_stmt, 'i', $order_id);
        mysqli_stmt_execute($delete_items_stmt);
        
        // Then delete the order
        $delete_order_stmt = mysqli_prepare($myConnection, "DELETE FROM orders WHERE id = ?");
        mysqli_stmt_bind_param($delete_order_stmt, 'i', $order_id);
        mysqli_stmt_execute($delete_order_stmt);
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$orders_per_page = 10;

$query = "SELECT o.id as order_id, o.order_date, o.status, 
          u.id as user_id, u.username, u.email,
          i.id as item_id, i.name as item_name, i.price,
          oi.quantity
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN order_items oi ON o.id = oi.order_id
          JOIN items i ON oi.item_id = i.id";

// Add conditions
$conditions = [];
$params = [];
$types = '';

if (!empty($search_query)) {
    $conditions[] = "(u.username LIKE ? OR u.email LIKE ? OR i.name LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'sss';
}

if ($status_filter !== 'all') {
    $conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Count total orders
$count_query = "SELECT COUNT(DISTINCT temp.order_id) as total FROM ($query) as temp";
$count_stmt = mysqli_prepare($myConnection, $count_query);

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $orders_per_page);

// Add sorting and pagination
$query .= " ORDER BY o.order_date DESC LIMIT ? OFFSET ?";
$params[] = $orders_per_page;
$params[] = ($current_page - 1) * $orders_per_page;
$types .= 'ii';

// Fetch orders
$stmt = mysqli_prepare($myConnection, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$orders_result = mysqli_stmt_get_result($stmt);

// Group orders by order ID
$grouped_orders = [];
while ($order = mysqli_fetch_assoc($orders_result)) {
    if (!isset($grouped_orders[$order['order_id']])) {
        $grouped_orders[$order['order_id']] = [
            'order_id' => $order['order_id'],
            'user_id' => $order['user_id'],
            'username' => $order['username'],
            'email' => $order['email'],
            'order_date' => $order['order_date'],
            'status' => $order['status'],
            'items' => []
        ];
    }
    $grouped_orders[$order['order_id']]['items'][] = [
        'item_id' => $order['item_id'],
        'item_name' => $order['item_name'],
        'price' => $order['price'],
        'quantity' => $order['quantity'],
        'total_price' => $order['price'] * $order['quantity']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-completed { background-color: #28a745; color: #fff; }
        .badge-cancelled { background-color: #dc3545; color: #fff; }
        .pagination .page-item.active .page-link { background-color: #6c757d; border-color: #6c757d; }
        .table td, .table th { vertical-align: middle; }
        .action-btns { white-space: nowrap; }
    </style>
</head>

<body class="bg-light">

<div class="container py-5">
    <h2 class="text-center mb-4">Order Management</h2>

    <div class="d-grid gap-2 mt-2" style="max-width:100px">
        <a href="addorder-admin.php" class="btn btn-primary btn-md">
            <i class="fas fa-plus"></i> Add Order
        </a>
    </div>

    <!-- Search and Filter Form -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by user or item..." value="<?= htmlspecialchars($search_query) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php if (!empty($grouped_orders)): ?>
                        <?php foreach ($grouped_orders as $order): ?>
                            <?php 
                            $order_total_price = 0;
                            foreach ($order['items'] as $item) {
                                $order_total_price += $item['total_price'];
                            }
                            ?>
                            <tr>
                                <td><?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['username']) ?></td>
                                <td><?= htmlspecialchars($order['email']) ?></td>
                                <td><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $order['status'] === 'pending' ? 'badge-pending' : 
                                           ($order['status'] === 'completed' ? 'badge-completed' : 'badge-cancelled') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="text-start mb-2">
                                            <strong><?= htmlspecialchars($item['item_name']) ?></strong> 
                                            (x<?= $item['quantity'] ?>) 
                                            <br>
                                            <small class="text-muted">
                                                $<?= number_format($item['price'], 2) ?> each 
                                                → <strong>$<?= number_format($item['total_price'], 2) ?></strong>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <strong>$<?= number_format($order_total_price, 2) ?></strong>
                                </td>
                                <td class="action-btns">
                                    <!-- Status Update Form -->
                                    <form method="POST" class="mb-2">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <select name="new_status" class="form-select form-select-sm mb-1">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-warning w-100">
                                            <i class="fas fa-sync-alt"></i> Update
                                        </button>
                                    </form>
                                    
                                    <!-- Delete Button (only shown for pending orders) -->
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <button type="submit" name="delete_order" class="btn btn-sm btn-danger w-100">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search_query) ?>&status=<?= urlencode($status_filter) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>