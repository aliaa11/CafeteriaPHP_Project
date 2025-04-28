<?php
session_start();
include_once './config/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my_orders.php");
    exit();
}

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

// Begin transaction
mysqli_begin_transaction($myConnection);

try {
    // Update room number
    $update_query = "UPDATE orders SET room_number = ? WHERE id = ?";
    $stmt = mysqli_prepare($myConnection, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $room_number, $order_id);
    mysqli_stmt_execute($stmt);

    // Update quantities
    foreach ($quantities as $item_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity < 1) continue;
        
        $update_item_query = "UPDATE order_items SET quantity = ? WHERE order_id = ? AND item_id = ?";
        $stmt = mysqli_prepare($myConnection, $update_item_query);
        mysqli_stmt_bind_param($stmt, "iii", $quantity, $order_id, $item_id);
        mysqli_stmt_execute($stmt);
    }

    // Commit transaction
    mysqli_commit($myConnection);
    $_SESSION['success'] = "Order updated successfully";
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($myConnection);
    $_SESSION['error'] = "Failed to update order: " . $e->getMessage();
}

header("Location: order_details.php?order_id=" . $order_id);
exit();
?>








