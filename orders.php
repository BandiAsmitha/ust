<?php
session_start();

if (!isset($_SESSION['mobile'])) {
    die("Please log in first.");
}

$mobile = $_SESSION['mobile'];

// Connect to databases
$conn_amazon = new mysqli("localhost", "root", "", "AMAZON");
$conn_flipkart = new mysqli("localhost", "root", "", "FLIPKART");
$conn_ust = new mysqli("localhost", "root", "", "UST");

// Check connections
if ($conn_amazon->connect_error) die("Connection to AMAZON failed: " . $conn_amazon->connect_error);
if ($conn_flipkart->connect_error) die("Connection to FLIPKART failed: " . $conn_flipkart->connect_error);
if ($conn_ust->connect_error) die("Connection to UST failed: " . $conn_ust->connect_error);

// Get user ID from Amazon login
function getUserId($conn, $mobile) {
    $stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
    $stmt->bind_param("s", $mobile);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['id'] : null;
}

$amazon_user_id = getUserId($conn_amazon, $mobile);
$flipkart_user_id = getUserId($conn_flipkart, $mobile);

// Fetch orders from Amazon
function fetchOrders($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT p.*, o.order_date, o.status, o.approved_at, o.shipped_at,
               o.out_for_delivery_at, o.delivered_at, o.cancelled_at
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$amazon_orders = ($amazon_user_id !== null) ? fetchOrders($conn_amazon, $amazon_user_id) : [];
$flipkart_orders = ($flipkart_user_id !== null) ? fetchOrders($conn_flipkart, $flipkart_user_id) : [];

// Sync to UST
$platforms = ['AMAZON' => $amazon_orders, 'FLIPKART' => $flipkart_orders];

foreach ($platforms as $platform => $orders) {
    foreach ($orders as $row) {
        $product_id = $row['id'];
        $product_name = $row['product_name'];
        $description = $row['description'];
        $price = $row['price'];
        $category = $row['category'];
        $subcategory = $row['subcategory'];
        $image_path = $row['image_path'];
        $sizes = 'Small, Medium, Large';
        $order_date = $row['order_date'];
        $status = $row['status'] ?? 'Placed';
        $approved_at = $row['approved_at'] ?? null;
        $shipped_at = $row['shipped_at'] ?? null;
        $out_for_delivery_at = $row['out_for_delivery_at'] ?? null;
        $delivered_at = $row['delivered_at'] ?? null;
        $cancelled_at = $row['cancelled_at'] ?? null;

        // Check if already synced
        $check_stmt = $conn_ust->prepare("
            SELECT id FROM order_items 
            WHERE product_id = ? AND mobile = ? AND platform = ?");
        $check_stmt->bind_param("iss", $product_id, $mobile, $platform);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $stmt_ust = $conn_ust->prepare("
                INSERT INTO order_items (
                    product_id, product_name, description, price, category, subcategory,
                    image_path, order_date, sizes, mobile, platform,
                    status, approved_at, shipped_at, out_for_delivery_at, delivered_at, cancelled_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt_ust->bind_param(
                "issdsssssssssssss",
                $product_id,
                $product_name,
                $description,
                $price,
                $category,
                $subcategory,
                $image_path,
                $order_date,
                $sizes,
                $mobile,
                $platform,
                $status,
                $approved_at,
                $shipped_at,
                $out_for_delivery_at,
                $delivered_at,
                $cancelled_at
            );

            $stmt_ust->execute();
        }
    }
}

// Display orders
$stmt_display = $conn_ust->prepare("
    SELECT * FROM order_items 
    WHERE mobile = ? AND platform IN ('AMAZON', 'FLIPKART') 
    ORDER BY order_date DESC");
$stmt_display->bind_param("s", $mobile);
$stmt_display->execute();
$display_result = $stmt_display->get_result();

// Close DB connections
$conn_amazon->close();
$conn_flipkart->close();
$conn_ust->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f1f1f1;
      margin: 0;
      padding: 0;
    }
    header {
      background-color: #131921;
      color: white;
      padding: 15px;
      text-align: center;
    }
    h1 {
      margin: 0;
      color: #1e90ff;
    }
    .orders {
      margin: 20px;
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      gap: 40px;
    }
    .order {
      background-color: white;
      padding: 20px;
      margin: 10px;
      width: 22%;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .order img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .back-link {
      display: inline-block;
      margin: 20px;
      background-color: #1e90ff;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
    }
    .back-link:hover {
      background-color: #63b3ed;
    }
    .track-link {
      display: inline-block;
      margin-top: 10px;
      background-color: #28a745;
      color: white;
      padding: 8px 12px;
      border-radius: 5px;
      text-decoration: none;
    }
    .track-link:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

<header>
  <h1>My Orders</h1>
</header>

<a class="back-link" href="index.php">← Back to Home</a>

<section class="orders">
  <?php
  if ($display_result->num_rows > 0) {
    while ($row = $display_result->fetch_assoc()) {
      $image_path = "http://localhost/" . ($row['platform'] === 'AMAZON' ? 'Amazon' : 'Flipkart') . "/" . $row['image_path'];
      echo "<div class='order'>";
      echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($row['product_name']) . "'>";
      echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
      echo "<p>" . htmlspecialchars($row['description']) . "</p>";
      echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
      echo "<p><strong>Order Date:</strong> " . $row['order_date'] . "</p>";
      echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
      echo "<p><strong>Platform:</strong> " . $row['platform'] . "</p>";
      echo "<a href='track_order.php?order_id=" . $row['id'] . "' class='track-link'>Track Order</a>";
      echo "</div>";
    }
  } else {
    echo "<p style='margin: 20px;'>You have no orders.</p>";
  }
  ?>
</section>

</body>
</html>
