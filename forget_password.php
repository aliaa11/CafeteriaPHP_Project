<?php
session_start();
include_once './config/dbConnection.php';

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Validation
    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $errors[] = "Please fill in all fields.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        // Check if email exists
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($myConnection, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            // Email exists, update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE email = ?";
            $update_stmt = mysqli_prepare($myConnection, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $email);
            mysqli_stmt_execute($update_stmt);

            $success = "Password updated successfully. You can now login.";
        } else {
            $errors[] = "No account found with this email.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
   .navbar {
            background-color: rgb(75, 49, 102);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
        
        .nav-link {
            color: white;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }
</style>
<body style="
  background: url('images/2151561247.jpg') no-repeat center center;
  background-size: cover;" 
  class="d-flex justify-content-center align-items-center vh-100">
  <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="./home.php">Cafeteria</a>
        </div>
    </nav>
  <div class="card p-4 shadow-lg rounded-4" 
     style="width: 600px; background-color: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
    <h3 class="mb-3 text-center">Forgot Password</h3>

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

    <?php if (!empty($success)): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        let success = <?php echo json_encode($success); ?>;

        let alertDiv = document.createElement("div");
        alertDiv.className = "alert alert-success";
        alertDiv.role = "alert";
        alertDiv.textContent = success;

        let card = document.querySelector(".card");
        card.prepend(alertDiv);
      });
    </script>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="email" class="form-label">Enter Your Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="new_password" class="form-label">New Password</label>
        <input type="password" name="new_password" id="new_password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
      </div>

      <button type="submit" class="btn w-100" style="background-color:  #6a4c93; color: white;">Reset Password</button>
    </form>

    <div class="text-center mt-3">
      <a href="login.php">Back to Login</a>
    </div>

  </div>

</body>
</html>