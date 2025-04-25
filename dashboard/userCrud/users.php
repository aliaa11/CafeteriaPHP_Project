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
    <title>Users List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-body">
            <h3 class="text-center text-primary mb-4">Users List</h3>

            <table class="table table-bordered table-hover table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users)) : ?>
                        <tr>
                            <td>
                                <?php if (!empty($user['profile_pic'])): ?>
                                    <img src="<?= $user['profile_pic'] ?>" alt="User Pic" width="50" class="rounded-circle">
                                <?php else: ?>
                                    <img src="default.png" alt="Default" width="50" class="rounded-circle">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : 'secondary' ?>">
                                <?= ucfirst($user['role']) ?></span></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

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
