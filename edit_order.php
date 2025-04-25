<?php
session_start();
<<<<<<< HEAD
<<<<<<< HEAD
include_once './config/dbConnection.php';

<<<<<<< HEAD
=======
include_once 'db.php';
=======
include_once './config/dbConnection.php';
>>>>>>> 000af98 (order status done)

// التأكد من إن المستخدم مسجل دخول
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
=======
>>>>>>> f3c3ffe (order status done)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

<<<<<<< HEAD
<<<<<<< HEAD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
=======
=======
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my_orders.php");
    exit();
}

$order_id = $_POST['order_id'];
>>>>>>> f3c3ffe (order status done)
$user_id = $_SESSION['user_id'];
$room_number = $_POST['room_number'];
$quantities = $_POST['quantities'];

// Validate order belongs to user and is pending
$check_query = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($myConnection, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

<<<<<<< HEAD
if (!$order) {
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
    header("Location: my_orders.php");
    exit();
}

<<<<<<< HEAD
$order_id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];
$room_number = $_POST['room_number'];
$quantities = $_POST['quantities'];

// Validate order belongs to user and is pending
$check_query = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($myConnection, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order || $order['status'] !== 'pending') {
    $_SESSION['error'] = "Order cannot be updated";
    header("Location: order_details.php?order_id=" . $order_id);
    exit();
}

=======
if (!$order || $order['status'] !== 'pending') {
    $_SESSION['error'] = "Order cannot be updated";
    header("Location: order_details.php?order_id=" . $order_id);
    exit();
}

>>>>>>> f3c3ffe (order status done)
$update_query = "UPDATE orders SET room_number = ? WHERE id = ?";
$stmt = mysqli_prepare($myConnection, $update_query);
mysqli_stmt_bind_param($stmt, "si", $room_number, $order_id);
mysqli_stmt_execute($stmt);
<<<<<<< HEAD

foreach ($quantities as $item_id => $quantity) {
    $quantity = (int)$quantity;
    if ($quantity < 1) continue;
    
    $update_item_query = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND item_id = ?";
    $stmt = mysqli_prepare($myConnection, $update_item_query);
    mysqli_stmt_bind_param($stmt, "iii", $quantity, $order_id, $item_id);
    mysqli_stmt_execute($stmt);
}

$_SESSION['success'] = "Order updated successfully";
header("Location: order_details.php?order_id=" . $order_id);
exit();
?>
=======
if (isset($_POST['btn'])) {
    $quantity = $_POST['quantity'];
    $room_number = $_POST['room_number'];
=======
>>>>>>> f3c3ffe (order status done)

foreach ($quantities as $item_id => $quantity) {
    $quantity = (int)$quantity;
    if ($quantity < 1) continue;
    
    $update_item_query = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND item_id = ?";
    $stmt = mysqli_prepare($myConnection, $update_item_query);
    mysqli_stmt_bind_param($stmt, "iii", $quantity, $order_id, $item_id);
    mysqli_stmt_execute($stmt);
}

<<<<<<< HEAD
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
=======
$_SESSION['success'] = "Order updated successfully";
header("Location: order_details.php?order_id=" . $order_id);
exit();
?>
>>>>>>> f3c3ffe (order status done)
