<?php
include_once __DIR__ . "/../../config/dbConnection.php";


 
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM categories WHERE id = $id";
    $result = mysqli_query($myConnection, $sql);
    $category = mysqli_fetch_assoc($result);

    if (!$category) {
        $_SESSION['flash_message'] = [
            'message' => 'Category not found.',
            'type' => 'danger'
        ];
        header("Location: categories.php");
        exit();
    }
}

if (isset($_POST['confirmDelete'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM categories WHERE id = $id";

    if (mysqli_query($myConnection, $sql)) {
        $_SESSION['flash_message'] = [
            'message' => 'Category deleted successfully',
            'type' => 'success'
        ];
        header("Location: categories.php");
        exit();
    } else {
        $_SESSION['flash_message'] = [
            'message' => 'Error deleting category: ' . mysqli_error($myConnection),
            'type' => 'danger'
        ];
        header("Location: categories.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #6a4c93;
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
            border-left: 4px solid var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-body p-4 text-center">
                        <h4 class="mb-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Delete Category</h4>
                        <p class="fs-5">Are you sure you want to delete the category:</p>
                        <h5 class="text-uppercase text-dark mb-4">"<?= $category['name'] ?>"</h5>
                        
                        <form method="POST" class="text-center">
                            <input type="hidden" name="id" value="<?= $category['id'] ?>">
                            <button type="submit" name="confirmDelete" class="btn btn-danger me-3">
                                <i class="fas fa-trash-alt me-2"></i>Yes, Delete
                            </button>
                            <a href="categories.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>