<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "AMAZON";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$subcategory = '';

// Subcategory logic
$fashion_subs = ['Women', 'Men', 'Kids', 'Girls', 'Boys'];
$home_subs = ['Kitchen', 'Hall', 'Bedroom', 'Decorating Items'];
$electronics_subs = ['Mobile', 'Laptop', 'Tab', 'Smart Watches', 'TV', 'Refrigerator'];

if (in_array($category, $fashion_subs)) {
  $subcategory = $category;
  $category = 'Fashion';
} elseif (in_array($category, $home_subs)) {
  $subcategory = $category;
  $category = 'Home';
} elseif (in_array($category, $electronics_subs)) {
  $subcategory = $category;
  $category = 'Electronics';
}

if ($subcategory) {
  $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND subcategory = ?");
  $stmt->bind_param("ss", $category, $subcategory);
} else {
  $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
  $stmt->bind_param("s", $category);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($category . ($subcategory ? " - $subcategory" : '')) ?> Products</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 20px; }
    .product { background: white; padding: 15px; margin: 10px auto; width: 300px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    img { max-width: 100%; border-radius: 6px; }
  </style>
</head>
<body>
<h2>Products in <?= htmlspecialchars($category) ?> <?= $subcategory ? "- " . htmlspecialchars($subcategory) : "" ?></h2>
<?php
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo "<div class='product'>";
    echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
    echo "<p>" . htmlspecialchars($row['description']) . "</p>";
    echo "<p>Price: $" . htmlspecialchars($row['price']) . "</p>";
    echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt=''>";
    echo "</div>";
  }
} else {
  echo "<p>No products found.</p>";
}
$conn->close();
?>
</body>
</html>
