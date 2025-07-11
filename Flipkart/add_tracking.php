<?php
session_start();

// Optional: Add admin check here if needed

if (!isset($_GET['order_id'])) {
    die("Order ID missing.");
}

$order_id = intval($_GET['order_id']);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Flipkart";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status']);
    $location = trim($_POST['location']);

    if (empty($status) || empty($location)) {
        $error = "Please enter both status and location.";
    } else {
        $sql = "INSERT INTO tracking (order_id, status, location) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $order_id, $status, $location);

        if ($stmt->execute()) {
            $success = "Tracking update added successfully.";
        } else {
            $error = "Failed to insert tracking data.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Tracking Update</title>
  <style>
    body { font-family: Arial; background-color: #f1f1f1; padding: 30px; }
    form {
      background: white;
      padding: 20px;
      border-radius: 8px;
      max-width: 500px;
      margin: auto;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    label {
      display: block;
      margin-bottom: 5px;
      margin-top: 15px;
    }
    input[type="text"], select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    input[type="submit"] {
      margin-top: 20px;
      background-color: #1e90ff;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .message {
      text-align: center;
      margin-top: 15px;
      font-weight: bold;
    }
    .success { color: green; }
    .error { color: red; }
    a.back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      text-decoration: none;
      color: #1e90ff;
    }
  </style>
</head>
<body>

<h2 style="text-align: center;">Add Tracking Update for Order #<?= $order_id ?></h2>

<form method="post" action="">
  <label for="status">Status:</label>
  <select name="status" id="status">
    <option value="">-- Select Status --</option>
    <option value="Order Placed">Order Placed</option>
    <option value="Shipped">Shipped</option>
    <option value="In Transit">In Transit</option>
    <option value="Out for Delivery">Out for Delivery</option>
    <option value="Delivered">Delivered</option>
  </select>

  <label for="location">Location:</label>
  <input type="text" name="location" id="location" placeholder="e.g. Delhi Hub">

  <input type="submit" value="Add Tracking Update">

  <?php if ($success): ?>
    <p class="message success"><?= $success ?></p>
  <?php elseif ($error): ?>
    <p class="message error"><?= $error ?></p>
  <?php endif; ?>
</form>

<a class="back-link" href="track_order.php?order_id=<?= $order_id ?>">← Back to Tracking Page</a>

</body>
</html>

<?php $conn->close(); ?>
