<?php
include 'db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $bloodGroup = $_POST['bloodGroup'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Get the password from the form

    // Hash the password before storing it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Handle file upload for profile photo
    $photo = $_FILES['photo'];
    $photoName = $photo['name'];
    $photoTmpName = $photo['tmp_name'];
    $photoError = $photo['error'];
    $photoSize = $photo['size'];

    // Validate file upload
    $photoExt = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png'];

    if (in_array($photoExt, $allowedExts) && $photoError === 0 && $photoSize <= 2000000) {
        $photoNewName = uniqid('', true) . "." . $photoExt;
        $photoDestination = 'uploads/' . $photoNewName;

        // Move the uploaded file to the 'uploads' folder
        move_uploaded_file($photoTmpName, $photoDestination);

        // Insert data into the database
        $sql = "INSERT INTO patients (name, bloodGroup, address, mobile, email, password, photo) 
                VALUES ('$name', '$bloodGroup', '$address', '$mobile', '$email', '$hashedPassword', '$photoNewName')";

        if ($conn->query($sql) === TRUE) {
            echo "Account created successfully!";
            header("Location: login.html"); // Redirect to login page after signup
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Invalid file type or size exceeds limit.";
    }
    $conn->close();
}
?>
