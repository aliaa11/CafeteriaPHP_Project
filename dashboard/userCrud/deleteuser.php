<?php
// Include database connection
include_once __DIR__ . "/../../config/dbConnection.php";

// Check if user ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch user data to confirm before deletion
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($myConnection, $sql);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        echo "<div class='alert alert-danger m-4'>User not found.</div>";
        exit;
    }
}

// Delete user if the form is submitted
if (isset($_POST['confirmDelete'])) {
    $id = intval($_POST['id']);
    $sql_delete = "DELETE FROM users WHERE id = $id";
    
    if (mysqli_query($myConnection, $sql_delete)) {
        echo "<div class='alert alert-success m-4'>User deleted successfully</div>";
        echo "<script>setTimeout(() => { window.location.href='users.php'; }, 1500);</script>";
    } else {
        echo "<div class='alert alert-danger m-4'>Error deleting user: " . mysqli_error($myConnection) . "</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow rounded-4 border-danger">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center text-danger">Delete User</h4>

                    <p class="text-center fs-5">Are you sure you want to delete the user:</p>
                    <h5 class="text-center text-uppercase text-dark mb-4">"<?= htmlspecialchars($user['username']) ?>"</h5>

                    <form method="POST" class="text-center">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <button type="submit" name="confirmDelete" class="btn btn-danger px-4">Yes, Delete</button>
                        <a href="user_management.php" class="btn btn-secondary px-4">Cancel</a>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
