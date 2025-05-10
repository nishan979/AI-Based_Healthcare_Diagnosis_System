<!-- =============================================================
     2. prescription_submit.php  (handles POST, writes DB rows)
     ============================================================= -->
     <?php /*  save as doctor_portal/prescription_submit.php  */

session_start();
include '../patient_portal/db.php';   // mysqli $conn

$patientId = (int)($_POST['patient_id'] ?? 0);
if (!$patientId) die('Missing patient ID');

$doctorId = $_SESSION['doctor_id'] ?? 0;  // if you track doctor login

// 1) create parent prescription row
$stmt = $conn->prepare('INSERT INTO prescriptions (patient_id, doctor_id) VALUES (?, ?)');
$stmt->bind_param('ii', $patientId, $doctorId);
$stmt->execute();
$prescriptionId = $stmt->insert_id;
$stmt->close();

// 2) insert each medicine row
$names = $_POST['medicine_name'] ?? [];
$periods = $_POST['period'] ?? [];
foreach ($names as $i => $name) {
    if (!$name) continue;
    $morning = isset($_POST['morning'][$i]) ? 1 : 0;
    $noon    = isset($_POST['noon'][$i])    ? 1 : 0;
    $night   = isset($_POST['night'][$i])   ? 1 : 0;
    $before  = isset($_POST['before_meal'][$i]) ? 1 : 0;
    $after   = isset($_POST['after_meal'][$i])  ? 1 : 0;
    $period  = (int)($periods[$i] ?? 0);

    $stmt = $conn->prepare('INSERT INTO prescription_items
        (prescription_id, medicine_name, morning, noon, night, period_days, before_meal, after_meal)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isiiiiii', $prescriptionId, $name, $morning, $noon, $night, $period, $before, $after);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header('Location: doctor_prescription_generator.php?success=1');
exit;
?>
