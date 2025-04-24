<?php
session_start();

// تحقق إذا كان المستخدم قد سجل الدخول
if (!isset($_SESSION["username"])) {
    // إذا لم يكن هناك جلسة، إعادة التوجيه إلى صفحة تسجيل الدخول
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container text-center mt-5">
        <h1>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>!</h1>
        <p>You are logged in as a <?= htmlspecialchars($_SESSION["role"]) ?>.</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

</body>
</html>
