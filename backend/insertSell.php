<?php
require_once __DIR__ . '/../vendor/autoload.php';
require 'db.php';


use Google\Client;
use Google\Service\Drive;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 🔐 Google Client Setup
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

try {
    // 📥 Get POST Data
    $imei = $_POST['imei'];
    $buyerName = $_POST['buyer_name'];
    $buyerMobile = $_POST['buyer_mobile'];
    $soldPrice = $_POST['sold_price'];
    $soldDate = date("Y-m-d");

    // 📦 Get existing folder ID from DB
    $sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei'";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception("IMEI not found in database.");
    }
    $row = mysqli_fetch_assoc($result);
    $imeiFolderId = $row['drive_folder_id'];

    // 📂 Google Drive permission setup
    $permission = new Drive\Permission([
        'type' => 'anyone',
        'role' => 'reader'
    ]);

    // 📸 Upload Buyer Photos (file + captured)
    $buyerPhotoLinks = [];
    $photoCount = 1;

    if (!empty($_FILES['buyer_photo']['tmp_name'][0])) {
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
    }

    // ➕ Captured camera photos
    if (!empty($_POST['captured_photos'])) {
        $photos = explode('|', $_POST['captured_photos']);
        foreach ($photos as $photoData) {
            if (trim($photoData) == '') continue;
            $base64 = explode(',', $photoData)[1];
            $decoded = base64_decode($base64);

            $fileName = 'buyerPic' . $photoCount . '.png';
            $fileMetadata = new Drive\DriveFile([
                'name' => $fileName,
                'parents' => [$imeiFolderId]
            ]);

            $file = $service->files->create($fileMetadata, [
                'data' => $decoded,
                'mimeType' => 'image/png',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            $service->permissions->create($file->id, $permission);
            $buyerPhotoLinks[] = "https://drive.google.com/file/d/" . $file->id . "/view";
            $photoCount++;
        }
    }

    // 🎥 Upload Buyer Verification Video
    $buyerVideoLink = '';
    if (!empty($_FILES['buyer_verification']['tmp_name'])) {
        $tmpName = $_FILES['buyer_verification']['tmp_name'];
        $fileSize = filesize($tmpName);
        $fileName = 'BuyerVerification.mp4';
        $mimeType = mime_content_type($tmpName);

        // ✅ Upload using resumable upload
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
                    'name' => $fileName,
                    'parents' => [$imeiFolderId]
                ])
            ]
        );

        $uploadUrl = $response->getHeaderLine('Location');
        if (!$uploadUrl) throw new Exception("❌ Failed to obtain upload URL.");

        $httpClient = $client->authorize();
        $handle = fopen($tmpName, 'rb');
        $chunkSize = 5 * 1024 * 1024;
        $offset = 0;

        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $chunkLength = strlen($chunk);
            $headers = [
                'Content-Length' => $chunkLength,
                'Content-Range' => "bytes $offset-" . ($offset + $chunkLength - 1) . "/$fileSize"
            ];

            $res = $httpClient->request('PUT', $uploadUrl, [
                'headers' => $headers,
                'body' => $chunk,
            ]);
            $offset += $chunkLength;
        }
        fclose($handle);
        $resBody = json_decode($res->getBody()->getContents(), true);
        $fileId = $resBody['id'];
        $service->permissions->create($fileId, $permission);
        $buyerVideoLink = "https://drive.google.com/file/d/$fileId/view";
    }

    // 🛠️ Update purchased_mobiles table
    $update = "UPDATE purchased_mobiles SET
    buyer_name = '$buyerName',
    buyer_mobile = '$buyerMobile',
    buyer_photo = '" . json_encode($buyerPhotoLinks) . "',
    buyer_verification = '$buyerVideoLink',
    sold_price = '$soldPrice',
    sell_date = '$soldDate',
    status = 'sold'
    WHERE IMEI = '$imei'";



    if (mysqli_query($conn, $update)) {
        header("Location: generateSellPDF.php?imei=$imei");
        exit;
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    header("Location: ../error.php?msg=" . urlencode($e->getMessage()));
    exit;
}
?>
