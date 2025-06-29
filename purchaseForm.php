<form action="backend/insertPurchase.php" method="POST" enctype="multipart/form-data">

    <label>IMEI Number *</label>
    <input type="text" name="imei" required><br><br>

    <label>Seller Name *</label>
    <input type="text" name="seller_name" required><br><br>

    <label>Seller Photos (Multiple) *</label>
    <input type="file" name="seller_photo[]" multiple accept="image/*" required><br><br>

    <label>Verification Video (MP4) *</label>
    <input type="file" name="verification_video" accept="video/mp4" required><br><br>

    <label>Mobile Name *</label>
    <input type="text" name="mobile_name" required><br><br>

    <label>Fault Description</label>
    <textarea name="fault_description"></textarea><br><br>

    <label>Price *</label>
    <input type="number" name="price" step="0.01" required><br><br>

    <label>Purchase Date *</label>
    <input type="date" name="purchase_date" required><br><br>

    <button type="submit" name="submit">Add Purchase</button>
</form>
