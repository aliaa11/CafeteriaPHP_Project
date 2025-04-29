<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Initialize all variables at the top
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
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
        
        .filter-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
            transition: all 0.3s ease;
        }
        
        .filter-card:hover {
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
        
        .table {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .table:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: var(--text-light);
            font-weight: 500;
            letter-spacing: 0.5px;
            text-align: center;
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
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .action-btns {
            white-space: nowrap;
        }
        
        .animate-bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% {transform: translateY(0px);}
            50% {transform: translateY(-8px);}
            100% {transform: translateY(0px);}
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {box-shadow: 0 0 0 0 rgba(106, 76, 147, 0.4);}
            70% {box-shadow: 0 0 0 12px rgba(106, 76, 147, 0);}
            100% {box-shadow: 0 0 0 0 rgba(106, 76, 147, 0);}
        }
        
        .add-order-btn {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
            transition: all 0.3s ease;
        }
        
        .add-order-btn:hover {
            background-color: #e893b5;
            border-color: #e893b5;
            transform: translateY(-2px);
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .item-details {
            background-color: rgba(249, 247, 247, 0.7);
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body class="bg-light">
<div class="container py-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-clipboard-list me-2"></i>Order Management</h2>
        <div class="animate__animated animate__fadeInRight">
            <a href="addorder-admin.php" class="btn add-order-btn">
                <i class="fas fa-plus me-2"></i>Add Order
            </a>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="filter-card animate__animated animate__fadeInUp">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label fw-medium"><i class="fas fa-search me-2"></i>Search</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Search by user or item..." value="<?= htmlspecialchars($search_query) ?>">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label fw-medium"><i class="fas fa-filter me-2"></i>Status</label>
                <select name="status" class="form-select">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="preparing" <?= $status_filter === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                    <option value="out_for_delivery" <?= $status_filter === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filter</button>
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
                                    <?= 
                                        str_replace('_', ' ', 
                                            ucfirst($order['status'])
                                        ) 
                                    ?>
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
                                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                                                <option value="out_for_delivery" <?= $order['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
                                    
                                    <!-- Delete Button (only shown for pending orders) -->
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <button type="submit" name="delete_order" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="animate__animated animate__fadeIn">
                                <i class="fas fa-clipboard-list fa-3x mb-3" style="color: var(--primary-color);"></i>
                                <h4 class="text-muted">No orders found</h4>
                                <p class="text-muted">Try adjusting your filters</p>
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