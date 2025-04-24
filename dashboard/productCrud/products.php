<?php
    include_once __DIR__ . "/../../config/dbConnection.php";
    if (!$myConnection) {
      die("Database connection failed");
  }
<<<<<<< HEAD
<<<<<<< HEAD
  $items_per_page = 6; 
  $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
=======
  $items_per_page = 4; 
=======
  $items_per_page = 3; 
<<<<<<< HEAD
>>>>>>> 0d05dec (Product Crud Done for all requirment)
  $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
>>>>>>> 5baea74 (Product Crud Done for all requirment)
=======
  $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
>>>>>>> c88e851 (update product done)
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color:rgb(243, 130, 169);
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
            --text-dark: #2d3436;
            --text-light: #f5f6fa;
        }
        .alert-warning {
    border-left-color: #ffc107;
}

.alert-dismissible .btn-close {
    position: absolute;
    top: 10px;
    right: 10px;
}

.alert p {
    margin-bottom: 1rem;
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
        
        .filter-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
            transition: all 0.3s ease;
        }
        
        .filter-card:hover {
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
        
        .btn-success {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background-color:rgb(228, 105, 154);
            border-color: #e67aa5;
            transform: translateY(-2px);
        }
        
        .product-card {
            transition: all 0.3s ease;
            overflow: hidden;
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background: var(--card-bg);
            z-index: -1;
        }
        
        .product-card:hover {
<<<<<<< HEAD
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
=======
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
>>>>>>> 5baea74 (Product Crud Done for all requirment)
        }
        
        .product-img {
            height: 330px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-img:hover {
            transform: scale(1.1);
        }
        
        .out-of-stock {
            position: relative;
            opacity: 0.85;
        }
        
        .out-of-stock::after {
            content: "Out of Stock";
            position: absolute;
            top: 20px;
            right: -30px;
            background-color: #dc3545;
            color: white;
            padding: 5px 30px;
            transform: rotate(45deg);
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .text-unavailable {
            color: #dc3545;
        }
        
        .text-available {
            color: #28a745;
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
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color:white;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 350px;
        }
        
        .alert {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            border-left: 4px solid;
        }
        
        .animate-bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% {transform: translateY(0px);}
            50% {transform: translateY(-8px);}
            100% {transform: translateY(0px);}
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {box-shadow: 0 0 0 0 rgba(106, 76, 147, 0.4);}
            70% {box-shadow: 0 0 0 12px rgba(106, 76, 147, 0);}
            100% {box-shadow: 0 0 0 0 rgba(106, 76, 147, 0);}
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal-footer .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body class="bg-light">
<<<<<<< HEAD
    <div class="alert-container" id="flashMessageContainer"></div>
    
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-boxes me-2"></i>Products Management</h2>
            <div class="animate__animated animate__fadeInRight">
                <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="fas fa-question-circle me-2"></i>Help
                </a>
            </div>
        </div>
        
        <!-- Search and Filter Card -->
        <div class="filter-card animate__animated animate__fadeInUp">
            <form method="GET" class="mb-4 needs-validation" novalidate>
                <div class="d-flex justify-content-between">
                    <div class="w-75">
                        <div class="input-group">
                            <select class="form-select w-25" name="search_by">
                                <option value="name" <?= isset($_GET['search_by']) && $_GET['search_by'] == 'name' ? 'selected' : '' ?>>Search by Name</option>
                                <option value="category" <?= isset($_GET['search_by']) && $_GET['search_by'] == 'category' ? 'selected' : '' ?>>Search by Category</option>
                            </select>
                            <input type="text" class="form-control" name="search_term" value="<?= isset($_GET['search_term']) ? $_GET['search_term'] : '' ?>" placeholder="Enter search term">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search me-2"></i>Search</button>
                        </div>
=======
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
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                    </div>
                </div>
<<<<<<< HEAD
                <button class="btn btn-success mt-4 floating" type="button" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add Product
                </button>
<<<<<<< HEAD
                <input type="hidden" name="page" value="1">
            </form>
        </div>
        
        <!-- Products Display -->
        <div class="row row-cols-1 row-cols-md-3 g-4 animate__animated animate__fadeInUp">
            <?php
            //=============================
            //queries
            $sql = "SELECT p.*, c.name AS category_name FROM items p 
            JOIN categories c ON p.category_id = c.id";
            $count_sql = "SELECT COUNT(*) as total FROM items p 
                          JOIN categories c ON p.category_id = c.id";
            
            if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
                $search_term = mysqli_real_escape_string($myConnection, $_GET['search_term']);
                $search_by = $_GET['search_by'] ?? 'name';
                
=======
=======
>>>>>>> 0d05dec (Product Crud Done for all requirment)
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
<<<<<<< HEAD
                $search_term = mysqli_real_escape_string($myConnection, $_GET['search_term']);
                $search_by = mysqli_real_escape_string($myConnection, $_GET['search_by'] ?? 'name');
>>>>>>> 5baea74 (Product Crud Done for all requirment)
=======
                $search_term = $_GET['search_term'];
                $search_by = $_GET['search_by'] ?? 'name';
>>>>>>> c88e851 (update product done)
                if ($search_by === 'name') {
                    $sql .= " WHERE p.name LIKE '%$search_term%'";
                    $count_sql .= " WHERE p.name LIKE '%$search_term%'";
                } else if ($search_by === 'category') {
                    $sql .= " WHERE c.name LIKE '%$search_term%'";
                    $count_sql .= " WHERE c.name LIKE '%$search_term%'";
                }
            }
<<<<<<< HEAD
<<<<<<< HEAD
            
            $sql .= " LIMIT $offset, $items_per_page";
            
            error_log("Main Query: ".$sql);
            error_log("Count Query: ".$count_sql);
            
            $count_result = mysqli_query($myConnection, $count_sql);
            if (!$count_result) {
                die("Count query failed: " . mysqli_error($myConnection));
            }
            
            $total_items = mysqli_fetch_assoc($count_result)['total'];
            $total_pages = ceil($total_items / $items_per_page);
            
            $result = mysqli_query($myConnection, $sql);
            if (!$result) {
                die("Main query failed: " . mysqli_error($myConnection));
            }
            
            if (mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
                    $is_available = $product['is_available'];
                    ?>
                    
                    <div class="col animate__animated animate__fadeIn">
                        <div class="card h-100 product-card <?= !$is_available ? 'out-of-stock' : '' ?>">
                            <div class="card-img-container" style="overflow: hidden;">
                                <?php if(!empty($product['image_url'])): ?>
                                    <img src="../../Public/uploads/products/<?= $product['image_url'] ?>" class="card-img-top product-img" alt="<?= $product['name'] ?>">
=======
=======

>>>>>>> c88e851 (update product done)
            $sql .= " LIMIT $offset, $items_per_page";
            $count_result = mysqli_query($myConnection, $count_sql);
            $total_items = mysqli_fetch_assoc($count_result)['total'];
            $total_pages = ceil($total_items / $items_per_page);
            $result = mysqli_query($myConnection, $sql);

            if (mysqli_num_rows($result) > 0) {
<<<<<<< HEAD
                while ($product = mysqli_fetch_assoc($result)) {
                    $is_out_of_stock = $product['stock'] <= 0;
                    $is_low_stock = $product['stock'] > 0 && $product['stock'] <= 5;
                    ?>
                    <div class="col">
                        <div class="card h-100 product-card <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
                            <div class="card-img-container">
                                <?php if(!empty($product['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($product['name']) ?>">
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                                <?php else: ?>
                                    <div class="product-img bg-secondary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-box-open fa-4x text-light"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
<<<<<<< HEAD
                                <h5 class="card-title"><?= $product['name'] ?></h5>
                                <p class="card-text text-muted"><?= substr($product['description'], 0, 100) ?>...</p>
                                <p class="text-primary fw-bold">Price: $<?= number_format($product['price'], 2) ?></p>
                                <div class="availability-info">
                                    Status: <span class="status-badge <?= $is_available ? 'status-available' : 'status-unavailable' ?>">
                                        <?= $is_available ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </div>
                                <p class="text-muted"><i class="fas fa-tag me-2"></i>Category: <?= $product['category_name'] ?></p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-primary me-2"><i class="fas fa-edit me-2"></i>Edit</a>
                                <button class="btn btn-danger"  onclick="confirmDelete(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')">
                                     <i class="fas fa-trash-alt me-2"></i>Delete
                                </button>
=======
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text"><?= substr(htmlspecialchars($product['description']), 0, 100) ?>...</p>
                                <p class="text-success fw-bold">Price: $<?= number_format($product['price'], 2) ?></p>
                                <div class="stock-info <?= $is_low_stock ? 'low-stock' : '' ?>">
                                    Stock: <?= htmlspecialchars($product['stock']) ?>
=======
            while ($product = mysqli_fetch_assoc($result)) {
                $is_out_of_stock = $product['stock'] <= 0;
                $is_low_stock = $product['stock'] > 0 && $product['stock'] <= 5;
                ?>
                
                <div class="col">
                    <div class="card h-100 product-card <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
                        <div class="card-img-container">
                            <?php if(!empty($product['image_url'])): ?>
                                <img src="<?= $product['image_url'] ?>" class="card-img-top product-img" alt="<?= $product['name'] ?>">
                            <?php else: ?>
                                <div class="product-img bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-box-open fa-4x text-light"></i>
>>>>>>> c88e851 (update product done)
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <p class="card-text"><?= substr($product['description'], 0, 100) ?>...</p>
                            <p class="text-success fw-bold">Price: $<?= number_format($product['price'], 2) ?></p>
                            <div class="stock-info <?= $is_low_stock ? 'low-stock' : '' ?>">
                                Stock: <?= $product['stock'] ?>
                            </div>
                            <p class="text-muted">Category: <?= $product['category_name'] ?></p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary me-2">Edit</a>
                            <?php if($is_out_of_stock): ?>
                                <button class="btn btn-sm btn-danger" disabled>Delete</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $product['id'] ?>">Delete</button>
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
<<<<<<< HEAD
                                <?php endif; ?>
>>>>>>> 5baea74 (Product Crud Done for all requirment)
=======
                                </div>
>>>>>>> c88e851 (update product done)
                            </div>
                            <?php endif; ?>
                        </div>
<<<<<<< HEAD
                    </div>    
                    <?php
                }
=======
                    </div>
                </div>
                <?php
            }
>>>>>>> c88e851 (update product done)
            } else {
                echo '<div class="col-12 animate__animated animate__fadeIn"><div class="alert alert-info text-center py-5">
                    <i class="fas fa-box-open fa-3x mb-3" style="color: var(--primary-color);"></i>
                    <h4 class="text-muted">No products found</h4>
                    <p class="text-muted">Try adjusting your search criteria</p>
                </div></div>';
            }
            ?>
        </div>
<<<<<<< HEAD
        
        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4 animate__animated animate__fadeInUp">
=======
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
>>>>>>> 5baea74 (Product Crud Done for all requirment)
            <ul class="pagination justify-content-center">
                <?php if($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" aria-label="Previous">
<<<<<<< HEAD
                            <i class="fas fa-chevron-left"></i>
=======
                            <span aria-hidden="true">&laquo;</span>
>>>>>>> 5baea74 (Product Crud Done for all requirment)
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
<<<<<<< HEAD
                            <i class="fas fa-chevron-right"></i>
=======
                            <span aria-hidden="true">&raquo;</span>
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
<<<<<<< HEAD
    
    <!-- Add Product Modal -->
=======
    <!-- Add Form -->
>>>>>>> 5baea74 (Product Crud Done for all requirment)
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel"><i class="fas fa-plus-circle me-2"></i>Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
<<<<<<< HEAD
                            <label class="form-label"><i class="fas fa-tag me-2"></i>Product Name</label>
                            <input type="text" class="form-control" name="name" required>
=======
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="name">
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                            <div class="invalid-feedback">Please enter the product name.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-align-left me-2"></i>Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
<<<<<<< HEAD
                            <label class="form-label"><i class="fas fa-dollar-sign me-2"></i>Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
                            <div class="invalid-feedback">Please enter the price.</div>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="is_available" id="is_available" checked>
                            <label class="form-check-label" for="is_available"><i class="fas fa-check-circle me-2"></i>Available</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-tags me-2"></i>Category</label>
                            <select class="form-select" name="category" required>
=======
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price">
                            <div class="invalid-feedback">Please enter the price.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" step="0.01" class="form-control" name="stock">
                            <div class="invalid-feedback">Please enter the stock quantity.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                                <option value="" selected disabled>Select a category</option>
                                <?php
                                $categories = mysqli_query($myConnection, "SELECT * FROM categories");
                                print_r($category);
                                while($category = mysqli_fetch_assoc($categories)) {
                                    echo "<option value='{$category['id']}'>{$category['name']}</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        <div class="mb-3">
<<<<<<< HEAD
                            <label class="form-label"><i class="fas fa-image me-2"></i>Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
=======
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="invalid-feedback">Please upload an image.</div>
                            <div class="form-text">Allowed formats: jpg, png, gif, svg</div>
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Close</button>
                        <button type="submit" name="addProduct" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    
    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel"><i class="fas fa-question-circle me-2"></i>Help Center</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6><i class="fas fa-search me-2"></i>Searching Products</h6>
                    <p>Use the search bar to find products by name or category. You can switch between search modes using the dropdown.</p>
                    
                    <h6 class="mt-4"><i class="fas fa-plus-circle me-2"></i>Adding Products</h6>
                    <p>Click the "Add Product" button to create new product entries. Fill in all required fields and upload an image if available.</p>
                    
                    <h6 class="mt-4"><i class="fas fa-edit me-2"></i>Editing Products</h6>
                    <p>Use the Edit button on each product card to modify existing product information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-check me-2"></i>Got it!</button>
                </div>
            </div>
        </div>
    </div>


=======
>>>>>>> 5baea74 (Product Crud Done for all requirment)
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation')
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Add animation to product cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
        //////
        // Delete confirmation function
function confirmDelete(productId, productName) {
    // Create a Bootstrap alert dynamically
    const container = document.getElementById('flashMessageContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-warning alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <strong><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</strong>
        <p>Are you sure you want to delete "${productName}"?</p>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-sm btn-secondary me-2" onclick="dismissAlert(this)">
                Cancel
            </button>
            <a href="delete_product.php?id=${productId}" class="btn btn-sm btn-danger">
                <i class="fas fa-trash-alt me-1"></i> Delete
            </a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    container.appendChild(alertDiv);
    
    // Auto-dismiss after 10 seconds if not interacted with
    setTimeout(() => {
        if (alertDiv.parentNode) {
            bootstrap.Alert.getInstance(alertDiv).close();
        }
    }, 10000);
}

// Function to dismiss alert
function dismissAlert(button) {
    const alertDiv = button.closest('.alert');
    bootstrap.Alert.getInstance(alertDiv).close();
}
        // Delete modal handling
        document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        // Handle all delete buttons
        document.querySelectorAll('[data-bs-target="#deleteModal"]').forEach(button => {
            button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            const productDesc = this.getAttribute('data-product-desc');
            const productPrice = this.getAttribute('data-product-price');
            const productStatus = this.getAttribute('data-product-status');
            const productCategory = this.getAttribute('data-product-category');
            
            // Update modal content
            document.getElementById('modalProductContent').innerHTML = `
                <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">${productName}</h5>
                    <p class="card-text">${productDesc}</p>
                    <div class="d-flex justify-content-between">
                    <span class="text-primary fw-bold">Price: $${productPrice}</span>
                    <span class="badge ${productStatus === 'Available' ? 'bg-success' : 'bg-danger'}">
                        ${productStatus}
                    </span>
                    </div>
                    <p class="text-muted mt-2">
                    <i class="fas fa-tag me-2"></i>Category: ${productCategory}
                    </p>
                </div>
                </div>
            `;
            
            // Update delete link
            document.getElementById('confirmDeleteBtn').href = `delete_product.php?id=${productId}`;
            });
        });
        });
    </script>
    
</body>
</html>
<?php
<<<<<<< HEAD

define('UPLOAD_DIR', '/opt/lampp/htdocs/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/');
define('PUBLIC_URL', '/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/');


if (isset($_POST['addProduct'])) {
    $name = $_POST['name'];
    $description =  $_POST['description'];
    $price = $_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $category = $_POST['category'];

    $image_url = null;
    if (!empty($_FILES['image']['name'])) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size < 500000000) { 
                $new_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-]/", "", $file_name);
                $target_path = UPLOAD_DIR . $new_filename;
                
                if (move_uploaded_file($file_tmp, $target_path)) {
                    $image_url = $new_filename;
=======
if(isset($_POST['addProduct'])) {
    $name =$_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
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
                        $sql = "INSERT INTO items (name, description, price, stock, image_url, category_id) 
                        VALUES ('$name', '$description', $price, $stock, '$fileDestination', $category)";
                        if(mysqli_query($myConnection, $sql)) {
                            echo "<div class='alert alert-success'>Product added successfully</div>";

                        } else {
                            echo "<div class='alert alert-danger'>Error adding product: ".mysqli_error($myConnection)."</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>Error uploading file</div>";
                    }
>>>>>>> 5baea74 (Product Crud Done for all requirment)
                } else {
                    echo "<script>
                        const container = document.getElementById('flashMessageContainer');
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-danger show';
                        alert.innerHTML = `
                            <strong><i class=\"fas fa-exclamation-circle me-2\"></i>Error!</strong> 
                            Failed to upload image
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                        `;
                        container.appendChild(alert);
                        
                        setTimeout(() => {
                            alert.classList.remove('show');
                            setTimeout(() => alert.remove(), 500);
                        }, 5000);
                    </script>";
                }
            } else {
                echo "<script>
                    const container = document.getElementById('flashMessageContainer');
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger show';
                    alert.innerHTML = `
                        <strong><i class=\"fas fa-exclamation-circle me-2\"></i>Error!</strong> 
                        File too large (max 5MB)
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    `;
                    container.appendChild(alert);
                    
                    setTimeout(() => {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 500);
                    }, 5000);
                </script>";
            }
        } else {
            echo "<script>
                const container = document.getElementById('flashMessageContainer');
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger show';
                alert.innerHTML = `
                    <strong><i class=\"fas fa-exclamation-circle me-2\"></i>Error!</strong> 
                    Invalid file type. Allowed: JPG, PNG, GIF, WEBP
                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                `;
                container.appendChild(alert);
                
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            </script>";
        }
    }

    $sql = "INSERT INTO items (name, description, price, is_available, image_url, category_id) 
            VALUES ('$name', '$description', $price, $is_available, " . 
            ($image_url ? "'$image_url'" : "NULL") . ", $category)";
    
    if (mysqli_query($myConnection, $sql)) {
        echo "<script>
            const container = document.getElementById('flashMessageContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-success show';
            alert.innerHTML = `
                <strong><i class=\"fas fa-check-circle me-2\"></i>Success!</strong> 
                Product added successfully
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
            `;
            container.appendChild(alert);
            
            setTimeout(() => {
                window.location.href = 'products.php';
            }, 1500);
        </script>";
    } else {
        echo "<script>
            const container = document.getElementById('flashMessageContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger show';
            alert.innerHTML = `
                <strong><i class=\"fas fa-exclamation-circle me-2\"></i>Error!</strong> 
                Database error occurred
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
            `;
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        </script>";
    }
<<<<<<< HEAD
}

=======
}}
>>>>>>> c88e851 (update product done)
?>