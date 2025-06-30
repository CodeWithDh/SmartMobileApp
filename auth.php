<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php"); // 🔥 Redirect to login if not logged in
    exit;
}
?>
