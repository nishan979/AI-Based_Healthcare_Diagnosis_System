<?php
session_start();
include 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Check if the user ID is passed through the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the user's current data from the database
    $sql = "SELECT * FROM patients WHERE id = '$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission to update the user data
    $name = $_POST['name'];
    $bloodGroup = $_POST['bloodGroup'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];

    // Update the database
    $update_sql = "UPDATE patients SET name = '$name', bloodGroup = '$bloodGroup', address = '$address', mobile = '$mobile', email = '$email' WHERE id = '$user_id'";

    if ($conn->query($update_sql) === TRUE) {
        // Successfully updated
        header("Location: profile.php"); // Redirect back to the profile page after update
        exit();
    } else {
        // Handle error
        echo "Error: " . $conn->error;
    }
}

// Do not close the connection here, wait until the query is done.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <!-- SIDEBAR (same as in profile.php) -->

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <header>
                <div class="search-wrapper">
                    <input type="search" placeholder="Search..." />
                </div>
            </header>

            <main>
                <h2>Update Profile</h2>
                <div class="profile-details">
                    <div class="profile-photo">
                        <img src="uploads/<?php echo $user['photo']; ?>" alt="Profile Photo" width="150" height="150">
                    </div>
                    <div class="profile-form">
                        <form action="update-profile.php?user_id=<?php echo $user['id']; ?>" method="POST">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required />

                            <label for="bloodGroup">Blood Group:</label>
                            <input type="text" id="bloodGroup" name="bloodGroup" value="<?php echo $user['bloodGroup']; ?>" required />

                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" value="<?php echo $user['address']; ?>" required />

                            <label for="mobile">Mobile Number:</label>
                            <input type="text" id="mobile" name="mobile" value="<?php echo $user['mobile']; ?>" required />

                            <label for="email">Email Address:</label>
                            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required />

                            <button type="submit">Update Profile</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

<?php
// Close the connection only after the query has been executed
$conn->close();
?>
