<?php include 'components/navbar.php'; ?>
<?php
$imei = isset($_POST['imei']) ? $_POST['imei'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Mobile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
       /* 🔥 Page Background */
body {
    background-color: white;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 20px;
    transition: margin-left 0.3s ease;
}

/* 🔥 Form Container Styling */
.form-container {
    margin-left: 260px;
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    border-top: 5px solid #5409DA;
    transition: margin-left 0.3s ease;
}

/* 🔥 Responsive to Sidebar Hide */
.sidebar.hide ~ .form-container {
    margin-left: 0;
}

/* 🔥 Labels */
.form-label {
    color: #5409DA;
    font-weight: 500;
}

/* 🔥 Primary Buttons */
.btn-primary {
    background-color: #5409DA;
    border: none;
}

.btn-primary:hover {
    background-color: #4E71FF;
}

/* 🔥 Outline Buttons */
.btn-outline-primary, .btn-outline-secondary {
    border-radius: 8px;
}

/* 🔥 Video Preview Box */
video {
    border: 2px solid #5409DA;
    border-radius: 8px;
}

    </style>
</head>

<body>

    <div class="form-container">
    
    <h2>Sell Mobile - IMEI: <?php echo htmlspecialchars($imei); ?></h2>
        <form action="backend/insertSell.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imei" value="<?php echo htmlspecialchars($imei); ?>">

        <!-- 🔥 Buyer Name -->
        <div class="mb-3">
            <label class="form-label">Buyer Name *</label>
            <input type="text" name="buyer_name" class="form-control" placeholder="Enter buyer name" required>
        </div>

        <!-- 🔥 Buyer Mobile -->
        <div class="mb-3">
            <label class="form-label">Buyer Mobile *</label>
            <input type="text" name="buyer_mobile" class="form-control" placeholder="Enter buyer mobile" required>
        </div>

        <!-- 🔥 Buyer Photos -->
        <div class="mb-3">
            <label class="form-label">Buyer Photos (Multiple) *</label>
            <input type="file" name="buyer_photo[]" multiple accept="image/*" class="form-control" required>
        </div>

        <!-- 🔥 Buyer Verification Video -->
        <div class="mb-3">
            <label class="form-label">Buyer Verification Video (MP4) *</label>
            <input type="file" name="buyer_verification" accept="video/mp4" class="form-control" required>
        </div>
        <div class="mb-3">
    <label class="form-label">Sold Price *</label>
    <input type="number" name="sold_price" step="0.01" class="form-control" placeholder="Enter selling price" required>
</div>


        <button type="submit" name="submit" class="btn btn-primary w-100">Complete Sale</button>
    </form>
</div>

</body>
</html>
