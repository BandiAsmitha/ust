<?php
session_start();

// Ensure the user is logged in by checking if mobile is set in session
if (!isset($_SESSION['mobile'])) {
    die("Please log in first.");
}

// Get the mobile number from session
$mobile = $_SESSION['mobile'];

// Connect to the database for Amazon
$conn = new mysqli("localhost", "root", "", "Flipkart");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id based on the mobile number
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

// Get the product_id to remove
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

if ($product_id) {
    // Remove the product from the wishlist
    $delete_query = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "removed"; // Successfully removed
    } else {
        echo "failed"; // Failed to remove
    }
} else {
    echo "invalid"; // Invalid product ID
}

$conn->close();
?>
