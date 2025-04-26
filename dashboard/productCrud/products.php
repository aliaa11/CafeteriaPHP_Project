<?php
    include_once __DIR__ . "/../../config/dbConnection.php";
    if (!$myConnection) {
      die("Database connection failed");
  }
  $items_per_page = 6; 
  $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
  if ($current_page < 1) $current_page = 1;
  $offset = ($current_page - 1) * $items_per_page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-card {
            transition: transform 0.2s;
            overflow:hidden;
        }
        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 300px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .product-img:hover {
            transform : scale(1.1);
        }
        .out-of-stock {
            opacity: 0.7;
            background-color: #f8f9fa;
        }
        .text-unavailable {
            color: #dc3545;
        }
        .text-available {
            color: #28a745;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Items Management</h1>
        <!-- Search  -->
        <form method="GET" class="mb-4 needs-validation" novalidate>
            <div class="d-flex justify-content-between">
                <div class="w-75">
                    <div class="input-group">
                        <select class="form-select w-25" name="search_by">
                            <option value="name" <?= isset($_GET['search_by']) && $_GET['search_by'] == 'name' ? 'selected' : '' ?>>Search by Name</option>
                            <option value="category" <?= isset($_GET['search_by']) && $_GET['search_by'] == 'category' ? 'selected' : '' ?>>Search by Category</option>
                        </select>
                        <input type="text" class="form-control" name="search_term" value="<?= isset($_GET['search_term']) ? $_GET['search_term'] : '' ?>" placeholder="Enter search term">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </div>
            <button class="btn btn-success mt-4" type="button" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add Product
            </button>
            <input type="hidden" name="page" value="1">
        </form>
        <!-- display items -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            //=============================
            //queries
            $sql = "SELECT p.*, c.name as category_name FROM items p 
                    LEFT JOIN categories c ON p.category_id = c.id";
            $count_sql = "SELECT COUNT(*) as total FROM items p 
                    LEFT JOIN categories c ON p.category_id = c.id";
            // ============================
            if (isset($_GET['search_term'])) {
                $search_term = $_GET['search_term'];
                $search_by = $_GET['search_by'] ?? 'name';
                if ($search_by === 'name') {
                    $sql .= " WHERE p.name LIKE '%$search_term%'";
                    $count_sql .= " WHERE p.name LIKE '%$search_term%'";
                } else if ($search_by === 'category') {
                    $sql .= " WHERE c.name LIKE '%$search_term%'";
                    $count_sql .= " WHERE c.name LIKE '%$search_term%'";
                }
            }

            $sql .= " LIMIT $offset, $items_per_page";
            $count_result = mysqli_query($myConnection, $count_sql);
            $total_items = mysqli_fetch_assoc($count_result)['total'];
            $total_pages = ceil($total_items / $items_per_page);
            $result = mysqli_query($myConnection, $sql);

            if (mysqli_num_rows($result) > 0) {
            while ($product = mysqli_fetch_assoc($result)) {
                $is_available = $product['is_available'];
                ?>
                
                <div class="col">
                    <div class="card h-100 product-card <?= !$is_available ? 'out-of-stock' : '' ?>">
                        <div class="card-img-container">
                            <?php if(!empty($product['image_url'])): ?>
                                <img src="<?= $product['image_url'] ?>" class="card-img-top product-img" alt="<?= $product['name'] ?>">
                            <?php else: ?>
                                <div class="product-img bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-box-open fa-4x text-light"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <p class="card-text"><?= substr($product['description'], 0, 100) ?>...</p>
                            <p class="text-success fw-bold">Price: $<?= number_format($product['price'], 2) ?></p>
                            <div class="availability-info">
                                Status: <span class="<?= $is_available ? 'text-available' : 'text-unavailable' ?>">
                                    <?= $is_available ? 'Available' : 'Unavailable' ?>
                                </span>
                            </div>
                            <p class="text-muted">Category: <?= $product['category_name'] ?></p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-primary me-2">Edit</a>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $product['id'] ?>">Delete</button>
                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteModal<?= $product['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete this product?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-danger">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No products found</div></div>';
            }
            ?>
        </div>
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <!-- Add Form -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name" required>
                            <div class="invalid-feedback">Please enter the product name.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
                            <div class="invalid-feedback">Please enter the price.</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_available" id="is_available" checked>
                            <label class="form-check-label" for="is_available">Available</label>
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
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="invalid-feedback">Please upload an image.</div>
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
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $category = $_POST['category'] ?? '';
    
    if(empty($name) || empty($price) || empty($category)) {
        echo "<div class='alert alert-danger'>Please fill all required fields</div>";
        exit();
    }
    
    if(isset($_FILES['image'])) {
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileError = $_FILES['image']['error'];
        
        if(!empty($fileName) && !empty($fileTmp)) {
            $fileArray = explode(".", $fileName);
            $lastElementExt = strtolower(end($fileArray)); 
            $arr = ["png", "jpg", "gif", "svg"];
            
            if(in_array($lastElementExt,$arr)) {
                if($fileError === 0) {
                    if($fileSize < 200000000) { 
                        $fileDestination = '../uploads/products/'.time().$fileName;
                        if(move_uploaded_file($fileTmp, $fileDestination)) {
                            $sql = "INSERT INTO items (name, description, price, is_available, image_url, category_id) 
                            VALUES ('$name', '$description', $price, $is_available, '$fileDestination', $category)";
                            if(mysqli_query($myConnection, $sql)) {
                                echo "<div class='alert alert-success'>Product added successfully</div>";
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
            $sql = "INSERT INTO items (name, description, price, is_available, category_id) 
                    VALUES ('$name', '$description', $price, $is_available, $category)";
            if(mysqli_query($myConnection, $sql)) {
                echo "<div class='alert alert-success'>Product added successfully</div>";
            } else {
                echo "<div class='alert alert-danger'>Error adding product: ".mysqli_error($myConnection)."</div>";
            }
        }
    } else {
        $sql = "INSERT INTO items (name, description, price, is_available, category_id) 
                VALUES ('$name', '$description', $price, $is_available, $category)";
        if(mysqli_query($myConnection, $sql)) {
            echo "<div class='alert alert-success'>Product added successfully</div>";
        } else {
            echo "<div class='alert alert-danger'>Error adding product: ".mysqli_error($myConnection)."</div>";
        }
    }
}
?>