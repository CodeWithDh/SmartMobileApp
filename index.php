<?php
include 'backend/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'backend/PHPMailer/src/Exception.php';
require 'backend/PHPMailer/src/PHPMailer.php';
require 'backend/PHPMailer/src/SMTP.php';

session_start();

$error = "";
$step = "login"; // 'login' or 'otp'
$generatedOtp = "";

// OTP length
$otp = rand(100000, 999999);

// Handle Login Form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin_credentials WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        if ($password === $row['password']) {
            // ✅ Credentials correct → Send OTP
            $_SESSION['email'] = $email;
            $_SESSION['otp'] = $otp;

            // Send OTP via Email
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'iamuniguy@gmail.com'; // Your Gmail
                $mail->Password   = 'fwkh fqnq szje rife'; // App password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('yourgmail@gmail.com', 'SmartMobileApp');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for SmartMobileApp';
                $mail->Body    = "Your OTP is <b>$otp</b>";

                $mail->send();
                $step = "otp"; // Move to OTP step

            } catch (Exception $e) {
                $error = "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        } else {
            $error = "Incorrect Password";
        }
    } else {
        $error = "Email Not Found";
    }
}

// Handle OTP Verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $enteredOtp = $_POST['otp'];

    if ($enteredOtp == $_SESSION['otp']) {
        // ✅ OTP correct → Redirect to dashboard
        unset($_SESSION['otp']);
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Incorrect OTP";
        $step = "otp"; // Stay in OTP step
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartMobileApp - Login & OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <style>
    body {
        background-color: #ffffff; /* ✅ White background */
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

    .btn-success {
        background-color: #4E71FF;
        border: none;
    }
    .btn-success:hover {
        background-color: #5409DA;
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
        width: 160px; /* ✅ Increase from 100px to 160px or more */
    max-width: 100%;
    height: auto;
    }
</style>

</head>
<body>

<div class="login-box">
    <div class="text-center mb-4 logo">
        <img src="assets/logo.png" width="100" alt="Logo">
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- ✅ Step 1: Login Form -->
    <?php if($step == "login"): ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button class="btn btn-danger w-100" type="submit" name="login">Login</button>
        </form>
    <?php endif; ?>

    <!-- ✅ Step 2: OTP Verification Form -->
    <?php if($step == "otp"): ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Enter OTP sent to your email</label>
                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
            </div>
            <button class="btn btn-success w-100" type="submit" name="verify_otp">Verify OTP</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>

