<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

include 'db.php'; // Include the database connection

// Fetch the user's data from the database using the user ID stored in the session
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM patients WHERE id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
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
        <li><a href="messages.php" class="icon-messages">Messages</a></li>
        <li><a href="reviews.html" class="icon-reviews">Reviews</a></li>
        <li><a href="bookmarks.html" class="icon-bookmarks">Bookmarks</a></li>
        <li><a href="prescription.php" class="icon-prescription">Prescription</a></li>
        <li><a href="medical_report.php" class="icon-medical-reports">Medical Reports</a></li>
        <li class="active"><a href="profile.php" class="icon-profile">My Profile</a></li>
        <!-- 🔄 Logout should now go to logout.php -->
        <li><a href="logout.php" class="icon-logout">Logout</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
      <!-- HEADER -->
      <header>
        <div class="search-wrapper">
          <input type="search" placeholder="Search..." />
        </div>
        <div class="user-wrapper">
          <img src="uploads/<?php echo $user['photo']; ?>" width="40" height="40" alt="User" />
          <div>
            <h4><?php echo $user['name']; ?></h4>
            <small>Patient</small>
          </div>
        </div>
      </header>

      <!-- PROFILE INFORMATION -->
      <main>
        <h2>My Profile</h2>
        <div class="profile-details">
          <div class="profile-photo">
            <img src="uploads/<?php echo $user['photo']; ?>" alt="Profile Photo" width="150" height="150">
          </div>
          <div class="profile-form">
            <label for="patient-id">Patient ID:</label>
            <input type="text" id="patient-id" value="<?php echo $user['id']; ?>" disabled />

            <label for="name">Name:</label>
            <input type="text" id="name" value="<?php echo $user['name']; ?>" disabled />

            <label for="bloodGroup">Blood Group:</label>
            <input type="text" id="bloodGroup" value="<?php echo $user['bloodGroup']; ?>" disabled />

            <label for="address">Address:</label>
            <input type="text" id="address" value="<?php echo $user['address']; ?>" disabled />

            <label for="mobile">Mobile Number:</label>
            <input type="text" id="mobile" value="<?php echo $user['mobile']; ?>" disabled />

            <label for="email">Email Address:</label>
            <input type="email" id="email" value="<?php echo $user['email']; ?>" disabled />

            <!-- Update Profile Button -->
            <div class="update-button-container">
              <a href="update-profile.php?user_id=<?php echo $user['id']; ?>" class="update-button">
                <button type="button">Update Profile</button>
              </a>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
