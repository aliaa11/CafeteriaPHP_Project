<?php
include_once __DIR__ . "/../../config/dbConnection.php";

if (!$myConnection) {
    die("Database connection failed");
}

if(isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    $sql = "SELECT image_url FROM items WHERE id = $product_id";
    $result = mysqli_query($myConnection, $sql);
    
    if(mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        
        $sql = "DELETE FROM items WHERE id = $product_id";
        if(mysqli_query($myConnection, $sql)) {
   
            header("Location: products.php?status=success&message=Product+deleted+successfully");
            exit();
        } else {
            header("Location: products.php?status=error&message=" . urlencode("Error deleting product: " . mysqli_error($myConnection)));
            exit();
        }
    } else {
        header("Location: products.php?status=error&message=" . urlencode("Product not found"));
        exit();
    }
} else {
    header("Location: products.php?status=error&message=" . urlencode("No product ID provided"));
    exit();
}
?>