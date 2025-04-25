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
=======
>>>>>>> f3c3ffe (order status done)
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = $_GET['order_id'];
<<<<<<< HEAD
$user_id = $_SESSION['user_id'];

// Check if order belongs to user and is pending
$check_query = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($myConnection, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order || $order['status'] !== 'pending') {
    $_SESSION['error'] = "Order cannot be cancelled";
    header("Location: my_orders.php");
    exit();
}

// Delete order items first
$delete_items_query = "DELETE FROM order_items WHERE order_id = ?";
$stmt = mysqli_prepare($myConnection, $delete_items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

// Then delete the order
$delete_order_query = "DELETE FROM orders WHERE id = ?";
$stmt = mysqli_prepare($myConnection, $delete_order_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

$_SESSION['success'] = "Order cancelled successfully";
header("Location: my_orders.php");
exit();
=======
=======
>>>>>>> f3c3ffe (order status done)
$user_id = $_SESSION['user_id'];

// Check if order belongs to user and is pending
$check_query = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($myConnection, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order || $order['status'] !== 'pending') {
    $_SESSION['error'] = "Order cannot be cancelled";
    header("Location: my_orders.php");
    exit();
}

// Delete order items first
$delete_items_query = "DELETE FROM order_items WHERE order_id = ?";
$stmt = mysqli_prepare($myConnection, $delete_items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

// Then delete the order
$delete_order_query = "DELETE FROM orders WHERE id = ?";
$stmt = mysqli_prepare($myConnection, $delete_order_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

$_SESSION['success'] = "Order cancelled successfully";
header("Location: my_orders.php");
exit();
<<<<<<< HEAD

<<<<<<< HEAD
mysqli_close($connection);
>>>>>>> f5d4e80 (editorder,deletorder,upateorder&homepages)
=======
mysqli_close($myConnection);
>>>>>>> 000af98 (order status done)
=======
>>>>>>> f3c3ffe (order status done)
?>