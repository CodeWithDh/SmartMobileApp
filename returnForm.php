<?php
$imei = isset($_POST['imei']) ? $_POST['imei'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Mobile</title>
</head>
<body>

<h2 style="text-align: center; color: #5409DA;">Return Mobile - IMEI: <?php echo htmlspecialchars($imei); ?></h2>

<form action="backend/insertReturn.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="imei" value="<?php echo htmlspecialchars($imei); ?>">

    <label>Return Photos (Multiple) *</label><br>
    <input type="file" name="return_photo[]" multiple accept="image/*" required><br><br>

    <label>Return Verification Video (MP4) *</label><br>
    <input type="file" name="return_verification" accept="video/mp4" required><br><br>

    <label>Return Description *</label><br>
    <textarea name="return_description" rows="4" cols="50" placeholder="Describe the reason for return..." required></textarea><br><br>

    <label>Return Date *</label><br>
    <input type="date" name="return_date" required><br><br>

    <button type="submit">Complete Return</button>
</form>


</body>
</html>
