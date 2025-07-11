<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in again.");
}

// Validate order_id from GET
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = intval($_GET['order_id']);
$mobile = $_SESSION['mobile'];

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Amazon";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id
$sql_user = "SELECT id FROM login WHERE phone_number = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $mobile);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("User not found.");
}
$user_id = $result_user->fetch_assoc()['id'];

// Get order details (including status timestamps)
$sql = "SELECT o.*, p.product_name, p.image_path, 
               o.approved_at, o.shipped_at, o.out_for_delivery_at, o.delivered_at, o.cancelled_at
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.id = ? AND o.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
$status = $order['status'];

// Add cancellation timestamp
$status_timestamps = [
    'Approved' => $order['approved_at'],
    'Shipped' => $order['shipped_at'],
    'Out for Delivery' => $order['out_for_delivery_at'],
    'Delivered' => $order['delivered_at'],
    'Cancelled' => $order['cancelled_at'] ?? null  // Make sure this column exists
];

// Define the steps dynamically
$steps = ['Placed', 'Approved', 'Shipped', 'Out for Delivery', 'Delivered'];
if ($status === 'Cancelled') {
    $steps = ['Placed', 'Cancelled']; // Cancelled early
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Track Order</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color:rgb(53, 90, 126);
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #131921;
      color: white;
      padding: 20px;
      text-align: center;
    }

    .container {
      margin: 30px auto;
      padding: 20px;
      max-width: 600px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    h2 {
      color: #1e90ff;
    }

    .order-info {
      margin-top: 20px;
    }

    .order-info p {
      margin: 8px 0;
    }

    img.product-img {
      width: 100%;
      max-height: 250px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .tracking-steps {
      list-style: none;
      padding: 0;
      margin-top: 20px;
      border-left: 3px solid #ccc;
    }

    .tracking-steps li {
      padding: 10px 15px;
      position: relative;
      margin-left: 20px;
      margin-bottom: 15px;
      background-color: #f9f9f9;
      border-radius: 5px;
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

    .tracking-steps li.active::before {
      background-color: #1e90ff;
    }

    .tracking-steps li.active {
      border-left: 5px solid #1e90ff;
      background-color: #e6f2ff;
    }

    .back-link {
      display: inline-block;
      margin-top: 20px;
      background-color: #1e90ff;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
    }

    .back-link:hover {
      background-color: #63b3ed;
    }

    .timestamp {
      display: block;
      font-size: 0.85em;
      color: #555;
      margin-top: 4px;
    }
  </style>
</head>
<body>

<header>
  <h1>Track Your Order</h1>
</header>

<div class="container">
  <h2><?php echo htmlspecialchars($order['product_name']); ?></h2>
  <img src="<?php echo htmlspecialchars($order['image_path']); ?>" alt="Product Image" class="product-img">
  
  <div class="order-info">
    <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
    <p><strong>Order Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></p>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>

    <h3>Tracking Progress</h3>
    <ul class="tracking-steps">
      <?php
      foreach ($steps as $step) {
          $isActive = array_search($step, $steps) <= array_search($status, $steps) ? 'active' : '';
          $timestamp = '';

          if ($step === 'Placed') {
              $timestamp = "<span class='timestamp'>" . date("F j, Y, g:i a", strtotime($order['order_date'])) . "</span>";
          } elseif (isset($status_timestamps[$step]) && $status_timestamps[$step]) {
              $timestamp = "<span class='timestamp'>" . date("F j, Y, g:i a", strtotime($status_timestamps[$step])) . "</span>";
          }

          echo "<li class='$isActive'><strong>$step</strong>$timestamp</li>";
      }
      ?>
    </ul>
  </div>

  <a href="orders.php" class="back-link">← Back to Orders</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
