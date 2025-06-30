<?php include 'components/navbar.php'; ?>
<?php
$imei = isset($_POST['imei']) ? $_POST['imei'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Mobile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: white;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .form-container {
            margin-left: 260px;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border-top: 5px solid #5409DA;
            transition: margin-left 0.3s ease;
        }

        .sidebar.hide ~ .form-container {
            margin-left: 0;
        }

        h2 {
            text-align: center;
            color: #5409DA;
            margin-bottom: 20px;
        }

        .form-label {
            color: #5409DA;
            font-weight: 500;
        }

        .form-control {
            background-color: white;
            border: 1.5px solid #4E71FF;
            color: #333;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #5409DA;
            box-shadow: 0 0 0 0.15rem rgba(84,9,218,0.25);
        }

        .btn-primary {
            background-color: #4E71FF;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5409DA;
        }

        textarea.form-control {
            resize: none;
        }
    </style>
</head>

<body>

<h2>Return Mobile - IMEI: <?php echo htmlspecialchars($imei); ?></h2>

<div class="form-container">
    <form action="backend/insertReturn.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imei" value="<?php echo htmlspecialchars($imei); ?>">

        <!-- 🔥 Return Photos -->
        <div class="mb-3">
            <label class="form-label">Return Photos (Multiple) *</label>
            <input type="file" name="return_photo[]" multiple accept="image/*" class="form-control" required>
        </div>

        <!-- 🔥 Return Verification Video -->
        <div class="mb-3">
            <label class="form-label">Return Verification Video (MP4) *</label>
            <input type="file" name="return_verification" accept="video/mp4" class="form-control" required>
        </div>

        <!-- 🔥 Return Description -->
        <div class="mb-3">
            <label class="form-label">Return Description *</label>
            <textarea name="return_description" class="form-control" rows="4" placeholder="Describe the reason for return..." required></textarea>
        </div>

        <button type="submit" name="submit" class="btn btn-primary w-100">Complete Return</button>
    </form>
</div>

</body>
</html>
