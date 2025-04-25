<?php
include_once __DIR__ . "/../../config/dbConnection.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql="SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($myConnection, $sql);
    $category = mysqli_fetch_assoc($result);

    if (!$category) {
        echo "<div class='alert alert-danger m-4'>Category not found.</div>";
        exit;
    }
}

if (isset($_POST['confirmDelete'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM categories WHERE id = $id";

    if (mysqli_query($myConnection, $sql)) {
        echo "<div class='alert alert-success m-4'>Category deleted successfully</div>";
        echo "<script>setTimeout(() => { window.location.href='categories.php'; }, 1500);</script>";
    } else {
        echo "<div class='alert alert-danger m-4'>Error deleting category: " . mysqli_error($myConnection) . "</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow rounded-4 border-danger">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center text-danger">Delete Category</h4>

                    <p class="text-center fs-5">Are you sure you want to delete the category:</p>
                    <h5 class="text-center text-uppercase text-dark mb-4">"<?= $category['name'] ?>"</h5>

                    <form method="POST" class="text-center">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <button type="submit" name="confirmDelete" class="btn btn-danger px-4">Yes, Delete</button>
                        <a href="categories.php" class="btn btn-secondary px-4">Cancel</a>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
