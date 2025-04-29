<?php
session_start();
include_once './config/dbConnection.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Field-specific validation
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
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
                $errors['general'] = "Incorrect email or password.";
            }
        } else {
            $errors['general'] = "Incorrect email or password.";
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
        .is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            color: #dc3545;
            display: block;
            margin-top: 0.25rem;
        }
        .card {
            width: 600px; 
            background-color: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(8px);
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

  <div class="card p-4 shadow-lg rounded-4">
    <h3 class="mb-3 text-center">Login</h3>
    
    <?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger">
        <?php echo $errors['general']; ?>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" name="email" id="email" 
               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
               required>
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" 
               class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
               required>
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn w-100" style="background-color: #6a4c93; color: white;">Login</button>
      
      <div class="text-center mt-3">
        <a href="register.php" class="d-block">Don't have an account? Register</a>
        <a href="forget_password.php" class="d-block mt-2">Forgot your password?</a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Client-side validation example
    document.querySelector('form').addEventListener('submit', function(e) {
        let isValid = true;
        const email = document.getElementById('email');
        const password = document.getElementById('password');

        // Clear previous validation
        email.classList.remove('is-invalid');
        password.classList.remove('is-invalid');
        
        // Email validation
        if (!email.value) {
            email.classList.add('is-invalid');
            if (!document.querySelector('#email ~ .invalid-feedback')) {
                let div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = 'Email is required.';
                email.parentNode.appendChild(div);
            }
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            email.classList.add('is-invalid');
            if (!document.querySelector('#email ~ .invalid-feedback')) {
                let div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = 'Please enter a valid email address.';
                email.parentNode.appendChild(div);
            }
            isValid = false;
        }

        // Password validation
        if (!password.value) {
            password.classList.add('is-invalid');
            if (!document.querySelector('#password ~ .invalid-feedback')) {
                let div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = 'Password is required.';
                password.parentNode.appendChild(div);
            }
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
  </script>
</body>
</html>