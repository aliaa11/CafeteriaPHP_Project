<?php
include_once __DIR__ . "/../../config/dbConnection.php";

$nameError = $emailError = $passwordError = $confirmPasswordError = $profilePictureError = '';
$successMessage = '';

if (isset($_POST["btn"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $profile_picture = '';
    $valid = true;

    // Name validation
    if (empty($name)) {
        $nameError = 'Name is required.';
        $valid = false;
    }

    // Email validation
    if (empty($email)) {
        $emailError = 'Email is required.';
        $valid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = 'Invalid email format.';
        $valid = false;
    }

    // Password validation
    if (empty($password)) {
        $passwordError = 'Password is required.';
        $valid = false;
    } elseif (strlen($password) < 8) {
        $passwordError = 'Password must be at least 8 characters.';
        $valid = false;
    }

    // Confirm password validation
    if ($password !== $confirm_password) {
        $confirmPasswordError = 'Passwords do not match.';
        $valid = false;
    }

    // Profile picture validation
    if (!empty($_FILES["profile_picture"]["name"])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES["profile_picture"]["type"];
        
        if (!in_array($file_type, $allowed_types)) {
            $profilePictureError = 'Only JPG, PNG, and GIF files are allowed.';
            $valid = false;
        } else {
            $profile_picture = $_FILES["profile_picture"]["name"];
            $tmp = $_FILES["profile_picture"]["tmp_name"];
            $uploadPath = __DIR__ . "/images/" . $profile_picture;
            
            if (!move_uploaded_file($tmp, $uploadPath)) {
                $profilePictureError = 'Failed to upload image.';
                $valid = false;
            }
        }
    }

    if ($valid) {
        // Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO users (name, email, password, profile_picture) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($myConnection, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $profile_picture);
        
        if (mysqli_stmt_execute($stmt)) {
            $successMessage = 'User added successfully!';
            header("Location: users.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($myConnection);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<style>
    body {
        background: #f2f2f2;
        font-family: 'Poppins', sans-serif;
    }
    .add-user-card {
        max-width: 500px;
        margin: 60px auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        padding: 40px;
        transition: 0.3s;
    }
    .add-user-card:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    }
    .add-user-card h2 {
        margin-bottom: 30px;
        text-align: center;
        color: #333;
        font-weight: 600;
    }
    .form-label {
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        margin-bottom: 20px;
        transition: border-color 0.3s;
    }
    .form-control:focus {
        border-color: #4CAF50;
        box-shadow: none;
    }
    .btn-primary-custom {
        background-color: #4CAF50;
        border: none;
        width: 48%;
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-primary-custom:hover {
        background-color: #45A049;
    }
    .btn-secondary-custom {
        background-color: #ccc;
        border: none;
        width: 48%;
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-secondary-custom:hover {
        background-color: #bbb;
    }
    .btn-group-custom {
        display: flex;
        justify-content: space-between;
    }
    .alert {
        margin-top: 20px;
        font-size: 1.1rem;
    }
    .was-validated .form-control:invalid, .form-control.is-invalid {
        border-color: #dc3545;
    }
    .was-validated .form-control:valid, .form-control.is-valid {
        border-color: #28a745;
    }
    .invalid-feedback {
        color: #dc3545;
        margin-top: -15px;
        margin-bottom: 15px;
    }
    .valid-feedback {
        color: #28a745;
        margin-top: -15px;
        margin-bottom: 15px;
    }
</style>

<div class="add-user-card">
    <h2>Add New User</h2>

    <!-- Display Success Message -->
    <?php if ($successMessage) : ?>
        <div class="alert alert-success"><?= $successMessage; ?></div>
    <?php endif; ?>

    <!-- Form with Bootstrap Validation -->
    <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control <?= $nameError ? 'is-invalid' : ''; ?>" 
                   value="<?= htmlspecialchars($name ?? ''); ?>" placeholder="Enter name" required>
            <?php if ($nameError) : ?>
                <div class="invalid-feedback"><?= $nameError; ?></div>
        
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control <?= $emailError ? 'is-invalid' : ''; ?>" 
                   value="<?= htmlspecialchars($email ?? ''); ?>" placeholder="Enter email" required>
            <?php if ($emailError) : ?>
                <div class="invalid-feedback"><?= $emailError; ?></div>
        
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control <?= $passwordError ? 'is-invalid' : ''; ?>" 
                   placeholder="Enter password (min 8 characters)" required minlength="8">
            <?php if ($passwordError) : ?>
                <div class="invalid-feedback"><?= $passwordError; ?></div>
        
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= $confirmPasswordError ? 'is-invalid' : ''; ?>" 
                   placeholder="Confirm password" required>
            <?php if ($confirmPasswordError) : ?>
                <div class="invalid-feedback"><?= $confirmPasswordError; ?></div>
        <strong>enter </strong>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="profile_picture" class="form-label">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture" class="form-control <?= $profilePictureError ? 'is-invalid' : ''; ?>"
                   accept="image/jpeg, image/png, image/gif">
            <?php if ($profilePictureError) : ?>
                <div class="invalid-feedback"><?= $profilePictureError; ?></div>
            <?php else: ?>
                <small class="text-muted">Optional: JPG, PNG or GIF (max 5MB)</small>
            <?php endif; ?>
        </div>

        <div class="btn-group-custom mt-4">
            <button type="submit" name="btn" class="btn btn-primary-custom">Add User</button>
            <a href="users.php" class="btn btn-secondary-custom">Cancel</a>
        </div>
    </form>
</div>

<script>
// Bootstrap client-side validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // Password match validation
        var password = document.getElementById("password");
        var confirm_password = document.getElementById("confirm_password");

        function validatePassword() {
            if (password.value !== confirm_password.value) {
                confirm_password.setCustomValidity("Passwords don't match");
                confirm_password.classList.add('is-invalid');
            } else {
                confirm_password.setCustomValidity('');
                confirm_password.classList.remove('is-invalid');
                confirm_password.classList.add('is-valid');
            }
        }

        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
    });
})();
</script>
