<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";
$usersList = mysqli_query($myConnection, "SELECT id, username FROM users ORDER BY username");
$itemsList = mysqli_query($myConnection, "SELECT id, name, price FROM items ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_POST['user_id'];
    $selectedItems = $_POST['items'];
    $room = mysqli_real_escape_string($myConnection, $_POST['room']);
    $status = mysqli_real_escape_string($myConnection, $_POST['status']);

    mysqli_begin_transaction($myConnection);

    try {
        $orderSQL = "INSERT INTO orders (user_id, room_number, status) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($myConnection, $orderSQL);
        mysqli_stmt_bind_param($stmt, "iss", $uid, $room, $status);
        mysqli_stmt_execute($stmt);
        $orderID = mysqli_insert_id($myConnection);

        foreach ($selectedItems as $itemID => $qty) {
            if ($qty > 0) {
                $itemSQL = "INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($myConnection, $itemSQL);
                mysqli_stmt_bind_param($stmt, "iii", $orderID, $itemID, $qty);
                mysqli_stmt_execute($stmt);
            }
        }

        mysqli_commit($myConnection);
        $_SESSION['flash_message'] = [
            'message' => 'Order created successfully!',
            'type' => 'success'
        ];
        header("Location: orders.php");
        exit();
    } catch (Exception $e) {
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Order</title>
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
    </style>
</head>
<body>
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title animate__animated animate__fadeInLeft">
                <i class="fas fa-plus-circle me-2"></i>Create New Order
            </h2>
            <div class="animate__animated animate__fadeInRight">
                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        </div>

        <div class="card animate__animated animate__fadeInUp">
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-user me-2"></i>Select Customer</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                <?php while ($u = mysqli_fetch_assoc($usersList)): ?>
                                    <option value="<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['username']) ?> (ID: <?= $u['id'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="invalid-feedback">Please select a customer.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-door-open me-2"></i>Room Number</label>
                            <input type="text" name="room" class="form-control" required>
                            <div class="invalid-feedback">Room number is required.</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-info-circle me-2"></i>Status</label>
                            <div class="status-badge status-pending">Pending</div>
                            <input type="hidden" name="status" value="pending">
                        </div>
                    </div>

                    <h4 class="mb-3" style="color: var(--primary-color);">
                        <i class="fas fa-utensils me-2"></i>Select Items
                    </h4>

                    <div class="row">
                        <?php while ($item = mysqli_fetch_assoc($itemsList)): ?>
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
