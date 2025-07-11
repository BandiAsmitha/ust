<?php
session_start();

// Check if mobile is stored in session
if (!isset($_SESSION['mobile'])) {
    die("You must be logged in to view transactions.");
}

$mobile = $_SESSION['mobile'];

// DB connection setup
$host = 'localhost';
$db = 'Amazon';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Retrieve transactions where user is the receiver
$sql = "SELECT * FROM transactions WHERE user_number = :mobile ORDER BY transaction_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':mobile' => $mobile]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Received Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(53, 90, 126);
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007185;
            color: white;
        }
        tr:hover {
            background-color: #f1f9ff;
        }
    </style>
</head>
<body>

<h2>Transactions You've Received</h2>

<?php if (count($transactions) === 0): ?>
    <p style="text-align: center;">No incoming transactions found.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>User Name</th>
            <th>User Number</th>
            <th>Payment Method</th>
            <th>Transaction Type</th>
            <th>Seller_name</th>   
            <th> Seller_number</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
        <?php foreach ($transactions as $tx): ?>
            <tr>
                <td><?= htmlspecialchars($tx['transaction_id']) ?></td>
                <td><?= htmlspecialchars($tx['user_name']) ?></td>
                <td><?= htmlspecialchars($tx['user_number']) ?></td>
                <td><?= htmlspecialchars($tx['payment_method']) ?></td>
                <td><?= htmlspecialchars($tx['transaction_type']) ?></td>
                <td><?= htmlspecialchars($tx['seller_name']) ?></td>
                <td><?= htmlspecialchars($tx['seller_number']) ?></td>
                <td>$<?= htmlspecialchars($tx['amount']) ?></td>
                <td><?= htmlspecialchars($tx['transaction_date']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>
