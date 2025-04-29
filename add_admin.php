<?php
session_start();
include './config/dbConnection.php'; 

$name = 'Admin';
$email = 'admin@gmail.com';
$password = password_hash('aliaa123', PASSWORD_DEFAULT);
$profile_picture_path = './Public/uploads/users/admin.jpg';
$profile_picture = basename($profile_picture_path); 
$role = 'admin';

$sql = "INSERT INTO users (username, email, password, role, profile_picture)
        VALUES ('$name', '$email', '$password', '$role', '$profile_picture')";

if (mysqli_query($myConnection, $sql)) {
    $_SESSION['user_id'] = mysqli_insert_id($myConnection);
    $_SESSION['username'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['profile_picture'] = $profile_picture;
    
    mysqli_close($myConnection);
    ?>
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Created</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .admin-card {
                max-width: 500px;
                margin: 50px auto;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                background-color: white;
                text-align: center;
            }
            .admin-avatar {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                object-fit: cover;
                border: 5px solid #6a4c93;
                margin-bottom: 20px;
            }
            .success-message {
                color: #28a745;
                font-weight: bold;
                margin-bottom: 20px;
            }
            .admin-info {
                text-align: left;
                margin-top: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="admin-card">
            <h2 class="text-center mb-4">Admin Account Created</h2>
            
            <div class="success-message">
                <i class="fas fa-check-circle fa-2x"></i><br>
                Admin added successfully and logged in!
            </div>
            
            <!-- Display Admin Profile Picture -->
            <img src="./Public/uploads/users/<?php echo $profile_picture; ?>" alt="Admin Profile" class="admin-avatar">
            
            <!-- Display Admin Information -->
            <div class="admin-info">
                <h4>Admin Details:</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($role); ?></p>
                <p><strong>Profile Picture Path:</strong> <?php echo htmlspecialchars($profile_picture); ?></p>
            </div>
            
            <!-- Display Session Data -->
            <div class="admin-info mt-4">
                <h4>Session Data:</h4>
                <pre><?php print_r($_SESSION); ?></pre>
            </div>
            
            <a href="dashboard/index.php" class="btn btn-primary mt-3">
                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
            </a>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    </body>
    </html>
    <?php
} else {
    echo "<div class='alert alert-danger'>Error: " . mysqli_error($myConnection) . "</div>";
    mysqli_close($myConnection);
}
?>