<?php
session_start();
include_once './config/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = (int)$_GET['order_id'];
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

// Begin transaction
mysqli_begin_transaction($myConnection);

try {
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

    // Commit transaction
    mysqli_commit($myConnection);
    $_SESSION['success'] = "Order cancelled successfully";
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($myConnection);
    $_SESSION['error'] = "Failed to cancel order: " . $e->getMessage();
}

header("Location: my_orders.php");
exit();
?>