<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Users Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../styles/users.css" />
  </head>

  <body class="bg-light">
    <div class="container py-4">
      <h1 class="text-center mb-4">Users Management</h1>
      
      <!-- Search Section -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row justify-content-center">
            <div class="col-md-8">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="search-bar" class="form-control" placeholder="Search by user name...">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users List -->
      <div id="usersDashboard" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      const usersDashboard = document.getElementById("usersDashboard");
      const searchBar = document.getElementById("search-bar");

      function getUsersFromLocalStorage() {
        return JSON.parse(localStorage.getItem("users")) || [];
      }

      function renderUsers(filteredUsers) {
        usersDashboard.innerHTML = "";

        if (filteredUsers.length === 0) {
          usersDashboard.innerHTML = '<div class="col"><div class="alert alert-info">No users found</div></div>';
          return;
        }

        filteredUsers.forEach((user) => {
          const card = document.createElement("div");
          card.className = "col";
          card.innerHTML = `
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title">${user.username}</h5>
                <h6 class="card-subtitle mb-3 text-muted">${user.email || 'No email provided'}</h6>
                
                <h6 class="mt-3">Purchased Products:</h6>
                <ul class="list-group list-group-flush">
                  ${user.products ? user.products.map(product => `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      ${product.Title}
                      <span class="badge bg-primary rounded-pill">${product.progressPercentage || 0}%</span>
                    </li>
                  `).join('') : '<li class="list-group-item">No products purchased</li>'}
                </ul>
              </div>
              <div class="card-footer bg-transparent">
                <small class="text-muted">Joined: ${new Date(user.joinDate || Date.now()).toLocaleDateString()}</small>
              </div>
            </div>
          `;
          usersDashboard.appendChild(card);
        });
      }

      const users = getUsersFromLocalStorage();
      renderUsers(users);

      searchBar.addEventListener("input", (event) => {
        const searchTerm = event.target.value.toLowerCase();
        const filteredUsers = users.filter((user) =>
          user.username.toLowerCase().includes(searchTerm) || 
          (user.email && user.email.toLowerCase().includes(searchTerm))
        );
        renderUsers(filteredUsers);
      });
    </script>
  </body>
</html>