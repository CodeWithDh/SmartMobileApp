<?php
require_once __DIR__ . '/backend/tcpdf/tcpdf.php';

require '../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;
use TCPDF;

// 🔥 Get IMEI from URL or POST
$imei = $_GET['imei'] ?? die("IMEI not provided");

// 🔥 Fetch Data from DB
$sql = "SELECT * FROM purchased_mobiles WHERE IMEI = '$imei'";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    die("IMEI not found.");
}
$row = mysqli_fetch_assoc($result);

// 🔥 Generate PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$html = '
<h2 style="color:#5409DA;">SmartMobileApp - Purchase Invoice</h2>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
    
        <td><b>IMEI</b></td>
        <td>' . htmlspecialchars($row['IMEI']) . '</td>
    </tr>
    <tr>
        <td><b>Mobile Name</b></td>
        <td>' . htmlspecialchars($row['mobile_name']) . '</td>
    </tr>
    <tr>
        <td><b>Seller Name</b></td>
        <td>' . htmlspecialchars($row['seller_name']) . '</td>
    </tr>
    <tr>
        <td><b>Seller Mobile</b></td>
        <td>' . htmlspecialchars($row['seller_mobile']) . '</td>
    </tr>
    <tr>
        <td><b>Fault Description</b></td>
        <td>' . htmlspecialchars($row['fault_description']) . '</td>
    </tr>
    <tr>
        <td><b>Price</b></td>
        <td>' . htmlspecialchars($row['price']) . '</td>
    </tr>
    <tr>
        <td><b>Purchase Date</b></td>
        <td>' . htmlspecialchars($row['purchase_date']) . '</td>
    </tr>
</table>

<p style="color:#5409DA; margin-top:20px;">Thank you for using SmartMobileApp.</p>
';

$pdf->writeHTML($html);

// 🔥 Save PDF Temporarily
$pdfFileName = $imei . ".pdf";
$pdfFilePath = __DIR__ . "/temp/" . $pdfFileName;

if (!file_exists(__DIR__ . "/temp")) {
    mkdir(__DIR__ . "/temp", 0777, true);
}

$pdf->Output($pdfFilePath, 'F');

// 🔥 Upload PDF to Google Drive
$client = new Client();
$client->setAuthConfig('credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// 🔥 Fetch Folder ID
$folderSql = "SELECT drive_folder_id FROM purchased_mobiles WHERE IMEI = '$imei'";
$folderResult = mysqli_query($conn, $folderSql);
$folderRow = mysqli_fetch_assoc($folderResult);
$folderId = $folderRow['drive_folder_id'];

// 🔥 Permission
$permission = new Drive\Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);

// 🔥 Upload File
$fileMetadata = new Drive\DriveFile([
    'name' => $pdfFileName,
    'parents' => [$folderId]
]);

$content = file_get_contents($pdfFilePath);

$file = $service->files->create($fileMetadata, [
    'data' => $content,
    'mimeType' => 'application/pdf',
    'uploadType' => 'multipart',
    'fields' => 'id'
]);

$service->permissions->create($file->id, $permission);

$pdfLink = "https://drive.google.com/file/d/" . $file->id . "/view";

// 🔥 Delete local temp file
unlink($pdfFilePath);

mysqli_close($conn);

// 🔥 Redirect to Success Page with Link
header("Location: success.php?imei=$imei&pdf=" . urlencode($pdfLink));
exit;
?>
