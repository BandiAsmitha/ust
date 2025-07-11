<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); 
if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Flipkart";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$seller_id = $_SESSION['seller_id'];

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $timestamp = date("Y-m-d H:i:s"); // Get the current timestamp

    // Prepare the SQL statement to update the status and the corresponding timestamp
    $update_stmt = $conn->prepare("
        UPDATE orders 
        JOIN products ON orders.product_id = products.id
        SET orders.status = ?,
            orders.approved_at = CASE WHEN ? = 'Approved' THEN ? ELSE orders.approved_at END,
            orders.shipped_at = CASE WHEN ? = 'Shipped' THEN ? ELSE orders.shipped_at END,
            orders.out_for_delivery_at = CASE WHEN ? = 'Out for Delivery' THEN ? ELSE orders.out_for_delivery_at END,
            orders.delivered_at = CASE WHEN ? = 'Delivered' THEN ? ELSE orders.delivered_at END
        WHERE orders.id = ? AND products.seller_id = ?");
    
    // Bind the parameters correctly
    $update_stmt->bind_param(
        "sssssssssii",  // type definition string (types of the parameters)
        $new_status, $new_status, $timestamp,  // For the approved_at timestamp
        $new_status, $timestamp,               // For the shipped_at timestamp
        $new_status, $timestamp,               // For the out_for_delivery_at timestamp
        $new_status, $timestamp,               // For the delivered_at timestamp
        $order_id, $seller_id                  // For the order_id and seller_id
    );

    // Execute the statement
    $update_stmt->execute();
    $update_stmt->close();
}

// Fetch orders for this seller
$sql = "
    SELECT orders.id AS order_id, orders.product_id, orders.quantity, orders.order_date, 
           orders.status, products.product_name AS product_name
    FROM orders
    JOIN products ON orders.product_id = products.id
    WHERE products.seller_id = ?
    ORDER BY orders.order_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f7f9;
        padding: 20px;
    }
    .order-container {
        max-width: 900px;
        margin: auto;
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    th {
        background-color: #0073e6;
        color: white;
    }
    select, button {
        padding: 6px 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    button {
        background-color: #1e90ff;
        color: white;
        cursor: pointer;
        transition: 0.2s;
    }
    button:hover {
        background-color: #006ad1;
    }
  </style>
</head>
<body>

<div class="order-container">
    <h2>📬 Manage Orders</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                <td>
                    <form method="POST" style="display:flex; gap:5px;">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <select name="new_status">
                            <option value="Pending" <?php if ($row['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Approved" <?php if ($row['status'] === 'Approved') echo 'selected'; ?>>Approved</option>
                            <option value="Processed" <?php if ($row['status'] === 'Processed') echo 'selected'; ?>>Processed</option>
                            <option value="Shipped" <?php if ($row['status'] === 'Shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="Out for Delivery" <?php if ($row['status'] === 'Out for Delivery') echo 'selected'; ?>>Out for Delivery</option>
                            <option value="Delivered" <?php if ($row['status'] === 'Delivered') echo 'selected'; ?>>Delivered</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
