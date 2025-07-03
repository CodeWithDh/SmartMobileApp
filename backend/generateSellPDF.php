<?php
require '../vendor/autoload.php';
require 'db.php';

use Mpdf\Mpdf;

if (!isset($_GET['imei'])) {
    die("❌ IMEI missing.");
}

$imei = mysqli_real_escape_string($conn, $_GET['imei']);
$sql = "SELECT * FROM purchased_mobiles WHERE IMEI = '$imei'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("❌ IMEI not found.");
}

$data = mysqli_fetch_assoc($result);

// Format dates
$purchaseDate = date('d-m-Y', strtotime($data['purchase_date']));
$sellDate = isset($data['sell_date']) ? date('d-m-Y', strtotime($data['sell_date'])) : 'N/A';

// Decode photo arrays
$sellerPhotos = json_decode($data['seller_photo'] ?? '[]', true);
$buyerPhotos = json_decode($data['buyer_photo'] ?? '[]', true);

// Start HTML buffer
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        h2 {
            background-color: #5409DA;
            color: white;
            padding: 8px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        td, th {
            padding: 6px;
            border: 1px solid #ccc;
        }
        .photos img {
            height: 100px;
            margin: 5px;
            border: 1px solid #999;
        }
        .section {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2>Seller Details</h2>
<table>
    <tr><td><strong>Name:</strong></td><td><?= htmlspecialchars($data['seller_name']) ?></td></tr>
    <tr><td><strong>Mobile:</strong></td><td><?= htmlspecialchars($data['seller_mobile']) ?></td></tr>
    <tr><td><strong>Purchase Price:</strong></td><td>₹<?= htmlspecialchars($data['purchase_price']) ?></td></tr>
    <tr><td><strong>Purchase Date:</strong></td><td><?= $purchaseDate ?></td></tr>
</table>

<?php if (!empty($sellerPhotos)) : ?>
<div class="photos">
    <?php foreach ($sellerPhotos as $link): ?>
        <img src="<?= htmlspecialchars($link) ?>" alt="Seller Photo">
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="section">
    <h2>Buyer Details</h2>
    <table>
        <tr><td><strong>Name:</strong></td><td><?= htmlspecialchars($data['buyer_name']) ?></td></tr>
        <tr><td><strong>Mobile:</strong></td><td><?= htmlspecialchars($data['buyer_mobile']) ?></td></tr>
        <tr><td><strong>Sold Price:</strong></td><td>₹<?= htmlspecialchars($data['sold_price']) ?></td></tr>
        <tr><td><strong>Sell Date:</strong></td><td><?= $sellDate ?></td></tr>
        <tr><td><strong>Verification Video:</strong></td>
            <td>
                <?php if (!empty($data['buyer_verification'])): ?>
                    <a href="<?= htmlspecialchars($data['buyer_verification']) ?>" target="_blank">Watch Video</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <?php if (!empty($buyerPhotos)) : ?>
        <div class="photos">
            <?php foreach ($buyerPhotos as $link): ?>
                <img src="<?= htmlspecialchars($link) ?>" alt="Buyer Photo">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<h2>Mobile Details</h2>
<table>
    <tr><td><strong>Brand:</strong></td><td><?= htmlspecialchars($data['brand']) ?></td></tr>
    <tr><td><strong>Model:</strong></td><td><?= htmlspecialchars($data['model']) ?></td></tr>
    <tr><td><strong>IMEI:</strong></td><td><?= htmlspecialchars($data['IMEI']) ?></td></tr>
</table>

</body>
</html>

<?php
$html = ob_get_clean();

// ✅ Generate PDF using mPDF
$mpdf = new Mpdf();
$mpdf->WriteHTML($html);

// ✅ Save PDF to server (optional) or output directly
$pdfOutputPath = __DIR__ . "/pdfs/SELL_" . $imei . ".pdf";
$mpdf->Output($pdfOutputPath, \Mpdf\Output\Destination::FILE);

// ✅ Redirect or force download
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=SELL_$imei.pdf");
readfile($pdfOutputPath);
exit;
?>
