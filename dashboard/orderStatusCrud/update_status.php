<?php
session_start();
include_once __DIR__ . "/../../config/dbConnection.php";


if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id']) || !isset($_GET['status'])) {
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
$status = $_GET['status'];

$allowed_statuses = ['pending', 'confirmed', 'delivered', 'canceled'];
if (!in_array($status, $allowed_statuses)) {
    header("Location: orders.php");
    exit();
}

$query = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($myConnection, $query);
mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
mysqli_stmt_execute($stmt);

header("Location: order_details.php?order_id=$order_id");
exit();

?>