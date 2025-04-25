<?php
include_once __DIR__ . "/../../config/dbConnection.php";


if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($myConnection, "SELECT * FROM users WHERE id = $id");
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        echo "<div class='alert alert-danger m-4'>User not found.</div>";
        exit;
    }
}

if (isset($_POST['updateUser'])) {
    $name = mysqli_real_escape_string($myConnection, $_POST['name']);
    $email = mysqli_real_escape_string($myConnection, $_POST['email']);
    $role = mysqli_real_escape_string($myConnection, $_POST['role']);

    if (empty($name) || empty($email)) {
        $message = "<div class='alert alert-danger'>All fields are required</div>";
    } else {
        $sql = "UPDATE users SET name = '$name', email = '$email', role = '$role' WHERE id = $id";

        if (mysqli_query($myConnection, $sql)) {
            $message = "<div class='alert alert-success'>User updated successfully</div>";
            echo "<script>setTimeout(() => { window.location.href='users.php'; }, 1500);</script>";
        } else {
            $message = "<div class='alert alert-danger'>Error updating user: " . mysqli_error($myConnection) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-center text-primary">Edit User</h4>

                    <?= $message ?? '' ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" id="name" value="<?= $user['name'] ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" value="<?= $user['email'] ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select">
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" name="updateUser" class="btn btn-success px-4">Update</button>
                            <a href="users.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
