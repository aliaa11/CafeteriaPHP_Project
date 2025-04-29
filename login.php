<?php
session_start();
include_once './config/dbConnection.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($errors)) {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($myConnection, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Verify the password using password_verify
            if (password_verify($password, $user['password'])) {
                
                $_SESSION["user_id"] = $user["id"]; 
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                // Redirect based on role
                if ($user["role"] === "admin") {
                  
                    header("Location: ./dashboard/index.php");
                } else {
                    header("Location: home.php");
                }
                exit;
            } else {
                $errors[] = "Incorrect email or password.";
            }
        } else {
            $errors[] = "Incorrect email or password.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="
  background: url('images/5785243870685153502.jpg') no-repeat center center;
  background-size: cover;" 
  class="d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow-lg rounded-4" 
     style="width: 600px; background-color: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);">
    <h3 class="mb-3 text-center">Login</h3>
    
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
        <label for="email" class="form-label">Email address</label>
        <input type="text" name="email" id="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>

      <button type="submit" class="btn w-100" style="background-color: #6f4e37; color: white;">Login</button>
      <div class="text-center mt-3">
  <a href="register.php">Don't have an account? Register</a>
  <br>
  <a href="forgot_password.php">Forgot your password?</a>
</div>

    </form>
  </div>

</body>
</html>



