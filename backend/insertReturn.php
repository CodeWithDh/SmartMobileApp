<?php
require '../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

// 🔐 Google Drive Setup
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

try {
    $imei = $_POST['imei'];
    $return_description = $_POST['return_description'];
    $return_date = date('Y-m-d H:i:s');

    // 🔍 Fetch Drive Folder from DB
    $sql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei' AND status = 'sold'";
    $result = mysqli_query($conn, $sql);
    if (!$result || mysqli_num_rows($result) === 0) {
        throw new Exception("IMEI not found or not in 'sold' status.");
    }
    $row = mysqli_fetch_assoc($result);
    $imeiFolderId = $row['drive_folder_id'];

    $permission = new Drive\Permission([
        'type' => 'anyone',
        'role' => 'reader'
    ]);

    // 📸 Upload Return Photos
    $returnPhotoLinks = [];
    $photoCount = 1;

    // ➕ From file input
    if (!empty($_FILES['return_photo']['tmp_name'][0])) {
        foreach ($_FILES['return_photo']['tmp_name'] as $index => $tmpName) {
            if (!file_exists($tmpName)) continue;

            $fileName = 'returnPic' . $photoCount++ . '.' . pathinfo($_FILES['return_photo']['name'][$index], PATHINFO_EXTENSION);
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
            $returnPhotoLinks[] = "https://drive.google.com/file/d/{$file->id}/view";
        }
    }

    // ➕ From captured base64 photos
    if (!empty($_POST['captured_photos'])) {
        $photos = explode('|', $_POST['captured_photos']);
        foreach ($photos as $base64Data) {
            if (trim($base64Data) === '') continue;

            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Data));
            $fileName = 'returnPic' . $photoCount++ . '.png';

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
            $returnPhotoLinks[] = "https://drive.google.com/file/d/{$file->id}/view";
        }
    }

    // 🎥 Upload Return Verification Video (resumable)
    $returnVerificationLink = '';
    if (!empty($_FILES['return_verification']['tmp_name'])) {
        $videoTmp = $_FILES['return_verification']['tmp_name'];
        $videoSize = filesize($videoTmp);
        $videoMimeType = mime_content_type($videoTmp);
        $videoName = 'ReturnVerification.mp4';

        // 🔁 Start resumable upload session
        $token = $client->fetchAccessTokenWithAssertion();
        $response = $client->getHttpClient()->request('POST',
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
        if (!$uploadUrl) throw new Exception("❌ Failed to obtain resumable upload URL.");

        // 🔁 Upload chunks
        $httpClient = $client->authorize();
        $handle = fopen($videoTmp, 'rb');
        $offset = 0;
        $chunkSize = 5 * 1024 * 1024;

        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $chunkLength = strlen($chunk);

            $httpClient->request('PUT', $uploadUrl, [
                'headers' => [
                    'Content-Length' => $chunkLength,
                    'Content-Range' => "bytes $offset-" . ($offset + $chunkLength - 1) . "/$videoSize"
                ],
                'body' => $chunk
            ]);

            $offset += $chunkLength;
        }

        fclose($handle);

        $res = $httpClient->request('GET', "https://www.googleapis.com/drive/v3/files?fields=id&alt=json&q=name='$videoName' and '$imeiFolderId' in parents");
        $resultJson = json_decode($res->getBody(), true);
        $fileId = $resultJson['files'][0]['id'] ?? null;

        if ($fileId) {
            $service->permissions->create($fileId, $permission);
            $returnVerificationLink = "https://drive.google.com/file/d/{$fileId}/view";
        }
    }

    // ✅ Update DB
    $update = "UPDATE purchased_mobiles SET
        status = 'purchased',
        return_photo = '" . json_encode($returnPhotoLinks) . "',
        return_verification = '$returnVerificationLink',
        return_description = '$return_description',
        return_date = '$return_date'
        WHERE IMEI = '$imei'";

    if (mysqli_query($conn, $update)) {
        header("Location: generateReturnPDF.php?imei=" . urlencode($imei));
        exit;
    } else {
        throw new Exception("Database update failed: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage());
}
