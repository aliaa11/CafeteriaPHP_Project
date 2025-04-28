<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Initialize variables
$total_pages = 1;
$total_orders = 0;
$grouped_orders = [];
$flash_message = null;

// Delete completed orders if requested
if (isset($_GET['delete_completed'])) {
    $delete_query = "DELETE FROM orders WHERE status != 'pending'";
    mysqli_query($myConnection, $delete_query);
    
    $_SESSION['flash_message'] = [
        'message' => 'Completed orders have been deleted successfully!',
        'type' => 'success'
    ];
    header("Location: orders.php");
    exit();
}

// Pagination and filtering setup
$orders_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $orders_per_page;

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE conditions
$where_conditions = ["o.status = 'pending'"];
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

// Count total orders
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

// Fetch orders
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
while ($order = mysqli_fetch_assoc($orders_result)) {
    $grouped_orders[$order['user_id']]['user'] = [
        'id' => $order['user_id'],
        'username' => $order['username']
    ];
    $grouped_orders[$order['user_id']]['orders'][] = $order;
}

// Status update handling
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($myConnection, $_POST['new_status']);
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($myConnection, $query);
    mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    mysqli_stmt_execute($stmt);
    
    $_SESSION['flash_message'] = [
        'message' => 'Order status updated successfully!',
        'type' => 'success'
    ];
    header("Location: orders.php?" . $_SERVER['QUERY_STRING']);
    exit();
}

// Check for flash messages
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>


<style>
  
    .order-details {
        display: none;
    }
    
    .order-details.show {
        display: table-row;
    }
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
        }
        
        .user-row {
            background-color: rgba(106, 76, 147, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .user-row:hover {
            background-color: rgba(106, 76, 147, 0.1);
        }
        
        .order-row {
            background-color: var(--card-bg);
        }
        
        .order-row:nth-child(even) {
            background-color: rgba(249, 247, 247, 0.7);
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
            background-color: #e8f7f0;
            color: #218c74;
            border: 1px solid #7bed9f;
        }
        
        .status-delivered {
            background-color: #e3f3fd;
            color: #2980b9;
            border: 1px solid #74b9ff;
        }
        
        .status-canceled {
            background-color: #ffebee;
            color: #c0392b;
            border: 1px solid #ff7675;
        }
        
        .toggle-icon {
            cursor: pointer;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            background-color: rgba(106, 76, 147, 0.1);
        }
        
        .toggle-icon:hover {
            background-color: rgba(106, 76, 147, 0.2);
            transform: rotate(90deg);
        }
        
        .user-summary {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .user-order-count {
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8rem;
            margin-left: 8px;
        }
        
        .cleanup-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--accent-color);
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 350px;
        }
        
        .alert {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            border-left: 4px solid;
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
    </style>
</head>
<body>
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-clipboard-list me-2"></i>Orders Management</h2>
            <div class="animate__animated animate__fadeInRight">
                <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="fas fa-question-circle me-2"></i>Help
                </a>
            </div>
        </div>
        
        <!-- Filter Card -->
        <div class="filter-card animate__animated animate__fadeInUp">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label fw-medium"><i class="far fa-calendar-alt me-2"></i>From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label fw-medium"><i class="far fa-calendar-alt me-2"></i>To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label fw-medium"><i class="fas fa-search me-2"></i>Search Customer</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Name or email" value="<?= htmlspecialchars($search_query) ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-3"><i class="fas fa-check-circle me-2"></i>Apply Filters</button>
                    <a href="orders.php" class="btn btn-outline-secondary"><i class="fas fa-sync-alt me-2"></i>Reset</a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive animate__animated animate__fadeInUp">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Room</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
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
                            <tr class="user-row animate__animated animate__fadeIn">
                            <td>
                                <span class="toggle-icon" id="icon-<?= $user_id ?>" onclick="toggleOrders(<?= $user_id ?>, event)">
                                    <?= $order_count > 1 ? '+' : '' ?>
                                </span>
                            </td>
                                <td class="user-summary">
                                    <i class="far fa-user me-2"></i><?= htmlspecialchars($user_data['user']['username']) ?>
                                    <?php if ($order_count > 1): ?>
                                        <span class="user-order-count"><?= $order_count ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><i class="far fa-clock me-2"></i><?= date('Y/m/d h:i A', strtotime($first_order['order_date'])) ?></td>
                                <td><i class="fas fa-door-open me-2"></i><?= htmlspecialchars($first_order['room_number']) ?></td>
                                <td><i class="fas fa-receipt me-2"></i>$ <?= number_format($first_order['total_price'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($first_order['status']) ?>">
                                        <?= ucfirst($first_order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <form method="post" class="me-2">
                                            <input type="hidden" name="order_id" value="<?= $first_order['id'] ?>">
                                          
                                        </form>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary me-2"><i class="fas fa-save"></i></button>
                                        <a href="order_details.php?order_id=<?= $first_order['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Additional Orders (hidden by default) -->
                            <?php if ($order_count > 1): ?>
                                <?php foreach (array_slice($user_data['orders'], 1) as $order): ?>
                                    <tr class="order-details order-row animate__animated animate__fadeIn" id="orders-<?= $user_id ?>">
                                        <td></td>
                                        <td><i class="far fa-user me-2"></i><?= htmlspecialchars($user_data['user']['username']) ?></td>
                                        <td><i class="far fa-clock me-2"></i><?= date('Y/m/d h:i A', strtotime($order['order_date'])) ?></td>
                                        <td><i class="fas fa-door-open me-2"></i><?= htmlspecialchars($order['room_number']) ?></td>
                                        <td><i class="fas fa-receipt me-2"></i><?= number_format($order['total_price'], 2) ?> EGP</td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <form method="post" class="me-2">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select name="new_status" class="form-select form-select-sm">
                                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                                    </select>
                                                </form>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary me-2"><i class="fas fa-save"></i></button>
                                                <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="animate__animated animate__fadeIn">
                                    <i class="fas fa-clipboard-list fa-3x mb-3" style="color: var(--primary-color);"></i>
                                    <h4 class="text-muted">No pending orders found</h4>
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
        
        <!-- Cleanup Section -->
        <div class="cleanup-section animate__animated animate__fadeInUp">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><i class="fas fa-database me-2"></i>Database Maintenance</h5>
                    <p class="text-muted mb-0">Remove completed orders (non-pending) from the system</p>
                </div>
                <button class="btn btn-danger pulse" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                    <i class="fas fa-trash-alt me-2"></i>Cleanup Orders
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete all non-pending orders?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cancel</button>
                    <a href="?delete_completed=1" class="btn btn-danger"><i class="fas fa-trash-alt me-2"></i>Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel"><i class="fas fa-question-circle me-2"></i>Help Center</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6><i class="fas fa-filter me-2"></i>Filtering Orders</h6>
                    <p>Use the date range and search fields to filter orders by date or customer information.</p>
                    
                    <h6 class="mt-4"><i class="fas fa-sync-alt me-2"></i>Status Management</h6>
                    <p>Update order status using the dropdown menu and save button for each order.</p>
                    
                    <h6 class="mt-4"><i class="fas fa-trash-alt me-2"></i>Database Cleanup</h6>
                    <p>The cleanup button removes all completed orders from the system.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-check me-2"></i>Got it!</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show flash message if exists
        <?php if (isset($flash_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('flashMessageContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${'<?= $flash_message['type'] ?>'} show`;
            alert.innerHTML = `
                <strong><i class="fas fa-${'<?= $flash_message['type'] === 'success' ? 'check' : 'exclamation' ?>'-circle me-2"></i>${'<?= ucfirst($flash_message['type']) ?>'}!</strong> 
                ${'<?= $flash_message['message'] ?>'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            container.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
        <?php endif; ?>

        function toggleOrders(userId, event) {
            if (event) {
                event.stopPropagation();
            }
            
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
            // Date pickers
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            
            if (dateFrom && dateTo) {
                dateFrom.addEventListener('change', function() {
                    dateTo.min = this.value;
                    if (dateTo.value && dateTo.value < this.value) {
                        dateTo.value = this.value;
                    }
                });
            }
            
            // Add animation to table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>