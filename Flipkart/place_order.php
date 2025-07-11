<?php
session_start();

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Flipkart";

// Check if the user is logged in via mobile number
if (!isset($_SESSION['mobile'])) {
    die("❌ Session expired. Please <a href='login.php'>log in</a> again.");
}

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Fetch user ID using mobile number if not stored
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $mobile = $_SESSION['mobile'];
    $user_stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
    $user_stmt->bind_param("s", $mobile);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows === 0) {
        die("❌ User not found. Please log in again.");
    }
    $user_id = $user_result->fetch_assoc()['id'];
    $_SESSION['user_id'] = $user_id; // Save it for future use
}

// Retrieve session and post data
$product_id = $_SESSION['product_id'] ?? null;
$address = $_SESSION['address'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;

if (!$product_id) {
    die("❌ Product not selected.");
}
if (!is_array($address) || empty($address['full_name']) || empty($address['address']) || empty($address['city']) || empty($address['pincode']) || empty($address['phone'])) {
    die("❌ Address is incomplete or not set properly.");
}
if (!$payment_method) {
    die("❌ Payment method is missing.");
}

// Get seller_id from product
$product_stmt = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
if ($product_result->num_rows === 0) {
    die("❌ Product not found.");
}
$seller_id = $product_result->fetch_assoc()['seller_id'];

// Insert address
$addr_stmt = $conn->prepare("INSERT INTO addresses (user_id, full_name, address, city, pincode, phone) VALUES (?, ?, ?, ?, ?, ?)");
$addr_stmt->bind_param("isssss", $user_id, $address['full_name'], $address['address'], $address['city'], $address['pincode'], $address['phone']);
$addr_stmt->execute();
$address_id = $conn->insert_id;

// Insert order
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, seller_id, address_id, payment_method) VALUES (?, ?, ?, ?, ?)");
$order_stmt->bind_param("iiiis", $user_id, $product_id, $seller_id, $address_id, $payment_method);
$order_stmt->execute();
$order_id = $conn->insert_id;

// Insert payment
$payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status) VALUES (?, ?, 'Pending')");
$payment_stmt->bind_param("is", $order_id, $payment_method);
$payment_stmt->execute();

// Clear session data (optional)
unset($_SESSION['product_id']);
unset($_SESSION['address']);

$conn->close();
?>

<!-- 🎉 Success UI -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 100px 20px;
        }
        .box {
            background: white;
            padding: 40px;
            margin: auto;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #28a745;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            background-color: #1e90ff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        a:hover {
            background-color: #187bcd;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>✅ Order Placed Successfully!</h2>
        <p>Thank you for shopping with us. Your order ID is <strong>#<?php echo $order_id; ?></strong>.</p>
        <a href="orders.php">View My Orders</a>
        <a href="home.php" style="margin-left: 10px;">Continue Shopping</a>
    </div>
</body>
</html>
