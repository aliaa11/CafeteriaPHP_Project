<?php
include_once __DIR__ . "/../../config/dbConnection.php";

// Pagination setup
$limit = 10; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$serrch = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$whcon = [];
$params = [];
$types = '';

// Add search condition
if (!empty($serrch)) {
    $serachcard = "%$serrch%";
    $whcon[] = "(username LIKE ? OR email LIKE ?)";
    $params[] = $serachcard;
    $params[] = $serachcard;
    $types .= 'ss';
}

// Add filter condition
if ($filter === 'admin') {
    $whcon[] = "role = 'admin'";
} elseif ($filter === 'user') {
    $whcon[] = "role = 'user'";
}

// Prepare WHERE clause
$whereClause = empty($whcon) ? '' : 'WHERE ' . implode(' AND ', $whcon);

// Fetch total number of users for pagination
$sql_count = "SELECT COUNT(*) AS total FROM users $whereClause";
$stmt_count = $myConnection->prepare($sql_count);

if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}

$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row = $result_count->fetch_assoc();
$total_users = $row['total'];
$total_pages = ceil($total_users / $limit);

// Fetch users with pagination, search and filter
$sql = "SELECT id, username, email, profile_picture, role FROM users $whereClause ORDER BY username LIMIT ? OFFSET ?";
$stmt = $myConnection->prepare($sql);

// Bind parameters based on whether we have search parameters or not
if (!empty($params)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-secondary:hover, .btn-outline-secondary.active {
            background-color: var(--primary-color);
            color: white;
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
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-color);
        }
        
        .action-btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 3px;
        }
        
        .no-users {
            text-align: center;
            padding: 50px 0;
        }
        
        .no-users i {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="header-title"><i class="fas fa-users me-2"></i>User Management</h2>
            <a href="adduser.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Add New User
            </a>
        </div>
        
        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <label for="search" class="form-label fw-medium"><i class="fas fa-search me-2"></i>Search Users</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Name or email" value="<?= htmlspecialchars($serrch) ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-filter me-2"></i>Search
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium"><i class="fas fa-filter me-2"></i>Filter</label>
                    <div class="btn-group w-100">
                        <button type="submit" name="filter" value="all" class="btn btn-outline-secondary <?= $filter === 'all' ? 'active' : '' ?>">All</button>
                        <button type="submit" name="filter" value="admin" class="btn btn-outline-secondary <?= $filter === 'admin' ? 'active' : '' ?>">Admins</button>
                        <button type="submit" name="filter" value="user" class="btn btn-outline-secondary <?= $filter === 'user' ? 'active' : '' ?>">Users</button>
                    </div>
                </div>
                <input type="hidden" name="page" value="1">
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($user['profile_picture'])): ?>
                                        <img src="../../Public/uploads/users/<?= $user['profile_picture'] ?>" 
                                             alt="User Image" class="user-avatar">
                                    <?php else: ?>
                                        <img src="../../Public/uploads/users/default.png" 
                                             alt="Default User Image" class="user-avatar">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <a href="edituser.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary action-btn me-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="deleteuser.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger action-btn" title="Delete" >
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-users">
                                <i class="fas fa-user-slash"></i>
                                <h4>No users found</h4>
                                <p class="text-muted">Try adjusting your search or filter</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Client-side search functionality
            const searchInput = document.getElementById('search');
            const userTableBody = document.getElementById('userTableBody');
            
            searchInput.addEventListener('input', function() {
                const serrch = this.value.toLowerCase();
                const rows = userTableBody.querySelectorAll('tr');
                
                rows.forEach(function(row) {
                    if (row.cells.length > 1) { // Skip the "no users" row
                        const username = row.cells[1].textContent.toLowerCase();
                        const email = row.cells[2].textContent.toLowerCase();
                        
                        if (username.includes(serrch) || email.includes(serrch)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>