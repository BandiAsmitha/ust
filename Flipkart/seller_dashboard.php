<?php
// Start session and check if the seller is logged in
session_start();
if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit();
}

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Flipkart";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Retrieve seller information using session ID
$seller_id = $_SESSION['seller_id'];
$stmt = $conn->prepare("SELECT Sellername, PhoneNumber FROM seller_login WHERE SellerID = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $seller = $result->fetch_assoc();
    $seller_name = $seller['Sellername'];
    $seller_phone = $seller['PhoneNumber'];
} else {
    // Invalid session - seller ID not found in database
    session_destroy();
    header("Location: seller_login.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Seller Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #eef2f5;
        margin: 0;
        padding: 0;
    }
    .dashboard-container {
        max-width: 700px;
        margin: 50px auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }
    .info {
        font-size: 16px;
        margin-bottom: 20px;
        color: #444;
    }
    .info span {
        font-weight: bold;
    }
    .actions a {
        display: block;
        padding: 12px 20px;
        margin: 10px 0;
        background-color: #1e90ff;
        color: white;
        text-align: center;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    .actions a:hover {
        background-color: #0073e6;
    }
  </style>
</head>
<body>

<div class="dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($seller_name); ?> 👋</h2>

    <div class="info">
        <p><span>Phone:</span> <?php echo htmlspecialchars($seller_phone); ?></p>
    </div>

    <div class="actions">
        <a href="add_product.php">➕ Add New Product</a>
        <a href="view_products.php">📦 View My Products</a>
        <a href="manage_orders.php">📬 Manage Orders</a>
        <a href="seller_logout.php">🚪 Logout</a>
    </div>
</div>

</body>
</html>
