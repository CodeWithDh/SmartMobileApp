<?php include 'components/navbar.php'; ?>
<?php $imei = isset($_POST['imei']) ? $_POST['imei'] : ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sell Mobile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            margin-left: 260px;
            background: white;
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

        .photo-preview img, .video-preview video {
            width: 100px;
            height: auto;
            border: 2px solid #5409DA;
            border-radius: 8px;
        }

        video {
            border: 2px solid #5409DA;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<?php include 'components/topnav.php'; ?>
<div class="form-container">
    <h2>Sell Mobile - IMEI: <?php echo htmlspecialchars($imei); ?></h2>

    <form action="backend/insertSell.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imei" value="<?php echo htmlspecialchars($imei); ?>">

        <!-- Buyer Name -->
        <div class="mb-3">
            <label class="form-label">Buyer Name *</label>
            <input type="text" name="buyer_name" class="form-control" required>
        </div>

        <!-- Buyer Mobile -->
        <div class="mb-3">
            <label class="form-label">Buyer Mobile *</label>
            <input type="text" name="buyer_mobile" class="form-control" required>
        </div>

        <!-- Buyer Photos -->
        <div class="mb-3">
            <label class="form-label">Buyer Photos *</label><br>
            <button type="button" class="btn btn-outline-primary" onclick="openPhotoCamera()">📷 Camera</button>
            <input type="file" name="buyer_photo[]" accept="image/*" multiple class="form-control mt-2">
            <button type="button" class="btn btn-outline-danger mt-2" onclick="clearPhotos()">❌ Clear</button>
            <div id="photoPreview" class="photo-preview mt-2 d-flex gap-2 flex-wrap"></div>
            <input type="hidden" name="captured_photos" id="capturedPhotosInput">
        </div>

        <div id="photoCamera" style="display:none;">
            <video id="photoVideo" width="350" height="250" autoplay></video><br>
            <button type="button" class="btn btn-success mt-2" onclick="capturePhoto()">📸 Capture Photo</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="closePhotoCamera()">Close</button>
        </div>

        <!-- Buyer Verification Video -->
        <div class="mb-3">
            <label class="form-label">Buyer Verification Video *</label><br>
            <button type="button" class="btn btn-outline-primary" onclick="openVideoCamera()">🎥 Open Camera</button>
            <button type="button" class="btn btn-outline-danger" onclick="clearRecordedVideos()">❌ Clear</button>
            <div id="videoPreview" class="video-preview mt-2 d-flex gap-2 flex-wrap"></div>
            <input type="hidden" name="captured_video" id="capturedVideoData">
            <input type="file" id="videoInput" name="buyer_verification" style="display:none;">
        </div>

        <div id="videoCamera" style="display:none;">
            <video id="recordVideo" width="350" height="250" autoplay muted></video><br>
            <button class="btn btn-success mt-2" type="button" onclick="startRecording()">⏺️ Start</button>
            <button class="btn btn-danger mt-2" type="button" onclick="stopRecording()">⏹️ Stop</button>
            <button class="btn btn-secondary mt-2" type="button" onclick="closeVideoCamera()">Close</button>
        </div>

        <!-- Sold Price -->
        <div class="mb-3">
            <label class="form-label">Sold Price *</label>
            <input type="number" name="sold_price" step="0.01" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Complete Sale</button>
    </form>
</div>

<canvas id="photoCanvas" width="350" height="250" style="display:none;"></canvas>

<script>
    // ==== PHOTO CAMERA ====
    let photoStream;
    function openPhotoCamera() {
        navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
            photoStream = stream;
            document.getElementById("photoCamera").style.display = "block";
            document.getElementById("photoVideo").srcObject = stream;
        });
    }

    function capturePhoto() {
        const video = document.getElementById("photoVideo");
        const canvas = document.getElementById("photoCanvas");
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataURL = canvas.toDataURL("image/png");

        const preview = document.getElementById("photoPreview");
        const img = new Image();
        img.src = dataURL;
        preview.appendChild(img);

        let captured = document.getElementById("capturedPhotosInput").value;
        document.getElementById("capturedPhotosInput").value = captured + dataURL + "|";
    }

    function closePhotoCamera() {
        if (photoStream) {
            photoStream.getTracks().forEach(track => track.stop());
        }
        document.getElementById("photoCamera").style.display = "none";
    }

    function clearPhotos() {
        document.getElementById("photoPreview").innerHTML = "";
        document.getElementById("capturedPhotosInput").value = "";
    }

    // ==== VIDEO CAMERA ====
    let videoStream, mediaRecorder, recordedBlobs = [];

    function openVideoCamera() {
        navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(function (stream) {
            videoStream = stream;
            document.getElementById("videoCamera").style.display = "block";
            document.getElementById("recordVideo").srcObject = stream;
        });
    }

    function startRecording() {
        recordedBlobs = [];
        mediaRecorder = new MediaRecorder(videoStream, { mimeType: 'video/webm' });

        mediaRecorder.ondataavailable = function (e) {
            if (e.data.size > 0) recordedBlobs.push(e.data);
        };

        mediaRecorder.onstop = function () {
            const blob = new Blob(recordedBlobs, { type: 'video/mp4' });
            const file = new File([blob], 'BuyerVerification.mp4', { type: 'video/mp4' });
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById("videoInput").files = dt.files;

            const url = URL.createObjectURL(blob);
            const preview = document.getElementById("videoPreview");
            const video = document.createElement("video");
            video.src = url;
            video.controls = true;
            preview.appendChild(video);
        };

        mediaRecorder.start();
    }

    function stopRecording() {
        mediaRecorder.stop();
    }

    function closeVideoCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }
        document.getElementById("videoCamera").style.display = "none";
    }

    function clearRecordedVideos() {
        document.getElementById("videoPreview").innerHTML = "";
        document.getElementById("capturedVideoData").value = "";
    }
</script>
</body>
</html>
