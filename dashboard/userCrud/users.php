<?php

include_once __DIR__ . "/../../config/dbConnection.php";

// Pagination setup
$limit = 10; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get the search term from the input (no form submission)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch total number of users for pagination
$sql_count = "SELECT COUNT(*) AS total FROM users WHERE username LIKE ? OR email LIKE ?";
$stmt_count = $myConnection->prepare($sql_count);
$searchWildcard = "%$searchTerm%";
$stmt_count->bind_param("ss", $searchWildcard, $searchWildcard);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row = $result_count->fetch_assoc();
$total_users = $row['total'];
$total_pages = ceil($total_users / $limit);

// Fetch users with pagination and search
$sql = "SELECT id, username, email, profile_picture FROM users WHERE username LIKE ? OR email LIKE ? LIMIT ? OFFSET ?";
$stmt = $myConnection->prepare($sql);
$stmt->bind_param("ssii", $searchWildcard, $searchWildcard, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
} else {
    echo "<div class='alert alert-danger'>Error fetching users: " . $myConnection->error . "</div>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Existing styles remain unchanged */
    </style>
</head>
<body class="user-management">
    <div class="container-fluid px-4 py-4">
        <div class="card card-shadow mb-4">
            <div class="card-header py-3 d-flex flex-column flex-md-row justify-content-between align-items-center">
                <h2 class="m-0 font-weight-bold text-primary">User Management</h2>
                <div class="mt-2 mt-md-0">
                    <a href="adduser.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i> Add New User
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Search and Filter Row -->
                <div class="row mb-4 g-2">
                    <div class="col-12 col-md-6">
                        <div class="input-group search-box">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <div class="btn-group w-100 w-md-auto" role="group">
                            <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="all">All</button>
                            <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="admin">Admins</button>
                            <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="user">Users</button>
                        </div>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Image</th>
                                <th>Full Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
    <?php foreach ($users as $user): ?>
        <tr>
            <td data-label="Image">
                <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : '../assets/img/default-avatar.png' ?>" 
                     alt="User Image" class="user-avatar">
            </td>
            <td data-label="Full Name"><?= htmlspecialchars($user['username']) ?></td>
            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
            <td data-label="Actions">
                <!-- Edit Button -->
                <a href="edituser.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <!-- Delete Button -->
                <a href="deleteuser.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">
                    <i class="fas fa-trash-alt"></i> Delete
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>

                    </table>
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($searchTerm) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const userTableBody = document.getElementById('userTableBody');

            let debounceTimeout;

            // Debounced search function
            searchInput.addEventListener('input', function() {
                // Clear the previous timeout to reset the delay
                clearTimeout(debounceTimeout);

                // Set a new timeout to wait for the user to stop typing
                debounceTimeout = setTimeout(function() {
                    const searchTerm = searchInput.value.toLowerCase();

                    // Loop through the rows and filter by username or email
                    const rows = userTableBody.querySelectorAll('tr');
                    rows.forEach(function(row) {
                        const username = row.querySelector('td[data-label="Full Name"]').textContent.toLowerCase();
                        const email = row.querySelector('td[data-label="Email"]').textContent.toLowerCase();

                        // If either the username or email matches the search term, show the row
                        if (username.includes(searchTerm) || email.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }, 500); // 500ms debounce delay
            });
        });
    </script>
</body>
</html>
