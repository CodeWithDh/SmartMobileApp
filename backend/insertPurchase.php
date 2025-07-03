<?php
require_once __DIR__ . '/../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

// ✅ Enable mysqli exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ✅ Initialize Google Client
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

try {
    // ✅ Get Form Data
    $imei = $_POST['imei'];
    $sellerName = $_POST['seller_name'];
    $sellerMobile = $_POST['seller_mobile'];
    $mobileName = $_POST['mobile_name'];
    $faultDescription = $_POST['fault_description'];
    $price = $_POST['price'];
    $purchase_price = $_POST['price']; 


    // ✅ Create IMEI Folder in Google Drive
    $folderMetadata = new Drive\DriveFile([
        'name' => $imei,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents' => ['1Ah8EfWG-SpHPxZlggOk8VCCqfY_Q73W5'] // Change to your parent folder ID
    ]);

    $folder = $service->files->create($folderMetadata, ['fields' => 'id']);
    $imeiFolderId = $folder->id;

    // ✅ Set Public Permission
    $permission = new Drive\Permission([
        'type' => 'anyone',
        'role' => 'reader'
    ]);
    $service->permissions->create($imeiFolderId, $permission);

    // ✅ Upload Seller Photos (File + Camera)
    $sellerPhotoLinks = [];
    $photoCount = 1;

    // ➕ From file uploads
    if (!empty($_FILES['seller_photos']['tmp_name'][0])) {
        foreach ($_FILES['seller_photos']['tmp_name'] as $index => $tmpName) {
            $fileName = 'sellerPic' . $photoCount . '.' . pathinfo($_FILES['seller_photos']['name'][$index], PATHINFO_EXTENSION);
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
    }

    // ➕ From captured photos (base64)
    if (!empty($_POST['captured_photos'])) {
        $capturedPhotos = json_decode($_POST['captured_photos'], true);

        foreach ($capturedPhotos as $photoData) {
            $photoContent = explode(',', $photoData)[1];
            $decodedData = base64_decode($photoContent);

            $fileName = 'sellerPic' . $photoCount . '.png';
            $fileMetadata = new Drive\DriveFile([
                'name' => $fileName,
                'parents' => [$imeiFolderId]
            ]);

            $file = $service->files->create($fileMetadata, [
                'data' => $decodedData,
                'mimeType' => 'image/png',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            $service->permissions->create($file->id, $permission);

            $link = "https://drive.google.com/file/d/" . $file->id . "/view";
            $sellerPhotoLinks[] = $link;
            $photoCount++;
        }
    }

    // ✅ Upload Verification Videos (File + Camera)
    $verificationVideoLinks = [];

    // ➕ From file uploads
    if (!empty($_FILES['verification_video']['tmp_name'][0])) {
        foreach ($_FILES['verification_video']['tmp_name'] as $index => $tmpName) {
            $fileSize = filesize($tmpName);
            $fileName = 'VerificationVideo' . ($index + 1) . '.' . pathinfo($_FILES['verification_video']['name'][$index], PATHINFO_EXTENSION);
            $mimeType = mime_content_type($tmpName);

            // ✔️ Upload using resumable
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
            if (!$uploadUrl) {
                throw new Exception("❌ Failed to obtain upload URL.");
            }

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
            $fileId = $resBody['id'];

            $service->permissions->create($fileId, $permission);
            $link = "https://drive.google.com/file/d/$fileId/view";
            $verificationVideoLinks[] = $link;
        }
    }

    // ➕ From captured videos (base64)
    if (!empty($_POST['captured_videos'])) {
        $capturedVideos = json_decode($_POST['captured_videos'], true);
        $vidCount = 1;

        foreach ($capturedVideos as $videoData) {
            $videoContent = explode(',', $videoData)[1];
            $decodedData = base64_decode($videoContent);

            $fileName = 'VerificationVideoCaptured' . $vidCount . '.webm';
            $fileMetadata = new Drive\DriveFile([
                'name' => $fileName,
                'parents' => [$imeiFolderId]
            ]);

            $file = $service->files->create($fileMetadata, [
                'data' => $decodedData,
                'mimeType' => 'video/webm',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            $service->permissions->create($file->id, $permission);

            $link = "https://drive.google.com/file/d/" . $file->id . "/view";
            $verificationVideoLinks[] = $link;
            $vidCount++;
        }
    }

    // ✅ Insert Into Database
    $sql = "INSERT INTO purchased_mobiles (
        IMEI, seller_name, seller_mobile, seller_photo, seller_verification_video,
        mobile_name, fault_description, purchase_price, drive_folder_id
    ) VALUES (
        '$imei', '$sellerName', '$sellerMobile',
        '" . json_encode($sellerPhotoLinks) . "',
        '" . json_encode($verificationVideoLinks) . "',
        '$mobileName', '$faultDescription', '$purchase_price', '$imeiFolderId'
    )";

    if (mysqli_query($conn, $sql)) {
        header("Location: generatePDF.php?imei=$imei");
        exit;
    } else {
        header("Location: ../error.php?msg=" . urlencode(mysqli_error($conn)));
        exit;
    }

} catch (Exception $e) {
    header("Location: ../error.php?msg=" . urlencode($e->getMessage()));
    exit;
}
?>
