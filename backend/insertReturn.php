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
$return_description = $_POST['return_description'];  // ✅ Correct variable name

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

$chunkSizeBytes = 1 * 1024 * 1024; // 1 MB per chunk

// ✅ Prepare Drive File Metadata
$videoMetadata = new Google\Service\Drive\DriveFile([
    'name' => 'SellerVerification.mp4',  // 🔥 Change name as needed
    'parents' => [$imeiFolderId]
]);

// ✅ Create Upload Request
$request = $service->files->create($videoMetadata, [
    'fields' => 'id'
]);

// ✅ Initialize Chunk Upload
$media = new Google\Http\MediaFileUpload(
    $client,
    $request,
    'video/mp4',
    null,
    true,
    $chunkSizeBytes
);

$media->setFileSize(filesize($videoTmp));

// ✅ Upload in Chunks
$handle = fopen($videoTmp, "rb");
while (!feof($handle)) {
    $chunk = fread($handle, $chunkSizeBytes);
    $media->nextChunk($chunk);
}
fclose($handle);

// ✅ Get Uploaded File ID
$uploadedFile = $media->getMediaObject();
$service->permissions->create($uploadedFile->id, new Google\Service\Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]));

// ✅ Get Link
$videoLink = "https://drive.google.com/file/d/" . $uploadedFile->id . "/view";


$service->permissions->create($video->id, $permission);

$returnVerificationLink = "https://drive.google.com/file/d/" . $video->id . "/view";

// 🔥 Update Database with Return Info
$update = "UPDATE purchased_mobiles 
SET 
    status = 'returned',
    return_photo = '".json_encode($returnPhotoLinks)."',
    return_verification = '$returnVerificationLink',
    return_description = '$return_description',
    return_date = CURRENT_TIMESTAMP
WHERE IMEI = '$imei'";

if (mysqli_query($conn, $update)) {
    echo "✅ Mobile marked as 'RETURNED' successfully.";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
