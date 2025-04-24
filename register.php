<?php
session_start();
<<<<<<< HEAD
include_once './config/dbConnection.php';

$errors = [];
define('USER_UPLOAD_DIR', '/opt/lampp/htdocs/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/');
define('USER_PUBLIC_URL', '/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($myConnection, $_POST['username']);
    $email = mysqli_real_escape_string($myConnection, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $insert_query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    
    if (mysqli_query($myConnection, $insert_query)) {
        $user_id = mysqli_insert_id($myConnection);
        
        $profile_picture = null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $fileName = $_FILES['profile_picture']['name'];
            $fileTmp = $_FILES['profile_picture']['tmp_name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExt = ["png", "jpg", "jpeg", "gif"];
            
            if (in_array($fileExt, $allowedExt)) {
                $newFilename = uniqid('user_', true) . '.' . $fileExt;
                $targetPath = USER_UPLOAD_DIR . $newFilename;
                
                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $profile_picture = $newFilename;
                    
                    $update_query = "UPDATE users SET profile_picture = '$profile_picture' WHERE id = $user_id";
                    mysqli_query($myConnection, $update_query);
                }
            }
        }
      }

    if (empty($errors)) {
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($myConnection, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
=======
include_once 'db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];
    $room_number = $_POST["room_number"];

    if (empty($username) || empty($email) || empty($password) || empty($room_number)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Handle profile picture
    if (!empty($_FILES["profile_picture"]["name"])) {
        $fileName = $_FILES["profile_picture"]["name"];
        $fileTmp = $_FILES["profile_picture"]["tmp_name"];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ["png", "jpg", "jpeg", "gif"];

        if (in_array($ext, $allowed)) {
            $newFileName = "images/" . time() . "_" . $fileName;
            move_uploaded_file($fileTmp, $newFileName);
            $profile_picture = $newFileName;
        } else {
            $errors[] = "Invalid image format. Allowed: png, jpg, jpeg, gif";
        }
    } else {
        $profile_picture = "images/default.png";
    }

    if (empty($errors)) {
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $check_query);
>>>>>>> 63d3ef8 (my work for cafeteria project)

        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, email, password, room_number, profile_picture) 
                      VALUES ('$username', '$email', '$hashedPassword', '$room_number', '$profile_picture')";
            $insert_result = mysqli_query($connection, $query);

            if ($insert_result) {
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Error inserting user: " . mysqli_error($connection);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="
  background: url('images/5785243870685153502.jpg') no-repeat center center;
  background-size: cover;" 
  class="d-flex justify-content-center align-items-center vh-100">

  <div class="card p-4 shadow-lg rounded-4"
       style="width: 600px; background-color: rgba(255,255,255,0.7); backdrop-filter: blur(8px);">
    <h3 class="mb-3 text-center">Register</h3>

    <?php if (!empty($errors)): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        let errors = <?php echo json_encode($errors); ?>;
        let alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-danger";
        alertDiv.role = "alert";
        let list = document.createElement("ul");
        list.className = "mb-0";
        errors.forEach(function(err) {
          let li = document.createElement("li");
          li.textContent = err;
          list.appendChild(li);
        });
        alertDiv.appendChild(list);
        let card = document.querySelector(".card");
        card.prepend(alertDiv);
      });
    </script>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" name="email" id="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="confirm" class="form-label">Confirm Password</label>
        <input type="password" name="confirm" id="confirm" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="room_number" class="form-label">Room Number</label>
        <select name="room_number" id="room_number" class="form-control" required>
          <option value="">Select Room</option>
          <option value="101">101</option>
          <option value="102">102</option>
          <option value="103">103</option>
          <option value="104">104</option>
          <option value="105">105</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="profile_picture" class="form-label">Profile Picture</label>
        <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept="image/*">
      </div>

      <button type="submit" class="btn w-100" style="background-color: #6f4e37; color: white;">Register</button>
      <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
      </div>
    </form>
  </div>

</body>
</html>


