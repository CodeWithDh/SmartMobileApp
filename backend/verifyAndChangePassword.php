<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require 'db.php';

$otp = $_POST['otp'] ?? '';

if (!isset($_SESSION['otp']) || time() > $_SESSION['otp_expiry']) {
    echo json_encode(["status" => "error", "message" => "OTP expired or not sent"]);
    exit;
}

if ($_SESSION['otp'] != $otp) {
    echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
    exit;
}

$newHashed = $_SESSION['new_password'] ?? '';
if (!$newHashed) {
    echo json_encode(["status" => "error", "message" => "Missing new password"]);
    exit;
}

// ✅ Update
$update = "UPDATE admin_credentials SET password = '$newHashed' LIMIT 1";
if (mysqli_query($conn, $update)) {
    unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['new_password']);
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password"]);
}
