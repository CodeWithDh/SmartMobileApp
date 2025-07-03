<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/vendor/autoload.php';


use Google\Client;
use Google\Service\Drive;

$imei = $_GET['imei'] ?? null;
if (!$imei) {
    die("IMEI not provided.");
}

// Get data from DB
$sql = "SELECT * FROM purchased_mobiles WHERE IMEI = '$imei'";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    die("No data found for IMEI.");
}
$row = mysqli_fetch_assoc($result);

// Setup Google Drive
$client = new Client();
$client->setAuthConfig(__DIR__ . '/backend/credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// Get folder ID from DB
$folderId = $row['drive_folder_id'];

// List files (images, videos, PDFs) in that folder
$files = $service->files->listFiles([
    'q' => "'$folderId' in parents and trashed = false",
    'fields' => 'files(id, name, mimeType)',
]);

$photos = [];
$videos = [];
$pdf = null;

foreach ($files->getFiles() as $file) {
    $link = "https://drive.google.com/file/d/{$file->getId()}/view";
    if (str_starts_with($file->getMimeType(), 'image/')) {
        $photos[] = $link;
    } elseif (str_starts_with($file->getMimeType(), 'video/')) {
        $videos[] = $link;
    } elseif ($file->getMimeType() === 'application/pdf') {
        $pdf = $link;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IMEI Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f9f9f9; }
        .container { margin-top: 30px; }
        .section-title { color: #5409DA; font-weight: bold; margin-top: 20px; }
        .photo-gallery img { width: 120px; border-radius: 8px; border: 2px solid #5409DA; }
        .video-preview video { width: 250px; border: 2px solid #5409DA; border-radius: 8px; }
    </style>
</head>
<body>

<?php include 'components/topnav.php'; ?>

<div class="container">
    <h2 class="mb-4">📱 IMEI Details: <?= htmlspecialchars($imei) ?></h2>

    <table class="table table-bordered">
        <?php if ($row['seller_name']): ?>
            <tr><th>Seller Name</th><td><?= htmlspecialchars($row['seller_name']) ?></td></tr>
        <?php endif; ?>
        <?php if ($row['seller_mobile']): ?>
            <tr><th>Seller Mobile</th><td><?= htmlspecialchars($row['seller_mobile']) ?></td></tr>
        <?php endif; ?>
        <?php if ($row['mobile_name']): ?>
            <tr><th>Mobile Name</th><td><?= htmlspecialchars($row['mobile_name']) ?></td></tr>
        <?php endif; ?>
        <?php if ($row['fault_description']): ?>
            <tr><th>Fault</th><td><?= htmlspecialchars($row['fault_description']) ?></td></tr>
        <?php endif; ?>
        <?php if ($row['purchase_price']): ?>
            <tr><th>Price</th><td>₹ <?= htmlspecialchars($row['purchase_price']) ?></td></tr>
        <?php endif; ?>
        <?php if ($row['purchase_date']): ?>
            <tr><th>Purchase Date</th><td><?= htmlspecialchars($row['purchase_date']) ?></td></tr>
        <?php endif; ?>
        <?php if ($row['return_date']): ?>
            <tr><th>Return Date</th><td><?= htmlspecialchars($row['return_date']) ?></td></tr>
        <?php endif; ?>
        <?php if ($pdf): ?>
            <tr><th>Invoice PDF</th><td><a href="<?= $pdf ?>" target="_blank" class="btn btn-sm btn-primary">📄 View PDF</a></td></tr>
        <?php endif; ?>
    </table>

    <?php if ($photos): ?>
        <div class="section-title">🖼️ Seller Photos</div>
        <div class="photo-gallery d-flex flex-wrap gap-3 mt-2">
            <?php foreach ($photos as $link): ?>
                <img src="<?= $link ?>" alt="photo">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($videos): ?>
        <div class="section-title">🎥 Verification Video</div>
        <div class="video-preview d-flex gap-3 mt-2">
            <?php foreach ($videos as $video): ?>
                <video controls src="<?= $video ?>"></video>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
