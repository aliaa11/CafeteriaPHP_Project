<?php
session_start(); // Start the session
include './config/dbConnection.php'; 

$name = 'Admin';
$email = 'admin@gmail.com';
$password = password_hash('aliaa123', PASSWORD_DEFAULT);
$profile_picture = './Public/uploads/users/admin.jpg';
$role = 'admin';

$sql = "INSERT INTO users (username, email, password, role, profile_picture)
        VALUES ('$name', '$email', '$password', '$role', '$profile_picture')";

if (mysqli_query($myConnection, $sql)) {
    // Get the inserted admin ID
    $admin_id = mysqli_insert_id($myConnection);
    
    // Store admin data in session
    $_SESSION['user_id'] = $admin_id;
    $_SESSION['username'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['profile_picture'] = $profile_picture;
    print_r($_SESSION);
    echo "Admin added successfully and logged in";
} else {
    echo "Error: " . mysqli_error($myConnection);
}

mysqli_close($myConnection);
?>