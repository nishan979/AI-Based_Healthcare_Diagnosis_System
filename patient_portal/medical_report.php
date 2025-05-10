<?php
// ------------------------------
//  medical_report.php (Patient)
// ------------------------------
// Displays the upload form and the list of previously‑uploaded reports
// for the currently‑logged‑in patient.
// ---------------------------------------------------------------

session_start();

// If the patient isn’t logged in, kick them to the sign‑in page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // or login.php, whichever you're using for login
    exit();
}


$patientId = (int)$_SESSION['user_id'];

// ---------------------------------------------------------------
//  Pull the logged‑in patient’s name and their reports
// ---------------------------------------------------------------
include 'db.php';               // mysqli $conn

/* patient name (optional) */
$stmt = $conn->prepare('SELECT name FROM patients WHERE id = ?');
$stmt->bind_param('i', $patientId);
$stmt->execute();
$stmt->bind_result($patientName);
$stmt->fetch();
$stmt->close();

/* reports list */
$reports = [];
$stmt = $conn->prepare(
    'SELECT file_name, file_path, uploaded_at
     FROM medical_reports
     WHERE patient_id = ?
     ORDER BY uploaded_at DESC'
);
$stmt->bind_param('i', $patientId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $reports[] = $row;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Medical Reports – Patient Portal</title>
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
        <li><a href="index.html"        class="icon-dashboard">Dashboard</a></li>
        <li><a href="bookings.html"     class="icon-bookings">My Bookings</a></li>
        <li><a href="messages.php"     class="icon-messages">Messages</a></li>
        <li><a href="reviews.html"      class="icon-reviews">Reviews</a></li>
        <li><a href="bookmarks.html"    class="icon-bookmarks">Bookmarks</a></li>
        <li><a href="prescription.php" class="icon-prescription">Prescription</a></li>
        <li class="active"><a href="medical_report.php" class="icon-medical-reports">Medical Reports</a></li>
        <li><a href="profile.php"      class="icon-profile">My Profile</a></li>
        <li><a href="logout.php"        class="icon-logout">Logout</a></li>
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
            <h4><?= htmlspecialchars($patientName ?? 'Patient') ?></h4>
            <small>Patient</small>
          </div>
        </div>
      </header>

      <main>
        <h2>Medical Reports</h2>

        <!-- Upload form -->
        <form class="upload-form" action="upload_report.php" method="post" enctype="multipart/form-data">
          <label for="report">Upload Medical Report (PDF only):</label>
          <input type="file" id="report" name="report" accept="application/pdf" required />
          <button type="submit">Upload Report</button>
        </form>

        <?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
          <p style="color:green; margin-top:0.5rem;">Report uploaded successfully!</p>
        <?php endif; ?>

        <!-- List of previously uploaded reports -->
        <div class="uploaded-reports">
          <h3>Uploaded Reports</h3>
          <?php if (!$reports): ?>
            <p>No reports uploaded yet.</p>
          <?php else: ?>
            <ul>
              <?php foreach ($reports as $r): ?>
                <li>
                  <a href="../doctor_portal/<?= htmlspecialchars($r['file_path']) ?>" target="_blank">
                    <?= htmlspecialchars($r['file_name']) ?>
                  </a>
                  – Uploaded on <?= date('Y-m-d', strtotime($r['uploaded_at'])) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
