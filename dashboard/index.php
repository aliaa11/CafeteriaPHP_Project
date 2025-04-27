<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="./styles/Dashboard.css" />
    <style>
      .sidebar {
        transition: all 0.3s;
        background-color:rgb(75, 49, 102) !important;
      }
      .logo-txt {
      font-size: 1.5rem;
      font-weight: bold;
      color: #fff;
      border-bottom:rgb(122, 102, 143)  solid 2px !important;
    }

      .sidebar-collapsed {
        margin-left: -250px;
      }
      .toggle-btn {
        position: fixed;
        left: 10px;
        top: 10px;
        z-index: 1000;
      }
      @media (max-width: 767.98px) {
        .sidebar {
          position: fixed;
          z-index: 999;
          height: 100vh;
        }
        .sidebar-collapsed {
          margin-left: -100%;
        }
      }
    </style>
  </head>

  <body class="bg-dark text-light">
    <button id="sidebarToggle" class="btn btn-primary toggle-btn d-md-none">
      <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid">
      <div class="row min-vh-100">
        <div id="sidebar" class="col-md-3 col-lg-2 d-flex flex-column sidebar">
          <div class="logo-txt p-3 ">Dashboard</div>
          <div class="flex-grow-1">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link text-light" href="./userCrud/users.php" target="content-frame">Users</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-light" href="./categoriesCrud/categories.php" target="content-frame">Categories</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-light" href="./productCrud/products.php" target="content-frame">Items</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-light" href="./orderStatusCrud/orders.php" target="content-frame">Order Status</a>
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
      // Toggle sidebar functionality
      const sidebar = document.getElementById('sidebar');
      const sidebarToggle = document.getElementById('sidebarToggle');
      const contentFrame = document.querySelector('iframe[name="content-frame"]');
      
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
        
        // Change icon based on state
        const icon = sidebarToggle.querySelector('i');
        if (sidebar.classList.contains('sidebar-collapsed')) {
          icon.classList.remove('fa-bars');
          icon.classList.add('fa-bars');
        } else {
          icon.classList.remove('fa-bars');
          icon.classList.add('fa-times');
        }
      });

      document.addEventListener('click', (e) => {
        if (window.innerWidth < 768 && 
            !sidebar.contains(e.target) && 
            e.target !== sidebarToggle && 
            !sidebarToggle.contains(e.target)) {
          sidebar.classList.add('sidebar-collapsed');
        }
      });

      // Adjust iframe height when sidebar toggles
      function adjustIframeHeight() {
        const headerHeight = document.querySelector('.logo-txt').offsetHeight;
        const footerHeight = document.querySelector('#logout').parentElement.offsetHeight;
        const availableHeight = window.innerHeight - headerHeight - footerHeight;
        document.querySelector('.flex-grow-1').style.height = `${availableHeight}px`;
      }

      window.addEventListener('resize', adjustIframeHeight);
      adjustIframeHeight();
    </script>
  </body>
</html>