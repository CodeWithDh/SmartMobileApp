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
            padding: 20px;
            margin: 0;
            transition: margin-left 0.3s ease;
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
            font-weight: 600;
            margin-bottom: 5px;
        }

        .btn-primary {
            background-color: #5409DA;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4E71FF;
        }

        .btn-outline-primary, .btn-outline-secondary, .btn-outline-danger {
            border-radius: 8px;
        }

        video, canvas {
            border: 2px solid #5409DA;
            border-radius: 8px;
        }

        .photo-preview img,
        .video-preview video {
            width: 100px;
            height: auto;
            border: 2px solid #5409DA;
            border-radius: 8px;
        }

        .photo-preview,
        .video-preview {
            gap: 10px;
        }
    </style>
</head>

<body>
 <?php include 'components/topnav.php'; ?>
<div class="form-container">
    <h3 class="mb-4 fw-bold" style="color:#5409DA;">Purchase Mobile</h3>

    <form id="purchaseForm" action="backend/insertPurchase.php" method="POST" enctype="multipart/form-data">


        <!-- IMEI Section -->
        <div class="mb-3">
            <label class="form-label">IMEI Number *</label>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" name="imei" id="imeiInput" class="form-control"
                       placeholder="Scan or enter IMEI" required
                       pattern="\d{15,}" title="IMEI must be 15 digits">

                <button type="button" class="btn btn-outline-primary" onclick="openCamera()">📷</button>
                <input type="file" accept="image/*" id="fileInput" hidden onchange="decodeFromFile(this)">
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('fileInput').click()">📁</button>
            </div>
        </div>

        <div id="cameraContainer" style="display:none;">
            <video id="video" width="350" height="250" autoplay></video><br>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-danger" type="button" onclick="captureImage()">Capture</button>
                <button class="btn btn-secondary" type="button" onclick="closeCamera()">Close</button>
            </div>
        </div>

        <canvas id="canvas" width="350" height="250" style="display:none;"></canvas>

        <!-- Seller Info -->
        <div class="mb-3">
            <label class="form-label">Seller Name *</label>
            <input type="text" name="seller_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Seller Mobile *</label>
            <input type="text" name="seller_mobile" class="form-control" required>
        </div>

        <!-- Seller Photos -->
        <div class="mb-3">
            <label class="form-label">Seller Photos *</label>
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-outline-primary" onclick="openPhotoCamera()">📷 Camera</button>
                <button type="button" class="btn btn-outline-danger" onclick="clearPhotos()">❌ Clear Photos</button>
            </div>
            <input type="file" name="seller_photos[]" accept="image/*" multiple class="form-control">
            <div id="photoPreview" class="photo-preview d-flex flex-wrap mt-2"></div>
            <input type="hidden" name="captured_photos" id="capturedPhotosInput">
        </div>

        <div id="photoCamera" style="display:none;">
            <video id="photoVideo" width="350" height="250" autoplay></video><br>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-success" type="button" onclick="capturePhoto()">📸 Capture Photo</button>
                <button class="btn btn-secondary" type="button" onclick="closePhotoCamera()">Close</button>
            </div>
        </div>

        <!-- Verification Video -->
        <div class="mb-3">
            <label class="form-label">Verification Video *</label>
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-outline-primary" onclick="openVideoCamera()">🎥 Open Camera</button>
                <button type="button" class="btn btn-outline-danger" onclick="clearRecordedVideos()">❌ Clear Videos</button>
            </div>
            <input type="file" name="verification_video[]" accept="video/*" multiple class="form-control">
            <small class="text-muted">Record or upload verification video(s).</small>
            <div id="videoPreview" class="video-preview d-flex flex-wrap mt-2"></div>
            <input type="hidden" name="captured_videos" id="capturedVideosInput">
        </div>

        <div id="videoCamera" style="display:none;">
            <video id="recordVideo" width="350" height="250" autoplay muted></video><br>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-success" type="button" onclick="startRecording()">⏺️ Start Recording</button>
                <button class="btn btn-danger" type="button" onclick="stopRecording()">⏹️ Stop Recording</button>
                <button class="btn btn-secondary" type="button" onclick="closeVideoCamera()">Close</button>
            </div>
        </div>

        <!-- Other Details -->
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


<!-- 🔥 JavaScript -->
<script>
let stream = null;
let photoStream = null;
let videoStream = null;
let mediaRecorder;
const capturedPhotos = [];
const recordedVideos = [];
const recordedChunks = [];

// -------- IMEI Camera --------
function openCamera() {
    document.getElementById('cameraContainer').style.display = 'block';
    const video = document.getElementById('video');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(s => {
            stream = s;
            video.srcObject = stream;
        });
}

function closeCamera() {
    if (stream) stream.getTracks().forEach(track => track.stop());
    document.getElementById('cameraContainer').style.display = 'none';
}

function captureImage() {
    const canvas = document.getElementById('canvas');
    const video = document.getElementById('video');
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/png');
    decodeBarcode(dataUrl);
    closeCamera();
}

function decodeFromFile(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => decodeBarcode(e.target.result);
    reader.readAsDataURL(file);
}

function decodeBarcode(src) {
    Quagga.decodeSingle({
        src: src,
        numOfWorkers: 0,
        decoder: { readers: ["code_128_reader", "ean_reader", "code_39_reader"] }
    }, function(result) {
        if (result && result.codeResult) {
            const code = result.codeResult.code;
            if (code.length === 15) {
                document.getElementById('imeiInput').value = code;
            } else {
                alert(`❌ IMEI must be exactly 15 digits. Found: ${code.length}`);
            }
        } else {
            alert("❌ Barcode not detected.");
        }
    });
}

// -------- Seller Photo Camera --------
function openPhotoCamera() {
    document.getElementById('photoCamera').style.display = 'block';
    const video = document.getElementById('photoVideo');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(s => {
            photoStream = s;
            video.srcObject = photoStream;
        });
}

function closePhotoCamera() {
    if (photoStream) photoStream.getTracks().forEach(track => track.stop());
    document.getElementById('photoCamera').style.display = 'none';
}

function capturePhoto() {
    const video = document.getElementById('photoVideo');
    const canvas = document.createElement('canvas');
    canvas.width = 350;
    canvas.height = 250;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/png');
    capturedPhotos.push(dataUrl);

    const preview = document.getElementById('photoPreview');
    const img = document.createElement('img');
    img.src = dataUrl;
    preview.appendChild(img);

    document.getElementById('capturedPhotosInput').value = JSON.stringify(capturedPhotos);
}

function clearPhotos() {
    capturedPhotos.length = 0;
    document.getElementById('photoPreview').innerHTML = '';
    document.getElementById('capturedPhotosInput').value = '';
}

// -------- Verification Video Recording --------
function openVideoCamera() {
    document.getElementById('videoCamera').style.display = 'block';
    const video = document.getElementById('recordVideo');
    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(s => {
            videoStream = s;
            video.srcObject = videoStream;
        });
}

function closeVideoCamera() {
    if (videoStream) videoStream.getTracks().forEach(track => track.stop());
    document.getElementById('videoCamera').style.display = 'none';
}

function startRecording() {
    recordedChunks.length = 0;
    mediaRecorder = new MediaRecorder(videoStream, { mimeType: 'video/webm' });

    mediaRecorder.ondataavailable = function(e) {
        if (e.data.size > 0) recordedChunks.push(e.data);
    };

    mediaRecorder.onstop = function() {
        const blob = new Blob(recordedChunks, { type: 'video/webm' });
        const url = URL.createObjectURL(blob);

        const preview = document.getElementById('videoPreview');
        const video = document.createElement('video');
        video.src = url;
        video.controls = true;
        video.width = 200;
        preview.appendChild(video);

        const reader = new FileReader();
        reader.onload = function() {
            recordedVideos.push(reader.result);
            document.getElementById('capturedVideosInput').value = JSON.stringify(recordedVideos);
        };
        reader.readAsDataURL(blob);
    };

    mediaRecorder.start();
}

function stopRecording() {
    mediaRecorder.stop();
}

function clearRecordedVideos() {
    recordedVideos.length = 0;
    document.getElementById('videoPreview').innerHTML = '';
    document.getElementById('capturedVideosInput').value = '';
}

// -------- IMEI Manual Validation --------
document.getElementById("purchaseForm")?.addEventListener("submit", function(e) {
    const imei = document.getElementById("imeiInput").value.trim();
    if (imei.length !== 15) {
        alert("❌ IMEI must be exactly 15 digits.");
        e.preventDefault();
    }
});
</script>


</body>
</html>
