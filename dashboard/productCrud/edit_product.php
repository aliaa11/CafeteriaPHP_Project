<?php
include_once __DIR__ . "/../../config/dbConnection.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

$product_query = "SELECT * FROM items WHERE id = $product_id";
$product_result = mysqli_query($myConnection, $product_query);

if (mysqli_num_rows($product_result) === 0) {
    header("Location: products.php");
    exit();
}

$product = mysqli_fetch_assoc($product_result);

$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($myConnection, $categories_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProduct'])) {
    $name = $_POST['name'];
    $description =$_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category'];
    
    $image_path = $product['image_url'];
    
    if (isset($_FILES['image'])) {
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileArray = explode(".", $fileName);
        $lastElementExt = strtolower(end($fileArray)); 
        $arr = ["png", "jpg", "gif", "svg"];
        
        if (in_array($lastElementExt, $arr) && $fileSize < 5000000) {
            $fileNameNew = uniqid('', true).'.'.$fileExt;
            $UpdatedImage = '../uploads/products/'.time().$fileName;
            
            if (move_uploaded_file($fileTmp, $UpdatedImage)) {
                $image_path = $UpdatedImage;
            }
        }
    }
    
    $update_query = "UPDATE items SET 
                    name = '$name', 
                    description = '$description', 
                    price = $price, 
                    stock = $stock, 
                    category_id = $category_id, 
                    image_url = '$image_path' 
                    WHERE id = $product_id";
    
    if (mysqli_query($myConnection, $update_query)) {
        header("Location: products.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Error updating product: " . mysqli_error($myConnection);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-preview {
            max-width: 200px;
            max-height: 200px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Edit Product</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <option value="" disabled>Select a category</option>
                            <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <?php if (!empty($product['image_url'])): ?>
                            <div>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-preview">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <div class="form-text">Leave blank to keep current image. Allowed formats: jpg, png, gif, svg</div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="updateProduct" class="btn btn-primary">Update Product</button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>