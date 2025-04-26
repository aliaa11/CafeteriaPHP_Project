<?php
session_start();
include_once './config/dbConnection.php';

// إنشاء جلسة للسلة لو مش موجودة
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// التأكد من إن المستخدم مسجل دخول وجلب بياناته
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // تعديل: استخدام mysqli_query بدل Prepared Statement
    $user_query = "SELECT username, profile_picture FROM users WHERE id = $user_id";
    $user_result = mysqli_query($myConnection, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);
}

// إضافة منتج للسلة
if (isset($_POST['add_to_cart'])) {
    // التأكد من إن المستخدم مسجل دخول
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

    // تعديل: إلغاء AJAX واستخدام Redirect
    header("Location: home.php");
    exit();
}

// إزالة منتج من السلة
if (isset($_POST['remove_from_cart'])) {
    $item_id = $_POST['remove_from_cart'];
    unset($_SESSION['cart'][$item_id]);
    header("Location: home.php");
    exit();
}

// جلب الفئات مع المنتجات
$query = "SELECT items.*, categories.name AS category_name FROM items 
          JOIN categories ON items.category_id = categories.id";
$result = mysqli_query($myConnection, $query);

// تجهيز المنتجات حسب الفئة
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $catName = $row['category_name'];
    $categories[$catName][] = $row;
}

// إعادة ضبط المؤشر للاستخدام مرة أخرى
mysqli_data_seek($result, 0);

// حساب عدد العناصر في السلة
$cart_count = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feane Cafeteria - Home</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Hero Section with Carousel */
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
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
        }
        .hero-content p {
            font-size: 1.2rem;
            margin: 20px 0;
        }
        .hero-content .btn-order {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 10px 30px;
            font-weight: bold;
            border-radius: 25px;
        }
        .hero-content .btn-order:hover {
            background-color: #6d3e1a;
        }

        /* Navigation Bar */
        .navbar {
            background-color: transparent;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 3;
        }
        .navbar .nav-link {
            color: white;
            margin: 0 15px;
        }
        .navbar .nav-link:hover {
            color: #8d5524;
        }
        .navbar .btn-order-online {
            background-color: #8d5524;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
        }
        .navbar .btn-order-online:hover {
            background-color: #6d3e1a;
        }
        .cart-icon {
            position: relative;
            margin-left: 10px;
        }
        .cart-icon i {
            font-size: 1.5rem;
            color: white;
        }
        .cart-icon .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #8d5524;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
        .profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Products Section */
        .food_section {
            background-color: #F5F5DC;
            padding: 50px 0;
        }
        .heading_container h2 {
            font-family: 'Playfair Display', serif;
            color: #5C4033;
        }
        .filters_menu {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }
        .filters_menu li {
            margin: 0 15px;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 20px;
            color: #5C4033;
            transition: background-color 0.3s;
        }
        .filters_menu li.active {
            background-color: #8d5524;
            color: white;
        }
        .box {
            background-color: #5C4033;
            color: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .img-box img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            cursor: pointer;
        }
        .detail-box {
            padding: 20px;
        }
        .detail-box h5 {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .detail-box p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .options h6 {
            font-size: 1.2rem;
            font-weight: bold;
            color: #d2b48c;
        }
        .btn-add-to-cart {
            background-color: #8d5524;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .btn-add-to-cart:hover {
            background-color: #6d3e1a;
        }

        /* Footer */
        footer {
            background-color: #5C4033;
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        footer a {
            color: #d2b48c;
            text-decoration: none;
        }
        footer a:hover {
            color: #8d5524;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-white" href="#">Feane</a>
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
                                <img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>" alt="Profile Image" class="profile-img me-2">
                            <?php else: ?>
                                <img src="./dashboard/uploads/users/default.png" alt="Default Profile Image" class="profile-img me-2">
                            <?php endif; ?>
                            <span class="text-white">Welcome, <?php echo htmlspecialchars($user_data['username']); ?>!</span>
                        </div>
                        <a href="logout.php" class="btn btn-order-online">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-order-online">Login</a>
                    <?php endif; ?>
                    <a href="cart.php" class="cart-icon">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Carousel -->
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
            <button class="btn btn-order" onclick="scrollToProducts()">Order Now</button>
        </div>
    </div>

    <!-- Food Section -->
    <section class="food_section layout_padding-bottom" id="products-section">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>Our Menu</h2>
            </div>
            <ul class="filters_menu">
                <li class="active" data-filter="*">All</li>
                <li data-filter=".hot-drinks">Hot Drinks</li>
                <li data-filter=".cold-drinks">Cold Drinks</li>
                <li data-filter=".sweets">Sweets</li>
            </ul>
            <div class="row">
                <!-- Products -->
                <div class="col-md-12">
                    <div class="filters-content">
                        <div class="row grid">
                            <?php while ($item = mysqli_fetch_assoc($result)) : ?>
                                <div class="col-sm-12 col-md-6 col-lg-4 all <?php echo htmlspecialchars(str_replace(' ', '-', strtolower($item['category_name']))); ?>">
                                    <div class="box">
                                        <div class="img-box order-item-img">
                                            <?php
                                            $image_path = $_SERVER['DOCUMENT_ROOT'] . '/cafateriapro/uploads/' . $item['image_url'];
                                            echo "<!-- Debug: Image path = " . $image_path . " -->";
                                            if (file_exists($image_path)):
                                            ?>
                                                <img src="/cafateriapro/uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-img">
                                            <?php else: ?>
                                                <p>Image not available</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="detail-box">
                                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                                            <div class="options">
                                                <h6>$<?php echo htmlspecialchars($item['price']); ?></h6>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" name="add_to_cart" class="btn btn-add-to-cart">
                                                        <i class="bi bi-cart"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Food Section -->

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2025 Feane Cafeteria. All Rights Reserved.</p>
            <p><a href="#">Contact Us</a> | <a href="#">About Us</a> | <a href="#">Privacy Policy</a></p>
        </div>
    </footer>

    <!-- Bootstrap JS and Custom JS -->
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




