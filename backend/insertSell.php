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

// ✅ Get IMEI and Buyer Details
$imei = $_POST['imei'];
$buyer_name = $_POST['buyer_name'];
$buyer_mobile = $_POST['buyer_mobile'];
$sold_price = $_POST['sold_price'];



// ✅ Fetch Drive Folder ID
$sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei' AND status = 'purchased'";
$result = mysqli_query($conn, $sql);


if (mysqli_num_rows($result) == 0) {
    die("❌ Error: IMEI not found or not in 'purchased' status.");
}

$row = mysqli_fetch_assoc($result);
$imeiFolderId = $row['1Ah8EfWG-SpHPxZlggOk8VCCqfY_Q73W5'];

// ✅ Set Public Permission
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);

// ✅ Upload Buyer Photos
$buyerPhotoLinks = [];
$photoCount = 1;

foreach ($_FILES['buyer_photo']['tmp_name'] as $index => $tmpName) {
    $fileName = 'buyerPic' . $photoCount . '.' . pathinfo($_FILES['buyer_photo']['name'][$index], PATHINFO_EXTENSION);
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
    $photoCount++;
}

// ✅ Upload Buyer Verification Video with Resumable Upload
$videoTmp = $_FILES['buyer_verification']['tmp_name'];
$videoName = 'BuyerVerification.mp4';
$videoSize = filesize($videoTmp);
$videoMimeType = mime_content_type($videoTmp);

// ✔️ Create Upload Session
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
if (!$uploadUrl) {
    throw new Exception("❌ Failed to obtain upload URL.");
}

// ✔️ Upload in Chunks
$httpClient = $client->authorize();
$handle = fopen($videoTmp, 'rb');
$chunkSize = 5 * 1024 * 1024; // 5MB
$offset = 0;

while (!feof($handle)) {
    $chunk = fread($handle, $chunkSize);
    $chunkLength = strlen($chunk);

    $headers = [
        'Content-Length' => $chunkLength,
        'Content-Range' => "bytes $offset-" . ($offset + $chunkLength - 1) . "/$videoSize"
    ];

    $res = $httpClient->request(
        'PUT',
        $uploadUrl,
        [
            'headers' => $headers,
            'body' => $chunk,
        ]
    );

    $offset += $chunkLength;
}
fclose($handle);

$resBody = json_decode($res->getBody()->getContents(), true);
$videoFileId = $resBody['id'];

$service->permissions->create($videoFileId, $permission);
$buyerVerificationLink = "https://drive.google.com/file/d/$videoFileId/view";

// ✅ Update Database
$update = "UPDATE purchased_mobiles 
SET 
    status = 'sold',
    buyer_name = '$buyer_name',
    buyer_mobile = '$buyer_mobile',
    buyer_photo = '".json_encode($buyerPhotoLinks)."',
    buyer_verification = '$buyerVerificationLink',
    sold_price = '$sold_price',
    sell_date = CURRENT_TIMESTAMP
WHERE IMEI = '$imei'";





if (mysqli_query($conn, $update)) {
   header("Location: generateSellPDF.php?imei=" . urlencode($imei));


exit;

} else {
    echo "❌ Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
