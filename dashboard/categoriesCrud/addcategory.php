<?php
include_once __DIR__ . "/../../config/dbConnection.php";

$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];

  if (!empty($name)) {


    
    $stmt = mysqli_prepare($myConnection, "INSERT INTO categories (name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $name);
    if (mysqli_stmt_execute($stmt)) {
      $success = "Category added successfully!";
    } else {
      $error = "Failed to add category.";
    }
    mysqli_stmt_close($stmt);
  } else {
    $error = "Category name is required.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Category</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Add New Category</h4>
          </div>
          <div class="card-body">
            <?php if ($success): ?>
              <div class="alert alert-success"> <?= $success ?> </div>
            <?php elseif ($error): ?>
              <div class="alert alert-danger"> <?= $error ?> </div>
            <?php endif; ?>
            <form method="POST" action="">
              <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
              </div>
              <div class="d-flex justify-content-between">
                <a href="categories.php" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-success">Add Category</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
