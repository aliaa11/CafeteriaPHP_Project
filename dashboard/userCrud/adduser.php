<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
</head>
<body>
    <form action="" method="POST" enctype="multipart/form-data">
        <table>
            <tr>
                <td>Name</td>
                <td><input type="text" name="name" required></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td>Confirm Password</td>
                <td><input type="password" name="confirm_password" required></td>
            </tr>
            <tr>
                <td>Upload Image</td>
                <td><input type="file" name="profile_picture"></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="btn" value="Register"></td>
            </tr>
        </table>
    </form>
</body>
</html>

<?php
include_once __DIR__ . "/../../config/dbConnection.php";

if (isset($_POST["btn"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    
    $profile_picture = '';
    if (!empty($_FILES["profile_picture"]["name"])) {
        $profile_picture = $_FILES["profile_picture"]["name"];
        $tmp = $_FILES["profile_picture"]["tmp_name"];
        $uploadPath = "../uploads/picture/" . $profile_picture;
        move_uploaded_file($tmp, $uploadPath);
    }

    
    $sql = "INSERT INTO users (name, email, password, profile_picture) VALUES ('$name', '$email', '$password', '$profile_picture')";
    
    if (mysqli_query($myConnection, $sql)) {
        header("Location: users.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($myConnection);
    }
}
?>
