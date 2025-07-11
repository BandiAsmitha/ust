<?php
// Initialize variables to store error messages (if any)
$nameErr = $phoneErr = $passwordErr = "";
$name = $phone_number = $password = "";

// Database connection details
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "AMAZON";

// Create a connection to the database
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = sanitize_input($_POST["name"]);
    }

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

    // If no errors, proceed to insert the new seller into the database
    if (empty($nameErr) && empty($phoneErr) && empty($passwordErr)) {
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL query to insert the seller's data into the database
        $stmt = $conn->prepare("INSERT INTO sellers (name, phone_number, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone_number, $hashed_password);

        // Execute the query
        if ($stmt->execute()) {
            echo "<p>Seller registered successfully! <a href='seller_login.php'>Login here</a></p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }

        // Close the statement
        $stmt->close();
    }
}

// Function to sanitize input (avoiding XSS, etc.)
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 50px;
        }
        .signup-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .signup-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group input[type="submit"] {
            background-color: #131921;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #1e90ff;
        }
        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="signup-container">
        <h2>Seller Signup</h2>

        <!-- Signup Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Name -->
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
                <span class="error"><?php echo $nameErr; ?></span>
            </div>

            <!-- Phone Number -->
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo $phone_number; ?>" required>
                <span class="error"><?php echo $phoneErr; ?></span>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="<?php echo $password; ?>" required>
                <span class="error"><?php echo $passwordErr; ?></span>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <input type="submit" value="Sign Up">
            </div>

            <!-- Link to Login -->
            <div class="form-group">
                <p>Already have an account? <a href="seller_login.php">Login here</a></p>
            </div>
        </form>
    </div>

</body>
</html>
