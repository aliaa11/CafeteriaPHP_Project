<?php
session_start();
include_once 'db.php';

// التأكد من إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['orderid'];

$delete_query = "DELETE FROM orders WHERE id = $order_id AND user_id = $user_id";
mysqli_query($connection, $delete_query);

header("Location: my_orders.php");
exit();

mysqli_close($connection);
?>