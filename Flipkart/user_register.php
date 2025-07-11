<?php
// Database connection settings
$servername = "localhost";
$username = "root";  // MySQL username
$password = "";  // MySQL password
$dbname = "Flipkart";  // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the registration form is submitted
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password for security

    // SQL query to insert user details into the database
    $sql = "INSERT INTO login (username, phone_number, email, password) 
            VALUES ('$username', '$phone_number', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "Registration successful! You can now <a href='user_login.php'>login</a>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
