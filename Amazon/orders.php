<?php
session_start();

// Validate session
if (!isset($_SESSION['mobile'])) {
    die("Session expired. Please log in again.");
}

$mobile = $_SESSION['mobile'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Amazon";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user ID
$sql_user = "SELECT id FROM login WHERE phone_number = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $mobile);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("User not found.");
}

$user_row = $result_user->fetch_assoc();
$user_id = $user_row['id'];

// Fetch orders
$sql = "SELECT o.id AS order_id, o.order_date, o.payment_method, o.status, o.cancellation_reason,
               p.product_name, p.description, p.image_path, p.price,
               s.name AS seller_name
        FROM orders o
        JOIN products p ON o.product_id = p.id
        JOIN sellers s ON p.seller_id = s.id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    header {
      background-color: #131921;
      color: white;
      padding: 20px;
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
      gap: 30px;
    }
    .order {
      background-color: white;
      width: 30%;
      padding: 15px;
      margin: 15px 0;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .order img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .status {
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
      display: inline-block;
    }
    .status.Pending { background-color: orange; color: white; }
    .status.Shipped { background-color: dodgerblue; color: white; }
    .status.Delivered { background-color: green; color: white; }
    .status.Cancelled { background-color: gray; color: white; }
    .back-link {
      margin: 20px;
      display: inline-block;
      background-color: #1e90ff;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
    }
    .track-link, .cancel-button {
      margin-top: 10px;
      padding: 8px 12px;
      background-color: #ffa500;
      color: white;
      border: none;
      text-decoration: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .cancel-button {
      background-color: red;
    }
    .cancel-button:hover {
      background-color: darkred;
    }
    .track-link:hover {
      background-color: #ff8c00;
    }
    textarea {
      width: 100%;
      margin-top: 10px;
      padding: 5px;
      resize: vertical;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    @media (max-width: 768px) {
      .order {
        width: 100%;
      }
    }
  </style>
  <script>
    function confirmCancel(form) {
      const reason = form.reason.value.trim();
      if (!reason) {
        alert("Please provide a cancellation reason.");
        return false;
      }
      return confirm("Are you sure you want to cancel this order?");
    }
  </script>
</head>
<body>

<header>
  <h1>My Orders</h1>
</header>

<a class="back-link" href="home.php">← Back to Home</a>

<?php
if (isset($_GET['cancelled']) && $_GET['cancelled'] === 'success') {
    echo "<p style='color: green; text-align: center;'>Order cancelled successfully.</p>";
}
?>

<section class="orders">
  <?php
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo "<div class='order'>";
      echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['product_name']) . "'>";
      echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
      echo "<p>" . htmlspecialchars($row['description']) . "</p>";
      echo "<p><strong>Price:</strong> $" . $row['price'] . "</p>";
      echo "<p><strong>Seller:</strong> " . htmlspecialchars($row['seller_name']) . "</p>";
      echo "<p><strong>Payment:</strong> " . htmlspecialchars($row['payment_method']) . "</p>";
      echo "<p><strong>Date:</strong> " . date("F j, Y", strtotime($row['order_date'])) . "</p>";
      echo "<p><strong>Status:</strong> <span class='status " . $row['status'] . "'>" . $row['status'] . "</span></p>";
      echo "<a href='track_order.php?order_id=" . $row['order_id'] . "' class='track-link'>Track Order</a>";

      if ($row['status'] === 'Pending') {
        echo "<form method='POST' action='cancel_order.php' onsubmit='return confirmCancel(this);'>";
        echo "<input type='hidden' name='order_id' value='" . $row['order_id'] . "'>";
        echo "<textarea name='reason' placeholder='Reason for cancellation' required></textarea>";
        echo "<button type='submit' class='cancel-button'>Cancel Order</button>";
        echo "</form>";
      }

      if ($row['status'] === 'Cancelled' && !empty($row['cancellation_reason'])) {
        echo "<p><strong>Cancellation Reason:</strong> " . htmlspecialchars($row['cancellation_reason']) . "</p>";
      }

      echo "</div>";
    }
  } else {
    echo "<p style='margin: 20px;'>You have no orders yet.</p>";
  }
  ?>
</section>

</body>
</html>

<?php $conn->close(); ?>
