<?php
ob_start(); // Start output buffering
session_start();

// Error and input variable initialization
$phoneErr = $passwordErr = $loginErr = "";
$phone_number = $password = "";

// Database connection details
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "Flipkart";

// Connect to the database
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    if (empty($_POST["phone_number"])) {
        $phoneErr = "Phone number is required";
    } else {
        $phone_number = sanitize_input($_POST["phone_number"]);
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = sanitize_input($_POST["password"]);
    }

    if (empty($phoneErr) && empty($passwordErr)) {
        $stmt = $conn->prepare("SELECT * FROM seller_login WHERE PhoneNumber = ?");
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row["Password"])) {
                $_SESSION['seller_id'] = $row["SellerID"]; // Use SellerID as session identifier
                header("Location: seller_dashboard.php");
                exit();
            } else {
                $loginErr = "Invalid phone number or password.";
            }
        } else {
            $loginErr = "Invalid phone number or password.";
        }

        $stmt->close();
    }
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$conn->close();
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 50px;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #131921;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 4px;
        }
        input[type="submit"]:hover {
            background-color: #1e90ff;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .form-group a {
            display: block;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Seller Login</h2>

    <?php if (!empty($loginErr)): ?>
        <p class="error"><?php echo $loginErr; ?></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" value="<?php echo $phone_number; ?>" required>
            <span class="error"><?php echo $phoneErr; ?></span>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <span class="error"><?php echo $passwordErr; ?></span>
        </div>

        <div class="form-group">
            <input type="submit" value="Login">
        </div>

        <div class="form-group">
            <p>Don't have an account? <a href="seller_signup.php">Register here</a></p>
        </div>
    </form>
</div>
</body>
</html>
