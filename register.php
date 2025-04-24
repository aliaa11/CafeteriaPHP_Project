<?php
session_start();
include_once 'db.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
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

    if (empty($errors)) {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $check_query);

        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Email already registered.";
        } else {
          // Hash the password
          $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // هنا تشفير كلمة المرور

            $query = "INSERT INTO users (username, email, password) 
                      VALUES ('$username', '$email', '$hashedPassword')"; // استخدم $hashedPassword هنا
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
     style="width: 600px; background-color: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">


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


    <form method="POST">
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

      <button type="submit" class="btn w-100" style="background-color: #6f4e37; color: white;">Register</button>
      </form>
  </div>

</body>
</html>
