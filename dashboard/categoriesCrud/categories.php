<<<<<<< HEAD
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Categories Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../styles/categories.css" />
  </head>

  <body class="bg-light">
    <!-- Notification Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
      <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">Notification</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
      </div>
    </div>

    <div class="container py-4">
      <h1 class="text-center mb-4">Categories Management</h1>
      
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-body">
              <h2 class="h4 mb-4">Add New Category</h2>
              <form id="category-form">
                <div class="mb-3">
                  <input type="text" class="form-control" id="category-name" placeholder="Enter category name" required>
                  <input type="hidden" id="category-id">
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-save me-2"></i>Save Category
                  </button>
                  <button type="button" id="update-category" class="btn btn-warning flex-grow-1" style="display: none">
                    <i class="fas fa-edit me-2"></i>Update Category
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h2 class="h4 mb-4">Search Categories</h2>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="search-category" class="form-control" placeholder="Search category by name">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-body">
          <h2 class="h4 mb-4">Categories List</h2>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-dark">
                <tr>
                  <th>Category Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="categories-list"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module">
      // Initialize toast
      const notificationToast = new bootstrap.Toast(document.getElementById('notificationToast'));
      
      function showToast(message, type = 'success') {
        const toast = document.getElementById('notificationToast');
        const toastMessage = document.getElementById('toastMessage');
        
        // Set message and style based on type
        toastMessage.textContent = message;
        toast.className = `toast ${type === 'error' ? 'bg-danger text-white' : 'bg-success text-white'}`;
        
        // Show the toast
        notificationToast.show();
      }

      // Load categories from localStorage
      function loadCategories() {
        const categories = JSON.parse(localStorage.getItem('categories')) || [];
        const categoriesList = document.getElementById('categories-list');
        categoriesList.innerHTML = '';

        if (categories.length === 0) {
          categoriesList.innerHTML = `
            <tr>
              <td colspan="2" class="text-center py-4">No categories found</td>
            </tr>
          `;
          return;
        }

        categories.forEach((category, index) => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${category.name}</td>
            <td>
              <button class="btn btn-sm btn-warning me-2 edit-category" data-id="${index}">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button class="btn btn-sm btn-danger delete-category" data-id="${index}">
                <i class="fas fa-trash"></i> Delete
              </button>
            </td>
          `;
          categoriesList.appendChild(row);
        });

        // Add event listeners to edit and delete buttons
        document.querySelectorAll('.edit-category').forEach(button => {
          button.addEventListener('click', () => editCategory(button.dataset.id));
        });

        document.querySelectorAll('.delete-category').forEach(button => {
          button.addEventListener('click', () => deleteCategory(button.dataset.id));
        });
      }

      // Add or update category
      document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const categoryName = document.getElementById('category-name').value.trim();
        const categoryId = document.getElementById('category-id').value;
        const categories = JSON.parse(localStorage.getItem('categories')) || [];
        
        if (!categoryName) {
          showToast('Category name cannot be empty', 'error');
          return;
        }

        if (categoryId) {
          // Update existing category
          categories[categoryId].name = categoryName;
          showToast('Category updated successfully');
        } else {
          // Add new category
          if (categories.some(cat => cat.name.toLowerCase() === categoryName.toLowerCase())) {
            showToast('Category already exists', 'error');
            return;
          }
          categories.push({ name: categoryName });
          showToast('Category added successfully');
        }

        localStorage.setItem('categories', JSON.stringify(categories));
        loadCategories();
        this.reset();
        document.getElementById('update-category').style.display = 'none';
      });

      // Edit category
      function editCategory(id) {
        const categories = JSON.parse(localStorage.getItem('categories')) || [];
        const category = categories[id];
        
        document.getElementById('category-name').value = category.name;
        document.getElementById('category-id').value = id;
        document.getElementById('update-category').style.display = 'block';
        
        // Scroll to form
        document.getElementById('category-name').focus();
      }

      // Delete category
      function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) return;
        
        const categories = JSON.parse(localStorage.getItem('categories')) || [];
        categories.splice(id, 1);
        localStorage.setItem('categories', JSON.stringify(categories));
        
        showToast('Category deleted successfully');
        loadCategories();
      }

      // Search categories
      document.getElementById('search-category').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#categories-list tr');
        
        rows.forEach(row => {
          const name = row.querySelector('td:first-child').textContent.toLowerCase();
          row.style.display = name.includes(searchTerm) ? '' : 'none';
        });
      });

      // Initialize the page
      document.addEventListener('DOMContentLoaded', () => {
        loadCategories();
      });
    </script>
  </body>
</html>
=======
<?php
include_once __DIR__ . "/../../config/dbConnection.php";
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$sql = "SELECT * FROM categories";
$result = mysqli_query($myConnection, $sql);
$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management</title>
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
        
        .filter-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
            transition: all 0.3s ease;
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
        
        .table {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: var(--text-light);
            font-weight: 500;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% {transform: translateY(0px);}
            50% {transform: translateY(-8px);}
            100% {transform: translateY(0px);}
        }
    </style>
</head>
<body>
    <div class="alert-container" id="flashMessageContainer"></div>
    
    <div class="container py-4 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title animate__animated animate__fadeInLeft"><i class="fas fa-tags me-2"></i>Categories Management</h2>
            <div class="animate__animated animate__fadeInRight">
                <a href="addcategory.php" class="btn btn-success floating">
                    <i class="fas fa-plus me-2"></i>Add Category
                </a>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="table-responsive animate__animated animate__fadeInUp">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="animate__animated animate__fadeIn">
                                    <i class="fas fa-tags fa-3x mb-3" style="color: var(--primary-color);"></i>
                                    <h4 class="text-muted">No categories found</h4>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="animate__animated animate__fadeIn">
                                <td><?= $cat['id'] ?></td>
                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="text-center">
                                    <a href="editcategory.php?id=<?= $cat['id'] ?>" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="deletecategory.php?id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this category?');
        }

        // Show flash messages if they exist
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($success): ?>
                showFlashMessage('<?= $success ?>', 'success');
            <?php endif; ?>
            
            <?php if ($error): ?>
                showFlashMessage('<?= $error ?>', 'danger');
            <?php endif; ?>
            
            // Add animation to table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
            });
        });

        function showFlashMessage(message, type) {
            const container = document.getElementById('flashMessageContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} show`;
            alert.innerHTML = `
                <strong><i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>${type === 'success' ? 'Success' : 'Error'}!</strong> 
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>
>>>>>>> origin/merging-branch
