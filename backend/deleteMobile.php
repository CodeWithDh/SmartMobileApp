<?php
require_once __DIR__ . '/../vendor/autoload.php';
require 'db.php';

use Google\Client;
use Google\Service\Drive;

try {
    // Fetch inputs
    $imei = $_POST['imei'] ?? '';
    $folderId = $_POST['drive_folder_id'] ?? '';

    if (!$imei || !$folderId) {
        throw new Exception("Missing required data.");
    }

    // Google Client setup
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->addScope(Drive::DRIVE);
    $service = new Drive($client);

    // Delete Drive folder
    $service->files->delete($folderId);

    // Delete DB record
    $sql = "DELETE FROM purchased_mobiles WHERE IMEI = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $imei);
    $stmt->execute();

    // Redirect
    header("Location: ../success.php?msg=Mobile+Record+Deleted");
    exit;
} catch (Exception $e) {
    header("Location: ../error.php?msg=" . urlencode($e->getMessage()));
    exit;
}
