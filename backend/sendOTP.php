<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generate OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 300;

$toEmail = "smartmobileapp4u@gmail.com";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'iamuniguy@gmail.com'; // Your Gmail
    $mail->Password = 'fwkh fqnq szje rife'; // Your App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('iamuniguy@gmail.com', 'SmartMobileApp');
    $mail->addAddress($toEmail);

    $mail->isHTML(true);
    $mail->Subject = 'OTP for IMEI Deletion';
    $mail->Body    = "<h3>Your OTP is: <b>$otp</b></h3><p>This OTP is valid for 5 minutes.</p>";

    $mail->send();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "❌ Failed to send OTP."]);
}
?>
