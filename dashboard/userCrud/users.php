<?php
include_once __DIR__ . "/../../config/dbConnection.php";

$users = mysqli_query($myConnection, "SELECT * FROM users");
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

</body>
</html>
