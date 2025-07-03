<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

// ✅ Get IMEI
$imei = $_POST['imei'] ?? die("IMEI not provided");

// ✅ Find Drive folder ID from DB
$sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei'";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    die("IMEI not found in database.");
}
$row = mysqli_fetch_assoc($result);
$folderId = $row['drive_folder_id'];

// ✅ Delete Google Drive Folder
$client = new Client();
$client->setAuthConfig(__DIR__ . '/backend/credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

try {
    $service->files->delete($folderId);
} catch (Exception $e) {
    // Optional: Log error but continue with DB delete
}

// ✅ Delete from database
mysqli_query($conn, "DELETE FROM purchased_mobiles WHERE IMEI = '$imei'");

// ✅ Redirect to index or success page
header("Location: success.php?message=IMEI deleted successfully");
exit;
