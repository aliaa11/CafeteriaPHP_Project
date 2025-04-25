<?php
session_start();
include_once 'db.php';

// التأكد من إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب الأوردارات بتاعة المستخدم
$query = "SELECT orders.*, items.name AS item_name, items.price AS item_price 
          FROM orders 
          JOIN items ON orders.item_id = items.id 
          WHERE orders.user_id = $user_id";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Feane Cafeteria</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Navigation Bar */
        .navbar {
            background-color: transparent;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 3;
        }
        .navbar .nav-link {
            color: white;
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
            color: #8d5524;
        }
        .navbar .btn-order-online {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
        }
        .navbar .btn-order-online:hover {
            background-color: #6d3e1a;
        }

        /* Orders Section */
        .orders-section {
            padding: 50px 0;
            background-color: #F5F5DC;
        }
        .heading_container h2 {
            font-family: 'Playfair Display', serif;
            color: #5C4033;
            text-align: center;
        }

        /* Footer */
        footer {
            background-color: #5C4033;
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        footer a {
            color: #d2b48c;
            text-decoration: none;
        }
        footer a:hover {
            color: #8d5524;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-white" href="#">Feane</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">MENU</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">ABOUT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">BOOK TABLE</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_orders.php">MY ORDERS</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="logout.php" class="btn btn-order-online">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Orders Section -->
    <section class="orders-section">
        <div class="container">
            <div class="heading_container">
                <h2>My Orders</h2>
            </div>
            <table class="table">
                <tr>
                    <th>Order ID</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Room Number</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Delete</th>
                    <th>Edit</th>
                </tr>
                <?php while ($order = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['item_name']); ?></td>
                        <td>$<?php echo htmlspecialchars($order['item_price']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars($order['quantity'] * $order['item_price']); ?></td>
                        <td><?php echo htmlspecialchars($order['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><a href="delete_order.php?orderid=<?php echo $order['id']; ?>" class="btn btn-danger">Delete</a></td>
                        <td><a href="edit_order.php?orderid=<?php echo $order['id']; ?>" class="btn btn-warning">Edit</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>






