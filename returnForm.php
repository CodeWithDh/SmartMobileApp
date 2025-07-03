<?php include 'components/navbar.php'; ?>
<?php $imei = isset($_POST['imei']) ? $_POST['imei'] : ''; ?>

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
            padding: 20px;
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
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #4E71FF;
            border: none;
        }

        .btn-outline-primary, .btn-outline-danger {
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
<h2>Return Mobile - IMEI: <?= htmlspecialchars($imei); ?></h2>

<div class="form-container">
    <form action="backend/insertReturn.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="imei" value="<?= htmlspecialchars($imei); ?>">

        <!-- Return Photos -->
        <div class="mb-3">
            <label class="form-label">Return Photos *</label>
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-outline-primary" onclick="openPhotoCamera()">📷 Camera</button>
                <button type="button" class="btn btn-outline-danger" onclick="clearPhotos()">❌ Clear</button>
            </div>
            <input type="file" name="return_photo[]" accept="image/*" multiple class="form-control">
            <div id="photoPreview" class="photo-preview d-flex flex-wrap mt-2"></div>
            <input type="hidden" name="captured_photos" id="capturedPhotosInput">
        </div>

        <div id="photoCamera" style="display:none;">
            <video id="photoVideo" width="350" height="250" autoplay></video><br>
            <button class="btn btn-success mt-2" type="button" onclick="capturePhoto()">📸 Capture Photo</button>
            <button class="btn btn-secondary mt-2" type="button" onclick="closePhotoCamera()">Close</button>
        </div>

        <!-- Return Verification Video -->
        <div class="mb-3">
            <label class="form-label">Return Verification Video *</label>
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn btn-outline-primary" onclick="openVideoCamera()">🎥 Open Camera</button>
                <button type="button" class="btn btn-outline-danger" onclick="clearRecordedVideos()">❌ Clear</button>
            </div>
            <input type="file" name="return_verification" accept="video/mp4" class="form-control">
            <div id="videoPreview" class="video-preview d-flex flex-wrap mt-2"></div>
            <input type="hidden" name="captured_videos" id="capturedVideosInput">
        </div>

        <div id="videoCamera" style="display:none;">
            <video id="recordVideo" width="350" height="250" autoplay muted></video><br>
            <button class="btn btn-success mt-2" type="button" onclick="startRecording()">⏺️ Start Recording</button>
            <button class="btn btn-danger mt-2" type="button" onclick="stopRecording()">⏹️ Stop</button>
            <button class="btn btn-secondary mt-2" type="button" onclick="closeVideoCamera()">Close</button>
        </div>

        <!-- Return Description -->
        <div class="mb-3 mt-4">
            <label class="form-label">Return Description *</label>
            <textarea name="return_description" class="form-control" rows="4" placeholder="Describe reason for return..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Complete Return</button>
    </form>
</div>

<canvas id="photoCanvas" width="350" height="250" style="display:none;"></canvas>

<!-- JavaScript -->
<script>
let photoStream, videoStream, mediaRecorder, recordedBlobs = [];
const capturedPhotos = [];

function openPhotoCamera() {
    navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
        photoStream = stream;
        document.getElementById('photoVideo').srcObject = stream;
        document.getElementById('photoCamera').style.display = 'block';
    });
}

function capturePhoto() {
    const canvas = document.getElementById('photoCanvas');
    const context = canvas.getContext('2d');
    context.drawImage(document.getElementById('photoVideo'), 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/png');

    capturedPhotos.push(dataUrl);
    document.getElementById('capturedPhotosInput').value = capturedPhotos.join("|");

    const img = new Image();
    img.src = dataUrl;
    document.getElementById('photoPreview').appendChild(img);
}

function closePhotoCamera() {
    if (photoStream) photoStream.getTracks().forEach(track => track.stop());
    document.getElementById('photoCamera').style.display = 'none';
}

function clearPhotos() {
    capturedPhotos.length = 0;
    document.getElementById('photoPreview').innerHTML = '';
    document.getElementById('capturedPhotosInput').value = '';
}

function openVideoCamera() {
    navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(stream => {
        videoStream = stream;
        document.getElementById('recordVideo').srcObject = stream;
        document.getElementById('videoCamera').style.display = 'block';
    });
}

function startRecording() {
    recordedBlobs = [];
    mediaRecorder = new MediaRecorder(videoStream, { mimeType: 'video/webm' });

    mediaRecorder.ondataavailable = e => {
        if (e.data.size > 0) recordedBlobs.push(e.data);
    };

    mediaRecorder.onstop = () => {
        const blob = new Blob(recordedBlobs, { type: 'video/webm' });
        const url = URL.createObjectURL(blob);
        const preview = document.getElementById('videoPreview');
        const video = document.createElement('video');
        video.src = url;
        video.controls = true;
        preview.appendChild(video);

        const reader = new FileReader();
        reader.onload = () => {
            document.getElementById('capturedVideosInput').value = JSON.stringify([reader.result]);
        };
        reader.readAsDataURL(blob);
    };

    mediaRecorder.start();
}

function stopRecording() {
    mediaRecorder.stop();
}

function closeVideoCamera() {
    if (videoStream) videoStream.getTracks().forEach(track => track.stop());
    document.getElementById('videoCamera').style.display = 'none';
}

function clearRecordedVideos() {
    document.getElementById('videoPreview').innerHTML = '';
    document.getElementById('capturedVideosInput').value = '';
}
</script>
</body>
</html>
