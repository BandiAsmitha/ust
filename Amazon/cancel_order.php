<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Amazon";

if (!isset($_SESSION['mobile']) || !isset($_POST['order_id'])) {
    die("❌ Unauthorized or missing data.");
}

$order_id = intval($_POST['order_id']);

// DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Get order info
$order_stmt = $conn->prepare("
    SELECT o.id, o.user_id, o.product_id, o.payment_method, l.username AS payer_name, p.price, s.name AS seller_name, s.phone_number AS seller_number
    FROM orders o
    JOIN login l ON o.user_id = l.id
    JOIN products p ON o.product_id = p.id
    JOIN sellers s ON o.seller_id = s.id
    WHERE o.id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$result = $order_stmt->get_result();
if ($result->num_rows === 0) {
    die("❌ Order not found.");
}
$order = $result->fetch_assoc();

// Update order status to 'Cancelled'
$cancel_stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
$cancel_stmt->bind_param("i", $order_id);
$cancel_stmt->execute();

// Prepare transaction values
$transaction_id = uniqid("TXN"); // Or use UUID
$payer_name = $order['payer_name'];
$payment_method = $order['payment_method'];
$amount = $order['price'];
$seller_name = $order['seller_name'];
$seller_number = $order['seller_number'];
$receiver_number = $_SESSION['mobile']; // Assuming refund goes to user
$transaction_date = date("Y-m-d H:i:s");
$transaction_type = "Credited";

// Insert into transactions table
$txn_stmt = $conn->prepare("
    INSERT INTO transactions (transaction_id, payer_name, payment_method, amount, seller_name, seller_number, transaction_date, transaction_type, receiver_number)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$txn_stmt->bind_param(
    "sssssssss",
    $transaction_id,
    $payer_name,
    $payment_method,
    $amount,
    $seller_name,
    $seller_number,
    $transaction_date,
    $transaction_type,
    $receiver_number
);
$txn_stmt->execute();

$conn->close();
?>

<!-- Simple Success HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Cancelled</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; text-align: center; padding: 100px; background: #f8f9fa; }
        .box { background: white; padding: 40px; margin: auto; max-width: 500px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { color: #dc3545; }
        a { display: inline-block; margin-top: 20px; background: #6c757d; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        a:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="box">
        <h2>❌ Order Cancelled</h2>
        <p>Your order has been cancelled. ₹<?php echo htmlspecialchars($amount); ?> has been credited back to your account.</p>
        <a href="orders.php">Back to Orders</a>
    </div>
</body>
</html>
