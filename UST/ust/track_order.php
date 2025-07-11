<?php
session_start();

if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in.");
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);
$mobile = $_SESSION['mobile'];

$conn = new mysqli("localhost", "root", "", "UST");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM order_items WHERE id = ? AND mobile = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $order_id, $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
$status = $order['status'];

$status_timestamps = [
    'Approved' => $order['approved_at'],
    'Shipped' => $order['shipped_at'],
    'Out for Delivery' => $order['out_for_delivery_at'],
    'Delivered' => $order['delivered_at'],
    'Cancelled' => $order['cancelled_at']
];

$steps = ['Placed', 'Approved', 'Shipped', 'Out for Delivery', 'Delivered'];
if ($status === 'Cancelled') {
    $steps = ['Placed', 'Cancelled'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Track Your Order</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #eef2f3;
    }
    header {
      background-color: #0d6efd;
      color: white;
      padding: 20px;
      text-align: center;
    }
    .container {
      max-width: 600px;
      margin: 30px auto;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .product-img {
      width: 100%;
      max-height: 250px;
      object-fit: cover;
      border-radius: 8px;
    }
    .tracking-steps {
      list-style: none;
      padding: 0;
      border-left: 3px solid #ccc;
      margin-top: 20px;
    }
    .tracking-steps li {
      padding: 10px 15px;
      margin-left: 20px;
      margin-bottom: 15px;
      background-color: #f9f9f9;
      border-radius: 5px;
      position: relative;
    }
    .tracking-steps li::before {
      content: '';
      width: 12px;
      height: 12px;
      background-color: #ccc;
      border-radius: 50%;
      position: absolute;
      left: -27px;
      top: 18px;
    }
    .tracking-steps li.active {
      background-color: #e6f2ff;
      border-left: 5px solid #0d6efd;
    }
    .tracking-steps li.active::before {
      background-color: #0d6efd;
    }
    .timestamp {
      font-size: 0.85em;
      color: #555;
      display: block;
      margin-top: 4px;
    }
    .back-link {
      margin-top: 20px;
      display: inline-block;
      background-color: #0d6efd;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
    }
    .back-link:hover {
      background-color: #5aafff;
    }
  </style>
</head>
<body>

<header>
  <h1>Track Your Order</h1>
</header>

<div class="container">
  <h2><?= htmlspecialchars($order['product_name']) ?></h2>
  <img src="http://localhost/<?= $order['platform'] ?>/<?= htmlspecialchars($order['image_path']) ?>" class="product-img" alt="Product">

  <p><strong>Order ID:</strong> <?= $order['id'] ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
  <p><strong>Order Date:</strong> <?= date("F j, Y, g:i a", strtotime($order['order_date'])) ?></p>
  <p><strong>Platform:</strong> <?= $order['platform'] ?></p>

  <h3>Tracking Progress</h3>
  <ul class="tracking-steps">
    <?php
    foreach ($steps as $step) {
        $isActive = array_search($step, $steps) <= array_search($status, $steps) ? 'active' : '';
        $timestamp = '';

        if ($step === 'Placed') {
            $timestamp = "<span class='timestamp'>" . date("F j, Y, g:i a", strtotime($order['order_date'])) . "</span>";
        } elseif (!empty($status_timestamps[$step])) {
            $timestamp = "<span class='timestamp'>" . date("F j, Y, g:i a", strtotime($status_timestamps[$step])) . "</span>";
        }

        echo "<li class='$isActive'><strong>$step</strong>$timestamp</li>";
    }
    ?>
  </ul>

  <a href="orders.php" class="back-link">← Back to Orders</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
