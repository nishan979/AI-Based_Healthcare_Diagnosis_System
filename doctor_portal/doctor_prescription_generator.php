<?php
session_start();
// TODO: add your doctor‑auth check here
include '../patient_portal/db.php';   // mysqli $conn (same DB)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Prescription Generator – Doctor Portal</title>
  <link rel="stylesheet" href="../patient_portal/styles.css" />
  <style>
    table { width:100%; border-collapse: collapse; margin-top:1rem; }
    th, td { border:1px solid #ccc; padding:0.5rem; text-align:left; }
    th { background:#f4f6f9; }
    .center { text-align:center; }
  </style>
</head>
<body>
  <div class="container">
    <!-- SIDEBAR (update other links as needed) -->
    <aside class="sidebar">
      <div class="sidebar-brand"><h2>DoctorPortal</h2></div>
      <ul class="sidebar-menu">
      <li class="active"><a href="doctor_index.html" class="icon-dashboard">Dashboard</a></li>
        <li><a href="doctor_appointments.html" class="icon-bookings">Appointments</a></li>
        <li><a href="message_patient.php" class="icon-profile">Message Patient</a></li>
        <li><a href="doctor_reviews.html" class="icon-reviews">Review</a></li>
        <li class="active"><a href="doctor_prescription_generator.php" class="icon-prescription">Prescription Generator</a></li>
        <li><a href="doctor_medical_reports.php" class="icon-medical-reports">Patient Medical Report</a></li>
        <li><a href="login.html" class="icon-logout">Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <!-- HEADER -->
      <header>
        <h2 style="margin:0;">Prescription Generator</h2>
      </header>

      <main>
        <!-- -------------------- form -------------------- -->
        <form action="prescription_submit.php" method="post" id="rxForm">
          <label for="patient_id"><strong>Patient&nbsp;ID:</strong></label>
          <input type="number" id="patient_id" name="patient_id" required style="margin-bottom:1rem;">

          <table id="rxTable">
            <thead>
              <tr>
                <th>Medicine&nbsp;Name</th>
                <th class="center">Morning</th>
                <th class="center">Noon</th>
                <th class="center">Night</th>
                <th>Period (days)</th>
                <th class="center">Before&nbsp;Meal</th>
                <th class="center">After&nbsp;Meal</th>
              </tr>
            </thead>
            <tbody>
              <!-- first blank row -->
              <tr>
                <td><input type="text" name="medicine_name[]" required></td>
                <td class="center"><input type="checkbox" name="morning[]"></td>
                <td class="center"><input type="checkbox" name="noon[]"></td>
                <td class="center"><input type="checkbox" name="night[]"></td>
                <td><input type="number" name="period[]" min="1" required></td>
                <td class="center"><input type="checkbox" name="before_meal[]"></td>
                <td class="center"><input type="checkbox" name="after_meal[]"></td>
              </tr>
            </tbody>
          </table>
          <button type="button" id="addRowBtn" style="margin-top:0.5rem;">+ Add Medicine</button>

          <br><br>
          <button type="submit">Save Prescription</button>
        </form>

        <?php if (isset($_GET['success'])): ?>
          <p style="color:green;">Prescription saved!</p>
        <?php endif; ?>
      </main>
    </div>
  </div>

<script>
/* add a new blank medicine row */
document.getElementById('addRowBtn').addEventListener('click', () => {
  const row = document.querySelector('#rxTable tbody tr').cloneNode(true);
  row.querySelectorAll('input').forEach(el => {
    if (el.type === 'checkbox') el.checked = false;
    else el.value = '';
  });
  document.querySelector('#rxTable tbody').appendChild(row);
});
</script>
</body>
</html>




