<?php
require '../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

// ✅ Initialize Google Client
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// ✅ Sanitize Inputs
$imei = mysqli_real_escape_string($conn, $_POST['imei']);
$buyer_name = mysqli_real_escape_string($conn, $_POST['buyer_name']);
$buyer_mobile = mysqli_real_escape_string($conn, $_POST['buyer_mobile']);
$sold_price = mysqli_real_escape_string($conn, $_POST['sold_price']);

// ✅ Fetch Drive Folder ID
$sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei' AND status = 'purchased'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("❌ Error: IMEI not found or not in 'purchased' status.");
}

$row = mysqli_fetch_assoc($result);
$imeiFolderId = $row['drive_folder_id'];

// ✅ Public permission for uploaded files
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);

// ✅ Initialize photo storage
$buyerPhotoLinks = [];
$photoCount = 1;

// ✅ Upload base64 captured buyer photos
if (!empty($_POST['captured_photos'])) {
    $capturedPhotos = explode("|", $_POST['captured_photos']);
    foreach ($capturedPhotos as $base64) {
        if (empty($base64)) continue;
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));

        $fileName = 'buyerPic' . ($photoCount++) . '.png';
        $fileMetadata = new Drive\DriveFile([
            'name' => $fileName,
            'parents' => [$imeiFolderId]
        ]);

        $file = $service->files->create($fileMetadata, [
            'data' => $imageData,
            'mimeType' => 'image/png',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        $service->permissions->create($file->id, $permission);
        $buyerPhotoLinks[] = "https://drive.google.com/file/d/" . $file->id . "/view";
    }
}

// ✅ Upload file-input buyer photos
if (!empty($_FILES['buyer_photo']['tmp_name'][0])) {
    foreach ($_FILES['buyer_photo']['tmp_name'] as $index => $tmpName) {
        if (!file_exists($tmpName) || empty($_FILES['buyer_photo']['name'][$index])) continue;

        $ext = pathinfo($_FILES['buyer_photo']['name'][$index], PATHINFO_EXTENSION);
        $fileName = 'buyerPic' . ($photoCount++) . '.' . $ext;
        $mimeType = mime_content_type($tmpName);

        $fileMetadata = new Drive\DriveFile([
            'name' => $fileName,
            'parents' => [$imeiFolderId]
        ]);

        $file = $service->files->create($fileMetadata, [
            'data' => file_get_contents($tmpName),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        $service->permissions->create($file->id, $permission);
        $buyerPhotoLinks[] = "https://drive.google.com/file/d/" . $file->id . "/view";
    }
}

// ✅ Upload buyer verification video (if present)
$buyerVerificationLink = '';
if (!empty($_FILES['buyer_verification']['tmp_name'])) {
    $videoTmp = $_FILES['buyer_verification']['tmp_name'];
    $videoName = 'BuyerVerification.mp4';
    $videoSize = filesize($videoTmp);
    $videoMimeType = mime_content_type($videoTmp);

    // Create resumable session
    $token = $client->fetchAccessTokenWithAssertion();
    $response = $client->getHttpClient()->request(
        'POST',
        'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Content-Type' => 'application/json; charset=UTF-8'
            ],
            'body' => json_encode([
                'name' => $videoName,
                'parents' => [$imeiFolderId]
            ])
        ]
    );

    $uploadUrl = $response->getHeaderLine('Location');
    if (!$uploadUrl) throw new Exception("❌ Failed to obtain upload URL.");

    // Upload video in chunks
    $httpClient = $client->authorize();
    $handle = fopen($videoTmp, 'rb');
    $chunkSize = 5 * 1024 * 1024;
    $offset = 0;

    while (!feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        $chunkLength = strlen($chunk);

        $res = $httpClient->request('PUT', $uploadUrl, [
            'headers' => [
                'Content-Length' => $chunkLength,
                'Content-Range' => "bytes $offset-" . ($offset + $chunkLength - 1) . "/$videoSize"
            ],
            'body' => $chunk,
        ]);

        $offset += $chunkLength;
    }

    fclose($handle);

    $resBody = json_decode($res->getBody()->getContents(), true);
    $videoFileId = $resBody['id'];

    $service->permissions->create($videoFileId, $permission);
    $buyerVerificationLink = "https://drive.google.com/file/d/$videoFileId/view";
}

// ✅ Update database with sell info
$update = "UPDATE purchased_mobiles 
SET 
    status = 'sold',
    buyer_name = '$buyer_name',
    buyer_mobile = '$buyer_mobile',
    buyer_photo = '" . json_encode($buyerPhotoLinks) . "',
    buyer_verification = '$buyerVerificationLink',
    sold_price = '$sold_price',
    sell_date = CURRENT_TIMESTAMP
WHERE IMEI = '$imei'";



if (mysqli_query($conn, $update)) {
    error_log("✅ Buyer data updated successfully for IMEI: $imei");
    header("Location: generateSellPDF.php?imei=" . urlencode($imei));
    exit;
} else {
    error_log("❌ Failed to update buyer data: " . mysqli_error($conn));
    echo "❌ Database Update Error: " . mysqli_error($conn);
    exit;
}


mysqli_close($conn);
?>
