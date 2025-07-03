<?php
header('Content-Type: application/json');
session_start();
require '../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

// Read POST data
$otp = $_POST['otp'] ?? '';
$imei = $_POST['imei'] ?? '';

if (!isset($_SESSION['otp']) || time() > ($_SESSION['otp_expiry'] ?? 0)) {
    echo json_encode(["status" => "error", "message" => "OTP expired or not sent."]);
    exit;
}

if ($_SESSION['otp'] != $otp) {
    echo json_encode(["status" => "error", "message" => "Invalid OTP"]);
    exit;
}

// Get Google Drive folder ID
$result = mysqli_query($conn, "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei'");
if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["status" => "error", "message" => "IMEI not found."]);
    exit;
}
$row = mysqli_fetch_assoc($result);
$folderId = $row['drive_folder_id'];

// Delete folder from Google Drive
try {
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->addScope(Drive::DRIVE);
    $service = new Drive($client);

    $service->files->delete($folderId); // If already deleted, it will throw an error — you may catch it if needed
} catch (Exception $e) {
    // continue, maybe already deleted
}

// Delete from database
mysqli_query($conn, "DELETE FROM purchased_mobiles WHERE IMEI = '$imei'");

// Clear session OTP
unset($_SESSION['otp'], $_SESSION['otp_expiry']);

echo json_encode(["status" => "success"]);
exit;
