<?php
// Enable error reporting (for development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and check login
session_start();
if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in.");
}

$phone_number = $_SESSION['mobile'];
$product_id = $_POST['product_id'] ?? null;

// Validate product_id
if ($product_id === null || !is_numeric($product_id)) {
    die("Invalid or missing product ID.");
}
$product_id = (int)$product_id;

// Connect to the database
$conn = new mysqli("localhost", "root", "", "AMAZON");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id from phone_number
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $phone_number);
$stmt->execute();
$stmt->bind_result($user_id);
if (!$stmt->fetch()) {
    $stmt->close();
    $conn->close();
    die("User not found.");
}
$stmt->close();

// Check if the product is already in the cart
$check = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Already in cart";
} else {
    // Insert into cart
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $product_id);
    if ($insert->execute()) {
        echo "Added to cart";
    } else {
        echo "Failed to add to cart: " . $insert->error;
    }
    $insert->close();
}

$check->close();
$conn->close();
?>
