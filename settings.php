<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings - Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7f7f7;
        }
        .container {
            max-width: 500px;
            margin-top: 60px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center text-primary mb-4">🔒 Change Admin Password</h3>
    
    <form id="passwordForm">
        <div class="mb-3">
            <label>Current Password</label>
            <input type="password" name="current" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="new" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    </form>

    <div id="otpSection" class="mt-4" style="display:none;">
        <label>Enter OTP</label>
        <input type="text" id="otpInput" class="form-control mb-2" maxlength="6">
        <button onclick="verifyOTP()" class="btn btn-success w-100">Confirm & Change Password</button>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e){
    e.preventDefault();
    const form = new FormData(this);
    
    fetch('backend/sendPasswordOTP.php', {
        method: 'POST',
        body: form
    }).then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
            alert("✅ OTP sent to admin email");
            document.getElementById('otpSection').style.display = 'block';
        } else {
            alert("❌ " + data.message);
        }
    });
});

function verifyOTP() {
    const otp = document.getElementById('otpInput').value;
    fetch('backend/verifyAndChangePassword.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'otp=' + encodeURIComponent(otp)
    }).then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
            alert("✅ Password changed successfully");
            window.location.reload();
        } else {
            alert("❌ " + data.message);
        }
    });
}
</script>
</body>
</html>
