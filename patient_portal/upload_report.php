<?php
session_start();

// Check if user is logged in using the correct session key
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // or login.php depending on your setup
    exit;
}

$patientId = (int)$_SESSION['user_id'];

// Check if a file was uploaded
if (!isset($_FILES['report'])) {
    exit("No file sent");
}

$file = $_FILES['report'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    exit("Upload error");
}

if ($file['type'] !== 'application/pdf') {
    exit("Only PDF files allowed");
}

// ---------- Move file into doctor_portal/uploads ----------
$uploadsDir = __DIR__ . "/../doctor_portal/uploads/";
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$randomName = bin2hex(random_bytes(8)) . ".pdf";
$targetPath = $uploadsDir . $randomName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    exit("Failed to save file");
}

// ---------- Record in DB ----------
include 'db.php';  // includes $conn (mysqli)

$stmt = $conn->prepare(
    "INSERT INTO medical_reports (patient_id, file_name, file_path)
     VALUES (?, ?, ?)"
);
$relativePath = "uploads/$randomName"; // Path to use in href
$stmt->bind_param('iss', $patientId, $file['name'], $relativePath);
$stmt->execute();

$stmt->close();
$conn->close();

// ---------- Redirect back with success message ----------
header("Location: medical_report.php?upload=success");
exit;
?>
