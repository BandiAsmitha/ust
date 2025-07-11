<?php
session_start();
if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in.");
}

$phone_number = $_SESSION['mobile'];
$product_id = $_POST['product_id'] ?? null;

// DB connection
$conn = new mysqli("localhost", "root", "", "Flipkart");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get user_id
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Check if product already in cart
$check = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Already in cart";
} else {
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $product_id);
    if ($insert->execute()) {
        echo "Added to cart";
    } else {
        echo "Failed to add to cart";
    }
    $insert->close();
}

$check->close();
$conn->close();
?>
