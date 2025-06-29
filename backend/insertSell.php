<?php
require '../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

// 🔥 Initialize Google Client
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// 🔥 Get IMEI
$imei = $_POST['imei'];
$sellDate = $_POST['sell_date'];

// 🔥 Fetch Folder ID from DB
$sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei' AND status = 'purchased'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("❌ Error: IMEI not found or not in 'purchased' status.");
}

$row = mysqli_fetch_assoc($result);
$imeiFolderId = $row['drive_folder_id'];

// 🔥 Make Public Permission
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);

// 🔥 Upload Buyer Photos
$buyerPhotoLinks = [];
$photoCount = 1;

foreach ($_FILES['buyer_photo']['tmp_name'] as $index => $tmpName) {
    $fileName = 'buyerPic' . $photoCount . '.' . pathinfo($_FILES['buyer_photo']['name'][$index], PATHINFO_EXTENSION);
    $mimeType = mime_content_type($tmpName);

    $fileMetadata = new Drive\DriveFile([
        'name' => $fileName,
        'parents' => [$imeiFolderId]
    ]);

    $content = file_get_contents($tmpName);

    $file = $service->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => $mimeType,
        'uploadType' => 'multipart',
        'fields' => 'id'
    ]);

    $service->permissions->create($file->id, $permission);

    $link = "https://drive.google.com/file/d/" . $file->id . "/view";
    $buyerPhotoLinks[] = $link;
    $photoCount++;
}

// 🔥 Upload Buyer Verification Video
$videoTmp = $_FILES['buyer_verification']['tmp_name'];
$videoName = 'BuyerVerification.mp4';
$videoMimeType = mime_content_type($videoTmp);

$videoMetadata = new Drive\DriveFile([
    'name' => $videoName,
    'parents' => [$imeiFolderId]
]);

$videoContent = file_get_contents($videoTmp);

$video = $service->files->create($videoMetadata, [
    'data' => $videoContent,
    'mimeType' => $videoMimeType,
    'uploadType' => 'multipart',
    'fields' => 'id'
]);

$service->permissions->create($video->id, $permission);

$buyerVerificationLink = "https://drive.google.com/file/d/" . $video->id . "/view";

// 🔥 Update Database
$update = "UPDATE purchased_mobiles 
    SET 
    status = 'sold',
    buyer_photo = '".json_encode($buyerPhotoLinks)."',
    buyer_verification = '$buyerVerificationLink',
    sell_date = '$sellDate'
    WHERE IMEI = '$imei'";

if (mysqli_query($conn, $update)) {
    echo "✅ Mobile marked as 'SOLD' successfully.";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
