<?php
session_start();

$loginErr = "";
$phone_number = $password = "";

// DB config
$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "UST";

// Create DB connection
$conn = new mysqli($servername, $username, $dbpassword, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = trim($_POST["phone_number"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM login WHERE phone_number = ?");
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $loginErr = "No user found. Please register.";
    } else {
        $row = $result->fetch_assoc();

        // Check plain or hashed password
        if ($password === $row["password"] || password_verify($password, $row["password"])) {
            $_SESSION['mobile'] = $phone_number; // Match variable used in index.php
            header("Location: index.php");
            exit();
        } else {
            $loginErr = "Incorrect password.";
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!-- HTML for Login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('http://localhost/UST/ust/bg.jpg') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            color: white;
        }

        .login-container {
            background: rgba(0, 0, 0, 0.75);
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
        }

        input[type="submit"] {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        a {
            color: #0dcaf0;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($loginErr)) echo "<p class='error'>$loginErr</p>"; ?>
        <form method="post" action="">
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="user_signup.php">Register here</a></p>
    </div>
</body>
</html>
