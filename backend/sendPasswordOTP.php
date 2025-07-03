<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0); // Don't show raw errors
ini_set('log_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;

// Validate input
$current = $_POST['current'] ?? '';
$new = $_POST['new'] ?? '';

if (!$current || !$new) {
    echo json_encode(["status" => "error", "message" => "Missing input fields"]);
    exit;
}

// Fetch admin
$result = mysqli_query($conn, "SELECT * FROM admin_credentials LIMIT 1");
$admin = mysqli_fetch_assoc($result);

if (!$admin || !password_verify($current, $admin['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect current password"]);
    exit;
}

// Generate OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 300;
$_SESSION['new_password'] = password_hash($new, PASSWORD_DEFAULT);

// Send OTP
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'iamuniguy@gmail.com';      // ✅ Your Gmail
    $mail->Password = 'fwkh fqnq szje rife';      // ✅ Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('iamuniguy@gmail.com', 'SmartMobileApp');
    $mail->addAddress('smartmobileapp4u@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'OTP for Password Change';
    $mail->Body = "<h3>Your OTP is: <b>$otp</b></h3><p>This OTP is valid for 5 minutes.</p>";

    $mail->send();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Failed to send OTP: {$e->getMessage()}"]);
}
