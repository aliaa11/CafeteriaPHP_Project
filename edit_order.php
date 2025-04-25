<?php
session_start();
include_once './config/dbConnection.php';

// التأكد من إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['orderid'];

$query = "SELECT orders.*, items.name AS item_name, items.price AS item_price 
          FROM orders 
          JOIN items ON orders.item_id = items.id 
          WHERE orders.id = $order_id AND orders.user_id = $user_id";
$order_result = mysqli_query($myConnection, $query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header("Location: my_orders.php");
    exit();
}

if (isset($_POST['btn'])) {
    $quantity = $_POST['quantity'];
    $room_number = $_POST['room_number'];

    $update_query = "UPDATE orders SET quantity = $quantity, room_number = '$room_number' WHERE id = $order_id AND user_id = $user_id";
    mysqli_query($myConnection, $update_query);
    header("Location: my_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - Feane Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <style>
        body {
            background-color: #F5F5DC;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Edit Order Section */
        .edit-section {
            background-color: #5C4033;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 100px auto 30px auto;
            max-width: 600px;
        }
        .edit-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #8d5524;
            padding-bottom: 10px;
            text-align: center;
        }
        .edit-section .form-label {
            font-family: 'Playfair Display', serif;
            color: #d2b48c;
        }
        .edit-section .form-control {
            background-color: #6d4c41;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px;
        }
        .edit-section .form-control:focus {
            background-color: #6d4c41;
            color: white;
            box-shadow: none;
            border: 1px solid #8d5524;
        }
        .edit-section .form-control[readonly] {
            opacity: 0.7;
        }
        .edit-section .btn-primary {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 10px;
            font-weight: bold;
            border-radius: 25px;
            width: 100%;
            transition: background-color 0.3s;
        }
        .edit-section .btn-primary:hover {
            background-color: #6d3e1a;
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
    <div class="edit-section">
        <h1>Edit Order</h1>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Item</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['item_name']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($order['quantity']); ?>" min="1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_number" class="form-control" value="<?php echo htmlspecialchars($order['room_number']); ?>" required>
            </div>
            <button type="submit" name="btn" class="btn btn-primary">Update</button>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>



