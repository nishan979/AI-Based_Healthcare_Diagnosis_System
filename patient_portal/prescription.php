<?php
// -------------------------------------------------------------------
//  patient_prescription.php   (patient_portal)
//  • Shows every prescription the logged‑in patient has, with a
//    medicine table for each.
// -------------------------------------------------------------------

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // or login.php, whichever you're using for login
    exit();
}

$patientId = (int)$_SESSION['user_id'];

include 'db.php';      // $conn (mysqli)

// -------------------- pull prescriptions --------------------
$prescriptions = [];
$stmt = $conn->prepare(
    'SELECT id, doctor_id, created_at
     FROM prescriptions
     WHERE patient_id = ?
     ORDER BY created_at DESC' );
$stmt->bind_param('i', $patientId);
$stmt->execute();
$res = $stmt->get_result();
while ($p = $res->fetch_assoc()) {
    // child rows
    $items = [];
    $s2 = $conn->prepare(
        'SELECT medicine_name, morning, noon, night, period_days, before_meal, after_meal
         FROM prescription_items
         WHERE prescription_id = ?');
    $s2->bind_param('i', $p['id']);
    $s2->execute();
    $child = $s2->get_result();
    while ($row = $child->fetch_assoc()) $items[] = $row;
    $s2->close();

    $p['items'] = $items;
    $prescriptions[] = $p;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Prescriptions – Patient Portal</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    table { width:100%; border-collapse:collapse; margin-top:0.5rem; }
    th, td { border:1px solid #ddd; padding:0.4rem; text-align:center; }
    th { background:#f4f6f9; }
  </style>
</head>
<body>
  <div class="container">
    <?php include 'sidebar.html'; ?>

    <div class="main-content">
      <header><h2>My Prescriptions</h2></header>
      <main>
        <?php if (!$prescriptions): ?>
          <p>No prescriptions yet.</p>
        <?php else: ?>
          <?php foreach ($prescriptions as $p): ?>
            <div class="prescription-item" style="background:#fff; padding:1rem; margin-bottom:1rem; border-radius:4px; box-shadow:0 1px 4px rgba(0,0,0,0.1);">
              <h4>Date: <?= date('Y‑m‑d', strtotime($p['created_at'])) ?></h4>
              <table>
                <thead>
                  <tr>
                    <th>Medicine</th><th>M</th><th>N</th><th>Ni</th><th>Days</th><th>Before</th><th>After</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($p['items'] as $it): ?>
                    <tr>
                      <td style="text-align:left;"><?= htmlspecialchars($it['medicine_name']) ?></td>
                      <td><?= $it['morning'] ? '✔' : '' ?></td>
                      <td><?= $it['noon']    ? '✔' : '' ?></td>
                      <td><?= $it['night']   ? '✔' : '' ?></td>
                      <td><?= $it['period_days'] ?></td>
                      <td><?= $it['before_meal'] ? '✔' : '' ?></td>
                      <td><?= $it['after_meal']  ? '✔' : '' ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </main>
    </div>
  </div>
</body>
</html>
