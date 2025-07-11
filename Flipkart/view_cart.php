<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['mobile'])) {
    die("Please log in first.");
}

$mobile = $_SESSION['mobile'];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "Flipkart");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id from phone number
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
} else {
    die("No user found with this mobile number.");
}

// Fetch cart products
$sql = "SELECT p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Cart</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f1f1f1;
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

    .products {
      margin: 20px;
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
    }

    .product {
      background-color: white;
      padding: 20px;
      margin: 10px;
      width: 20%;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: relative;
    }

    .product img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }

    .remove-btn {
      display: inline-block;
      margin-top: 10px;
      background-color: #ff4747;
      color: white;
      padding: 5px 10px;
      border-radius: 5px;
      text-decoration: none;
    }

    .remove-btn:hover {
      background-color: #ff6363;
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
  </style>
</head>
<body>

<header>
  <h1>My Cart</h1>
</header>

<a class="back-link" href="home.php">← Back to Home</a>

<section class="products">
  <?php
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo "<div class='product'>";
      echo "<img src='" . $row['image_path'] . "' alt='" . $row['product_name'] . "'>";
      echo "<h3>" . $row['product_name'] . "</h3>";
      echo "<p>" . $row['description'] . "</p>";
      echo "<p>Price: $" . $row['price'] . "</p>";
      echo "<a href='#' class='remove-btn' data-product-id='" . $row['id'] . "'>Remove</a>";
      echo "</div>";
    }
  } else {
    echo "<p style='margin: 20px;'>Your cart is empty.</p>";
  }
  ?>
</section>

<!-- JavaScript to handle product removal -->
<script>
  document.querySelectorAll('.remove-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();

      const productId = this.getAttribute('data-product-id');

      fetch('remove_cart_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
      })
      .then(response => response.text())
      .then(data => {
        if (data === 'removed') {
          alert('Product removed from cart.');
          window.location.reload();
        } else {
          alert('Failed to remove the product.');
        }
      });
    });
  });
</script>

</body>
</html>

<?php
$conn->close();
?>
