<?php
session_start();

// Check if mobile session exists
if (!isset($_SESSION['mobile'])) {
    header("Location: user_login.php");
    exit();
}

$phone_number = $_SESSION['mobile'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Flipkart";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details using phone number
$stmt = $conn->prepare("SELECT id, username, phone_number FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $user_name = $user['username'];
    $user_phone = $user['phone_number'];
} else {
    session_destroy();
    header("Location: user_login.php");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
        font-family: Arial, sans-serif;
        
        margin: 0;
        padding: 0;
    }
    .dashboard-container {
        padding: 20px;
    }
    .welcome {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }
    .card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .info {
        margin-bottom: 10px;
    }
    .info span {
        font-weight: bold;
    }
    .action-button {
        display: block;
        width: 100%;
        margin-top: 10px;
        background-color:rgb(28, 120, 49);
        color: #fff;
        text-align: center;
        padding: 12px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    .action-button:hover {
        background-color: #218838;
    }

    @media (min-width: 600px) {
        .dashboard-container {
            max-width: 500px;
            margin: 40px auto;
        }
    }
  </style>
</head>
<body>

<div class="dashboard-container">
    <h2 class="welcome">Hi, <?php echo htmlspecialchars($user_name); ?> 👋</h2>

    <div class="card">
        <div class="info">
            <p><span>Phone:</span> <?php echo htmlspecialchars($user_phone); ?></p>
        </div>
    </div>

    <a href="home.php" class="action-button">🛍️ Browse Products</a>
    <a href="orders.php" class="action-button">📦 My Orders</a>
    <a href="transactions.php" class="action-button">⚙️ Transactions</a>
    <a href="track_refund.php" class="action-button">🔄 Track Refund</a>
    <a href="user_logout.php" class="action-button">🚪 Logout</a>
</div>

</body>
</html>
