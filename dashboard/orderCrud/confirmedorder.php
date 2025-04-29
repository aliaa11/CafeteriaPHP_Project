<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Initialize all variables at the top
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$orders_per_page = 10;
$grouped_orders = [];
$total_orders = 0;
$total_pages = 1;

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

$query = "SELECT o.id as order_id, o.order_date, o.status, 
          u.id as user_id, u.username, u.email,
          i.id as item_id, i.name as item_name, i.price,
          oi.quantity
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN order_items oi ON o.id = oi.order_id
          JOIN items i ON oi.item_id = i.id
          WHERE o.status IN ('confirmed', 'out_for_delivery', 'delivered')";

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

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
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
    <title>Active Orders Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        /* ... (keep all your existing styles) ... */
    </style>
</head>

<body class="bg-light">
<div class="container py-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-clipboard-list me-2"></i>Active Orders</h2>
        <div class="animate__animated animate__fadeInRight">
            <a href="addorder-admin.php" class="btn add-order-btn">
                <i class="fas fa-plus me-2"></i>Add Order
            </a>
        </div>
    </div>

    <!-- Search Form (removed status filter) -->
    <div class="filter-card animate__animated animate__fadeInUp">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <label for="search" class="form-label fw-medium"><i class="fas fa-search me-2"></i>Search</label>
                <div class="input-group">
                    <input type="text" id="search" name="search" class="form-control" placeholder="Search by user or item..." value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-filter me-2"></i>Search
                    </button>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="orders.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sync-alt me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive animate__animated animate__fadeInUp">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grouped_orders)): ?>
                    <?php foreach ($grouped_orders as $order): ?>
                        <?php 
                        $order_total_price = 0;
                        foreach ($order['items'] as $item) {
                            $order_total_price += $item['total_price'];
                        }
                        ?>
                        <tr class="animate__animated animate__fadeIn">
                            <td><?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['username']) ?></td>
                            <td><?= htmlspecialchars($order['email']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= str_replace('_', ' ', ucfirst($order['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="item-details">
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
                            <td class="text-center">
                                <div class="d-flex flex-column gap-2">
                                    <!-- Status Update Form -->
                                    <form method="POST" class="mb-2">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <div class="input-group input-group-sm">
                                            <select name="new_status" class="form-select form-select-sm">
                                                <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="out_for_delivery" <?= $order['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <!-- Edit Button -->
                                    <a href="editoncustomizedo.php?order_id=<?= $order['order_id'] ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="animate__animated animate__fadeIn">
                                <i class="fas fa-clipboard-list fa-3x mb-3" style="color: var(--primary-color);"></i>
                                <h4 class="text-muted">No active orders found</h4>
                                <p class="text-muted">All confirmed/delivery/delivered orders are displayed here</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add animation to table rows
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>
</body>
</html>