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

// 🔥 Get Data from Form
$imei = $_POST['imei'];
$returnDate = $_POST['return_date'];
$returnDescription = $_POST['return_description'];

// 🔥 Fetch IMEI Folder ID from DB
$sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei' AND status = 'sold'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("❌ Error: IMEI not found or not in 'sold' status.");
}

$row = mysqli_fetch_assoc($result);
$imeiFolderId = $row['drive_folder_id'];

// 🔥 Set Public Permission for Drive Files
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);

// 🔥 Upload Return Photos
$returnPhotoLinks = [];
$photoCount = 1;

foreach ($_FILES['return_photo']['tmp_name'] as $index => $tmpName) {
    $fileName = 'returnPic' . $photoCount . '.' . pathinfo($_FILES['return_photo']['name'][$index], PATHINFO_EXTENSION);
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
    $returnPhotoLinks[] = $link;
    $photoCount++;
}

// 🔥 Upload Return Verification Video
$videoTmp = $_FILES['return_verification']['tmp_name'];
$videoName = 'ReturnVerification.mp4';
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

$returnVerificationLink = "https://drive.google.com/file/d/" . $video->id . "/view";

// 🔥 Update Database with Return Info
$update = "UPDATE purchased_mobiles 
    SET 
    status = 'returned',
    return_photo = '".json_encode($returnPhotoLinks)."',
    return_verification = '$returnVerificationLink',
    return_date = '$returnDate',
    return_description = '$returnDescription'
    WHERE IMEI = '$imei'";

if (mysqli_query($conn, $update)) {
    echo "✅ Mobile marked as 'RETURNED' successfully.";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
