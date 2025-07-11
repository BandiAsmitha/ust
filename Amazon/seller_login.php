<?php
// Initialize variables to store error messages (if any)
$phoneErr = $passwordErr = "";
$phone_number = $password = "";
$loginErr = "";

// Database connection details
$servername = "localhost"; // Your database host
$dbusername = "root";      // Your database username
$dbpassword = "";          // Your database password
$dbname = "AMAZON"; // Your database name

// Create a connection to the database
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
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

    // If no errors, proceed to check the credentials in the database
    if (empty($phoneErr) && empty($passwordErr)) {
        // Prepare the SQL query to fetch the seller data from the database
        $stmt = $conn->prepare("SELECT * FROM sellers WHERE phone_number = ?");
        $stmt->bind_param("s", $phone_number); // 's' means the variable is a string

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a seller was found with the given phone number
        if ($result->num_rows > 0) {
            // Fetch the seller record
            $row = $result->fetch_assoc();

            // Verify the password using password_verify() if passwords are hashed in the database
            if (password_verify($password, $row["password"])) {
                // Start the session and store session variables for user identification
                session_start();
                $_SESSION['seller_id'] = $row["id"]; // Assuming 'id' is the primary key

                // Login successful - redirect to the seller's dashboard or homepage
                header("Location: seller_dashboard.php");
                exit();
            } else {
                // Invalid password
                $loginErr = "Invalid phone number or password";
            }
        } else {
            // Seller not found
            $loginErr = "Invalid phone number or password";
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
    <title>Seller Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(53, 90, 126);
            padding: 50px;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
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
        .success {
            color: green;
        }
        .message {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Seller Login</h2>

        <!-- Display error or success messages -->
        <div class="message">
            <?php 
            if (isset($loginErr)) {
                echo "<p class='error'>$loginErr</p>";
            }
            ?>
        </div>

        <!-- Login Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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
                <input type="submit" value="Login">
            </div>

            <!-- Link to Register -->
            <div class="form-group">
                <p>Don't have an account? <a href="seller_signup.php">Register here</a></p>
            </div>
        </form>
    </div>

</body>
</html>
