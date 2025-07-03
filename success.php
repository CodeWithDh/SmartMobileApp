<?php include 'components/navbar.php'; ?>
<?php
$imei = $_GET['imei'] ?? '';
$pdf = $_GET['pdf'] ?? '';
$type = $_GET['type'] ?? 'purchase';

// ✅ Dynamic Success Messages
$message = match($type) {
    'purchase' => '✅ Purchase Completed Successfully!',
    'sell'     => '✅ Mobile Sold Successfully!',
    'return'   => '✅ Mobile Returned Successfully!',
    default    => '✅ Operation Completed Successfully!',
};

// ✅ Dynamic PDF Label
$pdfLabel = match($type) {
    'purchase' => '📄 Download Purchase Invoice',
    'sell'     => '📄 Download Sell Invoice',
    'return'   => '📄 Download Return Invoice',
    default    => '📄 Download Invoice',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            margin: 0;
            padding: 30px;
            font-family: 'Segoe UI', sans-serif;
        }
        .success-container {
            margin-left: 260px;
            background-color: #BBFBFF;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 5px solid #5409DA;
            text-align: center;
        }
        h1 {
            color: #5409DA;
            margin-bottom: 20px;
            font-weight: bold;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        .btn-primary {
            background-color: #5409DA;
            border: none;
        }
        .btn-primary:hover {
            background-color: #4E71FF;
        }
        .btn-outline-secondary {
            border: 2px solid #5409DA;
            color: #5409DA;
        }
        .btn-outline-secondary:hover {
            background-color: #5409DA;
            color: white;
        }
    </style>
</head>
<body>
 <?php include 'components/topnav.php'; ?>
<div class="success-container">

    <h1><?= $message ?></h1>
    <p><b>IMEI:</b> <?= htmlspecialchars($imei) ?></p>

    <?php if ($pdf): ?>
        <a href="<?= htmlspecialchars($pdf) ?>" target="_blank" class="btn btn-primary mt-3">
            <?= $pdfLabel ?>
        </a>
    <?php endif; ?>

    <br><br>
    <a href="dashboard.php" class="btn btn-outline-secondary">
        🔙 Back to Dashboard
    </a>
</div>

</body>
</html>
