<?php
include 'backend/db.php';
session_start();

$error = "";

// Handle Login Form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin_credentials WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // ✅ Check hashed password
        if (password_verify($password, $row['password'])) {
            // ✅ Credentials correct → Login
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "❌ Incorrect Password";
        }
    } else {
        $error = "❌ Email Not Found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartMobileApp - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-box {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-top: 5px solid #5409DA;
        }

        .btn-primary {
            background-color: #5409DA;
            border: none;
        }
        .btn-primary:hover {
            background-color: #4E71FF;
        }

        .text-primary {
            color: #5409DA;
        }

        .form-control:focus {
            border-color: #4E71FF;
            box-shadow: 0 0 0 0.2rem rgba(78, 113, 255, 0.25);
        }

        .alert {
            background-color: #BBFBFF;
            color: #5409DA;
            border: 1px solid #8DD8FF;
        }

        .logo img {
            width: 200px;
            max-width: 100%;
            height: auto;
        }
    </style>

</head>
<body>

<div class="login-box">
    <div class="text-center mb-4 logo">
        <img src="assets/logo.png" alt="Logo">
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <!-- ✅ Login Form -->
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit" name="login">Login</button>
    </form>
</div>

</body>
</html>
