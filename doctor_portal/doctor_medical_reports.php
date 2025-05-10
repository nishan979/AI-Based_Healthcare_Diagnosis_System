<?php
// -------------------------------------------
//  doctor_medical_reports.php  (Doctor portal)
// -------------------------------------------
//   • Accepts a patient_id via GET or via the search box.
//   • Pulls every PDF path from medical_reports for that ID.
//   • Lists them so the doctor can view / download.
// -------------------------------------------

session_start();
// (Add your own doctor‑auth check here if needed)

include '../patient_portal/db.php';    // mysqli $conn

$patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$patientName = '';
$reports     = [];

if ($patientId) {
    /* fetch patient name */
    $stmt = $conn->prepare('SELECT name FROM patients WHERE id = ?');
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $stmt->bind_result($patientName);
    $stmt->fetch();
    $stmt->close();

    /* fetch reports */
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
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Reports – Doctor Portal</title>
  <link rel="stylesheet" href="../patient_portal/styles.css">
</head>
<body>
  <div class="container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-brand"><h2>DoctorPortal</h2></div>
      <ul class="sidebar-menu">
        <li><a href="doctor_index.html"              class="icon-dashboard">Dashboard</a></li>
        <li><a href="doctor_appointments.html"       class="icon-bookings">Appointments</a></li>
        <li><a href="message_patient.php" class="icon-profile">Message Patient</a></li>
        <li><a href="doctor_reviews.html"            class="icon-reviews">Review</a></li>
        <li><a href="doctor_prescription_generator.php" class="icon-prescription">Prescription Generator</a></li>
        <li class="active"><a href="doctor_medical_reports.php" class="icon-medical-reports">Patient Medical Report</a></li>
        <li><a href="login.html" class="icon-logout">Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <!-- HEADER -->
      <header>
        <form class="search-wrapper" action="doctor_medical_reports.php" method="get" style="display:flex; gap:0.5rem;">
          <input type="number" name="patient_id" placeholder="Enter Patient ID" value="<?= $patientId ?: '' ?>" required>
          <button type="submit">🔍</button>
        </form>
        <div class="user-wrapper">
          <img src="https://via.placeholder.com/40" alt="Doctor" />
          <div>
            <h4>Dr. John Smith</h4>
            <small>Doctor</small>
          </div>
        </div>
      </header>

      <main>
        <?php if (!$patientId): ?>
          <h2>Search for a Patient</h2>
          <p>Enter a patient ID in the box above to view their reports.</p>

        <?php elseif (!$patientName): ?>
          <h2>No patient found with ID <?= $patientId ?></h2>

        <?php else: ?>
          <h2>Reports for <?= htmlspecialchars($patientName) ?> (ID <?= $patientId ?>)</h2>

          <?php if (!$reports): ?>
            <p>No reports uploaded yet.</p>
          <?php else: ?>
            <ul class="uploaded-reports">
              <?php foreach ($reports as $r): ?>
                <li>
                  <a href="<?= htmlspecialchars($r['file_path']) ?>" target="_blank">
                    <?= htmlspecialchars($r['file_name']) ?>
                  </a>
                  – <?= date('Y‑m‑d H:i', strtotime($r['uploaded_at'])) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        <?php endif; ?>
      </main>
    </div>
  </div>
</body>
</html>
