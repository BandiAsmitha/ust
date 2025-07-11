<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Amazon";

if (!isset($_SESSION['mobile'])) {
    die("❌ Unauthorized access.");
}

$user_mobile = $_SESSION['mobile'];

// DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Fetch credited transactions for this user
$stmt = $conn->prepare("
    SELECT transaction_id, user_name, payment_method, amount, seller_name, seller_number, transaction_date
    FROM transactions
    WHERE user_number = ? AND transaction_type = 'Credited'
    ORDER BY transaction_date DESC
");
$stmt->bind_param("s", $user_mobile);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Refunded Transactions</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color:rgb(53, 90, 126);; padding: 50px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
        th { background-color: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h2>💰Refunded Transactions</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Transaction ID</th>
                    <th>Payer Name</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Seller Name</th>
                    <th>Seller Number</th>
                    <th>Date</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['payment_method']) ?></td>
                        <td>₹<?= htmlspecialchars($row['amount']) ?></td>
                        <td><?= htmlspecialchars($row['seller_name']) ?></td>
                        <td><?= htmlspecialchars($row['seller_number']) ?></td>
                        <td><?= htmlspecialchars($row['transaction_date']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="text-align:center;">No credited transactions found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
