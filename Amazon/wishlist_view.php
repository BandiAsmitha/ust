<?php
session_start();

// Ensure the user is logged in by checking if mobile is set in session
if (!isset($_SESSION['mobile'])) {
    die("Please log in first.");
}

// Get the mobile number from session
$mobile = $_SESSION['mobile'];

// Connect to the database for Amazon
$conn = new mysqli("localhost", "root", "", "AMAZON");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user_id based on the mobile number
$stmt = $conn->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    // Fetch the user id
    $user = $result->fetch_assoc();
    $user_id = $user['id'];  // The user_id to be used for wishlist operations
} else {
    die("No user found with this mobile number.");
}

// Get wishlisted products for this user
$sql = "SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Wishlist</title>
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
      justify-content: space-between;
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
  <h1>My Wishlist</h1>
</header>

<a class="back-link" href="home.php">← Back to Home</a>

<section class="products">
  <?php
  // Display the products in the wishlist if there are any
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
    // If no products are in the wishlist
    echo "<p style='margin: 20px;'>You have no items in your wishlist.</p>";
  }
  ?>
</section>

<!-- JavaScript to handle product removal -->
<script>
  document.querySelectorAll('.remove-btn').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      
      const productId = this.getAttribute('data-product-id');
      
      // Send a request to remove the product from the wishlist
      fetch('remove_wishlist_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
      })
      .then(response => response.text())
      .then(data => {
        if (data === 'removed') {
          alert('Product removed successfully.');
          window.location.reload(); // Reload the page to reflect changes
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
// Close the connection
$conn->close();
?>
