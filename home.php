<?php
session_start();
include_once './config/dbConnection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT username, profile_picture FROM users WHERE id = $user_id";
    $user_result = mysqli_query($myConnection, $user_query);
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

// Pagination setup
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count of available items
$count_query = "SELECT COUNT(*) as total FROM items WHERE is_available = 1";
$count_result = mysqli_query($myConnection, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_items = $count_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get categories for filtering
$categories_query = "SELECT id, name FROM categories";
$categories_result = mysqli_query($myConnection, $categories_query);
$all_categories = [];
while ($cat = mysqli_fetch_assoc($categories_result)) {
    $all_categories[$cat['id']] = $cat['name'];
}

// Get items with pagination
$query = "SELECT 
            items.*, 
            categories.name AS category_name,
            (SELECT status FROM orders 
             JOIN order_items ON orders.id = order_items.order_id 
             WHERE order_items.item_id = items.id 
             ORDER BY orders.order_date DESC LIMIT 1) AS last_order_status,
            (SELECT order_date FROM orders 
             JOIN order_items ON orders.id = order_items.order_id 
             WHERE order_items.item_id = items.id 
             ORDER BY orders.order_date DESC LIMIT 1) AS last_order_date
            FROM items 
            JOIN categories ON items.category_id = categories.id
            LIMIT $items_per_page OFFSET $offset";
$result = mysqli_query($myConnection, $query);

$cart_count = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feane Cafeteria - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: rgb(126, 105, 155);
            --accent-color: #f8a5c2;
            --light-bg: rgb(231, 231, 231);
            --card-bg: #ffffff;
            --text-dark: rgb(67, 38, 109);
            --text-light: #f5f6fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
        }
        
        .navbar {
            background-color: rgb(75, 49, 102);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
        
        .nav-link {
            color: white;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--accent-color);
        }
        
        .btn-order-online {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
        }
        
        .btn-order-online:hover {
            background-color: var(--secondary-color);
        }
        
        .cart-icon {
            position: relative;
            margin-left: 15px;
        }
        
        .cart-icon i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }
        
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
        
        .hero-section {
            position: relative;
            height: 500px;
            color: white;
        }
        
        .carousel-item {
            height: 500px;
            background-size: cover;
            background-position: center;
        }
        
        .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: left;
            z-index: 2;
        }
        
        .food_section {
            padding: 100px 0 50px;
        }
        
        .heading_container h2 {
            font-family: 'Playfair Display', serif;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .filters_menu {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filters_menu li {
            margin: 5px 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 20px;
            color: var(--text-dark);
            transition: all 0.3s;
            border: 1px solid var(--primary-color);
        }
        
        .filters_menu li.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .box {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
            height: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .img-box img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .detail-box {
            padding: 20px;
        }
        
        .detail-box h5 {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--text-dark);
        }
        
        .detail-box p {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .options h6 {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        .btn-add-to-cart {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .btn-add-to-cart:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-add-to-cart:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 2;
        }
        .out-of-stock {
    background-color: #dc3545;
    color: white;
}

.available {
    background-color: #28a745;
    color: white;
}

.filters_menu li {
    cursor: pointer;
    transition: all 0.3s;
}

.filters_menu li:hover {
    background-color: var(--secondary-color);
    color: white;
}
        
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-link {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            margin: 0 5px;
            border-radius: 20px !important;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        footer {
            background-color: var(--text-dark);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        footer a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        .profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Cafeteria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products-section">MENU</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_orders.php">MY ORDERS</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user_id']) && $user_data): ?>
                        <div class="d-flex align-items-center me-3">
                            <?php if ($user_data['profile_picture']): ?>
                                <img src="/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/<?= htmlspecialchars($user_data['profile_picture']) ?>" 
                                    alt="Profile Image" 
                                    class="profile-img">
                            <?php else: ?>  
                                <img src="/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/default.png" 
                                    alt="Default Profile Image" 
                                    class="profile-img">
                            <?php endif; ?>
                            <span class="text-white"><?= htmlspecialchars($user_data['username']) ?></span>
                        </div>
                        <a href="logout.php" class="btn btn-order-online">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-order-online">Login</a>
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
    <div class="hero-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1495474472287-4d71bcdd2085');"></div>
                <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb');"></div>
                <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1505275350441-83dc7c7c5b5b');"></div>
            </div>
            <div class="carousel-overlay"></div>
        </div>
        <div class="hero-content container">
            <h1>Feane Cafeteria</h1>
            <p>Enjoy a cozy experience with the best coffee and snacks in a warm atmosphere.</p>
            <button class="btn btn-order-online" onclick="scrollToProducts()">Order Now</button>
        </div>
    </div>

    <!-- Food Section -->
    <section class="food_section" id="products-section">
    <div class="container">
        <div class="heading_container">
            <h2>Our Menu</h2>
        </div>
        <ul class="filters_menu">
            <li class="active" data-filter="*">All</li>
            <?php foreach ($all_categories as $cat_id => $cat_name): ?>
                <li data-filter=".category-<?= $cat_id ?>"><?= htmlspecialchars($cat_name) ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="row">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($result)): ?>
                    <div class="col-sm-6 col-md-4 col-lg-4 mb-4 all category-<?= $item['category_id'] ?>">
                        <div class="box">
                            <span class="availability-badge <?= $item['is_available'] ? 'available' : 'out-of-stock' ?>">
                                <?= $item['is_available'] ? 'Available' : 'Out of Stock' ?>
                            </span>
                                <div class="img-box">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/products/<?= htmlspecialchars($item['image_url']) ?>" 
                                            alt="<?= htmlspecialchars($item['name']) ?>">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center" style="height: 250px; background: #f0f0f0;">
                                            <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="detail-box">
                                    <?php if ($item['last_order_status']): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">Last ordered on <?= date('M j, Y', strtotime($item['last_order_date'])) ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                                    <p><?= htmlspecialchars($item['description']) ?></p>
                                    
                                    <div class="options">
                                        <h6>$<?= htmlspecialchars($item['price']) ?></h6>
                                        <form method="post">
                                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" name="add_to_cart" class="btn btn-add-to-cart" <?= $item['is_available'] ? '' : 'disabled' ?>>
                                                <i class="bi bi-cart"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-cup-hot" style="font-size: 3rem; color: var(--accent-color);"></i>
                        <h4 class="mt-3">No items available</h4>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
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
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; 
                        
                        if ($end_page < $total_pages) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
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
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/isotope-layout@3.0.6/dist/isotope.pkgd.min.js"></script>
    <script>
        function scrollToProducts() {
            document.getElementById('products-section').scrollIntoView({ behavior: 'smooth' });
        }

        $(document).ready(function() {
    var $grid = $('.row').isotope({
        itemSelector: '.col-sm-6',
        layoutMode: 'fitRows'
    });

    $('.filters_menu li').click(function() {
        $('.filters_menu li').removeClass('active');
        $(this).addClass('active');
        
        var filterValue = $(this).attr('data-filter');
        $grid.isotope({ filter: filterValue });
    });
});
    </script>
</body>
</html>