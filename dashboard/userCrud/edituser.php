<?php
// Include database connection
include_once __DIR__ . "/../../config/dbConnection.php";

// Fetch user data if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($myConnection, $sql);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        echo "<div class='alert alert-danger m-4'>User not found.</div>";
        exit;
    }
}

// Update user information if the form is submitted
if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Update user in the database
    $sql_update = "UPDATE users SET username = '$username', email = '$email' WHERE id = $id";
    if (mysqli_query($myConnection, $sql_update)) {
        echo "<div class='alert alert-success m-4'>User updated successfully.</div>";
        echo "<script>setTimeout(() => { window.location.href='users.php'; }, 1500);</script>";
    } else {
        echo "<div class='alert alert-danger m-4'>Error updating user: " . mysqli_error($myConnection) . "</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

    <h2>Edit User</h2>

    <form action="edituser.php?id=<?= $user['id'] ?>" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
    </form>

</body>
</html>
