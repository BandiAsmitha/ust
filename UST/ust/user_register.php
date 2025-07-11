<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $phone_number = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (!preg_match("/^\d{10}$/", $phone_number)) {
        echo "<script>alert('Phone number must be exactly 10 digits.'); window.history.back();</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // DB connection
    $conn = new mysqli("localhost", "root", "", "UST");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    try {
        // Check if phone or email already exists
        $stmt = $conn->prepare("SELECT * FROM login WHERE phone_number = ? OR email = ?");
        $stmt->bind_param("ss", $phone_number, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('User with this phone number or email already exists.'); window.history.back();</script>";
            exit();
        }

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO login (username, phone_number, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $phone_number, $email, $hashed_password);
        $stmt->execute();

        echo "<script>alert('Registration successful! Please login.'); window.location.href = 'user_login.php';</script>";
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('Something went wrong. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
