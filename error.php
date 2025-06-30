<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : 'Unknown error';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Error Occurred</title>
</head>
<body>

<h2 style="color: red; text-align:center;">❌ Error Occurred</h2>
<p style="text-align:center;"><?php echo htmlspecialchars($msg); ?></p>

<div style="text-align:center;">
    <a href="purchaseForm.php">
        <button>Go Back to Purchase Form</button>
    </a>
</div>

</body>
</html>
