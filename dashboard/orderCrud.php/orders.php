<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Check admin privileges (uncomment when ready)
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: login.php");
//     exit();
// }


$orders_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $orders_per_page;

// Get filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($date_from) && !empty($date_to)) {
    $where_conditions[] = "o.order_date BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= 'ss';
}

if (!empty($search_query)) {
    $where_conditions[] = "(u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'ss';
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Count total orders for pagination
$count_query = "SELECT COUNT(DISTINCT o.id) as total 
               FROM orders o
               JOIN users u ON o.user_id = u.id
               JOIN order_items oi ON o.id = oi.order_id
               $where_clause";

$count_stmt = mysqli_prepare($myConnection, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_orders = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_orders / $orders_per_page);

// Get orders grouped by user
$orders_query = "SELECT o.id, o.order_date, o.room_number, o.status, 
                u.id as user_id, u.username, 
                SUM(i.price * oi.quantity) as total_price
                FROM orders o
                JOIN users u ON o.user_id = u.id
                JOIN order_items oi ON o.id = oi.order_id
                JOIN items i ON oi.item_id = i.id
                $where_clause
                GROUP BY o.id
                ORDER BY u.username, o.order_date DESC
                LIMIT ? OFFSET ?";

// Add pagination parameters
$params[] = $orders_per_page;
$params[] = $offset;
$types .= 'ii';

$orders_stmt = mysqli_prepare($myConnection, $orders_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($orders_stmt, $types, ...$params);
}
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);

// Group orders by user
$grouped_orders = [];
while ($order = mysqli_fetch_assoc($orders_result)) {
    $grouped_orders[$order['user_id']]['user'] = [
        'id' => $order['user_id'],
        'username' => $order['username']
    ];
    $grouped_orders[$order['user_id']]['orders'][] = $order;
}

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($myConnection, $_POST['new_status']);
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($myConnection, $query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    mysqli_stmt_execute($stmt);
    
    // Redirect to same page with filters preserved
    $query_string = $_SERVER['QUERY_STRING'];
    header("Location: orders.php?$query_string");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
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
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-btn {
            border-radius: 0 5px 5px 0;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #6d3e1a;
        }
        .page-item.active .page-link {
            background-color: #6d3e1a;
            border-color: #6d3e1a;
        }
        .page-link {
            color: #6d3e1a;
        }
        /* Expandable rows styles */
        .user-row {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .user-row:hover {
            background-color: #e9ecef;
        }
        .order-details {
            display: none;
        }
        .order-details.show {
            display: table-row;
        }
        .toggle-icon {
            transition: transform 0.3s ease;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .toggle-icon.rotated {
            transform: rotate(45deg);
        }
        .user-summary {
            font-weight: bold;
        }
        .user-order-count {
            background-color: #6d3e1a;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        .order-row {
            background-color: #fff;
        }
        .order-row:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-orders {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Orders Management</h2>
        
        <!-- Filter Card -->
        <div class="filter-card mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Customer</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Name or email" value="<?= htmlspecialchars($search_query) ?>">
                        <button class="btn btn-outline-secondary search-btn" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30px;"></th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Room</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grouped_orders)): ?>
                        <?php foreach ($grouped_orders as $user_id => $user_data): ?>
                            <?php 
                            $order_count = count($user_data['orders']);
                            $first_order = $user_data['orders'][0];
                            ?>
                            
                            <!-- User Summary Row -->
                            <tr class="user-row" onclick="toggleOrders(<?= $user_id ?>)">
                                <td>
                                    <span class="toggle-icon" id="icon-<?= $user_id ?>">
                                        <?= $order_count > 1 ? '+' : '' ?>
                                    </span>
                                </td>
                                <td class="user-summary">
                                    <?= htmlspecialchars($user_data['user']['username']) ?>
                                    <?php if ($order_count > 1): ?>
                                        <span class="user-order-count"><?= $order_count ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y/m/d h:i A', strtotime($first_order['order_date'])) ?></td>
                                <td><?= htmlspecialchars($first_order['room_number']) ?></td>
                                <td><?= number_format($first_order['total_price'], 2) ?> EGP</td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($first_order['status']) ?>">
                                        <?= ucfirst($first_order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $first_order['id'] ?>">
                                        <select name="new_status" class="form-select form-select-sm d-inline" style="width: auto;">
                                            <option value="pending" <?= $first_order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $first_order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="delivered" <?= $first_order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="canceled" <?= $first_order['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary ms-2">Update</button>
                                    </form>
                                    <a href="order_details.php?order_id=<?= $first_order['id'] ?>" class="btn btn-sm btn-info ms-2">View</a>
                                </td>
                            </tr>

                            <!-- Additional Orders (hidden by default) -->
                            <?php if ($order_count > 1): ?>
                                <?php foreach (array_slice($user_data['orders'], 1) as $order): ?>
                                    <tr class="order-details order-row" id="orders-<?= $user_id ?>">
                                        <td></td>
                                        <td><?= htmlspecialchars($user_data['user']['username']) ?></td>
                                        <td><?= date('Y/m/d h:i A', strtotime($order['order_date'])) ?></td>
                                        <td><?= htmlspecialchars($order['room_number']) ?></td>
                                        <td><?= number_format($order['total_price'], 2) ?> EGP</td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <select name="new_status" class="form-select form-select-sm d-inline" style="width: auto;">
                                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                    <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary ms-2">Update</button>
                                            </form>
                                            <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-info ms-2">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-orders">No orders found matching your criteria</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <!-- Previous Page -->
                <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">
                        &laquo;
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" 
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" 
                       href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">
                        &raquo;
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleOrders(userId) {
            const orders = document.querySelectorAll(`#orders-${userId}`);
            const icon = document.getElementById(`icon-${userId}`);
            
            orders.forEach(order => {
                order.classList.toggle('show');
            });
            
            if (icon) {
                icon.classList.toggle('rotated');
                icon.textContent = icon.classList.contains('rotated') ? '-' : '+';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            dateFrom.addEventListener('change', function() {
                dateTo.min = this.value;
                if (dateTo.value && dateTo.value < this.value) {
                    dateTo.value = this.value;
                }
            });
        });
    </script>
</body>
</html>