<?php
session_start();
if (!isset($_SESSION['mobile'])) {
    die("Session expired.");
}

$mobile = $_SESSION['mobile'];
$product_id = $_POST['product_id'] ?? null;

if ($product_id === null || !is_numeric($product_id)) {
    die("Invalid product ID.");
}

$conn = new mysqli("localhost", "root", "", "Flipkart");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Delete from cart
$delete = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$delete->bind_param("ii", $user_id, $product_id);

if ($delete->execute()) {
    echo "removed";
} else {
    echo "error";
}

$delete->close();
$conn->close();
?>
