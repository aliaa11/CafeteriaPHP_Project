<?php
session_start();
include_once './config/dbConnection.php';

$errors = [];
define('USER_UPLOAD_DIR', '/opt/lampp/htdocs/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/');
define('USER_PUBLIC_URL', '/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($myConnection, $_POST['username']);
    $email = mysqli_real_escape_string($myConnection, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm'];
    
    // Validation checks
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }
    
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm'] = "Passwords do not match.";
    }
    
    // Check if email exists
    if (!isset($errors['email'])) {
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($myConnection, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors['email'] = "Email already registered.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Only proceed if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $profile_picture = null;
        
        // Handle file upload if present
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
                }
            } else {
                $errors['profile_picture'] = "Invalid file type. Only PNG, JPG, JPEG, GIF allowed.";
            }
        }
        
        // If still no errors after file validation
        if (empty($errors)) {
            // Insert user with prepared statement
            $query = "INSERT INTO users (username, email, password, profile_picture) 
                      VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($myConnection, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashedPassword, $profile_picture);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Registration failed: " . mysqli_error($myConnection);
            }
            
            mysqli_stmt_close($stmt);
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
       style="width: 600px; background-color: rgba(255,255,255,0.7); backdrop-filter: blur(8px);">
    <h3 class="mb-3 text-center">Register</h3>

    <?php if (!empty($errors) && !isset($errors['username']) && !isset($errors['email']) && !isset($errors['password']) && !isset($errors['confirm']) && !isset($errors['profile_picture'])): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
        <?php if (isset($errors['username'])): ?>
            <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" required>
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="confirm" class="form-label">Confirm Password</label>
        <input type="password" name="confirm" id="confirm" class="form-control <?php echo isset($errors['confirm']) ? 'is-invalid' : ''; ?>" required>
        <?php if (isset($errors['confirm'])): ?>
            <div class="invalid-feedback"><?php echo $errors['confirm']; ?></div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label for="profile_picture" class="form-label">Profile Picture</label>
        <input type="file" name="profile_picture" id="profile_picture" class="form-control <?php echo isset($errors['profile_picture']) ? 'is-invalid' : ''; ?>">
        <?php if (isset($errors['profile_picture'])): ?>
            <div class="invalid-feedback"><?php echo $errors['profile_picture']; ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn w-100" style="background-color:  #6a4c93;  color: white;">Register</button>
      <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Client-side validation example
    document.querySelector('form').addEventListener('submit', function(e) {
        let password = document.getElementById('password').value;
        let confirm = document.getElementById('confirm').value;
        
        if (password !== confirm) {
            e.preventDefault();
            let confirmField = document.getElementById('confirm');
            confirmField.classList.add('is-invalid');
            
            if (!document.querySelector('#confirm ~ .invalid-feedback')) {
                let div = document.createElement('div');
                div.className = 'invalid-feedback';
                div.textContent = 'Passwords do not match.';
                confirmField.parentNode.appendChild(div);
            }
        }
    });
  </script>
</body>
</html>