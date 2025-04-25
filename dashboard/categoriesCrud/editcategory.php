<?php
include_once __DIR__ . "/../../config/dbConnection.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql="SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($myConnection,$sql);
    $category = mysqli_fetch_assoc($result);
}

if (isset($_POST['updateCategory'])) {
    $name = mysqli_real_escape_string($myConnection, $_POST['name']);

    if (empty($name)) {
        echo "<div class='alert alert-danger'>Category name is required</div>";
    } else {
        $sql = "UPDATE categories SET name = '$name' WHERE id = $id";

        if (mysqli_query($myConnection, $sql)) {
            echo "<div class='alert alert-success'>Category updated successfully</div>";
            header("location:categories.php");
        } else {
            echo "<div class='alert alert-danger'>Error updating category: " . mysqli_error($myConnection) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow rounded-4">
                <div class="card-body p-4">
                    <h4 class="mb-4 text-center text-primary">Edit Category</h4>

                    <?= $message ?? '' ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" name="name" id="name" value="<?= $category['name'] ?>" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" name="updateCategory" class="btn btn-success px-4">Update</button>
                            <a href="categories.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>