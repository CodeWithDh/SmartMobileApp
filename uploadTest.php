

<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$client = new Client();
$client->setAuthConfig('backend/credentials.json');
$client->addScope(Drive::DRIVE);

$service = new Drive($client);

$folderId = '1Ah8EfWG-SpHPxZlggOk8VCCqfY_Q73W5';

$filePath = 'testfile.png';
$fileName = 'testfile.png';
$mimeType = mime_content_type($filePath);

$fileMetadata = new Drive\DriveFile([
    'name' => $fileName,
    'parents' => [$folderId]
]);

$content = file_get_contents($filePath);

$file = $service->files->create($fileMetadata, [
    'data' => $content,
    'mimeType' => $mimeType,
    'uploadType' => 'multipart',
    'fields' => 'id'
]);

// 🔥 Make file public
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);
$service->permissions->create($file->id, $permission);

// 🔥 Shareable link
$fileId = $file->id;
$link = "https://drive.google.com/file/d/$fileId/view";

echo "✅ File uploaded successfully!<br>";
echo "👉 <a href='$link' target='_blank'>$link</a>";
?>
