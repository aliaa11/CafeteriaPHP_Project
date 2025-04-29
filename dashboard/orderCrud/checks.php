<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";

// Initialize variables
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');
$orders = [];
$total_amount = 0;

$user = null;
if ($user_id > 0) {
    $user_query = "SELECT id, username, email FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($myConnection, $user_query);
    mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user = mysqli_fetch_assoc($user_result);
}

$query = "SELECT o.id as order_id, o.order_date, o.status, o.room_number,
          u.username, u.email,
          i.name as item_name, i.price, oi.quantity,
          (i.price * oi.quantity) as item_total
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN items i ON oi.item_id = i.id
          JOIN users u ON o.user_id = u.id
          WHERE o.order_date BETWEEN ? AND ?
          AND o.status NOT IN ('pending', 'cancelled')";

$params = [$date_from, $date_to];
$types = 'ss';

if ($user_id > 0) {
    $query .= " AND o.user_id = ?";
    $params[] = $user_id;
    $types .= 'i';
}

$query .= " ORDER BY o.order_date DESC, o.id";

$stmt = mysqli_prepare($myConnection, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Group orders and items
$grouped_orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $order_id = $row['order_id'];
    
    if (!isset($grouped_orders[$order_id])) {
        $grouped_orders[$order_id] = [
            'order_id' => $order_id,
            'order_date' => $row['order_date'],
            'status' => $row['status'],
            'room_number' => $row['room_number'],
            'username' => $row['username'],
            'email' => $row['email'],
            'items' => [],
            'order_total' => 0
        ];
    }
    
    $grouped_orders[$order_id]['items'][] = [
        'item_name' => $row['item_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'item_total' => $row['item_total']
    ];
    
    $grouped_orders[$order_id]['order_total'] += $row['item_total'];
    $total_amount += $row['item_total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color: #f8a5c2;
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .report-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        
        .filter-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .order-card {
            background-color: var(--card-bg);
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .order-header {
            background-color: rgba(106, 76, 147, 0.1);
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-body {
            padding: 15px;
        }
        
        .item-row {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .order-footer {
            background-color: rgba(106, 76, 147, 0.05);
            padding: 15px;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .status-pending { background-color: #fff0f6; color: #c44569; }
        .status-confirmed { background-color: #e3f2fd; color: #1976d2; }
        .status-delivered { background-color: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background-color: #ffebee; color: #c62828; }
        
        .total-card {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="report-header mb-4">
        <h2><i class="fas fa-file-invoice me-2"></i>Order Details Report</h2>
        <p class="mb-0">Detailed report showing all items in each order</p>
    </div>

    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="0">All Users</option>
                    <?php
                    $users_query = "SELECT id, username FROM users ORDER BY username";
                    $users_result = mysqli_query($myConnection, $users_query);
                    while ($u = mysqli_fetch_assoc($users_result)) {
                        $selected = $user_id == $u['id'] ? 'selected' : '';
                        echo "<option value='{$u['id']}' $selected>{$u['username']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <?php if ($user): ?>
    <div class="alert alert-info mb-4">
        <h5>User: <?= htmlspecialchars($user['username']) ?></h5>
        <p class="mb-1">Email: <?= htmlspecialchars($user['email']) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($grouped_orders)): ?>
        <?php foreach ($grouped_orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-1">Order #<?= $order['order_id'] ?></h5>
                        <p class="mb-1">
                            <i class="far fa-calendar-alt me-1"></i>
                            <?= date('M d, Y H:i', strtotime($order['order_date'])) ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-door-open me-1"></i>
                            Room: <?= htmlspecialchars($order['room_number']) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                        <?php if (!$user_id): ?>
                        <p class="mb-0 mt-2">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($order['username']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="order-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                                <td class="text-end"><?= $item['quantity'] ?></td>
                                <td class="text-end">$<?= number_format($item['item_total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="order-footer">
                <div class="d-flex justify-content-between">
                    <span>Items: <?= count($order['items']) ?></span>
                    <span class="fw-bold">Order Total: $<?= number_format($order['order_total'], 2) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="total-card">
            <div class="d-flex justify-content-between">
                <span>Total Orders: <?= count($grouped_orders) ?></span>
                <span class="fw-bold">Grand Total: $<?= number_format($total_amount, 2) ?></span>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center py-5">
            <i class="fas fa-clipboard-list fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h4>No orders found</h4>
            <p>Try adjusting your filters</p>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mt-4">
        <a href="javascript:window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print me-2"></i>Print Report
        </a>
        <span class="text-muted">Report generated on <?= date('M d, Y H:i') ?></span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Set date_to min value based on date_from
    document.querySelector('input[name="date_from"]').addEventListener('change', function() {
        document.querySelector('input[name="date_to"]').min = this.value;
    });
</script>
</body>
</html>