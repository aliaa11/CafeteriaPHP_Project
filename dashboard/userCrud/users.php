<?php

include_once __DIR__ . "/../../config/dbConnection.php";

<<<<<<< HEAD
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

=======
$users = mysqli_query($myConnection, "SELECT * FROM users ORDER BY created_at DESC");
>>>>>>> b944f28 (some changes)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-avatar {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            transition: transform 0.3s;
        }
        .user-avatar:hover {
            transform: scale(1.1);
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        .table th {
            border-bottom: none;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .badge-admin {
            background-color: #dc3545;
        }
        .badge-user {
            background-color: #6c757d;
        }
        .action-btn {
            min-width: 80px;
            margin: 2px;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
        .card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 15px;
        }
        .card-header {
            background: transparent;
            border-bottom: none;
            padding-bottom: 0;
        }
        .search-box {
            max-width: 300px;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(106, 17, 203, 0.05);
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary"><i class="fas fa-users me-2"></i>Users Management</h3>
            <div>
                <a href="add_user.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Add New User
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group search-box">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="admin">Admins</button>
                        <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="user">Users</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        while ($user = mysqli_fetch_assoc($users)) : 
                            $statusClass = ($user['status'] ?? 'active') === 'active' ? 'status-active' : 'status-inactive';
                        ?>
                        <!-- <tr class="user-row" data-role="<?= strtolower($user['role']) ?>"> -->
                            <td class="text-muted"><?= $count++ ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://via.placeholder.com/50?text=User' ?>" 
                                         alt="User" class="user-avatar me-3">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                                        <small class="text-muted">ID: <?= $user['id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($user['email']) ?></div>
                                <small class="text-muted"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'No phone' ?></small>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= $user['role'] == 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-circle <?= $statusClass ?> me-1"></i>
                                <?= ucfirst($user['status'] ?? 'active') ?>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary action-btn me-2">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger action-btn"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<<<<<<< HEAD
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
=======
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.user-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            const rows = document.querySelectorAll('.user-row');
            
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.role === filter ? '' : 'none';
                }
            });
        });
    });

    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
>>>>>>> b944f28 (some changes)
</body>
</html>