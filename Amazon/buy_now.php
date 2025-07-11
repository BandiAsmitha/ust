<?php
session_start();

// Check if the user is logged in and product details are present
if (!isset($_SESSION['mobile']) || !isset($_SESSION['buy_now_product']) || !isset($_SESSION['shipping_address'])) {
    die("Incomplete order details.");
}

// Get user information and product ID
$phone_number = $_SESSION['mobile'];
$product_id = $_POST['product_id'] ?? null;
$shipping_address = $_SESSION['shipping_address'];

// DB connection
$conn = new mysqli("localhost", "root", "", "AMAZON");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Fetch user_id and seller_id by joining the login and products table
$stmt = $conn->prepare("
    SELECT u.id, p.seller_id 
    FROM login u 
    JOIN products p ON p.id = ? 
    WHERE u.phone_number = ?
");
$stmt->bind_param("is", $product_id, $phone_number);
$stmt->execute();
$stmt->bind_result($user_id, $seller_id);
$stmt->fetch();
$stmt->close();

// Ensure that user and product exist
if (!$user_id || !$seller_id) {
    die("Invalid user or product.");
}

// Insert the order with seller_id and shipping address
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, seller_id, address, status) VALUES (?, ?, ?, ?, ?)");
$order_stmt->bind_param("iiiss", $user_id, $product_id, $seller_id, $shipping_address, $status = "Placed");
if ($order_stmt->execute()) {
    echo "<script>alert('Order placed successfully!'); window.location.href='orders.php';</script>";
} else {
    echo "<script>alert('Failed to place order.'); window.history.back();</script>";
}

$order_stmt->close();
$conn->close();
?>
