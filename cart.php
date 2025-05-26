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

if ($conn_amazon->connect_error || $conn_flipkart->connect_error || $conn_ust->connect_error) {
    die("Connection failed.");
}

// Get user ID from Amazon
$stmt_amazon = $conn_amazon->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt_amazon->bind_param("s", $mobile);
$stmt_amazon->execute();
$res_amazon = $stmt_amazon->get_result();
$user_amazon = $res_amazon->fetch_assoc();
$user_id_amazon = isset($user_amazon['id']) ? $user_amazon['id'] : null;

// Get user ID from Flipkart
$stmt_flipkart = $conn_flipkart->prepare("SELECT id FROM login WHERE phone_number = ?");
$stmt_flipkart->bind_param("s", $mobile);
$stmt_flipkart->execute();
$res_flipkart = $stmt_flipkart->get_result();
$user_flipkart = $res_flipkart->fetch_assoc();
$user_id_flipkart = isset($user_flipkart['id']) ? $user_flipkart['id'] : null;

// Function to fetch cart items from a platform
function fetchCartItems($conn, $user_id, $platform) {
    $stmt = $conn->prepare("SELECT p.*, ? AS platform FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("si", $platform, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch cart items
$amazon_cart_items = $user_id_amazon ? fetchCartItems($conn_amazon, $user_id_amazon, 'AMAZON') : [];
$flipkart_cart_items = $user_id_flipkart ? fetchCartItems($conn_flipkart, $user_id_flipkart, 'FLIPKART') : [];

// Merge cart items
$all_cart_items = array_merge($amazon_cart_items, $flipkart_cart_items);

// Step 1: Remove products in UST cart that no longer exist
$product_ids = array_column($all_cart_items, 'id');
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    $sql = "DELETE FROM cart_items WHERE mobile = ? AND platform IN ('AMAZON', 'FLIPKART') AND product_id NOT IN ($placeholders)";
    $stmt = $conn_ust->prepare($sql);
    $params = array_merge([$mobile], $product_ids);
    $bind_types = 's' . $types;
    $bind_names[] = $bind_types;
    foreach ($params as $key => $val) {
        $bind_names[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    $stmt->execute();
}

// Step 2: Insert new products into UST cart
$created_at = date('Y-m-d H:i:s');
foreach ($all_cart_items as $row) {
    $check = $conn_ust->prepare("SELECT id FROM cart_items WHERE product_id = ? AND mobile = ? AND platform = ?");
    $check->bind_param("iss", $row['id'], $mobile, $row['platform']);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $insert = $conn_ust->prepare("INSERT INTO cart_items (product_id, product_name, description, price, category, subcategory, image_path, created_at, sizes, mobile, platform) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sizes = 'Small, Medium, Large';
        $insert->bind_param("issdsssssss",
            $row['id'], $row['product_name'], $row['description'],
            $row['price'], $row['category'], $row['subcategory'],
            $row['image_path'], $created_at, $sizes, $mobile, $row['platform']
        );
        $insert->execute();
    }
}

// Fetch from UST for display
$stmt_display = $conn_ust->prepare("SELECT * FROM cart_items WHERE mobile = ? AND platform IN ('AMAZON', 'FLIPKART') ORDER BY created_at DESC");
$stmt_display->bind_param("s", $mobile);
$stmt_display->execute();
$display_result = $stmt_display->get_result();

$conn_amazon->close();
$conn_flipkart->close();
$conn_ust->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Cart</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f1f1f1; margin: 0; padding: 0; }
    header { background-color: #131921; color: white; padding: 15px; text-align: center; }
    h1 { margin: 0; color: #1e90ff; }
    .products { display: flex; flex-wrap: wrap; justify-content: flex-start; margin: 20px; gap: 10px; }
    .product { background-color: white; padding: 20px; width: 22%; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .product img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
    .remove-btn { margin-top: 10px; background-color: #ff4747; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; display: inline-block; }
    .remove-btn:hover { background-color: #ff6363; }
    .back-link { margin: 20px; background-color: #1e90ff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block; }
  </style>
</head>
<body>

<header>
  <h1>My Cart</h1>
</header>

<a class="back-link" href="index.php">← Back to Home</a>

<section class="products">
  <?php
  if ($display_result->num_rows > 0) {
      while ($row = $display_result->fetch_assoc()) {
          // Set image base URL depending on platform
          if ($row['platform'] === 'AMAZON') {
              $image_path = "http://localhost/Amazon/" . $row['image_path'];
          } else if ($row['platform'] === 'FLIPKART') {
              $image_path = "http://localhost/Flipkart/" . $row['image_path'];
          } else {
              $image_path = htmlspecialchars($row['image_path']);
          }

          echo "<div class='product'>";
          echo "<img src='" . htmlspecialchars($image_path) . "' alt='" . htmlspecialchars($row['product_name']) . "'>";
          echo "<h3>" . htmlspecialchars($row['product_name']) . " (" . htmlspecialchars($row['platform']) . ")</h3>";
          echo "<p>" . htmlspecialchars($row['description']) . "</p>";
          echo "<p>Price: $" . $row['price'] . "</p>";
          echo "<a href='#' class='remove-btn' data-product-id='" . $row['id'] . "'>Remove</a>";
          echo "</div>";
      }
  } else {
      echo "<p style='margin: 20px;'>Your cart is empty.</p>";
  }
  ?>
</section>

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
          alert('Product removed successfully.');
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
