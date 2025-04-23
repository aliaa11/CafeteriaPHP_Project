<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="./styles/Dashboard.css" />
  </head>

  <body class="bg-dark text-light">
    <div class="container-fluid">
      <div class="row min-vh-100">
        <div class="col-md-3 col-lg-2 bg-dark d-flex flex-column">
          <div class="logo-txt p-3 border-bottom border-primary">Dashboard</div>
          <div class="flex-grow-1">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link text-light" href="./userCrud/users.php" target="content-frame">Users</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-light" href="./categoriesCrud/categories.php" target="content-frame">Categories</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-light" href="./productCrud/products.php" target="content-frame">Products</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-light" href="./orderCrud.php/orders.php" target="content-frame">Order Status</a>
              </li>
            </ul>
          </div>
          <div class="p-3">
            <button id="logout" class="btn btn-outline-light w-100">
              Log Out <i class="fa-solid fa-right-from-bracket ms-2"></i>
            </button>
          </div>
        </div>

        <div class="col-md-9 col-lg-10 p-0">
          <iframe name="content-frame" src="./userCrud/users.php" class="w-100 h-100 border-0"></iframe>
        </div>
      </div>
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
      <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <span id="notification-message"></span>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module">
      import { showNotification, logOut, isLogin } from "../utils/user.js";
      if (!isLogin()) {
        window.location.href = "/";
      }
      document.getElementById("logout").addEventListener("click", handleLogout);
      function handleLogout() {
        logOut();
        window.location.href = "/";
      }
    </script>
  </body>
</html>