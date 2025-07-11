<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the mobile session exists
if (!isset($_SESSION['mobile'])) {
    die("Please log in first.");
}

$mobile = $_SESSION['mobile'];  // Get the mobile number from session
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null; // Get the product ID
$action = isset($_POST['action']) ? $_POST['action'] : null; // Get action (add/remove)

// Validate the inputs
if (empty($product_id) || empty($action)) {
    die("Invalid request.");
}

// Connect to the Amazon database to fetch the user_id based on the mobile number
$conn = new mysqli("localhost", "root", "", "Flipkart");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user_id based on the mobile number
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    // Fetch the user id
    $user = $result->fetch_assoc();
    $user_id = $user['id'];  // The user_id to be used for wishlist operations
} else {
    die("No user found with this mobile number.");
}

// Perform action based on the requested action (add/remove)
if ($action === 'add') {
    // Check if product is already in the wishlist
    $check = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        // Add product to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        echo "added";
    } else {
        echo "exists";
    }
} elseif ($action === 'remove') {
    // Remove product from wishlist
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    echo "removed";
} else {
    echo "Invalid action.";
}

$conn->close();
?>
