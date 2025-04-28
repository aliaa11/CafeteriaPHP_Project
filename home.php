<?php
session_start();
include_once './config/dbConnection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT username, profile_picture FROM users WHERE id = ?";
    $stmt = mysqli_prepare($myConnection, $user_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($user_result);
}

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }

    header("Location: home.php");
    exit();
}

if (isset($_POST['remove_from_cart'])) {
    $item_id = $_POST['remove_from_cart'];
    unset($_SESSION['cart'][$item_id]);
    header("Location: home.php");
    exit();
}

// عدد المنتجات في كل صفحة
$items_per_page = 6;

// جلب الصفحة الحالية من الـ URL
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// حساب الـ Offset
$offset = ($current_page - 1) * $items_per_page;

// جلب عدد المنتجات الكلي
$count_query = "SELECT COUNT(*) as total 
               FROM items 
               JOIN categories ON items.category_id = categories.id 
               WHERE items.is_available = 1";
$count_result = mysqli_query($myConnection, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_items = $count_row['total'];

// حساب عدد الصفحات الكلي
$total_pages = ceil($total_items / $items_per_page);

// جلب المنتجات مع الـ LIMIT والـ OFFSET
$query = "SELECT items.*, categories.name AS category_name 
          FROM items 
          JOIN categories ON items.category_id = categories.id 
          WHERE items.is_available = 1 
          LIMIT $items_per_page OFFSET $offset";
$result = mysqli_query($myConnection, $query);

// جلب كل الكاتيجوري من قاعدة البيانات
$category_query = "SELECT DISTINCT name FROM categories ORDER BY name";
$category_result = mysqli_query($myConnection, $category_query);
$categories = [];
while ($category = mysqli_fetch_assoc($category_result)) {
    $categories[] = $category['name'];
}

mysqli_data_seek($result, 0);

$cart_count = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luna Cafeteria - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Header Styles */
        .navbar {
            background-color: rgb(75, 49, 102);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important;
        }
        .navbar-nav .nav-link {
            color: #fff !important;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .navbar-nav .nav-link:hover {
            color: rgb(122, 102, 143) !important;
        }
        .btn-auth {
            background-color: rgb(122, 102, 143);
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-auth:hover {
            background-color: rgb(100, 80, 120);
            color: #fff;
        }
        .cart-icon {
            position: relative;
            margin-left: 15px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: #fff;
        }
        .cart-icon .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: rgb(122, 102, 143);
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
        .profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgb(122, 102, 143);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 400px;
            background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085');
            background-size: cover;
            background-position: center;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(75, 49, 102, 0.7);
        }
        .hero-content {
            position: relative;
            z-index: 1;
        }
        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .hero-content .btn-order {
            background-color: rgb(122, 102, 143);
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            transition: background-color 0.3s;
        }
        .hero-content .btn-order:hover {
            background-color: rgb(100, 80, 120);
        }

        /* Products Section */
        .products-section {
            padding: 50px 0;
            background-color: #fff;
        }
        .products-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: rgb(75, 49, 102);
            text-align: center;
            margin-bottom: 30px;
        }
        .filters-menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
        }
        .filters-menu .filter-btn {
            background-color: rgb(75, 49, 102);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .filters-menu .filter-btn.active {
            background-color: rgb(122, 102, 143);
        }
        .filters-menu .filter-btn:hover {
            background-color: rgb(122, 102, 143);
        }
        .product-card {
            border: 2px solid rgb(122, 102, 143);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: #fff;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-card .card-body {
            padding: 20px;
            text-align: center;
        }
        .product-card .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: rgb(75, 49, 102);
            margin-bottom: 10px;
        }
        .product-card .card-text {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }
        .product-card .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: rgb(122, 102, 143);
            margin-bottom: 15px;
        }
        .product-card .btn-add-to-cart {
            background-color: rgb(75, 49, 102);
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .product-card .btn-add-to-cart:hover {
            background-color: rgb(122, 102, 143);
        }

        /* Pagination Styles */
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        .pagination .page-link {
            background-color: rgb(75, 49, 102);
            color: #fff;
            border: none;
            border-radius: 20px;
            margin: 0 5px;
            padding: 8px 15px;
            transition: background-color 0.3s;
        }
        .pagination .page-link:hover {
            background-color: rgb(122, 102, 143);
        }
        .pagination .page-item.disabled .page-link {
            background-color: #ccc;
            color: #666;
        }
        .pagination .page-item.active .page-link {
            background-color: rgb(122, 102, 143);
            color: #fff;
        }

        /* Footer Styles */
        footer {
            background-color: rgb(75, 49, 102);
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        footer a {
            color: rgb(122, 102, 143);
            text-decoration: none;
            margin: 0 10px;
        }
        footer a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Luna Cafeteria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products-section">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_orders.php">My Orders</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id']) && $user_data): ?>
                        <div class="d-flex align-items-center me-3">
                            <?php if ($user_data['profile_picture']): ?>
                                <img src="/CafeteriaPHP_Project/Public/uploads/users/<?= htmlspecialchars($user_data['profile_picture']) ?>" 
                                     alt="Profile Image" 
                                     class="profile-img me-2">
                            <?php else: ?>  
                                <img src="/CafeteriaPHP_Project/Public/uploads/users/default.png" 
                                     alt="Default Profile Image" 
                                     class="profile-img me-2">
                            <?php endif; ?>
                            <span class="text-white">Welcome, <?= htmlspecialchars($user_data['username']) ?>!</span>
                        </div>
                        <a href="logout.php" class="btn btn-auth">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-auth">Login</a>
                    <?php endif; ?>
                    <a href="cart.php" class="cart-icon">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?= $cart_count ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Welcome to Luna Cafeteria</h1>
            <p>Discover the best coffee and snacks in a cozy atmosphere.</p>
            <button class="btn btn-order" onclick="scrollToProducts()">Order Now</button>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products-section">
        <div class="container">
            <h2>Our Menu</h2>
            <div class="filters-menu">
                <button class="filter-btn active" data-filter="*">All</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-filter=".<?= htmlspecialchars(str_replace(' ', '-', strtolower($category))) ?>">
                        <?= htmlspecialchars($category) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="row grid">
                <?php while ($item = mysqli_fetch_assoc($result)) : ?>
                    <div class="col-sm-12 col-md-6 col-lg-4 mb-4 all <?= htmlspecialchars(str_replace(' ', '-', strtolower($item['category_name']))) ?>">
                        <div class="product-card">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="/CafeteriaPHP_Project/Public/uploads/products/<?= htmlspecialchars($item['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                                <div class="no-image-placeholder text-center py-5">
                                    <i class="bi bi-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                <div class="price">$<?= htmlspecialchars($item['price']) ?></div>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart" class="btn btn-add-to-cart">
                                        <i class="bi bi-cart me-1"></i> Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                                   aria-label="First">
                                    <span aria-hidden="true">««</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                                   aria-label="Previous">
                                    <span aria-hidden="true">«</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php 
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; 
                        
                        if ($end_page < $total_pages) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
                                   aria-label="Next">
                                    <span aria-hidden="true">»</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                                   aria-label="Last">
                                    <span aria-hidden="true">»»</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Luna Cafeteria. All Rights Reserved.</p>
            <p>
                <a href="#">Contact Us</a> | 
                <a href="#">About Us</a> | 
                <a href="#">Privacy Policy</a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/isotope-layout@3.0.6/dist/isotope.pkgd.min.js"></script>
    <script>
        function scrollToProducts() {
            document.getElementById('products-section').scrollIntoView({ behavior: 'smooth' });
        }

        // Isotope Filter
        $(document).ready(function() {
            var $grid = $('.grid').isotope({
                itemSelector: '.all',
                layoutMode: 'fitRows'
            });

            $('.filters-menu .filter-btn').click(function() {
                $('.filters-menu .filter-btn').removeClass('active');
                $(this).addClass('active');
                var filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
            });
        });
    </script>
</body>
</html>




