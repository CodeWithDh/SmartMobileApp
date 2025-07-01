<?php include 'components/navbar.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <style>
        body {
    background-color: white;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 20px;
    transition: margin-left 0.3s ease;
}

/* 🔥 Form Container Responsive */
.form-container {
    margin-left: 260px;
    background: #fff ;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    border-top: 5px solid #5409DA;
    transition: margin-left 0.3s ease;
}

/* 🔥 If Sidebar is Hidden */
.sidebar.hide ~ .form-container {
    margin-left: 0;
}

/* Common Styling */
.form-label {
    color: #5409DA;
    font-weight: 500;
}
.btn-primary {
    background-color: #5409DA;
    border: none;
}
.btn-primary:hover {
    background-color: #4E71FF;
}
.btn-outline-primary, .btn-outline-secondary {
    border-radius: 8px;
}
video {
    border: 2px solid #5409DA;
    border-radius: 8px;
}

       
        .form-container {
            margin-left: 260px;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border-top: 5px solid #5409DA;
        }
        .form-label {
            color: #5409DA;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #5409DA;
            border: none;
        }
        .btn-primary:hover {
            background-color: #4E71FF;
        }
        .btn-outline-primary, .btn-outline-secondary {
            border-radius: 8px;
        }
        video {
            border: 2px solid #5409DA;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<div class="form-container">
    <h3 class="mb-4">Purchase Mobile</h3>

    <form action="backend/insertPurchase.php" method="POST" enctype="multipart/form-data">

        <!-- 🔥 IMEI with Scanner -->
        <div class="mb-3">
            <label class="form-label">IMEI Number *</label>
            <div style="display: flex; align-items: center; gap: 10px;">
              <input type="text" name="imei" id="imeiInput" class="form-control" 
    placeholder="Scan or enter IMEI" required 
    pattern="\d{15,}" 
    title="IMEI must be at least 15 digits">


                <!-- Camera -->
                <button type="button" class="btn btn-outline-primary" onclick="openCamera()">📷</button>

                <!-- File Upload -->
                <input type="file" accept="image/*" id="fileInput" style="display:none;" onchange="decodeFromFile(this)">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('fileInput').click()">📁</button>
            </div>
        </div>

        <!-- Camera Preview -->
        <div id="cameraContainer" style="display:none;">
            <video id="video" width="350" height="250" autoplay></video><br>
            <button class="btn btn-danger mt-2" type="button" onclick="captureImage()">Capture</button>
            <button class="btn btn-secondary mt-2" type="button" onclick="closeCamera()">Close</button>
        </div>
        <canvas id="canvas" width="350" height="250" style="display:none;"></canvas>

        <!-- Other Fields -->
        <div class="mb-3">
            <label class="form-label">Seller Name *</label>
            <input type="text" name="seller_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Seller Mobile *</label>
            <input type="text" name="seller_mobile" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Seller Photos (Multiple) *</label>
            <input type="file" name="seller_photo[]" multiple accept="image/*" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Verification Video (MP4) *</label>
            <input type="file" name="verification_video" accept="video/mp4" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mobile Name *</label>
            <input type="text" name="mobile_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fault Description</label>
            <textarea name="fault_description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Price *</label>
            <input type="number" name="price" step="0.01" class="form-control" required>
        </div>

        

        <button type="submit" class="btn btn-primary w-100">Add Purchase</button>
    </form>
</div>

<!-- 🔥 JavaScript for Barcode Scanner -->
<script>
let stream = null;

// 🔥 Open Camera
function openCamera() {
    const cameraContainer = document.getElementById('cameraContainer');
    const video = document.getElementById('video');

    cameraContainer.style.display = 'block';

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(s => {
            stream = s;
            video.srcObject = stream;
            video.play();
        })
        .catch(err => {
            alert('❌ Camera access denied or not supported.');
            console.error(err);
        });
}

// 🔥 Close Camera
function closeCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    document.getElementById('cameraContainer').style.display = 'none';
}

// 🔥 Capture Image from Camera
function captureImage() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');

    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    closeCamera();

    const dataUrl = canvas.toDataURL('image/png');

    decodeBarcode(dataUrl);
}

// 🔥 Decode Barcode from File Upload
function decodeFromFile(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        decodeBarcode(e.target.result);
    };
    reader.readAsDataURL(file);
}

// 🔥 Common Barcode Decoder Function
function decodeBarcode(src) {
    Quagga.decodeSingle({
        src: src,
        numOfWorkers: 0,
        decoder: {
            readers: ["code_128_reader", "ean_reader", "code_39_reader"]
        },
    }, function(result) {
        if (result && result.codeResult) {
            const code = result.codeResult.code;
            if (code.length === 15) {
                document.getElementById('imeiInput').value = code;
            } else {
                alert("❌ Scanned IMEI must be exactly 15 digits. Detected: " + code.length);
            }
        } else {
            alert("❌ Barcode not detected. Please capture a clearer image.");
        }
    });
}

// 🔥 IMEI Length Validation Before Submit (15 digits check)
document.querySelector("form").addEventListener("submit", function(e) {
    const imei = document.getElementById("imeiInput").value.trim();
    const imeiDigits = imei.replace(/\D/g, '');

    if (imeiDigits.length !== 15) {
        alert("❌ IMEI must be exactly 15 digits. Current: " + imeiDigits.length);
        e.preventDefault();
    }
});
</script>


</body>
</html>
