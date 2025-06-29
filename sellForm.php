<?php
$imei = isset($_POST['imei']) ? $_POST['imei'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Mobile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h2 style="text-align: center; color: #5409DA;">Sell Mobile - IMEI: <?php echo htmlspecialchars($imei); ?></h2>

<form action="backend/insertSell.php" method="POST" enctype="multipart/form-data" style="width: 70%; margin: auto;">

    <input type="hidden" name="imei" value="<?php echo htmlspecialchars($imei); ?>">

    <label>Buyer Photos (Multiple) *</label>
    <input type="file" name="buyer_photo[]" multiple accept="image/*" required><br><br>

    <label>Buyer Verification Video (MP4) *</label>
    <input type="file" name="buyer_verification" accept="video/mp4" required><br><br>

    <label>Sell Date *</label>
    <input type="date" name="sell_date" required><br><br>

    <button type="submit" name="submit">Complete Sale</button>
</form>

</body>
</html>
