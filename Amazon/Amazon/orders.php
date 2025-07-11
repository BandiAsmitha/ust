<?php
session_start();
$user_id = 1; // Replace with $_SESSION['user_id'] when login is active

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "AMAZON";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT o.id AS order_id, o.quantity, o.order_date, 
               p.product_name, p.description, p.image_path, p.price
        FROM orders o
        JOIN products p ON o.product_id = p.id
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
      justify-content: space-between;
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

    .order h3 {
      margin: 10px 0 5px;
    }

    .back-link {
      margin: 20px;
      display: inline-block;
      background-color: #1e90ff;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
    }

    .back-link:hover {
      background-color: #63b3ed;
    }

    @media (max-width: 768px) {
      .order {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>My Orders</h1>
</header>

<a class="back-link" href="home.php">← Back to Home</a>

<section class="orders">
  <?php
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo "<div class='order'>";
      echo "<img src='" . $row['image_path'] . "' alt='" . $row['product_name'] . "'>";
      echo "<h3>" . $row['product_name'] . "</h3>";
      echo "<p>" . $row['description'] . "</p>";
      echo "<p><strong>Quantity:</strong> " . $row['quantity'] . "</p>";
      echo "<p><strong>Price:</strong> $" . $row['price'] . "</p>";
      echo "<p><strong>Ordered on:</strong> " . date("F j, Y", strtotime($row['order_date'])) . "</p>";
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
