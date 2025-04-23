<?php
    include_once __DIR__ . "/../../config/dbConnection.php";
    if (!$myConnection) {
      die("Database connection failed");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
       <?php if(isset($_GET['status']) && isset($_GET['message'])): ?>
        <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars(urldecode($_GET['message'])) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
        <h1 class="text-center mb-4">Products Management</h1>
        
        <form method="GET" class="mb-4">
            <div class="d-flex justify-content-between">
                <div class="w-75">
                    <div class="input-group">
                        <select class="form-select w-25" name="search_by">
                            <option value="name" <?= isset($_GET['search_by']) && $_GET['search_by'] == 'name' ? 'selected' : '' ?>>Search by Name</option>
                            <option value="category" <?= isset($_GET['search_by']) && $_GET['search_by'] == 'category' ? 'selected' : '' ?>>Search by Category</option>
                        </select>
                        <input type="text" class="form-control" name="search_term" value="<?= isset($_GET['search_term']) ? ($_GET['search_term']) : '' ?>" placeholder="Enter search term">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add Product
                </button>
            </div>
        </form>


        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $sql = "SELECT p.*, c.name as category_name FROM items p 
                    LEFT JOIN categories c ON p.category_id = c.id";
            
            if (isset($_GET['search_term'])) {
                $search_term = mysqli_real_escape_string($myConnection, $_GET['search_term']);
                $search_by = mysqli_real_escape_string($myConnection, $_GET['search_by'] ?? 'name');
                
                if ($search_by === 'name') {
                    $sql .= " WHERE p.name LIKE '%$search_term%'";
                } else if ($search_by === 'category') {
                    $sql .= " WHERE c.name LIKE '%$search_term%'";
                }
            }
            
            $result = mysqli_query($myConnection, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <div class="card-img-container">
                                <?php if(!empty($product['image_url'])): ?>
                                    <img src="<?= ($product['image_url']) ?>" class="card-img-top product-img" alt="<?= ($product['name']) ?>">
                                <?php else: ?>
                                    <div class="product-img bg-secondary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-box-open fa-4x text-light"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= ($product['name']) ?></h5>
                                <p class="card-text"><?= substr(($product['description']), 0, 100) ?>...</p>
                                <p class="text-success fw-bold">Price: $<?= number_format($product['price'], 2) ?></p>
                                <div class="stock-info ">
                                    Stock: <?= ($product['stock']) ?>
                                </div>
                                <p class="text-muted">Category: <?= ($product['category_name']) ?></p>
                            </div>
                            <div class="card-footer bg-transparent">
                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary me-2">Edit</a>       
                            <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No products found</div></div>';
            }
            ?>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" step="0.01" class="form-control" name="stock" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="" selected disabled>Select a category</option>
                                <?php
                                $categories = mysqli_query($myConnection, "SELECT * FROM categories");
                                while($category = mysqli_fetch_assoc($categories)) {
                                    echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <div class="form-text">Allowed formats: jpg, png, gif, svg</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
if(isset($_POST['addProduct'])) {
    $name = mysqli_real_escape_string($myConnection, $_POST['name']);
    $description = mysqli_real_escape_string($myConnection, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category']);
    
    if(empty($name) || empty($price) || empty($category_id)) {
        echo "<div class='alert alert-danger'>Please fill all required fields</div>";
        exit();
    }
    
    if(isset($_FILES['image'])) {
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileError = $_FILES['image']['error'];
        
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        
        if(in_array($fileExt, $allowed)) {
            if($fileError === 0) {
                if($fileSize < 200000000) { 
                    $fileNameNew = uniqid('', true).'.'.$fileExt;
                    $fileDestination = '../uploads/products/'.$fileNameNew;
                    
                    if(move_uploaded_file($fileTmp, $fileDestination)) {
                        $sql = "INSERT INTO items (name, description, price, stock, image_url,category_id) 
                                VALUES ('$name', '$description', $price, $stock, '$fileDestination',$category_id)";
                        
                        if(mysqli_query($myConnection, $sql)) {
                            echo "<div class='alert alert-success'>Product added successfully</div>";
                            echo "<script>window.location.href = 'products.php';</script>";
                        } else {
                            echo "<div class='alert alert-danger'>Error adding product: ".mysqli_error($myConnection)."</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Error uploading file</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>File is too large (max 5MB)</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error uploading file</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Invalid file type. Allowed: jpg, png, gif, svg</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Please select an image</div>";
    }
}
?>