<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$patientId = $_SESSION['user_id'];

include 'db.php';

$stmt = $conn->prepare("SELECT doctor_name, message, sent_at FROM messages WHERE patient_id = ? ORDER BY sent_at DESC");
$stmt->bind_param('i', $patientId);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Messages - Patient Portal</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <h2>PatientPortal</h2>
      </div>
      <ul class="sidebar-menu">
        <li><a href="index.html" class="icon-dashboard">Dashboard</a></li>
        <li><a href="bookings.html" class="icon-bookings">My Bookings</a></li>
        <li class="active"><a href="messages.php" class="icon-messages">Messages</a></li>
        <li><a href="reviews.html" class="icon-reviews">Reviews</a></li>
        <li><a href="bookmarks.html" class="icon-bookmarks">Bookmarks</a></li>
        <li><a href="prescription.php" class="icon-prescription">Prescription</a></li>
        <li><a href="medical_report.php" class="icon-medical-reports">Medical Reports</a></li>
        <li><a href="profile.php" class="icon-profile">My Profile</a></li>
        <li><a href="logout.php" class="icon-logout">Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <!-- HEADER -->
      <header>
        <div class="search-wrapper">
          <input type="search" placeholder="Search..." />
          <i class="icon icon-search"></i>
        </div>
        <div class="user-wrapper">
          <img src="https://via.placeholder.com/40" alt="User" />
          <div>
            <h4>Patient</h4>
            <small>Messages</small>
          </div>
        </div>
      </header>

      <main>
        <h2>Messages from Doctor</h2>
        <div class="messages">
          <?php if (empty($messages)): ?>
            <p>No messages received yet.</p>
          <?php else: ?>
            <?php foreach ($messages as $msg): ?>
              <div class="message">
                <h4>Dr. <?= htmlspecialchars($msg['doctor_name']) ?></h4>
                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                <span class="date"><?= date('Y-m-d H:i', strtotime($msg['sent_at'])) ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
