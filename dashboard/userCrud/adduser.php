<div class="card" style="max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px #ccc;">
    <h2 style="text-align: center; margin-bottom: 20px;">Add New User</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label>Profile Picture</label>
            <input type="file" name="profile_picture" class="form-control">
        </div>

        <div style="display: flex; justify-content: space-between;">
            <button type="submit" name="btn" class="btn btn-success">Add User</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
include_once __DIR__ . "/../../config/dbConnection.php";

if (isset($_POST["btn"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
        exit();
    }

    $profile_picture = '';
    if (!empty($_FILES["profile_picture"]["name"])) {
        $profile_picture = $_FILES["profile_picture"]["name"];
        $tmp = $_FILES["profile_picture"]["tmp_name"];
        $uploadPath = __DIR__ . "/images/" . $profile_picture;

        // Move uploaded file
        if (!move_uploaded_file($tmp, $uploadPath)) {
            echo "<script>alert('Failed to upload image.');</script>";
            exit();
        }
    }

    // Insert user into database
    $sql = "INSERT INTO users (name, email, password, profile_picture) VALUES ('$name', '$email', '$password', '$profile_picture')";
    
    if (mysqli_query($myConnection, $sql)) {
        header("Location: users.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($myConnection);
    }
}
?>
