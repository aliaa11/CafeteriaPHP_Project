<?php
include_once __DIR__ . "/../../config/dbConnection.php";

define('UPLOAD_DIR', '/opt/lampp/htdocs/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/');
define('PUBLIC_URL', '/cafeteriaPHP/CafeteriaPHP_Project/Public/uploads/users/');

$nameError = $emailError = $passwordError = $confirmPasswordError = $profilePictureError = '';
$successMessage = '';

if (isset($_POST["btn"])) {
    $username = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $profile_picture = null;
    $valid = true;

    // Name validation
    if (empty($username)) {
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

    if (!empty($_FILES["profile_picture"]["name"])) {
        $allowed_extensions = ['jpeg', 'jpg', 'png', 'gif'];
        $file_name = $_FILES["profile_picture"]["name"];
        $file_tmp = $_FILES["profile_picture"]["tmp_name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $profilePictureError = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
            $valid = false;
        } else {
            $new_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-]/", "", $file_name);
            $target_path = UPLOAD_DIR . $new_filename;
                    
            if (move_uploaded_file($file_tmp, $target_path)) {
                $profile_picture = $new_filename;
            } else {
                $profilePictureError = 'Failed to upload image.';
                $valid = false;
            }
        }
    }

    if ($valid) {
        // Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO users (username, email, password, profile_picture) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($myConnection, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $profile_picture);
        
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a4c93;
            --secondary-color: #8a5a44;
            --accent-color: #f8a5c2;
            --light-bg: #f9f7f7;
            --card-bg: #ffffff;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(135deg, #f9f7f7 0%, #e8f4f8 100%);
        }
        
        .add-user-card {
            max-width: 600px;
            margin: 2rem auto;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 2.5rem;
            transition: all 0.3s ease;
            border: none;
        }
        
        .add-user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .add-user-card h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
        }
        
        .add-user-card h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--accent-color);
            border-radius: 3px;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(106, 76, 147, 0.25);
        }
        
        .btn-primary-custom {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            width: 48%;
        }
        
        .btn-primary-custom:hover {
            background-color: #5a3d7a;
            transform: translateY(-2px);
        }
        
        .btn-secondary-custom {
            background-color: #6c757d;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            width: 48%;
        }
        
        .btn-secondary-custom:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-group-custom {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
        }
        
        .preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #eee;
            display: none;
        }
        
        .file-upload-label {
            display: block;
            cursor: pointer;
        }
        
        .file-upload-label:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="add-user-card animate__animated animate__fadeIn">
            <h2><i class="fas fa-user-plus me-2"></i>Add New User</h2>

            <!-- Display Success Message -->
            <?php if ($successMessage) : ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form with Bootstrap Validation -->
            <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control <?= $nameError ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($username ?? ''); ?>" placeholder="Enter name" required>
                    <div class="invalid-feedback">
                        <?= $nameError ? $nameError : 'Please provide a valid name.' ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control <?= $emailError ? 'is-invalid' : '' ?>" 
                           value="<?= htmlspecialchars($email ?? ''); ?>" placeholder="Enter email" required>
                    <div class="invalid-feedback">
                        <?= $emailError ? $emailError : 'Please provide a valid email.' ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control <?= $passwordError ? 'is-invalid' : '' ?>" 
                           placeholder="Enter password (min 8 characters)" required minlength="8">
                    <div class="invalid-feedback">
                        <?= $passwordError ? $passwordError : 'Password must be at least 8 characters.' ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="form-control <?= $confirmPasswordError ? 'is-invalid' : '' ?>" 
                           placeholder="Confirm password" required>
                    <div class="invalid-feedback">
                        <?= $confirmPasswordError ? $confirmPasswordError : 'Passwords must match.' ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Profile Picture</label>
                    <label class="file-upload-label">
                        <input type="file" id="profile_picture" name="profile_picture" 
                               class="form-control <?= $profilePictureError ? 'is-invalid' : '' ?>"
                               accept="image/jpeg, image/png, image/gif" onchange="previewImage(this)">
                        <img id="imagePreview" class="preview-image" src="#" alt="Preview">
                    </label>
                    <?php if ($profilePictureError) : ?>
                        <div class="invalid-feedback d-block"><?= $profilePictureError; ?></div>
                    <?php else: ?>
                        <small class="text-muted">Optional: JPG, PNG or GIF (max 5MB)</small>
                    <?php endif; ?>
                </div>

                <div class="btn-group-custom">
                    <button type="submit" name="btn" class="btn btn-primary-custom">
                        <i class="fas fa-save me-2"></i>Add User
                    </button>
                    <a href="users.php" class="btn btn-secondary-custom">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap client-side validation
        (function() {
            'use strict';
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                    
                    // Custom password match validation
                    const password = document.getElementById("password");
                    const confirm_password = document.getElementById("confirm_password");
                    
                    if (password.value !== confirm_password.value) {
                        confirm_password.setCustomValidity("Passwords don't match");
                        confirm_password.classList.add('is-invalid');
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        confirm_password.setCustomValidity('');
                        confirm_password.classList.remove('is-invalid');
                    }
                }, false);
            });
            
            // Real-time password match validation
            document.getElementById('confirm_password').addEventListener('input', function() {
                const password = document.getElementById("password");
                const confirm_password = this;
                
                if (password.value !== confirm_password.value) {
                    confirm_password.setCustomValidity("Passwords don't match");
                    confirm_password.classList.add('is-invalid');
                } else {
                    confirm_password.setCustomValidity('');
                    confirm_password.classList.remove('is-invalid');
                }
            });
        })();
        
        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>