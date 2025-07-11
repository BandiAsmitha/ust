<?php
// Start the session
session_start();

// Check if the seller is logged in by checking session data
if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit();
}

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

// Fetch the seller's information
$seller_id = $_SESSION['seller_id'];
$stmt = $conn->prepare("SELECT * FROM sellers WHERE id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the seller exists in the database
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $seller_name = $row["name"];
    $seller_phone = $row["phone_number"];
} else {
    // Seller not found, log out and redirect to login
    session_destroy();
    header("Location: seller_login.php");
    exit();
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 50px;
        }
        .dashboard-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dashboard-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group p {
            font-weight: bold;
        }
        .form-group a {
            display: inline-block;
            margin-top: 10px;
            color: #1e90ff;
            text-decoration: none;
        }
        .form-group a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <h2>Welcome, <?php echo $seller_name; ?>!</h2>
        <div class="form-group">
            <p>Phone Number: <?php echo $seller_phone; ?></p>
        </div>
        <div class="form-group">
            <p><a href="seller_logout.php">Logout</a></p>
        </div>
        <div class="form-group">
            <p><a href="add_product.php">Add a New Product</a></p>
        </div>
        <div class="form-group">
            <p><a href="view_products.php">View Your Products</a></p>
        </div>
    </div>

</body>
</html>
