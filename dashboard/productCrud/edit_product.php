<?php
include_once __DIR__ . "/../../config/dbConnection.php";



define('UPLOAD_DIR', '/opt/lampp/htdocs/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/');
define('PUBLIC_URL', '/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/');
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
    $description = $_POST['description'];
    $price = $_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $category_id = $_POST['category'];
    
    // Initialize with current image
    $image_path = $product['image_url'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ["png", "jpg", "gif", "svg", "jpeg", "webp"];
        
        if (in_array($fileExt, $allowedExt) && $fileSize < 5000000) {
            $newFilename = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-]/", "", $fileName);
            $targetPath = UPLOAD_DIR . $newFilename;
            
            if (move_uploaded_file($fileTmp, $targetPath)) {
                // Delete old image if it exists
                if (!empty($product['image_url'])) {
                    $oldImagePath = UPLOAD_DIR . $product['image_url'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $image_path = $newFilename;
            }
        }
    }
    
    $update_query = "UPDATE items SET 
                    name = '$name', 
                    description = '$description', 
                    price = $price, 
                    is_available = $is_available, 
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color: #f8a5c2;
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
            --text-dark: #2d3436;
            --text-light: #f5f6fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(135deg, #f9f7f7 0%, #e8f4f8 100%);
        }
        
        .header-title {
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .header-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--accent-color);
            border-radius: 3px;
        }
        
        .form-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
            transition: all 0.3s ease;
        }
        
        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #5a3d7a;
            border-color: #5a3d7a;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .product-preview {
            max-width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .status-available {
            background-color: #e8f7f0;
            color: #218c74;
            border: 1px solid #7bed9f;
        }
        
        .status-unavailable {
            background-color: #ffebee;
            color: #c0392b;
            border: 1px solid #ff7675;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(106, 76, 147, 0.25);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-edit me-2"></i>Edit Product</h2>
            <div class="animate__animated animate__fadeInRight">
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger animate__animated animate__fadeInDown">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-card animate__animated animate__fadeInUp">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tag me-2"></i>Product Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-align-left me-2"></i>Description</label>
                            <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-image me-2"></i>Product Image</label>
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="../../Public/uploads/products/<?= $product['image_url'] ?>" class="product-preview mb-3" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <div class="product-preview bg-light d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-box-open fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="form-text text-muted">Leave blank to keep current image. Allowed formats: jpg, png, gif, svg, webp</div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-dollar-sign me-2"></i>Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" value="<?= $product['price'] ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tags me-2"></i>Category</label>
                            <select class="form-select" name="category" required>
                                <option value="" disabled>Select a category</option>
                                <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label d-block"><i class="fas fa-check-circle me-2"></i>Status</label>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_available" id="is_available" <?= $product['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_available">
                                    <span class="status-badge <?= $product['is_available'] ? 'status-available' : 'status-unavailable' ?>">
                                        <?= $product['is_available'] ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" name="updateProduct" class="btn btn-primary me-3">
                        <i class="fas fa-save me-2"></i>Update Product
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.querySelector('input[name="image"]').addEventListener('change', function(e) {
            const preview = document.querySelector('.product-preview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'product-preview mb-3';
                        preview.parentNode.replaceChild(img, preview);
                    }
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>



