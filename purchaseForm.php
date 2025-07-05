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
    .progress {
      height: 25px;
      background-color: #eee;
      border-radius: 8px;
      overflow: hidden;
    }
    .progress-bar {
      transition: width 0.3s ease;
      font-weight: bold;
    }
  </style>
</head>
<body>
<?php include 'components/topnav.php'; ?>
<div class="form-container">
  <h3 class="mb-4 fw-bold" style="color:#5409DA;">Purchase Mobile</h3>

  <form id="purchaseForm" action="backend/insertPurchase.php" method="POST" enctype="multipart/form-data">
    <!-- IMEI -->
    <div class="mb-3">
      <label class="form-label">IMEI Number *</label>
      <div class="d-flex gap-2 align-items-center">
        <input type="text" name="imei" id="imeiInput" class="form-control" required pattern="\d{15,}" title="IMEI must be 15 digits">
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
        <button type="button" class="btn btn-outline-danger" onclick="clearPhotos()">❌ Clear</button>
      </div>
      <input type="file" name="seller_photos[]" accept="image/*" multiple class="form-control">
      <div id="photoPreview" class="photo-preview d-flex flex-wrap mt-2"></div>
    </div>

    <div id="photoCamera" style="display:none;">
      <video id="photoVideo" width="350" height="250" autoplay></video><br>
      <div class="d-flex gap-2 mt-2">
        <button class="btn btn-success" type="button" onclick="capturePhoto()">📸 Capture</button>
        <button class="btn btn-secondary" type="button" onclick="closePhotoCamera()">Close</button>
      </div>
    </div>

    <!-- Verification Video -->
    <div class="mb-3">
      <label class="form-label">Verification Video *</label>
      <div class="d-flex gap-2 mb-2">
        <button type="button" class="btn btn-outline-primary" onclick="openVideoCamera()">🎥 Open Camera</button>
        <button type="button" class="btn btn-outline-danger" onclick="clearRecordedVideos()">❌ Clear</button>
      </div>
      <input type="file" name="verification_video[]" accept="video/*" multiple class="form-control">
      <div id="videoPreview" class="video-preview d-flex flex-wrap mt-2"></div>
    </div>

    <div id="videoCamera" style="display:none; position:relative;">
      <video id="recordVideo" width="350" height="250" autoplay muted></video>
      <div id="overlayTimer" style="position:absolute; top:8px; left:12px; color:white; font-weight:bold; background-color:rgba(0,0,0,0.5); padding:2px 6px; border-radius:4px;">⏱️ 0s</div>
      <div class="d-flex gap-2 mt-2">
        <button class="btn btn-success" type="button" onclick="startRecording()">⏺️ Start</button>
        <button class="btn btn-danger" type="button" onclick="stopRecording()">⏹️ Stop</button>
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

  <!-- Progress Loader -->
  <div id="loaderWrapper" style="display:none; margin-top: 20px;">
    <label style="font-weight: 600;">Uploading: <span id="progressText">0%</span></label>
    <div class="progress">
      <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%">0%</div>
    </div>
  </div>
</div>

<script>
let stream = null, photoStream = null, videoStream = null, mediaRecorder, timerInterval, time = 0;
let recordedChunks = [];

function updateProgress(value) {
  const bar = document.getElementById("progressBar");
  const text = document.getElementById("progressText");
  bar.style.width = value + '%';
  bar.textContent = value + '%';
  text.textContent = value + '%';
}

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const xhr = new XMLHttpRequest();
  let fakeProgressValue = 0;

  document.getElementById("loaderWrapper").style.display = "block";

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      const percent = Math.min(Math.round((e.loaded / e.total) * 100), 90);
      fakeProgressValue = percent;
      updateProgress(percent);
    }
  };

  xhr.onload = function() {
    const interval = setInterval(() => {
      if (fakeProgressValue < 99) {
        fakeProgressValue++;
        updateProgress(fakeProgressValue);
      } else {
        clearInterval(interval);
        updateProgress(100);
        document.getElementById("progressText").textContent = "✅ Completed!";
        setTimeout(() => {
          try {
            const response = JSON.parse(xhr.responseText);
            const imei = encodeURIComponent(response.imei || document.getElementById('imeiInput').value.trim());
            const pdf  = encodeURIComponent(response.pdf || '');
            const type = 'purchase';
            const base = window.location.origin + window.location.pathname.replace(/[^\/]*$/, '');
            window.location.href = `${base}success.php?imei=${imei}&pdf=${pdf}&type=${type}`;
          } catch (err) {
            console.error("Invalid JSON response", err, xhr.responseText);
            alert("Upload complete, but could not redirect with details.");
          }
        }, 800);
      }
    }, 80);
  };

  xhr.onerror = () => {
    alert("❌ Upload failed.");
    document.getElementById("loaderWrapper").style.display = "none";
  };

  xhr.open('POST', this.getAttribute('action'));
  xhr.send(formData);
});

// Barcode Camera
function openCamera() {
  document.getElementById('cameraContainer').style.display = 'block';
  navigator.mediaDevices.getUserMedia({ video: true }).then(s => {
    stream = s;
    document.getElementById('video').srcObject = s;
  });
}
function closeCamera() {
  if (stream) stream.getTracks().forEach(t => t.stop());
  document.getElementById('cameraContainer').style.display = 'none';
}
function captureImage() {
  const canvas = document.getElementById('canvas');
  const video = document.getElementById('video');
  canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
  decodeBarcode(canvas.toDataURL('image/png'));
  closeCamera();
}
function decodeFromFile(input) {
  const reader = new FileReader();
  reader.onload = e => decodeBarcode(e.target.result);
  reader.readAsDataURL(input.files[0]);
}
function decodeBarcode(src) {
  Quagga.decodeSingle({
    src,
    numOfWorkers: 0,
    decoder: { readers: ["code_128_reader", "ean_reader", "code_39_reader"] }
  }, result => {
    if (result?.codeResult?.code?.length === 15) {
      document.getElementById('imeiInput').value = result.codeResult.code;
    } else {
      alert("❌ IMEI not valid or not detected.");
    }
  });
}

// Seller Photo
function openPhotoCamera() {
  document.getElementById('photoCamera').style.display = 'block';
  navigator.mediaDevices.getUserMedia({ video: true }).then(s => {
    photoStream = s;
    document.getElementById('photoVideo').srcObject = s;
  });
}
function closePhotoCamera() {
  if (photoStream) photoStream.getTracks().forEach(t => t.stop());
  document.getElementById('photoCamera').style.display = 'none';
}
function capturePhoto() {
  const canvas = document.createElement('canvas');
  canvas.width = 350;
  canvas.height = 250;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(document.getElementById('photoVideo'), 0, 0, canvas.width, canvas.height);
  canvas.toBlob(blob => {
    const file = new File([blob], `photo_${Date.now()}.png`, { type: 'image/png' });
    const input = document.querySelector('input[name="seller_photos[]"]');
    const dt = new DataTransfer();
    for (let i = 0; i < input.files.length; i++) dt.items.add(input.files[i]);
    dt.items.add(file);
    input.files = dt.files;
    const img = document.createElement('img');
    img.src = URL.createObjectURL(blob);
    document.getElementById('photoPreview').appendChild(img);
  }, 'image/png');
}
function clearPhotos() {
  document.querySelector('input[name="seller_photos[]"]').value = '';
  document.getElementById('photoPreview').innerHTML = '';
}

// Video Recording
function openVideoCamera() {
  document.getElementById('videoCamera').style.display = 'block';
  navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(s => {
    videoStream = s;
    document.getElementById('recordVideo').srcObject = s;
  });
}
function closeVideoCamera() {
  if (videoStream) videoStream.getTracks().forEach(t => t.stop());
  document.getElementById('videoCamera').style.display = 'none';
  stopTimer();
}
function startRecording() {
  recordedChunks = [];
  mediaRecorder = new MediaRecorder(videoStream);
  mediaRecorder.ondataavailable = e => recordedChunks.push(e.data);
  mediaRecorder.onstop = () => {
    stopTimer();
    const blob = new Blob(recordedChunks, { type: 'video/webm' });
    const file = new File([blob], `video_${Date.now()}.webm`, { type: 'video/webm' });
    const input = document.querySelector('input[name="verification_video[]"]');
    const dt = new DataTransfer();
    for (let i = 0; i < input.files.length; i++) dt.items.add(input.files[i]);
    dt.items.add(file);
    input.files = dt.files;

    const video = document.createElement('video');
    video.src = URL.createObjectURL(blob);
    video.controls = true;
    video.width = 150;
    document.getElementById('videoPreview').appendChild(video);
  };
  mediaRecorder.start();
  startTimer();
}
function stopRecording() {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop();
  }
}
function clearRecordedVideos() {
  document.getElementById('videoPreview').innerHTML = '';
  document.querySelector('input[name="verification_video[]"]').value = '';
}
function startTimer() {
  time = 0;
  document.getElementById("overlayTimer").textContent = "⏱️ 0s";
  timerInterval = setInterval(() => {
    time++;
    document.getElementById("overlayTimer").textContent = `⏱️ ${time}s`;
  }, 1000);
}
function stopTimer() {
  clearInterval(timerInterval);
  document.getElementById("overlayTimer").textContent = "⏱️ 0s";
}
</script>

</body>
</html>
