<?php
require '../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

// Initialize Google Client
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);

$service = new Drive($client);

// 🔥 Parent Folder ID (main folder where all IMEI folders will be created)
$parentFolderId = '1Ah8EfWG-SpHPxZlggOk8VCCqfY_Q73W5';

// Get form data
$imei = $_POST['imei'];
$sellerName = $_POST['seller_name'];
$mobileName = $_POST['mobile_name'];
$faultDescription = $_POST['fault_description'];
$price = $_POST['price'];
$purchaseDate = $_POST['purchase_date'];

// 🔥 Create Folder with IMEI as name
$folderMetadata = new Drive\DriveFile([
    'name' => $imei,
    'parents' => [$parentFolderId],
    'mimeType' => 'application/vnd.google-apps.folder'
]);

$folder = $service->files->create($folderMetadata, [
    'fields' => 'id'
]);

$imeiFolderId = $folder->id;

// 🔥 Make Folder Public
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);
$service->permissions->create($imeiFolderId, $permission);

// ✅ Upload Seller Photos
$sellerPhotoLinks = [];
$photoCount = 1;

foreach ($_FILES['seller_photo']['tmp_name'] as $index => $tmpName) {
    $fileName = 'sellerPic' . $photoCount . '.' . pathinfo($_FILES['seller_photo']['name'][$index], PATHINFO_EXTENSION);
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
    $sellerPhotoLinks[] = $link;
    $photoCount++;
}

// ✅ Upload Verification Video
$videoTmp = $_FILES['verification_video']['tmp_name'];
$videoName = 'SellerVerification.mp4';
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

$verificationVideoLink = "https://drive.google.com/file/d/" . $video->id . "/view";

// ✅ Save to Database
$sql = "INSERT INTO purchased_mobiles 
(IMEI, seller_name, seller_photo, verification_video, mobile_name, fault_description, price, purchase_date, status, drive_folder_id) 
VALUES 
('$imei', '$sellerName', '".json_encode($sellerPhotoLinks)."', '$verificationVideoLink', '$mobileName', '$faultDescription', '$price', '$purchaseDate', 'purchased', '$imeiFolderId')";

if (mysqli_query($conn, $sql)) {
    echo "✅ Data saved successfully.";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
