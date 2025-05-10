<?php
include __DIR__ . '/../patient_portal/db.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id  = (int)$_POST['patient_id'];
    $doctor_name = trim($_POST['doctor_name']);
    $message     = trim($_POST['message']);

    if (!$patient_id || !$doctor_name || !$message) {
        exit("All fields are required.");
    }

    $stmt = $conn->prepare("INSERT INTO messages (patient_id, doctor_name, message) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $patient_id, $doctor_name, $message);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "Message sent successfully!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Message to Patient</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <h2>Send Message to Patient</h2>
    <form method="POST" action="message_patient.php">
      <label>Patient ID:</label><br>
      <input type="number" name="patient_id" required><br><br>

      <label>Doctor Name:</label><br>
      <input type="text" name="doctor_name" required><br><br>

      <label>Message:</label><br>
      <textarea name="message" rows="5" cols="40" required></textarea><br><br>

      <button type="submit">Send Message</button>
    </form>
  </div>
</body>
</html>
