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