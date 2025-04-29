<?php
include_once __DIR__ . "/../../config/dbConnection.php";
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($myConnection, $sql);
    $category = mysqli_fetch_assoc($result);
}

if (isset($_POST['updateCategory'])) {
    $name = mysqli_real_escape_string($myConnection, $_POST['name']);

    if (empty($name)) {
        echo "<div class='alert alert-danger'>Category name is required</div>";
    } else {
        $sql = "UPDATE categories SET name = '$name' WHERE id = $id";

        if (mysqli_query($myConnection, $sql)) {
            $_SESSION['flash_message'] = [
                'message' => 'Category updated successfully!',
                'type' => 'success'
            ];
            header("Location: categories.php");
            exit();
        } else {
            $error = "Error updating category: " . mysqli_error($myConnection);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color: #f8a5c2;
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #5a3d7a;
            border-color: #5a3d7a;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Category</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger animate__animated animate__shakeX">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label"><i class="fas fa-tag me-2"></i>Category Name</label>
                                <input type="text" name="name" id="name" value="<?= $category['name'] ?>" class="form-control" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="categories.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                                <button type="submit" name="updateCategory" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update
                                </button>
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