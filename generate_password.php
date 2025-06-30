<?php
$password = "Jindwalaphone";  // 🔥 Your desired password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashedPassword;
?>