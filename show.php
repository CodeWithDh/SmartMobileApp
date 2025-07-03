<?php
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$imei = $_GET['imei'] ?? die("IMEI not provided.");

// Setup Google Drive
$client = new Client();
$client->setAuthConfig(__DIR__ . '/backend/credentials.json');
$client->addScope(Drive::DRIVE);
$service = new Drive($client);

// Get data from DB
$sql = "SELECT * FROM purchased_mobiles WHERE IMEI = '$imei'";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    die("No data found for IMEI.");
}
$row = mysqli_fetch_assoc($result);

// Get folder ID from DB
$folderId = $row['drive_folder_id'];

// List files in Drive folder
$files = $service->files->listFiles([
    'q' => "'$folderId' in parents and trashed = false",
    'fields' => 'files(id, name, mimeType)',
]);

$photos = [];
$videos = [];
$pdf = null;

foreach ($files->getFiles() as $file) {
    $fileId = $file->getId();
    $mime = $file->getMimeType();

    if (str_starts_with($mime, 'image/')) {
        $photos[] = "https://drive.google.com/uc?export=view&id=$fileId";
    } elseif (str_starts_with($mime, 'video/')) {
        $videos[] = "https://drive.google.com/file/d/$fileId/preview";
    } elseif ($mime === 'application/pdf') {
        $pdf = "https://drive.google.com/file/d/$fileId/view";
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
        body {
            background-color: white;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            transition: margin-left 0.3s ease;
        }

        .main-container {
            margin-left: 260px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.hide ~ .main-container {
            margin-left: 0 !important;
        }

        h2 {
            color: #5409DA;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        .table th {
            background-color: #5409DA;
            color: white;
            width: 30%;
        }

        .table td {
            color: #333;
        }

        .section-title {
            color: #5409DA;
            font-size: 20px;
            font-weight: 600;
            margin-top: 40px;
            margin-bottom: 15px;
        }

        .photo-gallery,
        .video-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .photo-gallery a img {
            width: 120px;
            height: auto;
            border-radius: 10px;
            border: 2px solid #4E71FF;
            transition: transform 0.3s ease;
        }

        .photo-gallery a:hover img {
            transform: scale(1.1);
        }

        .video-preview iframe {
            width: 320px;
            height: 200px;
            border: 2px solid #8DD8FF;
            border-radius: 10px;
        }

        .btn-primary {
            background-color: #4E71FF;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5409DA;
        }
    </style>
</head>
<body>
<?php include 'components/navbar.php'; ?>
<?php include 'components/topnav.php'; ?>

<div class="main-container">
    <h2>📱 IMEI Details: <?= htmlspecialchars($imei) ?></h2>

    <div class="table-responsive">
        <table class="table table-bordered shadow-sm">
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
                <tr><th>Purchase Price</th><td>₹ <?= htmlspecialchars($row['purchase_price']) ?></td></tr>
            <?php endif; ?>
            <?php if ($row['purchase_date']): ?>
                <tr><th>Purchase Date</th><td><?= htmlspecialchars($row['purchase_date']) ?></td></tr>
            <?php endif; ?>
            <?php if ($row['return_date']): ?>
                <tr><th>Return Date</th><td><?= htmlspecialchars($row['return_date']) ?></td></tr>
            <?php endif; ?>
            <?php if ($pdf): ?>
                <tr><th>Invoice PDF</th>
                    <td><a href="<?= $pdf ?>" target="_blank" class="btn btn-sm btn-primary">📄 View PDF</a></td></tr>
            <?php endif; ?>
        </table>
    </div>

    <?php if (!empty($photos)): ?>
        <div class="section-title">🖼️ Uploaded Photos</div>
        <div class="photo-gallery">
            <?php foreach ($photos as $img): ?>
                <a href="<?= $img ?>" target="_blank"><img src="<?= $img ?>" alt="Photo"></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($videos)): ?>
        <div class="section-title">🎥 Verification Videos</div>
        <div class="video-preview">
            <?php foreach ($videos as $video): ?>
                <iframe src="<?= $video ?>" allowfullscreen></iframe>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
