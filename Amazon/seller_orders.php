<?php
session_start();
if (!isset($_SESSION['seller_id'])) {
    die("You must be logged in as a seller to access this page.");
}

$seller_id = $_SESSION['seller_id']; // Assuming the seller's ID is stored in the session

// Database connection
$conn = new mysqli("localhost", "root", "", "AMAZON");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all orders for the seller
$query = "SELECT o.id as order_id, o.status, o.created_at, p.product_name, l.phone_number, o.address 
          FROM orders o
          JOIN products p ON o.product_id = p.id
          JOIN login l ON o.user_id = l.id
          WHERE o.seller_id = ?
          ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

// Display orders in a table
echo "<h2>Seller Order Management</h2>";
echo "<table border='1'>
        <tr>
            <th>Order ID</th>
            <th>Product Name</th>
            <th>Customer Phone Number</th>
            <th>Shipping Address</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Actions</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row['order_id'] . "</td>
            <td>" . $row['product_name'] . "</td>
            <td>" . $row['phone_number'] . "</td>
            <td>" . $row['address'] . "</td>
            <td>" . $row['status'] . "</td>
            <td>" . $row['created_at'] . "</td>
            <td><a href='update_order_status.php?order_id=" . $row['order_id'] . "'>Update Status</a></td>
          </tr>";
}

echo "</table>";

$stmt->close();
$conn->close();
?>
